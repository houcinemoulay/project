<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Services\GeminiClient;
use Illuminate\Support\Facades\Auth;

class MedicalChatController extends Controller
{
    public function __construct(private GeminiClient $gemini)
    {
    }

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

        $result = $this->gemini->generateText($systemPrompt . "\n\nUser Question:\n" . $userMessage);

        $botResponse = match (true) {
            $result['success'] => $result['text'],
            $result['reason'] === GeminiClient::REASON_INVALID_RESPONSE => 'Sorry, I could not process that request.',
            $result['reason'] === GeminiClient::REASON_EXCEPTION => 'Error: ' . $result['message'],
            default => 'Error: Unable to reach the medical assistant service at the moment.',
        };

        Chat::create([
            'user_id' => Auth::id(),
            'message' => $userMessage,
            'response' => $botResponse,
        ]);

        return redirect()->route('medical-chat.index');
    }
}
