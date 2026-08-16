<?php

namespace App\Services;

class PrescriptionAIService
{
    public function __construct(private ?GeminiClient $gemini = null)
    {
        $this->gemini = $gemini ?? new GeminiClient();
    }

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
        $prescriptionText = "Medications: {$medications}\nInstructions: {$instructions}";

        $result = $this->gemini->generateText($this->buildPrompt($prescriptionText, $language));

        if ($result['success']) {
            return [
                'success' => true,
                'explanation' => $result['text']
            ];
        }

        return [
            'success' => false,
            'error' => GeminiFailure::describe('PrescriptionAIService', $result)
        ];
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
