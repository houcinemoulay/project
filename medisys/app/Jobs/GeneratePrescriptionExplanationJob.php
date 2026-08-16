<?php

namespace App\Jobs;

use App\Models\Ordonnance;
use App\Services\PrescriptionAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePrescriptionExplanationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The prescription instance.
     *
     * @var \App\Models\Ordonnance
     */
    protected $ordonnance;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Ordonnance $ordonnance
     */
    public function __construct(Ordonnance $ordonnance)
    {
        $this->ordonnance = $ordonnance;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\PrescriptionAIService $prescriptionAIService
     */
    public function handle(PrescriptionAIService $prescriptionAIService): void
    {
        try {
            // Check if explanation is already generated
            if ($this->ordonnance->explanation_generated) {
                Log::info('Prescription explanation already generated', [
                    'ordonnance_id' => $this->ordonnance->id
                ]);
                return;
            }

            // Detect patient language
            $language = $prescriptionAIService->detectPatientLanguage($this->ordonnance->patient);

            // Generate explanation
            $result = $prescriptionAIService->generateExplanation(
                $this->ordonnance->medications,
                $this->ordonnance->instructions,
                $language
            );

            if ($result['success']) {
                // Update the prescription with the generated explanation
                $this->ordonnance->update([
                    'explanation' => $result['explanation'],
                    'explanation_generated' => true
                ]);

                Log::info('Prescription explanation generated successfully', [
                    'ordonnance_id' => $this->ordonnance->id,
                    'language' => $language
                ]);
            } else {
                Log::error('Failed to generate prescription explanation', [
                    'ordonnance_id' => $this->ordonnance->id,
                    'error' => $result['error']
                ]);

                // Mark as failed but don't update the explanation
                $this->ordonnance->update([
                    'explanation_generated' => false
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Exception in GeneratePrescriptionExplanationJob', [
                'ordonnance_id' => $this->ordonnance->id,
                'attempt' => $this->attempts(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark as failed
            $this->ordonnance->update([
                'explanation_generated' => false
            ]);

            // Let the queue worker handle retries and, once attempts are
            // exhausted, record the job in the failed_jobs table.
            throw $e;
        }
    }

    /**
     * Handle a job failure once all retries are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GeneratePrescriptionExplanationJob failed permanently', [
            'ordonnance_id' => $this->ordonnance->id,
            'message' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['prescription', 'ai-explanation', 'ordonnance:' . $this->ordonnance->id];
    }
}
