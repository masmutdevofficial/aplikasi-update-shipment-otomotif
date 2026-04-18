<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $targetUser = $this->route('user');

        // Superadmin can update all levels
        if ($user->isSuperadmin()) {
            return true;
        }

        // Admin can only update vendor-level users
        if ($user->isAdmin() && $targetUser->level === 'vendor') {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        $level = $this->input('level', $this->route('user')->level);
        $minLength = ($level === 'vendor') ? 12 : 16;

        $rules = [
            'username' => ['required', 'string', 'max:100', 'unique:users,username,' . $userId, 'regex:/^[a-zA-Z0-9._-]+$/'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $userId],
            'phone' => ['nullable', 'string', 'max:20'],
            'level' => ['required', 'string', 'in:superadmin,admin,vendor'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        // Password is optional on update — only validate if provided
        if ($this->filled('password')) {
            $rules['password'] = [
                'required',
                'string',
                'confirmed',
                Password::min($minLength)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, underscore, dan strip.',
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.max' => 'No. telepon maksimal 20 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal :min karakter.',
            'level.required' => 'Level user wajib dipilih.',
            'level.in' => 'Level user tidak valid.',
        ];
    }
}
