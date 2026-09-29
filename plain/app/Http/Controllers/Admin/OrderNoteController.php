<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\OrderNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderNoteController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $order->notes()->create([
            'admin_id' => auth('admin')->id(),
            'body' => $data['body'],
            'is_pinned' => ! empty($data['is_pinned']),
        ]);

        return back()->with('status', 'Catatan ditambahkan.');
    }

    public function destroy(Order $order, OrderNote $note): RedirectResponse
    {
        abort_unless($note->order_id === $order->id, 404);

        $note->delete();

        return back()->with('status', 'Catatan dihapus.');
    }

    public function togglePin(Order $order, OrderNote $note): RedirectResponse
    {
        abort_unless($note->order_id === $order->id, 404);

        $note->update(['is_pinned' => ! $note->is_pinned]);

        return back()->with('status', $note->is_pinned ? 'Catatan di-pin.' : 'Pin dilepas.');
    }
}