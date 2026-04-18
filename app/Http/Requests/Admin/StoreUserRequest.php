<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Superadmin can create all levels; admin can only create vendor
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->isAdmin() && $this->input('level') === 'vendor') {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        $level = $this->input('level', 'vendor');
        $minLength = ($level === 'vendor') ? 12 : 16;

        return [
            'username' => ['required', 'string', 'max:100', 'unique:users,username', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min($minLength)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'level' => ['required', 'string', 'in:superadmin,admin,vendor'],
            'is_active' => ['sometimes', 'boolean'],
        ];
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
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal :min karakter.',
            'level.required' => 'Level user wajib dipilih.',
            'level.in' => 'Level user tidak valid.',
        ];
    }
}
