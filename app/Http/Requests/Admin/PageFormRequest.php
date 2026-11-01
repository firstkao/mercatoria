<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:170',
                Rule::unique('pages', 'slug')->ignore($this->route('page')),
                Rule::unique('preorder_pages', 'slug'),
                Rule::unique('games', 'slug'),
            ],
            'content' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'status' => ['required', Rule::in(['draft', 'publish'])],
        ];

        return $rules;
    }
}
