<?php

namespace App\Http\Controllers;

use App\Models\PaymentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofStreamController extends Controller
{
    public function show(Request $request, PaymentProof $proof): StreamedResponse
    {
        $webUser = auth('web')->user();
        $isAdmin = auth('admin')->check();
        $isOwner = $webUser !== null
            && (int) $webUser->id === (int) ($proof->order?->user_id);

        abort_unless($isAdmin || $isOwner, 403);

        $disk = Storage::disk('payment_proofs');

        abort_unless($disk->exists($proof->proof_path), 404);

        return $disk->response($proof->proof_path);
    }
}
