<?php

namespace Tests\Unit\Services;

use App\Services\PrescriptionAIService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PrescriptionAIServiceTest extends TestCase
{
    private PrescriptionAIService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PrescriptionAIService;
        $this->setApiKey('test-key');
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

    private function fakeGemini(array $body, int $status = 200): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($body, $status),
        ]);
    }

    private function candidatesResponse(string $text): array
    {
        return [
            'candidates' => [
                ['content' => ['parts' => [['text' => $text]]]],
            ],
        ];
    }

    public function test_returns_explanation_on_success(): void
    {
        $this->fakeGemini($this->candidatesResponse('Take one tablet daily.'));

        $result = $this->service->generateExplanation('Paracetamol 500mg', 'Twice a day', 'en');

        $this->assertTrue($result['success']);
        $this->assertSame('Take one tablet daily.', $result['explanation']);
    }

    public function test_sends_english_prompt_containing_prescription_details(): void
    {
        $this->fakeGemini($this->candidatesResponse('ok'));

        $this->service->generateExplanation('Ibuprofen', 'After meals', 'en');

        Http::assertSent(function (Request $request) {
            $prompt = $request->data()['contents'][0]['parts'][0]['text'];

            return str_contains($request->url(), 'key=test-key')
                && str_contains($prompt, 'Explain this prescription in simple terms')
                && str_contains($prompt, 'Medications: Ibuprofen')
                && str_contains($prompt, 'Instructions: After meals');
        });
    }

    public function test_defaults_to_arabic_prompt(): void
    {
        $this->fakeGemini($this->candidatesResponse('ok'));

        $this->service->generateExplanation('Ibuprofen', 'After meals');

        Http::assertSent(function (Request $request) {
            $prompt = $request->data()['contents'][0]['parts'][0]['text'];

            return str_contains($prompt, 'اشرح هذا الوصفة الطبية');
        });
    }

    public function test_fails_when_api_key_is_missing(): void
    {
        $this->setApiKey(null);
        Http::fake();

        $result = $this->service->generateExplanation('Paracetamol', 'Daily');

        $this->assertFalse($result['success']);
        $this->assertSame('AI service not configured', $result['error']);
        Http::assertNothingSent();
    }

    public function test_fails_when_api_call_fails(): void
    {
        $this->fakeGemini(['message' => 'boom'], 500);

        $result = $this->service->generateExplanation('Paracetamol', 'Daily');

        $this->assertFalse($result['success']);
        $this->assertSame('AI service temporarily unavailable', $result['error']);
    }

    public function test_fails_when_api_returns_error_payload(): void
    {
        $this->fakeGemini(['error' => ['message' => 'quota exceeded']]);

        $result = $this->service->generateExplanation('Paracetamol', 'Daily');

        $this->assertFalse($result['success']);
        $this->assertSame('AI service error', $result['error']);
    }

    public function test_fails_when_response_shape_is_unexpected(): void
    {
        $this->fakeGemini(['candidates' => []]);

        $result = $this->service->generateExplanation('Paracetamol', 'Daily');

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid AI response', $result['error']);
    }

    public function test_fails_gracefully_when_request_throws(): void
    {
        Http::fake(function () {
            throw new \RuntimeException('connection reset');
        });

        $result = $this->service->generateExplanation('Paracetamol', 'Daily');

        $this->assertFalse($result['success']);
        $this->assertSame('AI service error', $result['error']);
    }

    public function test_detect_patient_language_defaults_to_arabic(): void
    {
        $this->assertSame('ar', $this->service->detectPatientLanguage());
        $this->assertSame('ar', $this->service->detectPatientLanguage((object) ['language' => 'en']));
    }
}
