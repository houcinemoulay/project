<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MedicalChatController extends Controller
{
    public function index()
    {
        $chats = Chat::where('user_id', Auth::id())->latest()->get();
        return view('medical-chat', compact('chats'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->message;

        $systemPrompt = "You are a medical assistant. Provide general medical advice only based on trusted sources. Do not provide diagnosis. Always recommend consulting a doctor.";

        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            Log::error('MedicalChat: Gemini API key is not configured');

            return redirect()->route('medical-chat.index')
                ->with('error', 'The medical assistant is not configured. Please contact an administrator.');
        }

        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=" . $apiKey,
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $systemPrompt . "\n\nUser Question:\n" . $userMessage]
                            ]
                        ]
                    ]
                ]
            );

            if ($response->failed()) {
                Log::error('MedicalChat: Gemini API call failed', [
                    'user_id' => Auth::id(),
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);

                return redirect()->route('medical-chat.index')
                    ->with('error', 'The medical assistant is unavailable right now. Please try again later.');
            }

            $data = $response->json();
            $botResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($botResponse === null) {
                Log::error('MedicalChat: unexpected Gemini API response format', [
                    'user_id' => Auth::id(),
                    'body'    => $response->body(),
                ]);

                return redirect()->route('medical-chat.index')
                    ->with('error', 'The medical assistant returned an unexpected response. Please try again.');
            }
        } catch (\Throwable $e) {
            Log::error('MedicalChat: exception while calling Gemini API', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('medical-chat.index')
                ->with('error', 'Could not reach the medical assistant. Please try again later.');
        }

        Chat::create([
            'user_id' => Auth::id(),
            'message' => $userMessage,
            'response' => $botResponse,
        ]);

        return redirect()->route('medical-chat.index');
    }
}
