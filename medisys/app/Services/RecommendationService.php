<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    /**
     * Generate doctor and test recommendations based on patient data
     *
     * @param Patient $patient
     * @return array
     */
    public function generateRecommendations(Patient $patient): array
    {
        try {
            // Get patient's latest medical records with symptoms and test results
            $medicalRecords = $patient->medicalRecords()
                ->with(['doctor', 'labResults', 'prescriptions'])
                ->orderBy('visit_date', 'desc')
                ->take(10) // Last 10 records for analysis
                ->get();

            if ($medicalRecords->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No medical records found for analysis',
                    'recommendations' => []
                ];
            }

            // Analyze symptoms and test results
            $symptoms = $this->extractSymptoms($medicalRecords);
            $testResults = $this->extractTestResults($medicalRecords);
            
            // Generate recommendations based on analysis
            $recommendations = $this->applyRecommendationRules($symptoms, $testResults, $patient);
            
            // Analyze lab results with AI
            $labAnalysisService = new LabResultAnalysisService();
            $labAnalysis = $labAnalysisService->analyzeLabResults($patient);
            
            if ($labAnalysis['success']) {
                // Merge AI lab recommendations with existing recommendations
                $recommendations = array_merge($recommendations, $labAnalysis['recommendations']);
            } else {
                Log::warning('Lab result analysis unavailable while generating recommendations', [
                    'patient_id' => $patient->id,
                    'error' => $labAnalysis['error'] ?? $labAnalysis['message'] ?? 'unknown',
                ]);
            }

            // Store recommendations in database
            $stored = $this->storeRecommendations($patient->id, $recommendations);

            return [
                'success' => true,
                'message' => $stored
                    ? 'Recommendations generated successfully'
                    : 'Recommendations generated but could not be saved for later reference',
                'stored' => $stored,
                'data' => $recommendations,
                'lab_analysis' => $labAnalysis
            ];

        } catch (\Throwable $e) {
            Log::error('RecommendationService error', [
                'patient_id' => $patient->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate recommendations',
                'recommendations' => []
            ];
        }
    }

    /**
     * Extract symptoms from medical records
     *
     * @param \Illuminate\Support\Collection $medicalRecords
     * @return array
     */
    private function extractSymptoms($medicalRecords): array
    {
        $symptoms = [];
        
        foreach ($medicalRecords as $record) {
            if (isset($record->symptoms) && is_array($record->symptoms)) {
                $symptoms = array_merge($symptoms, $record->symptoms);
            }
            
            // Extract symptoms from doctor notes
            if (isset($record->notes) && $record->notes) {
                $extractedSymptoms = $this->parseSymptomsFromText($record->notes);
                $symptoms = array_merge($symptoms, $extractedSymptoms);
            }
        }

        return array_unique($symptoms);
    }

    /**
     * Extract test results from medical records
     *
     * @param \Illuminate\Support\Collection $medicalRecords
     * @return array
     */
    private function extractTestResults($medicalRecords): array
    {
        $testResults = [];
        
        foreach ($medicalRecords as $record) {
            if (isset($record->labResults) && $record->labResults->isNotEmpty()) {
                foreach ($record->labResults as $result) {
                    if (isset($result->test_name) && isset($result->result_value)) {
                        $testResults[] = [
                            'test_name' => $result->test_name,
                            'value' => $result->result_value,
                            'unit' => $result->unit ?? null,
                            'normal_range' => $result->normal_range ?? null,
                            'date' => $result->test_date ?? $record->visit_date
                        ];
                    }
                }
            }
        }

        return $testResults;
    }

    /**
     * Parse symptoms from text using keyword matching
     *
     * @param string $text
     * @return array
     */
    private function parseSymptomsFromText(string $text): array
    {
        $symptomKeywords = [
            'chest pain' => ['chest', 'heart', 'cardiac', 'angina'],
            'high glucose' => ['glucose', 'sugar', 'diabetes', 'hyperglycemia'],
            'fatigue' => ['tired', 'exhausted', 'weak', 'lethargic'],
            'headache' => ['headache', 'migraine', 'head'],
            'fever' => ['fever', 'temperature', 'hot'],
            'cough' => ['cough', 'breathing', 'respiratory'],
            'abdominal pain' => ['stomach', 'abdomen', 'belly'],
            'high blood pressure' => ['bp', 'blood pressure', 'hypertension'],
        ];

        $foundSymptoms = [];
        $text = strtolower($text);

        foreach ($symptomKeywords as $symptom => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $foundSymptoms[] = $symptom;
                    break 2; // Found this symptom, move to next
                }
            }
        }

        return array_unique($foundSymptoms);
    }

    /**
     * Apply recommendation rules based on symptoms and test results
     *
     * @param array $symptoms
     * @param array $testResults
     * @param Patient $patient
     * @return array
     */
    private function applyRecommendationRules(array $symptoms, array $testResults, Patient $patient): array
    {
        $recommendations = [];

        // Rule 1: Chest pain → Cardiologist
        if (in_array('chest pain', $symptoms)) {
            $recommendations[] = [
                'type' => 'doctor',
                'specialty' => 'Cardiologist',
                'reasoning' => 'Chest pain symptoms detected. Recommend cardiac evaluation.',
                'priority' => 'high',
                'recommended_doctors' => ['Cardiologist', 'Heart Specialist'],
                'recommended_tests' => ['ECG', 'Echocardiogram', 'Cardiac Enzymes'],
                'symptoms_triggers' => ['chest pain']
            ];
        }

        // Rule 2: High glucose → Diabetes test
        if (in_array('high glucose', $symptoms) || $this->hasHighGlucose($testResults)) {
            $recommendations[] = [
                'type' => 'test',
                'specialty' => 'Diabetes Test',
                'reasoning' => 'High glucose levels detected. Recommend diabetes screening.',
                'priority' => 'medium',
                'recommended_doctors' => ['Endocrinologist', 'Diabetologist'],
                'recommended_tests' => ['Fasting Blood Glucose', 'HbA1c', 'Oral Glucose Tolerance'],
                'symptoms_triggers' => ['high glucose']
            ];
        }

        // Rule 3: Fatigue → Blood test
        if (in_array('fatigue', $symptoms)) {
            $recommendations[] = [
                'type' => 'test',
                'specialty' => 'Blood Test',
                'reasoning' => 'Fatigue symptoms detected. Recommend comprehensive blood analysis.',
                'priority' => 'medium',
                'recommended_doctors' => ['General Practitioner', 'Hematologist'],
                'recommended_tests' => ['Complete Blood Count', 'Iron Studies', 'Vitamin D', 'Thyroid Panel'],
                'symptoms_triggers' => ['fatigue']
            ];
        }

        // Rule 4: Multiple symptoms → General practitioner
        if (count($symptoms) >= 2) {
            $recommendations[] = [
                'type' => 'doctor',
                'specialty' => 'General Practitioner',
                'reasoning' => 'Multiple symptoms detected. Recommend general medical evaluation.',
                'priority' => 'medium',
                'recommended_doctors' => ['General Practitioner', 'Family Doctor'],
                'recommended_tests' => ['Complete Blood Count', 'Metabolic Panel', 'Urinalysis'],
                'symptoms_triggers' => $symptoms
            ];
        }

        // Sort by priority
        usort($recommendations, function ($a, $b) {
            $priorityOrder = ['urgent' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            return $priorityOrder[$b['priority']] <=> $priorityOrder[$a['priority']];
        });

        return $recommendations;
    }

    /**
     * Check if test results show high glucose
     *
     * @param array $testResults
     * @return bool
     */
    private function hasHighGlucose(array $testResults): bool
    {
        foreach ($testResults as $result) {
            if (stripos(strtolower($result['test_name']), 'glucose') !== false) {
                $value = (float) $result['value'];
                if ($value > 126) { // Standard fasting glucose > 126 mg/dL
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Store recommendations in database
     *
     * @param int $patientId
     * @param array $recommendations
     * @return bool Whether the recommendations were persisted
     */
    private function storeRecommendations(int $patientId, array $recommendations): bool
    {
        try {
            foreach ($recommendations as $recommendation) {
                \App\Models\Recommendation::create([
                    'patient_id' => $patientId,
                    'medical_record_id' => null, // Can be linked to specific record if needed
                    'recommended_doctors' => json_encode($recommendation['recommended_doctors']),
                    'recommended_tests' => json_encode($recommendation['recommended_tests']),
                    'symptoms_triggers' => json_encode($recommendation['symptoms_triggers']),
                    'reasoning' => $recommendation['reasoning'],
                    'priority' => $recommendation['priority'],
                    'is_active' => true,
                    'expires_at' => now()->addDays(30) // Expire after 30 days
                ]);
            }

            Log::info('Recommendations stored', [
                'patient_id' => $patientId,
                'count' => count($recommendations)
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to store recommendations', [
                'patient_id' => $patientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Get active recommendations for a patient
     *
     * @param int $patientId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveRecommendations(int $patientId): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Recommendation::where('patient_id', $patientId)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
