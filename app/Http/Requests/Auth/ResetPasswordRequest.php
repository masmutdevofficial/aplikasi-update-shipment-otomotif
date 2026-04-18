<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = \App\Models\User::where('email', $this->input('email'))->first();
        $minLength = ($user && $user->level === 'vendor') ? 12 : 16;

        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:150'],
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
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token reset tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal :min karakter.',
        ];
    }
}
