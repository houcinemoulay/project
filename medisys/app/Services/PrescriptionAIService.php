<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrescriptionAIService
{
    /**
     * Generate explanation for prescription using AI
     *
     * @param string $medications
     * @param string $instructions
     * @param string $language
     * @return array
     */
    public function generateExplanation(string $medications, string $instructions, string $language = 'ar'): array
    {
        try {
            $apiKey = config('services.gemini.key');
            
            if (!$apiKey) {
                Log::error('PrescriptionAIService: API key not configured');
                return [
                    'success' => false,
                    'error' => 'AI service not configured'
                ];
            }

            // Build the prescription content
            $prescriptionText = "Medications: {$medications}\nInstructions: {$instructions}";
            
            // Create the prompt based on language
            $prompt = $this->buildPrompt($prescriptionText, $language);

            // Call Gemini API
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey,
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $prompt]
                            ]
                        ]
                    ]
                ]
            );

            if ($response->failed()) {
                Log::error('PrescriptionAIService: API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'error' => 'AI service temporarily unavailable'
                ];
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error('PrescriptionAIService: API returned error', $data['error']);
                return [
                    'success' => false,
                    'error' => 'AI service error'
                ];
            }

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('PrescriptionAIService: Invalid API response format', $data);
                return [
                    'success' => false,
                    'error' => 'Invalid AI response'
                ];
            }

            $explanation = $data['candidates'][0]['content']['parts'][0]['text'];

            return [
                'success' => true,
                'explanation' => $explanation
            ];

        } catch (\Throwable $e) {
            Log::error('PrescriptionAIService: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => 'AI service error'
            ];
        }
    }

    /**
     * Build the AI prompt based on language
     *
     * @param string $prescriptionText
     * @param string $language
     * @return string
     */
    private function buildPrompt(string $prescriptionText, string $language): string
    {
        if ($language === 'ar') {
            return "اشرح هذا الوصفة الطبية بلغة عربية بسيطة للمريض. يجب أن يتضمن الشرح كيفية تناول الأدوية والاحتياطات اللازمة. لا تعط تشخيصًا طبيًا.

الوصفة الطبية:
{$prescriptionText}

قدم شرحًا بسيطًا وواضحًا باللغة العربية يمكن للمريض فهمه بسهولة.";
        }

        return "Explain this prescription in simple terms for a patient. Include how to take medications and precautions. Do not give medical diagnosis.

Prescription:
{$prescriptionText}

Provide a simple, clear explanation that a patient can easily understand.";
    }

    /**
     * Detect patient language from their profile or default to Arabic
     *
     * @param mixed $patient
     * @return string
     */
    public function detectPatientLanguage($patient = null): string
    {
        // You can extend this to check patient preferences
        // For now, default to Arabic as requested
        return 'ar';
    }
}
