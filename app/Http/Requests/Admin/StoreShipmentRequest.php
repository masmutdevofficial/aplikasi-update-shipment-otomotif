<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->level, ['superadmin', 'admin']);
    }

    public function rules(): array
    {
        return [
            'lokasi' => ['required', 'string', 'max:50'],
            'no_do' => ['required', 'string', 'max:50'],
            'type_kendaraan' => ['required', 'string', 'max:50'],
            'no_rangka' => ['required', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/', 'unique:shipments,no_rangka'],
            'no_engine' => ['required', 'string', 'max:50'],
            'warna' => ['required', 'string', 'max:50'],
            'asal_pdc' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'tujuan_pengiriman' => ['required', 'string', 'max:100'],
            'terima_do' => ['required', 'date'],
            'keluar_dari_pdc' => ['required', 'date'],
            'nama_kapal' => ['required', 'string', 'max:100'],
            'keberangkatan_kapal' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_rangka.size' => 'No. Rangka (VIN) harus tepat 17 karakter.',
            'no_rangka.unique' => 'No. Rangka (VIN) sudah terdaftar di sistem.',
            'terima_do.date' => 'Format tanggal Terima DO tidak valid.',
            'keluar_dari_pdc.date' => 'Format tanggal Keluar dari PDC tidak valid.',
            'keberangkatan_kapal.date' => 'Format tanggal Keberangkatan Kapal tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'lokasi' => 'Lokasi',
            'no_do' => 'No. DO',
            'type_kendaraan' => 'Type Kendaraan',
            'no_rangka' => 'No. Rangka',
            'no_engine' => 'No. Engine',
            'warna' => 'Warna',
            'asal_pdc' => 'Asal PDC',
            'kota' => 'Kota',
            'tujuan_pengiriman' => 'Tujuan Pengiriman',
            'terima_do' => 'Terima DO',
            'keluar_dari_pdc' => 'Keluar dari PDC',
            'nama_kapal' => 'Nama Kapal',
            'keberangkatan_kapal' => 'Keberangkatan Kapal',
        ];
    }
}
