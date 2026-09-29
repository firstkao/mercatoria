<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $messages = ContactMessage::query()
            ->when($status === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($status === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.contact.index', [
            'messages' => $messages,
            'status' => $status,
            'unreadCount' => ContactMessage::query()->whereNull('read_at')->count(),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.contact.show', [
            'message' => $message,
        ]);
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $subject = $message->subject;
        $message->delete();

        return redirect()->route('admin.contact.index')->with('status', "Pesan \"{$subject}\" dihapus.");
    }
}