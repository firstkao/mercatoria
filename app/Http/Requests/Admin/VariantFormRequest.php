<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use App\Services\Image\UploadError;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class VariantFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $sku = Rule::unique('product_variants', 'sku');

        if ($this->route('variant') instanceof ProductVariant) {
            $sku->ignore($this->route('variant'));
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:64', $sku],
            'price_rmb' => ['required', 'numeric', 'min:0'],
            'weight_gram' => ['nullable', 'integer', 'min:1'],
            'availability' => ['required', 'in:tersedia,kosong'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ];
    }

    /**
     * Gagal level PHP (isValid() = false) selalu di-report Laravel generik
     * "The image failed to upload." — ganti dengan alasan spesifik.
     */
    public function messages(): array
    {
        $file = $this->file('image');

        if ($file instanceof UploadedFile && ! $file->isValid()) {
            return ['image.uploaded' => 'Upload gagal: '.UploadError::describe($file)];
        }

        return [];
    }
}
