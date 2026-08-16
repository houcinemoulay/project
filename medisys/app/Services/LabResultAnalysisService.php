<?php

namespace App\Services;

use App\Models\LabResult;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

class LabResultAnalysisService
{
    public function __construct(private ?GeminiClient $gemini = null)
    {
        $this->gemini = $gemini ?? new GeminiClient();
    }

    /**
     * Analyze lab results and provide AI-powered recommendations
     */
    public function analyzeLabResults(Patient $patient): array
    {
        try {
            // Collect lab results data
            $labResults = $this->getLabResultsData($patient);
            
            // Collect medical records data
            $medicalRecords = $this->getMedicalRecordsData($patient);
            
            // Generate AI analysis
            $analysis = $this->generateAIAnalysis($patient, $labResults, $medicalRecords);
            
            return [
                'success' => true,
                'analysis' => $analysis,
                'lab_results_count' => count($labResults),
                'recommendations' => $this->generateRecommendations($analysis, $patient)
            ];
            
        } catch (\Exception $e) {
            Log::error('LabResultAnalysisService error', [
                'patient_id' => $patient->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to analyze lab results: ' . $e->getMessage(),
                'recommendations' => []
            ];
        }
    }

    /**
     * Extract structured data from lab results
     */
    private function getLabResultsData(Patient $patient): array
    {
        $labResults = LabResult::where('patient_id', $patient->id)
            ->with('laboratory')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        
        foreach ($labResults as $result) {
            $data[] = [
                'id' => $result->id,
                'title' => $result->title,
                'note' => $result->note,
                'file_type' => $result->file_type,
                'laboratory' => $result->laboratory ? $result->laboratory->name : 'Unknown',
                'date' => $result->created_at->format('Y-m-d'),
                'extracted_data' => $this->extractLabDataFromNote($result->note)
            ];
        }

        return $data;
    }

    /**
     * Extract lab test values from notes/text
     */
    private function extractLabDataFromNote(?string $note): array
    {
        if (!$note) return [];

        $extracted = [];
        
        // Common lab test patterns
        $patterns = [
            'hemoglobin' => '/hemoglobin|hb|hgb|(\d+\.?\d*)\s*g\/dl/i',
            'white_blood_cells' => '/wbc|white\s+blood|(\d+\.?\d*)\s*\/\s*mm3|(\d+\.?\d*)\s*\/\s*µl/i',
            'red_blood_cells' => '/rbc|red\s+blood|(\d+\.?\d*)\s*m\/ul|(\d+\.?\d*)\s*\/\s*mm3/i',
            'platelets' => '/platelet|plt|(\d+\.?\d*)\s*\/\s*mm3|(\d+\.?\d*)\s*\/\s*µl/i',
            'glucose' => '/glucose|blood\s+sugar|(\d+\.?\d*)\s*mg\/dl|(\d+\.?\d*)\s*mmol\/l/i',
            'cholesterol' => '/cholesterol|(\d+\.?\d*)\s*mg\/dl/i',
            'triglycerides' => '/triglycerides|tg|(\d+\.?\d*)\s*mg\/dl/i',
            'hdl' => '/hdl|good\s+cholesterol|(\d+\.?\d*)\s*mg\/dl/i',
            'ldl' => '/ldl|bad\s+cholesterol|(\d+\.?\d*)\s*mg\/dl/i',
            'creatinine' => '/creatinine|(\d+\.?\d*)\s*mg\/dl/i',
            'urea' => '/urea|bun|(\d+\.?\d*)\s*mg\/dl/i',
            'alt' => '/alt|sgpt|alanine|(\d+\.?\d*)\s*\/\s*l/i',
            'ast' => '/ast|sgot|aspartate|(\d+\.?\d*)\s*\/\s*l/i',
            'bilirubin' => '/bilirubin|(\d+\.?\d*)\s*mg\/dl/i',
            'sodium' => '/sodium|na\+|(\d+\.?\d*)\s*mmol\/l/i',
            'potassium' => '/potassium|k\+|(\d+\.?\d*)\s*mmol\/l/i',
            'calcium' => '/calcium|ca\+\+|(\d+\.?\d*)\s*mg\/dl/i',
            'phosphorus' => '/phosphorus|phos|(\d+\.?\d*)\s*mg\/dl/i',
            'uric_acid' => '/uric\s+acid|(\d+\.?\d*)\s*mg\/dl/i',
            'protein' => '/protein|(\d+\.?\d*)\s*g\/dl/i',
            'albumin' => '/albumin|(\d+\.?\d*)\s*g\/dl/i',
        ];

        foreach ($patterns as $test => $pattern) {
            if (preg_match($pattern, $note, $matches)) {
                // Extract numeric value
                foreach ($matches as $match) {
                    if (is_numeric($match)) {
                        $extracted[$test] = floatval($match);
                        break;
                    }
                }
            }
        }

        return $extracted;
    }

    /**
     * Get medical records data for context
     */
    private function getMedicalRecordsData(Patient $patient): array
    {
        $records = MedicalRecord::where('patient_id', $patient->id)
            ->orderBy('visit_date', 'desc')
            ->limit(10)
            ->get();

        $data = [];
        
        foreach ($records as $record) {
            $data[] = [
                'date' => $record->visit_date->format('Y-m-d'),
                'diagnosis' => $record->diagnosis,
                'notes' => $record->notes,
                'vitals' => [
                    'temperature' => $record->temperature,
                    'blood_pressure' => $record->blood_pressure,
                    'heart_rate' => $record->heart_rate,
                    'weight' => $record->weight,
                    'height' => $record->height,
                ],
                'visit_type' => $record->visit_type
            ];
        }

        return $data;
    }

    /**
     * Generate AI analysis using Gemini API
     */
    private function generateAIAnalysis(Patient $patient, array $labResults, array $medicalRecords): array
    {
        // Prepare data for AI
        $patientInfo = [
            'age' => $patient->age ?? 'Unknown',
            'gender' => $patient->gender ?? 'Unknown',
            'blood_type' => $patient->blood_type ?? 'Unknown'
        ];

        $prompt = $this->buildAnalysisPrompt($patientInfo, $labResults, $medicalRecords);

        $result = $this->gemini->generateText($prompt, ['model' => 'gemini-1.5-flash-latest']);

        if ($result['success']) {
            return [
                'success' => true,
                'analysis' => $result['text']
            ];
        }

        return [
            'success' => false,
            'error' => GeminiFailure::describe('LabResultAnalysisService', $result)
        ];
    }

    /**
     * Build comprehensive analysis prompt
     */
    private function buildAnalysisPrompt(array $patientInfo, array $labResults, array $medicalRecords): string
    {
        $prompt = "You are a medical AI assistant specializing in laboratory result analysis. ";

        // Patient information
        $prompt .= "Patient Information:\n";
        $prompt .= "- Age: {$patientInfo['age']}\n";
        $prompt .= "- Gender: {$patientInfo['gender']}\n";
        $prompt .= "- Blood Type: {$patientInfo['blood_type']}\n\n";

        // Lab results
        if (!empty($labResults)) {
            $prompt .= "Recent Laboratory Results:\n";
            foreach ($labResults as $result) {
                $prompt .= "- Test: {$result['title']} ({$result['date']})\n";
                $prompt .= "- Laboratory: {$result['laboratory']}\n";
                if (!empty($result['extracted_data'])) {
                    $prompt .= "- Values: " . json_encode($result['extracted_data']) . "\n";
                }
                if ($result['note']) {
                    $prompt .= "- Notes: {$result['note']}\n";
                }
                $prompt .= "\n";
            }
        }

        // Medical history
        if (!empty($medicalRecords)) {
            $prompt .= "Recent Medical History:\n";
            foreach ($medicalRecords as $record) {
                $prompt .= "- Date: {$record['date']} ({$record['visit_type']})\n";
                if ($record['diagnosis']) {
                    $prompt .= "- Diagnosis: {$record['diagnosis']}\n";
                }
                if ($record['notes']) {
                    $prompt .= "- Notes: {$record['notes']}\n";
                }
                $vitals = array_filter($record['vitals']);
                if (!empty($vitals)) {
                    $prompt .= "- Vitals: " . json_encode($vitals) . "\n";
                }
                $prompt .= "\n";
            }
        }

        $prompt .= "\nPlease provide:\n";
        $prompt .= "1. Analysis of the laboratory results\n";
        $prompt .= "2. Identification of any abnormal values\n";
        $prompt .= "3. Potential medical concerns based on the results\n";
        $prompt .= "4. Recommendations for follow-up tests or consultations\n";
        $prompt .= "5. Lifestyle or dietary recommendations if applicable\n";
        $prompt .= "6. Any urgent medical concerns that need immediate attention\n\n";

        $prompt .= "Please provide the response in a structured format with clear sections and actionable recommendations. ";

        return $prompt;
    }

    /**
     * Generate structured recommendations based on AI analysis
     */
    private function generateRecommendations(array $analysis, Patient $patient): array
    {
        if (!$analysis['success']) {
            return [];
        }

        $recommendations = [];
        $aiText = $analysis['analysis'];

        // Parse AI analysis for actionable recommendations
        $patterns = [
            'urgent' => '/urgent|immediate|emergency|critical|severe/i',
            'follow_up' => '/follow[-\s]?up|consultation|specialist|referral/i',
            'lifestyle' => '/diet|exercise|lifestyle|avoid|reduce|increase/i',
            'monitoring' => '/monitor|check|test|repeat|measure/i',
            'medication' => '/medication|medicine|drug|prescribe/i'
        ];

        $priority = 'low';
        $reasoning = '';

        // Determine priority based on content
        if (preg_match($patterns['urgent'], $aiText)) {
            $priority = 'urgent';
            $reasoning = 'Urgent medical concerns identified in lab results';
        } elseif (preg_match($patterns['follow_up'], $aiText)) {
            $priority = 'high';
            $reasoning = 'Follow-up consultations recommended';
        } elseif (preg_match($patterns['monitoring'], $aiText)) {
            $priority = 'medium';
            $reasoning = 'Regular monitoring recommended';
        }

        // Generate doctor recommendations
        $recommendedDoctors = [];
        if (preg_match($patterns['urgent'], $aiText) || preg_match($patterns['follow_up'], $aiText)) {
            $recommendedDoctors = [
                'General Practitioner',
                'Laboratory Medicine Specialist'
            ];
        }

        // Generate test recommendations
        $recommendedTests = [];
        if (preg_match('/cholesterol|lipid/i', $aiText)) {
            $recommendedTests[] = 'Lipid Panel';
        }
        if (preg_match('/glucose|blood\s+sugar/i', $aiText)) {
            $recommendedTests[] = 'HbA1c Test';
        }
        if (preg_match('/liver|hepatic/i', $aiText)) {
            $recommendedTests[] = 'Liver Function Tests';
        }
        if (preg_match('/kidney|renal/i', $aiText)) {
            $recommendedTests[] = 'Kidney Function Tests';
        }

        $recommendations[] = [
            'patient_id' => $patient->id,
            'medical_record_id' => null,
            'recommended_doctors' => json_encode($recommendedDoctors),
            'recommended_tests' => json_encode($recommendedTests),
            'symptoms_triggers' => json_encode(['abnormal_lab_results']),
            'reasoning' => $reasoning . ': ' . substr($aiText, 0, 200) . '...',
            'priority' => $priority,
            'is_active' => true,
            'expires_at' => now()->addDays(30),
            'source' => 'lab_analysis'
        ];

        return $recommendations;
    }
}
