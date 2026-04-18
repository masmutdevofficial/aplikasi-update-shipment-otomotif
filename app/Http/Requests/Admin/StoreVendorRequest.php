<?php

namespace App\Http\Requests\Admin;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user->isSuperadmin() || $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where('level', 'vendor')->where('is_active', true),
                Rule::unique('vendors', 'user_id'),
            ],
            'vendor_name' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', Rule::in(Vendor::positions())],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User vendor wajib dipilih.',
            'user_id.exists' => 'User vendor tidak valid atau tidak aktif.',
            'user_id.unique' => 'User ini sudah terdaftar sebagai vendor.',
            'vendor_name.required' => 'Nama vendor wajib diisi.',
            'vendor_name.max' => 'Nama vendor maksimal 150 karakter.',
            'position.required' => 'Posisi wajib dipilih.',
            'position.in' => 'Posisi tidak valid.',
        ];
    }
}
