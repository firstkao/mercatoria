<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'verified', 'rejected'])],
        ]) + ['status' => 'pending'];

        $payments = Payment::query()->with('order')
            ->where('status', $filters['status'])
            ->when($filters['status'] === 'pending', fn ($query) => $query->whereNotNull('proof_path'))
            ->latest('id')->paginate(30)->withQueryString();

        return view('admin.payments.index', compact('payments', 'filters'));
    }
}
