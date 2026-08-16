<?php

namespace Tests\Unit\Services;

use App\Models\Doctor;
use App\Models\Laboratory;
use App\Models\LabResult;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Services\LabResultAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LabResultAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    private LabResultAnalysisService $service;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LabResultAnalysisService;
        $this->setApiKey('test-key');

        $this->patient = Patient::create([
            'name' => 'Jane Doe',
            'age' => 55,
            'gender' => 'female',
            'blood_type' => 'O+',
        ]);
    }

    protected function tearDown(): void
    {
        $this->setApiKey(null);

        parent::tearDown();
    }

    private function setApiKey(?string $key): void
    {
        foreach (['GEMINI_API_KEY', 'GOOGLE_API_KEY'] as $name) {
            if ($key === null) {
                unset($_ENV[$name], $_SERVER[$name]);
                putenv($name);

                continue;
            }

            $_ENV[$name] = $key;
            $_SERVER[$name] = $key;
            putenv("{$name}={$key}");
        }
    }

    private function fakeAnalysis(string $text): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => $text]]]],
                ],
            ]),
        ]);
    }

    private function addLabResult(array $attributes = []): LabResult
    {
        $laboratory = Laboratory::create([
            'user_id' => User::factory()->create(['role' => 'lab'])->id,
            'name' => 'Central Lab',
            'address' => '1 Main Street',
        ]);

        return LabResult::create(array_merge([
            'patient_id' => $this->patient->id,
            'laboratory_id' => $laboratory->id,
            'file_path' => 'lab/result.pdf',
            'file_type' => 'pdf',
            'title' => 'Blood Panel',
            'note' => 'Fasting sample, 150 mg/dl',
        ], $attributes));
    }

    private function addMedicalRecord(array $attributes = []): MedicalRecord
    {
        $doctor = Doctor::create([
            'user_id' => User::factory()->create(['role' => 'doctor'])->id,
            'specialty' => 'Cardiology',
        ]);

        return MedicalRecord::create(array_merge([
            'patient_id' => $this->patient->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Hypertension',
            'notes' => 'Patient reports fatigue',
            'visit_type' => 'consultation',
            'temperature' => 37.2,
            'blood_pressure' => '150/95',
            'heart_rate' => 88,
            'visit_date' => '2026-01-15',
        ], $attributes));
    }

    public function test_returns_analysis_and_counts_lab_results(): void
    {
        $this->addLabResult();
        $this->addLabResult(['title' => 'Lipid Panel', 'note' => null]);
        $this->fakeAnalysis('All values within range. Continue to monitor annually.');

        $result = $this->service->analyzeLabResults($this->patient);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['lab_results_count']);
        $this->assertTrue($result['analysis']['success']);
        $this->assertSame('All values within range. Continue to monitor annually.', $result['analysis']['analysis']);
    }

    public function test_prompt_includes_patient_lab_and_history_context(): void
    {
        $this->addLabResult();
        $this->addMedicalRecord();
        $this->fakeAnalysis('Nothing notable.');

        $this->service->analyzeLabResults($this->patient);

        Http::assertSent(function (Request $request) {
            $prompt = $request->data()['contents'][0]['parts'][0]['text'];

            return str_contains($prompt, 'Age: 55')
                && str_contains($prompt, 'Gender: female')
                && str_contains($prompt, 'Blood Type: O+')
                && str_contains($prompt, 'Test: Blood Panel')
                && str_contains($prompt, 'Laboratory: Central Lab')
                && str_contains($prompt, '"glucose":150')
                && str_contains($prompt, 'Diagnosis: Hypertension')
                && str_contains($prompt, '"blood_pressure":"150\/95"');
        });
    }

    public function test_unknown_laboratory_when_relation_is_missing(): void
    {
        $this->addLabResult(['laboratory_id' => null]);
        $this->fakeAnalysis('Nothing notable.');

        $this->service->analyzeLabResults($this->patient);

        Http::assertSent(function (Request $request) {
            return str_contains($request->data()['contents'][0]['parts'][0]['text'], 'Laboratory: Unknown');
        });
    }

    public function test_urgent_analysis_produces_urgent_recommendation_with_doctors(): void
    {
        $this->addLabResult();
        $this->fakeAnalysis('Critical potassium level, seek immediate care.');

        $recommendation = $this->service->analyzeLabResults($this->patient)['recommendations'][0];

        $this->assertSame('urgent', $recommendation['priority']);
        $this->assertSame($this->patient->id, $recommendation['patient_id']);
        $this->assertSame('lab_analysis', $recommendation['source']);
        $this->assertSame(
            ['General Practitioner', 'Laboratory Medicine Specialist'],
            json_decode($recommendation['recommended_doctors'], true)
        );
        $this->assertSame(['abnormal_lab_results'], json_decode($recommendation['symptoms_triggers'], true));
        $this->assertStringStartsWith('Urgent medical concerns identified in lab results:', $recommendation['reasoning']);
    }

    public function test_follow_up_analysis_produces_high_priority(): void
    {
        $this->addLabResult();
        $this->fakeAnalysis('Please schedule a follow-up with a specialist.');

        $recommendation = $this->service->analyzeLabResults($this->patient)['recommendations'][0];

        $this->assertSame('high', $recommendation['priority']);
        $this->assertSame('Follow-up consultations recommended', explode(':', $recommendation['reasoning'])[0]);
    }

    public function test_monitoring_analysis_produces_medium_priority_without_doctors(): void
    {
        $this->addLabResult();
        $this->fakeAnalysis('Repeat the test in three months to monitor the trend.');

        $recommendation = $this->service->analyzeLabResults($this->patient)['recommendations'][0];

        $this->assertSame('medium', $recommendation['priority']);
        $this->assertSame([], json_decode($recommendation['recommended_doctors'], true));
    }

    public function test_neutral_analysis_produces_low_priority(): void
    {
        $this->addLabResult();
        $this->fakeAnalysis('Values are normal.');

        $recommendation = $this->service->analyzeLabResults($this->patient)['recommendations'][0];

        $this->assertSame('low', $recommendation['priority']);
    }

    public function test_recommended_tests_are_derived_from_analysis_text(): void
    {
        $this->addLabResult();
        $this->fakeAnalysis('Elevated cholesterol and glucose with signs of liver and kidney strain.');

        $recommendation = $this->service->analyzeLabResults($this->patient)['recommendations'][0];

        $this->assertSame(
            ['Lipid Panel', 'HbA1c Test', 'Liver Function Tests', 'Kidney Function Tests'],
            json_decode($recommendation['recommended_tests'], true)
        );
    }

    public function test_no_recommendations_when_api_key_is_missing(): void
    {
        $this->setApiKey(null);
        Http::fake();
        $this->addLabResult();

        $result = $this->service->analyzeLabResults($this->patient);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['analysis']['success']);
        $this->assertSame('AI service not configured', $result['analysis']['error']);
        $this->assertSame([], $result['recommendations']);
        Http::assertNothingSent();
    }

    public function test_no_recommendations_when_api_call_fails(): void
    {
        $this->addLabResult();
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['message' => 'boom'], 503),
        ]);

        $result = $this->service->analyzeLabResults($this->patient);

        $this->assertSame('AI service temporarily unavailable', $result['analysis']['error']);
        $this->assertSame([], $result['recommendations']);
    }

    public function test_no_recommendations_when_api_returns_error_payload(): void
    {
        $this->addLabResult();
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']]),
        ]);

        $this->assertSame('AI service error', $this->service->analyzeLabResults($this->patient)['analysis']['error']);
    }

    public function test_no_recommendations_when_response_shape_is_unexpected(): void
    {
        $this->addLabResult();
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['candidates' => []]),
        ]);

        $this->assertSame('Invalid AI response', $this->service->analyzeLabResults($this->patient)['analysis']['error']);
    }

    public function test_returns_failure_when_request_throws(): void
    {
        $this->addLabResult();
        Http::fake(function () {
            throw new \RuntimeException('connection reset');
        });

        $result = $this->service->analyzeLabResults($this->patient);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to analyze lab results', $result['message']);
        $this->assertSame([], $result['recommendations']);
    }
}
