<?php

namespace Tests\Feature\Database;

use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\VendorSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VendorSeederTest extends TestCase
{
    public function test_vendor_seeder_creates_iso_and_tso_accounts_idempotently(): void
    {
        User::factory()->superadmin()->create();

        $accounts = [
            'iso.storageport@gmail.com' => ['ISO@Storage2026!', 'AT Storage Port'],
            'iso.loading@gmail.com' => ['ISO@Loading2026!', 'ATD Kapal (Loading)'],
            'iso.atakapal@gmail.com' => ['ISO@Kapal2026!', 'ATA Kapal'],
            'iso.destination@gmail.com' => ['ISO@Destination2026!', 'ATA Storage Port (Destination)'],
            'iso.ptddtd@gmail.com' => ['ISO@PtdDtd2026!', 'AT PTD/DTD'],
            'tso.dtp@gmail.com' => ['TSO@Dtp2026!', 'Door to Port (DTP)'],
            'tso.ptp@gmail.com' => ['TSO@Ptp2026!', 'Port to Port (PTP)'],
            'tso.ptd@gmail.com' => ['TSO@Ptd2026!', 'Port to Door (PTD)'],
        ];

        $this->seed(VendorSeeder::class);
        $this->seed(VendorSeeder::class);

        foreach ($accounts as $email => [$password, $position]) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->assertSame('vendor', $user->level);
            $this->assertTrue($user->is_active);
            $this->assertTrue(Hash::check($password, $user->password));
            $this->assertSame($position, $user->vendor?->position);
            $this->assertSame(1, User::query()->where('email', $email)->count());
            $this->assertSame(1, Vendor::query()->where('user_id', $user->id)->count());
        }
    }

    public function test_vendor_positions_include_iso_and_tso_workflow_positions(): void
    {
        foreach ([
            'AT PTD/DTD',
            'Door to Port (DTP)',
            'Port to Port (PTP)',
            'Port to Door (PTD)',
        ] as $position) {
            $this->assertContains($position, Vendor::positions());
        }
    }
}
