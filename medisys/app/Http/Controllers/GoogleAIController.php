<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAIController extends Controller
{
    public function index()
    {
        return view('medical-chat');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            Log::error('MedicalChat: Gemini API key is not configured');

            return response()->json([
                'error' => 'Chat bot is not configured.',
                'reply' => 'Chat bot is not configured. Please contact administrator.'
            ], 503);
        }

        $systemPrompt = "You are a medical assistant. Provide general medical advice only based on trusted sources. Do not provide diagnosis. Always recommend consulting a doctor.";

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
            
            $payload = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $systemPrompt . "\n\nUser Question:\n" . $request->message]
                        ]
                    ]
                ]
            ];

            $response = Http::timeout(30)->post($url, $payload);

            if ($response->failed()) {
                Log::error('MedicalChat: Gemini API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return response()->json([
                    'error' => 'The assistant service is unavailable.',
                    'reply' => 'Sorry, I am currently unavailable. Please try again later.'
                ], 502);
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error('MedicalChat: Gemini API returned an error', [
                    'error' => $data['error'],
                ]);

                return response()->json([
                    'error' => 'The assistant service returned an error.',
                    'reply' => 'Sorry, I am currently unavailable. Please try again later.'
                ], 502);
            }

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('MedicalChat: unexpected Gemini API response format', [
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Unexpected response from the assistant service.',
                    'reply' => 'Sorry, I received an invalid response. Please try again later.'
                ], 502);
            }

            $reply = $data['candidates'][0]['content']['parts'][0]['text'];

            return response()->json([
                'reply' => $reply
            ]);

        } catch (\Throwable $e) {
            Log::error('MedicalChat: exception while calling Gemini API', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Could not reach the assistant service.',
                'reply' => 'Sorry, I am currently unavailable. Please try again later.'
            ], 502);
        }
    }
}
