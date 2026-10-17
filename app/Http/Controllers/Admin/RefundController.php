<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\Order\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function __construct(private readonly RefundService $refunds) {}

    public function index(): View
    {
        $refunds = Refund::query()->with(['order.user', 'processedBy'])->latest()->paginate(25);

        return view('admin.refunds.index', compact('refunds'));
    }

    public function process(Request $request, Refund $refund): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->refunds->process(
            $refund,
            $request->user(),
            $validated['decision'] === 'approve',
            $validated['admin_note'] ?? null,
        );

        return back()->with('success', 'Permintaan refund berhasil diproses.');
    }
}
