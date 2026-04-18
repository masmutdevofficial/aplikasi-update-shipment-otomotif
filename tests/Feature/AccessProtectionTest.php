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
