<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');

        // Role yang dikirim sama dengan role saat ini bukan perubahan (form edit massal selalu mengirim role).
        if ($this->changesRole($target)) {
            return $this->user()->can('changeRole', $target);
        }

        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,'.$this->route('user')->id],
            'role' => ['sometimes', 'in:crush,customer,admin,super_admin'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $target = $this->route('user');

            if (! $this->changesRole($target) || ! $target->isSuperAdmin()) {
                return;
            }

            $otherSuperAdmins = User::where('role', User::ROLE_SUPER_ADMIN)->whereKeyNot($target->id)->count();

            if ($otherSuperAdmins === 0) {
                $validator->errors()->add('role', 'Minimal harus ada 1 super_admin aktif.');
            }
        }];
    }

    private function changesRole(User $target): bool
    {
        return $this->has('role') && $this->input('role') !== $target->role;
    }
}
