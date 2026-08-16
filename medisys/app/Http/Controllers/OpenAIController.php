<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIController extends Controller
{
    public function testGoogleAI()
    {
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            Log::error('Gemini connectivity test: API key is not configured');

            return response()->json(['error' => 'Gemini API key is not configured.'], 503);
        }

        $response = Http::timeout(30)->post(
            "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=".$apiKey,
            [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => "Hello, are you working?"]
                        ]
                    ]
                ]
            ]
        );

        if ($response->failed()) {
            Log::error('Gemini connectivity test failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json(['error' => 'Gemini API request failed.'], 502);
        }

        return response()->json($response->json());
    }
}