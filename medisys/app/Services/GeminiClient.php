<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Gemini generateContent endpoint.
 *
 * Every call returns a normalised array so callers can decide how to log,
 * translate or render failures:
 *
 *   ['success' => true,  'text' => '...', 'data' => [...]]
 *   ['success' => false, 'reason' => self::REASON_*, 'status' => ?int, 'body' => ?string, ...]
 */
class GeminiClient
{
    public const REASON_UNCONFIGURED = 'unconfigured';
    public const REASON_REQUEST_FAILED = 'request_failed';
    public const REASON_API_ERROR = 'api_error';
    public const REASON_INVALID_RESPONSE = 'invalid_response';
    public const REASON_EXCEPTION = 'exception';

    public function apiKey(): ?string
    {
        return config('services.gemini.key') ?: null;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== null;
    }

    /**
     * Send a single text prompt to Gemini and extract the generated text.
     *
     * @param  array{model?:string, version?:string, timeout?:int}  $options
     * @return array<string, mixed>
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $apiKey = $this->apiKey();

        if ($apiKey === null) {
            return ['success' => false, 'reason' => self::REASON_UNCONFIGURED];
        }

        $model = $options['model'] ?? config('services.gemini.model');
        $version = $options['version'] ?? config('services.gemini.version');
        $timeout = $options['timeout'] ?? config('services.gemini.timeout');

        $url = sprintf(
            'https://generativelanguage.googleapis.com/%s/models/%s:generateContent?key=%s',
            $version,
            $model,
            $apiKey
        );

        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                ]);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'reason' => self::REASON_EXCEPTION,
                'exception' => $e,
                'message' => $e->getMessage(),
            ];
        }

        if ($response->failed()) {
            return [
                'success' => false,
                'reason' => self::REASON_REQUEST_FAILED,
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ];
        }

        $data = $response->json();

        if (isset($data['error'])) {
            return [
                'success' => false,
                'reason' => self::REASON_API_ERROR,
                'error' => $data['error'],
                'data' => $data,
            ];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null) {
            return [
                'success' => false,
                'reason' => self::REASON_INVALID_RESPONSE,
                'data' => $data,
            ];
        }

        return ['success' => true, 'text' => $text, 'data' => $data];
    }
}
