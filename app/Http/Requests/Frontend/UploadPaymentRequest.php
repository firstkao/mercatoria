<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // PRD 13.6: bukti bayar 1 file, maks 1 MB, JPG/PNG.
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Pilih file bukti pembayaran.',
            'proof.mimes' => 'Bukti bayar harus berupa gambar JPG atau PNG.',
            'proof.max' => 'Ukuran bukti bayar maksimal 1 MB.',
        ];
    }
}
