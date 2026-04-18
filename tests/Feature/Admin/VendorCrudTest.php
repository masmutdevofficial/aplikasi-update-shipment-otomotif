<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vendor;
use Tests\TestCase;

class VendorCrudTest extends TestCase
{

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_vendors_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.vendors.index');
    }

    public function test_admin_can_create_vendor(): void
    {
        $vendorUser = User::factory()->vendor()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.vendors.store'), [
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PT Vendor Test',
            'position' => 'AT Storage Port',
        ]);

        $response->assertRedirect(route('admin.vendors.index'));
        $this->assertDatabaseHas('vendors', [
            'user_id' => $vendorUser->id,
            'position' => 'AT Storage Port',
        ]);
    }

    public function test_admin_can_update_vendor_position(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PT Vendor Test',
            'position' => 'AT Storage Port',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.vendors.update', $vendor), [
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PT Vendor Test',
            'position' => 'ATA Kapal',
        ]);

        $response->assertRedirect(route('admin.vendors.index'));
        $this->assertEquals('ATA Kapal', $vendor->fresh()->position);
    }

    public function test_admin_can_delete_vendor(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PT Vendor Test',
            'position' => 'AT Storage Port',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.vendors.destroy', $vendor));

        $response->assertRedirect(route('admin.vendors.index'));
        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    public function test_position_must_be_valid_enum(): void
    {
        $vendorUser = User::factory()->vendor()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.vendors.store'), [
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PT Vendor Test',
            'position' => 'Invalid Position',
        ]);

        $response->assertSessionHasErrors('position');
    }

    public function test_vendor_cannot_access_vendor_management(): void
    {
        $vendorUser = User::factory()->vendor()->create();

        $response = $this->actingAs($vendorUser)->get(route('admin.vendors.index'));
        $response->assertStatus(403);
    }
}
