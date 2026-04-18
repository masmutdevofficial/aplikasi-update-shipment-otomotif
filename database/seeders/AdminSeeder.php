<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Seed contoh data admin.
     */
    public function run(): void
    {
        $superadmin = User::where('level', 'superadmin')->first();
        $superadminId = $superadmin?->id;

        $admins = [
            [
                'email'    => 'admin1@gmail.com',
                'username' => 'admin.jakarta',
                'name'     => 'Admin Jakarta',
                'phone'    => '081200000001',
                'password' => 'Admin@Jakarta2026!!',
            ],
            [
                'email'    => 'admin2@gmail.com',
                'username' => 'admin.surabaya',
                'name'     => 'Admin Surabaya',
                'phone'    => '081200000002',
                'password' => 'Admin@Surabaya2026!!',
            ],
        ];

        foreach ($admins as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'id'         => Str::uuid(),
                    'username'   => $data['username'],
                    'name'       => $data['name'],
                    'phone'      => $data['phone'],
                    'password'   => Hash::make($data['password']),
                    'level'      => 'admin',
                    'is_active'  => true,
                    'created_by' => $superadminId,
                    'updated_by' => $superadminId,
                ]
            );
        }
    }
}
