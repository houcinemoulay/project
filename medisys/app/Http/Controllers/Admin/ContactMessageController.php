<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = ContactMessage::with('user')->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20);
        $unreadCount = ContactMessage::where('status', 'new')->count();

        return view('admin.contact-messages.index', compact('messages', 'unreadCount', 'status', 'search'));
    }

    public function show($id)
    {
        $contactMessage = ContactMessage::findOrFail($id);
        
        // Mark as read when viewed
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        $contactMessage->load('user');

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function markAsRead($id)
    {
        $contactMessage = ContactMessage::findOrFail($id);
        $contactMessage->update(['status' => 'read']);

        return back()->with('success', 'Message marked as read.');
    }

    public function markAsUnread($id)
    {
        $contactMessage = ContactMessage::findOrFail($id);
        $contactMessage->update(['status' => 'new']);

        return back()->with('success', 'Message marked as unread.');
    }

    public function destroy($id)
    {
        $contactMessage = ContactMessage::findOrFail($id);
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Message deleted successfully.');
    }
}
