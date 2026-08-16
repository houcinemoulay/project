<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Logs a failed {@see GeminiClient} call and maps it to a user-facing message.
 */
class GeminiFailure
{
    /**
     * @param  array<string, mixed>  $result  a failed GeminiClient result
     */
    public static function describe(string $context, array $result): string
    {
        switch ($result['reason']) {
            case GeminiClient::REASON_UNCONFIGURED:
                Log::error("{$context}: API key not configured");

                return 'AI service not configured';

            case GeminiClient::REASON_REQUEST_FAILED:
                Log::error("{$context}: API call failed", [
                    'status' => $result['status'],
                    'body' => $result['body'],
                ]);

                return 'AI service temporarily unavailable';

            case GeminiClient::REASON_API_ERROR:
                Log::error("{$context}: API returned error", $result['error']);

                return 'AI service error';

            case GeminiClient::REASON_INVALID_RESPONSE:
                Log::error("{$context}: Invalid API response format", $result['data']);

                return 'Invalid AI response';

            default:
                Log::error("{$context}: Exception occurred", [
                    'message' => $result['message'],
                    'trace' => $result['exception']->getTraceAsString(),
                ]);

                return 'AI service error';
        }
    }
}
