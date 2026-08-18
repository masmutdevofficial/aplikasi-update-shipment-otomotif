<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AccessProtectionTest extends TestCase
{

    public function test_guest_redirected_to_login_from_admin(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_guest_redirected_to_login_from_vendor(): void
    {
        $response = $this->get('/vendor/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_vendor_cannot_access_admin_routes(): void
    {
        $vendor = User::factory()->vendor()->create();

        $routes = [
            '/admin/dashboard',
            '/admin/users',
            '/admin/vendors',
            '/admin/shipments',
            '/admin/reports',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($vendor)->get($route);
            $this->assertEquals(403, $response->status(), "Vendor should not access {$route}");
        }
    }

    public function test_admin_cannot_access_vendor_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $routes = [
            '/vendor/dashboard',
            '/vendor/scanner',
            '/vendor/history',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $this->assertEquals(403, $response->status(), "Admin should not access {$route}");
        }
    }

    public function test_inactive_vendor_is_logged_out(): void
    {
        $vendorUser = User::factory()->vendor()->inactive()->create();

        $response = $this->actingAs($vendorUser)->get('/vendor/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }

    public function test_superadmin_can_access_admin_routes(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Performance Shipment DSO');
        $response->assertSee('Persentase Keterlambatan');
        $response->assertSee('Actual Lead Time (Days)');
    }

    public function test_admin_can_switch_between_dashboard_types(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard?type=tso');

        $response->assertOk();
        $response->assertSee('Dashboard DSO');
        $response->assertSee('Dashboard TSO');
        $response->assertSee('Dashboard ISO');
        $response->assertSee('value="tso" selected', false);
        $response->assertSee('<h1>Dashboard TSO</h1>', false);
    }

    public function test_unknown_dashboard_type_falls_back_to_dso(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard?type=unknown');

        $response->assertOk();
        $response->assertSee('value="dso" selected', false);
        $response->assertSee('<h1>Dashboard DSO</h1>', false);
    }

    public function test_authenticated_vendor_is_redirected_to_vendor_dashboard_from_login(): void
    {
        $vendor = User::factory()->vendor()->create();

        $response = $this->actingAs($vendor)->get('/login');

        $response->assertRedirect('/vendor/dashboard');
    }

    public function test_change_password_accessible_by_any_authenticated_user(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/password/change');
        $response->assertStatus(200);
    }

    public function test_logout_only_via_post(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/logout');
        $this->assertNotEquals(200, $response->status());
    }
}
