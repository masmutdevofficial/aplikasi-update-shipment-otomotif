<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Support\VendorAccess;
use Tests\TestCase;

class VendorAccessSettingTest extends TestCase
{
    public function test_admin_can_view_and_update_vendor_access_mode(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertSame(VendorAccess::MODE_ACTIVE, VendorAccess::mode());

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Akses Web Vendor')
            ->assertSee('Aktif')
            ->assertSee('Maintenance');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'vendor_access_mode' => VendorAccess::MODE_MAINTENANCE,
            ])
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success');

        $this->assertSame(VendorAccess::MODE_MAINTENANCE, VendorAccess::mode());
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => VendorAccess::SETTING_KEY,
            'setting_value' => VendorAccess::MODE_MAINTENANCE,
        ]);
    }

    public function test_vendor_cannot_open_admin_settings(): void
    {
        $vendor = User::factory()->vendor()->create();

        $this->actingAs($vendor)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_vendor_cannot_login_during_maintenance_but_admin_can(): void
    {
        VendorAccess::setMode(VendorAccess::MODE_MAINTENANCE);
        $vendor = User::factory()->vendor()->create(['email' => 'maintenance-vendor@test.com']);
        $admin = User::factory()->admin()->create(['email' => 'maintenance-admin@test.com']);

        $this->post('/login', [
            'email' => $vendor->email,
            'password' => 'Test@Password123!',
        ])
            ->assertRedirect()
            ->assertSessionHas('error', VendorAccess::maintenanceMessage());
        $this->assertGuest();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'Test@Password123!',
        ])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_logged_in_vendor_is_automatically_logged_out_during_maintenance(): void
    {
        $vendor = User::factory()->vendor()->create();
        VendorAccess::setMode(VendorAccess::MODE_MAINTENANCE);

        $this->actingAs($vendor)
            ->get(route('password.change'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', VendorAccess::maintenanceMessage());

        $this->assertGuest();
    }

    public function test_login_page_displays_vendor_maintenance_notice(): void
    {
        VendorAccess::setMode(VendorAccess::MODE_MAINTENANCE);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Portal Vendor Sedang Maintenance')
            ->assertSee('Admin dan superadmin tetap dapat login.');
    }

    public function test_vendor_can_login_again_after_mode_is_activated(): void
    {
        $vendor = User::factory()->vendor()->create(['email' => 'active-vendor@test.com']);
        VendorAccess::setMode(VendorAccess::MODE_MAINTENANCE);
        VendorAccess::setMode(VendorAccess::MODE_ACTIVE);

        $this->post('/login', [
            'email' => $vendor->email,
            'password' => 'Test@Password123!',
        ])->assertRedirect('/vendor/dashboard');

        $this->assertAuthenticatedAs($vendor);
    }
}
