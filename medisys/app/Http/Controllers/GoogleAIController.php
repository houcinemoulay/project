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
            'message' => 'required|string|max:1000',
        ]);

        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            Log::warning('Medical chat called without a configured Gemini API key.');

            return response()->json([
                'reply' => 'Chat bot is not configured. Please contact administrator.',
            ], 503);
        }

        $systemPrompt = "You are a medical assistant. Provide general medical advice only based on trusted sources. Do not provide diagnosis. Always recommend consulting a doctor.";

        $unavailable = response()->json([
            'reply' => 'Sorry, I am currently unavailable. Please try again later.',
        ], 503);

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
                Log::error('Gemini API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return $unavailable;
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error('Gemini API returned an error', ['error' => $data['error']]);

                return $unavailable;
            }

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('Gemini API returned an unexpected response format.');

                return $unavailable;
            }

            return response()->json([
                'reply' => $data['candidates'][0]['content']['parts'][0]['text'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API request threw an exception', ['exception' => $e->getMessage()]);

            return $unavailable;
        }
    }
}
