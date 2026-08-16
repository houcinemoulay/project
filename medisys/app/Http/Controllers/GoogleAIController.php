<?php

namespace App\Http\Controllers;

use App\Services\GeminiClient;
use Illuminate\Http\Request;

class GoogleAIController extends Controller
{
    public function __construct(private GeminiClient $gemini)
    {
    }

    public function index()
    {
        return view('medical-chat');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        if (!$this->gemini->isConfigured()) {
            return response()->json([
                'error' => 'API key not configured. Please add GOOGLE_API_KEY or GEMINI_API_KEY to your .env file.',
                'reply' => 'Chat bot is not configured. Please contact administrator.'
            ], 500);
        }

        $systemPrompt = "You are a medical assistant. Provide general medical advice only based on trusted sources. Do not provide diagnosis. Always recommend consulting a doctor.";

        $result = $this->gemini->generateText($systemPrompt . "\n\nUser Question:\n" . $request->message);

        if ($result['success']) {
            return response()->json(['reply' => $result['text']]);
        }

        return response()->json([
            'error' => $this->errorDetails($result),
            'reply' => $result['reason'] === GeminiClient::REASON_INVALID_RESPONSE
                ? 'Sorry, I received an invalid response. Please try again later.'
                : 'Sorry, I am currently unavailable. Please try again later.',
        ], 500);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function errorDetails(array $result): string
    {
        return match ($result['reason']) {
            GeminiClient::REASON_REQUEST_FAILED => 'API request failed: ' . json_encode([
                'status' => $result['status'],
                'body' => $result['body'],
                'headers' => $result['headers'],
            ]),
            GeminiClient::REASON_API_ERROR => 'API Error: ' . json_encode($result['error']),
            GeminiClient::REASON_INVALID_RESPONSE => 'Invalid API response format: ' . json_encode($result['data']),
            default => 'Exception: ' . ($result['message'] ?? 'Unknown error'),
        };
    }
}
