<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperadminSeeder extends Seeder
{
    /**
     * Seed superadmin default.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'id' => Str::uuid(),
                'username' => 'superadmin',
                'name' => 'Super Administrator',
                'phone' => null,
                'password' => Hash::make(config('app.default_superadmin_password', 'SuperAdmin@2026!!')),
                'level' => 'superadmin',
                'is_active' => true,
                'created_by' => null,
                'updated_by' => null,
            ]
        );
    }
}
