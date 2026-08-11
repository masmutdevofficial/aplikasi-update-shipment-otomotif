<?php

namespace Tests\Feature\Vendor;

use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ScanVinTest extends TestCase
{
    private User $vendorUser;

    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vendorUser = User::factory()->vendor()->create();
        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'vendor_name' => 'PT Vendor Test',
            'position' => 'AT Storage Port',
            'created_by' => $this->vendorUser->id,
            'updated_by' => $this->vendorUser->id,
        ]);
    }

    public function test_vendor_can_access_scanner_page(): void
    {
        $response = $this->actingAs($this->vendorUser)->get(route('vendor.scanner'));
        $response->assertStatus(200);
    }

    public function test_vendor_without_position_redirected_from_scanner(): void
    {
        $userNoPos = User::factory()->vendor()->create();
        // No vendor record = no position

        $response = $this->actingAs($userNoPos)->get(route('vendor.scanner'));
        $response->assertRedirect(route('vendor.dashboard'));
    }

    public function test_vendor_can_confirm_valid_scan(): void
    {
        $admin = User::factory()->admin()->create();
        $shipment = Shipment::factory()->create([
            'no_rangka' => 'MHFAA8GS4N0000001',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $response = $this->actingAs($this->vendorUser)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => 'MHFAA8GS4N0000001',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('shipment_updates', [
            'shipment_id' => $shipment->id,
            'vendor_id' => $this->vendor->id,
            'position' => 'AT Storage Port',
        ]);

        $this->assertDatabaseHas('scan_histories', [
            'user_id' => $this->vendorUser->id,
            'no_rangka' => 'MHFAA8GS4N0000001',
        ]);
    }

    public function test_confirm_fails_for_invalid_vin_length(): void
    {
        $response = $this->actingAs($this->vendorUser)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => 'SHORT',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('no_rangka');
    }

    public function test_confirm_fails_for_invalid_vin_characters(): void
    {
        $response = $this->actingAs($this->vendorUser)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => 'MHFAA8GI4N0000001', // Contains I
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('no_rangka');
    }

    public function test_confirm_fails_when_vin_not_in_shipments(): void
    {
        $response = $this->actingAs($this->vendorUser)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => 'MHFAA8GS4N9999999',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'No. Rangka tidak ditemukan di data shipment.']);
    }

    public function test_vendor_can_save_unknown_vin_as_pending_after_warning(): void
    {
        $vin = 'MHFAA8GS4N9999999';

        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.scanner.confirm'), ['no_rangka' => $vin])
            ->assertStatus(404)
            ->assertJsonPath('pending_allowed', true);

        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.scanner.confirm'), [
                'no_rangka' => $vin,
                'save_as_pending' => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('pending', true);

        $this->assertDatabaseHas('pending_vins', [
            'no_rangka' => $vin,
            'vendor_id' => $this->vendor->id,
            'position' => 'AT Storage Port',
        ]);
    }

    public function test_dooring_vendor_can_upload_document_from_scan_history(): void
    {
        Storage::fake('r2');
        $admin = User::factory()->admin()->create();
        $shipment = Shipment::factory()->create([
            'no_rangka' => 'MHFAA8GS4N0000001',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        $dooringUser = User::factory()->vendor()->create();
        $dooringVendor = Vendor::create([
            'user_id' => $dooringUser->id,
            'vendor_name' => 'PTD Dooring Test',
            'position' => 'AT PtD (Dooring)',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($dooringUser)
            ->postJson(route('vendor.scanner.confirm'), [
                'no_rangka' => $shipment->no_rangka,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $history = ScanHistory::where('user_id', $dooringUser->id)->firstOrFail();
        $this->assertNull($shipment->shipmentUpdates()->first()->document_path);

        $this->actingAs($dooringUser)
            ->post(route('vendor.history.document.upload', $history), [
                'document' => UploadedFile::fake()->image('surat-jalan.png')->size(5120),
            ])
            ->assertRedirect(route('vendor.history'));

        $update = $shipment->shipmentUpdates()->first();
        $this->assertSame($dooringVendor->id, $update->vendor_id);
        $this->assertNotNull($update->document_path);
        Storage::disk('r2')->assertExists($update->document_path);
    }

    public function test_non_dooring_vendor_cannot_upload_document_from_scan_history(): void
    {
        $history = ScanHistory::create([
            'user_id' => $this->vendorUser->id,
            'no_rangka' => 'MHFAA8GS4N0000001',
            'scan_date' => today(),
        ]);

        $this->actingAs($this->vendorUser)
            ->post(route('vendor.history.document.upload', $history), [
                'document' => UploadedFile::fake()->image('surat-jalan.png'),
            ])
            ->assertForbidden();
    }

    public function test_dooring_vendor_sees_a_clear_message_when_no_document_is_selected(): void
    {
        $admin = User::factory()->admin()->create();
        $dooringUser = User::factory()->vendor()->create();
        Vendor::create([
            'user_id' => $dooringUser->id,
            'vendor_name' => 'PTD Dooring Test',
            'position' => 'AT PtD (Dooring)',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        $history = ScanHistory::create([
            'user_id' => $dooringUser->id,
            'no_rangka' => 'MHFAA8GS4N0000001',
            'scan_date' => today(),
        ]);

        $this->actingAs($dooringUser)
            ->post(route('vendor.history.document.upload', $history))
            ->assertRedirect(route('vendor.history'))
            ->assertSessionHasErrors([
                'document' => 'Pilih foto dokumen terlebih dahulu.',
            ]);
    }

    public function test_confirm_fails_for_duplicate_position_scan(): void
    {
        $admin = User::factory()->admin()->create();
        $shipment = Shipment::factory()->create([
            'no_rangka' => 'MHFAA8GS4N0000001',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // First scan - success
        $this->actingAs($this->vendorUser)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => 'MHFAA8GS4N0000001',
        ]);

        // Second scan - duplicate
        $response = $this->actingAs($this->vendorUser)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => 'MHFAA8GS4N0000001',
        ]);

        $response->assertStatus(409);
    }

    public function test_admin_cannot_access_scanner(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get(route('vendor.scanner'));
        $response->assertStatus(403);
    }

    public function test_vendor_can_view_scan_history(): void
    {
        $response = $this->actingAs($this->vendorUser)->get(route('vendor.history'));
        $response->assertStatus(200);
    }
}
