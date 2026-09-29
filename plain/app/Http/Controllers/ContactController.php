<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage as ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact.show', [
            'title' => 'Hubungi Kami',
            'metaDescription' => 'Punya pertanyaan tentang produk, pesanan atau reseller? Hubungi CS MERCATORIA via WhatsApp atau email.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:3000'],
            'cf-turnstile-response' => [new \App\Rules\Turnstile($request->ip())],
        ]);

        unset($data['cf-turnstile-response']);

        $message = ContactMessage::create([
            ...$data,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke admin (opsional, kalau ada email CS)
        $csEmail = \App\Models\Setting::get('contact_email');
        if ($csEmail) {
            try {
                Mail::to($csEmail)->queue(new ContactMessageMail($message));
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim email contact form: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Pesan kamu sudah terkirim. CS akan membalas melalui email atau WhatsApp.');
    }
}