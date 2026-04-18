<?php

namespace App\Http\Requests\Admin;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user->isSuperadmin() || $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'vendor_name' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', Rule::in(Vendor::positions())],
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_name.required' => 'Nama vendor wajib diisi.',
            'vendor_name.max' => 'Nama vendor maksimal 150 karakter.',
            'position.required' => 'Posisi wajib dipilih.',
            'position.in' => 'Posisi tidak valid.',
        ];
    }
}
