<?php

namespace App\Http\Controllers;

use App\Services\GeminiClient;

class OpenAIController extends Controller
{
    public function __construct(private GeminiClient $gemini)
    {
    }

    public function testGoogleAI()
    {
        $result = $this->gemini->generateText('Hello, are you working?');

        return response()->json($result['data'] ?? ['error' => $result['reason']]);
    }
}
