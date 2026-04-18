<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    /**
     * Seed contoh data user vendor beserta record vendor-nya.
     */
    public function run(): void
    {
        $superadmin = User::where('level', 'superadmin')->first();
        $superadminId = $superadmin?->id;

        $vendors = [
            [
                'email'       => 'vendor.storageport1@gmail.com',
                'username'    => 'vendor.storageport1',
                'name'        => 'Budi Santoso',
                'phone'       => '081300000001',
                'password'    => 'Vendor@Port2026!',
                'vendor_name' => 'PT. Logistik Pelabuhan Utama',
                'position'    => 'AT Storage Port',
            ],
            [
                'email'       => 'vendor.loading@gmail.com',
                'username'    => 'vendor.loading',
                'name'        => 'Siti Rahayu',
                'phone'       => '081300000002',
                'password'    => 'Vendor@Load2026!',
                'vendor_name' => 'PT. Muat Kapal Nusantara',
                'position'    => 'ATD Kapal (Loading)',
            ],
            [
                'email'       => 'vendor.atakapal@gmail.com',
                'username'    => 'vendor.atakapal',
                'name'        => 'Ahmad Fauzi',
                'phone'       => '081300000003',
                'password'    => 'Vendor@Kapal2026!',
                'vendor_name' => 'PT. Armada Samudera',
                'position'    => 'ATA Kapal',
            ],
            [
                'email'       => 'vendor.storageport2@gmail.com',
                'username'    => 'vendor.storageport2',
                'name'        => 'Dewi Kusuma',
                'phone'       => '081300000004',
                'password'    => 'Vendor@Dest2026!!',
                'vendor_name' => 'PT. Gudang Tujuan Andalan',
                'position'    => 'ATA Storage Port (Destination)',
            ],
            [
                'email'       => 'vendor.dooring@gmail.com',
                'username'    => 'vendor.dooring',
                'name'        => 'Rizky Pratama',
                'phone'       => '081300000005',
                'password'    => 'Vendor@Door2026!!',
                'vendor_name' => 'PT. Pengiriman Terakhir',
                'position'    => 'AT PtD (Dooring)',
            ],
        ];

        foreach ($vendors as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'id'         => Str::uuid(),
                    'username'   => $data['username'],
                    'name'       => $data['name'],
                    'phone'      => $data['phone'],
                    'password'   => Hash::make($data['password']),
                    'level'      => 'vendor',
                    'is_active'  => true,
                    'created_by' => $superadminId,
                    'updated_by' => $superadminId,
                ]
            );

            Vendor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'id'          => Str::uuid(),
                    'vendor_name' => $data['vendor_name'],
                    'position'    => $data['position'],
                    'created_by'  => $superadminId,
                    'updated_by'  => $superadminId,
                ]
            );
        }
    }
}
