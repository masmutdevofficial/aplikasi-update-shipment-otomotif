<?php

namespace Database\Factories;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lokasi' => fake()->city(),
            'no_do' => 'DO-' . fake()->unique()->numerify('###'),
            'type_kendaraan' => fake()->randomElement(['Avanza', 'Xenia', 'Rush', 'Terios', 'Calya']),
            'no_rangka' => $this->generateVin(),
            'no_engine' => 'ENG-' . fake()->numerify('######'),
            'warna' => fake()->randomElement(['Putih', 'Hitam', 'Silver', 'Merah', 'Biru']),
            'asal_pdc' => 'PDC ' . fake()->city(),
            'kota' => fake()->city(),
            'tujuan_pengiriman' => fake()->city(),
            'terima_do' => fake()->date(),
            'keluar_dari_pdc' => fake()->date(),
            'nama_kapal' => 'KM ' . fake()->lastName(),
            'keberangkatan_kapal' => fake()->date(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    private function generateVin(): string
    {
        $chars = 'ABCDEFGHJKLMNPRSTUVWXYZ0123456789';
        $vin = '';
        for ($i = 0; $i < 17; $i++) {
            $vin .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $vin;
    }
}
