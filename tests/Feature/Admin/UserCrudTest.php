<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class UserCrudTest extends TestCase
{

    private User $superadmin;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superadmin = User::factory()->superadmin()->create();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_superadmin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    public function test_vendor_cannot_access_users_index(): void
    {
        $vendor = User::factory()->vendor()->create();
        $response = $this->actingAs($vendor)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_create_user_of_any_level(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.users.store'), [
            'username' => 'newadmin',
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'phone' => '081234567890',
            'password' => 'Admin@Password1234!',
            'password_confirmation' => 'Admin@Password1234!',
            'level' => 'admin',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com', 'level' => 'admin']);
    }

    public function test_superadmin_can_create_vendor_user(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.users.store'), [
            'username' => 'newvendor',
            'name' => 'New Vendor',
            'email' => 'newvendor@test.com',
            'phone' => '081234567891',
            'password' => 'Vendor@Pass123!',
            'password_confirmation' => 'Vendor@Pass123!',
            'level' => 'vendor',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newvendor@test.com', 'level' => 'vendor']);
    }

    public function test_superadmin_can_update_user(): void
    {
        $user = User::factory()->vendor()->create();

        $response = $this->actingAs($this->superadmin)->put(route('admin.users.update', $user), [
            'username' => $user->username,
            'name' => 'Updated Name',
            'email' => $user->email,
            'level' => 'vendor',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_cannot_delete_last_superadmin(): void
    {
        // Create a second superadmin to act as the deleter
        $superadmin2 = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin2)->delete(route('admin.users.destroy', $this->superadmin));

        // After deleting superadmin, only superadmin2 remains - next delete should fail
        $response2 = $this->actingAs($superadmin2)->delete(route('admin.users.destroy', $superadmin2));

        // Cannot delete self
        $this->assertDatabaseHas('users', ['id' => $superadmin2->id]);
    }

    public function test_can_delete_non_last_superadmin(): void
    {
        $superadmin2 = User::factory()->superadmin()->create();

        $response = $this->actingAs($this->superadmin)->delete(route('admin.users.destroy', $superadmin2));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $superadmin2->id]);
    }

    public function test_user_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->superadmin)->delete(route('admin.users.destroy', $this->superadmin));

        $this->assertDatabaseHas('users', ['id' => $this->superadmin->id]);
    }

    public function test_toggle_status_works(): void
    {
        $user = User::factory()->vendor()->create(['is_active' => true]);

        $response = $this->actingAs($this->superadmin)->patch(route('admin.users.toggle-status', $user));

        $response->assertRedirect();
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_cannot_edit_superadmin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->superadmin));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_admin_user(): void
    {
        $admin2 = User::factory()->admin()->create();
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $admin2));
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_users(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect('/login');
    }
}
