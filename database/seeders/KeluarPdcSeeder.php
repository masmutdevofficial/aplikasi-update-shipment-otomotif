<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeluarPdcSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $superadminId = User::query()->where('level', 'superadmin')->value('id');

            $user = User::firstOrCreate(
                ['email' => 'vendor.keluarpdc@gmail.com'],
                [
                    'username' => 'vendor.keluarpdc',
                    'name' => 'Operator Keluar PDC',
                    'password' => 'Vendor@Pdc2026!',
                    'level' => 'vendor',
                    'status' => User::STATUS_ACTIVE,
                    'is_active' => true,
                    'created_by' => $superadminId,
                    'updated_by' => $superadminId,
                ],
            );

            Vendor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'vendor_name' => 'Operasional Keluar PDC',
                    'position' => Vendor::POSITION_KELUAR_PDC,
                    'created_by' => $superadminId,
                    'updated_by' => $superadminId,
                ],
            );
        });
    }
}
