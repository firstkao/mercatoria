<?php

namespace App\Http\Requests\Admin;

use App\Support\Slugs;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class PageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $page = $this->route('page');

        $this->merge([
            // System pages are linked from code, so their address and visibility are fixed.
            'slug' => $page?->is_system ? $page->slug : Str::slug((string) ($this->input('slug') ?: $this->input('title'))),
            'is_published' => $page?->is_system ? true : $this->boolean('is_published'),
            'show_in_footer' => $this->boolean('show_in_footer'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:100000'],
            'is_published' => ['boolean'],
            'show_in_footer' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('slug')) {
                    return;
                }

                $conflict = Slugs::conflict($this->input('slug'), null, $this->route('page'));
                if ($conflict !== null) {
                    $validator->errors()->add('slug', $conflict);
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['title' => 'judul', 'slug' => 'alamat', 'content' => 'isi', 'sort_order' => 'urutan'];
    }
}