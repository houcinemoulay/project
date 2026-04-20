<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is admin
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        // Get total count
        $totalCount = ContactMessage::count();
        
        // Get unread count
        $unreadCount = ContactMessage::where('status', 'new')->count();
        
        // Get recent messages (last 10)
        $recentMessages = ContactMessage::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'user_id' => $message->user_id,
                    'user_role' => $message->user_role,
                    'role_label' => $message->role_label,
                    'role_color' => $message->role_color,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalCount,
                'unread' => $unreadCount,
                'recent' => $recentMessages,
            ]
        ]);
    }

    public function myMessages(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Get user's messages (by email or user_id)
        $messages = ContactMessage::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('email', $user->email);
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->format('M j, Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
}
