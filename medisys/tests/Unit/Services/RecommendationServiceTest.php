<?php

namespace Tests\Unit\Services;

use App\Models\Patient;
use App\Models\Recommendation;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationService $service;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RecommendationService;
        $this->patient = Patient::create([
            'name' => 'Jane Doe',
            'age' => 44,
            'gender' => 'female',
        ]);
    }

    private function createRecommendation(array $attributes = []): Recommendation
    {
        return Recommendation::create(array_merge([
            'patient_id' => $this->patient->id,
            'recommended_doctors' => ['Cardiologist'],
            'recommended_tests' => ['ECG'],
            'symptoms_triggers' => ['chest pain'],
            'reasoning' => 'Chest pain reported',
            'priority' => 'high',
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ], $attributes));
    }

    public function test_returns_failure_when_patient_has_no_medical_records(): void
    {
        $result = $this->service->generateRecommendations($this->patient);

        $this->assertFalse($result['success']);
        $this->assertSame('No medical records found for analysis', $result['message']);
        $this->assertSame([], $result['recommendations']);
        $this->assertSame(0, Recommendation::count());
    }

    public function test_active_recommendations_are_returned_newest_first(): void
    {
        $older = $this->createRecommendation();
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer = $this->createRecommendation();

        $recommendations = $this->service->getActiveRecommendations($this->patient->id);

        $this->assertSame([$newer->id, $older->id], $recommendations->pluck('id')->all());
    }

    public function test_inactive_and_expired_recommendations_are_excluded(): void
    {
        $active = $this->createRecommendation();
        $this->createRecommendation(['is_active' => false]);
        $this->createRecommendation(['expires_at' => now()->subDay()]);

        $recommendations = $this->service->getActiveRecommendations($this->patient->id);

        $this->assertSame([$active->id], $recommendations->pluck('id')->all());
    }

    public function test_recommendations_of_other_patients_are_excluded(): void
    {
        $this->createRecommendation();
        $otherPatient = Patient::create(['name' => 'John Doe', 'age' => 30, 'gender' => 'male']);

        $this->assertCount(0, $this->service->getActiveRecommendations($otherPatient->id));
    }

    public function test_recommendation_payload_is_cast_back_to_arrays(): void
    {
        $this->createRecommendation();

        $recommendation = $this->service->getActiveRecommendations($this->patient->id)->first();

        $this->assertSame(['Cardiologist'], $recommendation->recommended_doctors);
        $this->assertSame(['ECG'], $recommendation->recommended_tests);
        $this->assertSame(['chest pain'], $recommendation->symptoms_triggers);
        $this->assertTrue($recommendation->is_active);
    }
}
