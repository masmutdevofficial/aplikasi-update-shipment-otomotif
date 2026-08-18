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
            [
                'email'       => 'iso.storageport@gmail.com',
                'username'    => 'iso.storageport',
                'name'        => 'Operator ISO Storage Port',
                'phone'       => '081300000101',
                'password'    => 'ISO@Storage2026!',
                'vendor_name' => 'Operasional ISO Darat & Laut',
                'position'    => 'AT Storage Port',
            ],
            [
                'email'       => 'iso.loading@gmail.com',
                'username'    => 'iso.loading',
                'name'        => 'Operator ISO Loading',
                'phone'       => '081300000102',
                'password'    => 'ISO@Loading2026!',
                'vendor_name' => 'Operasional ISO Darat & Laut',
                'position'    => 'ATD Kapal (Loading)',
            ],
            [
                'email'       => 'iso.atakapal@gmail.com',
                'username'    => 'iso.atakapal',
                'name'        => 'Operator ISO ATA Kapal',
                'phone'       => '081300000103',
                'password'    => 'ISO@Kapal2026!',
                'vendor_name' => 'Operasional ISO Darat & Laut',
                'position'    => 'ATA Kapal',
            ],
            [
                'email'       => 'iso.destination@gmail.com',
                'username'    => 'iso.destination',
                'name'        => 'Operator ISO Storage Destination',
                'phone'       => '081300000104',
                'password'    => 'ISO@Destination2026!',
                'vendor_name' => 'Operasional ISO Darat & Laut',
                'position'    => 'ATA Storage Port (Destination)',
            ],
            [
                'email'       => 'iso.ptddtd@gmail.com',
                'username'    => 'iso.ptddtd',
                'name'        => 'Operator ISO PTD DTD',
                'phone'       => '081300000105',
                'password'    => 'ISO@PtdDtd2026!',
                'vendor_name' => 'Operasional ISO Darat & Laut',
                'position'    => 'AT PTD/DTD',
            ],
            [
                'email'       => 'tso.dtp@gmail.com',
                'username'    => 'tso.dtp',
                'name'        => 'Operator TSO Door to Port',
                'phone'       => '081300000201',
                'password'    => 'TSO@Dtp2026!',
                'vendor_name' => 'Operasional TSO',
                'position'    => 'Door to Port (DTP)',
            ],
            [
                'email'       => 'tso.ptp@gmail.com',
                'username'    => 'tso.ptp',
                'name'        => 'Operator TSO Port to Port',
                'phone'       => '081300000202',
                'password'    => 'TSO@Ptp2026!',
                'vendor_name' => 'Operasional TSO',
                'position'    => 'Port to Port (PTP)',
            ],
            [
                'email'       => 'tso.ptd@gmail.com',
                'username'    => 'tso.ptd',
                'name'        => 'Operator TSO Port to Door',
                'phone'       => '081300000203',
                'password'    => 'TSO@Ptd2026!',
                'vendor_name' => 'Operasional TSO',
                'position'    => 'Port to Door (PTD)',
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
