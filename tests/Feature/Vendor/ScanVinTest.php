<?php

namespace Tests\Feature\Vendor;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\TsoShipment;
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

    public function test_dooring_vendor_can_upload_document_for_any_dso_shipment(): void
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
            ->get(route('vendor.documents.index'))
            ->assertOk()
            ->assertSee('Upload Dokumen')
            ->assertSee('No. Rangka');

        $this->actingAs($dooringUser)
            ->postJson(route('vendor.documents.data'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.identifier', $shipment->no_rangka);

        $this->actingAs($dooringUser)
            ->post(route('vendor.documents.upload', ['dso', $shipment->id]), [
                'document' => UploadedFile::fake()->image('surat-jalan.png')->size(10240),
            ])
            ->assertRedirect(route('vendor.documents.index'));

        $document = ShipmentDocument::query()->firstOrFail();
        $this->assertSame($dooringVendor->id, $document->vendor_id);
        $this->assertSame($shipment->id, $document->documentable_id);
        Storage::disk('r2')->assertExists($document->document_path);
    }

    public function test_non_final_position_vendor_cannot_access_document_upload(): void
    {
        $shipment = Shipment::factory()->create();

        $this->actingAs($this->vendorUser)
            ->get(route('vendor.documents.index'))
            ->assertForbidden();

        $this->actingAs($this->vendorUser)
            ->post(route('vendor.documents.upload', ['dso', $shipment->id]), [
                'document' => UploadedFile::fake()->image('surat-jalan.png'),
            ])
            ->assertForbidden();
    }

    public function test_final_position_vendor_sees_a_clear_message_when_no_document_is_selected(): void
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
        $shipment = Shipment::factory()->create();

        $this->actingAs($dooringUser)
            ->postJson(route('vendor.documents.upload', ['dso', $shipment->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('document')
            ->assertJsonPath('errors.document.0', 'Pilih foto dokumen terlebih dahulu.');
    }

    public function test_iso_final_vendor_sees_iso_darat_and_iso_laut_identities(): void
    {
        $admin = User::factory()->admin()->create();
        $isoUser = User::factory()->vendor()->create();
        Vendor::create([
            'user_id' => $isoUser->id,
            'vendor_name' => 'ISO PTD DTD Test',
            'position' => 'AT PTD/DTD',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        IsoDaratShipment::create(['no_spb' => 'SPB-DOCUMENT-001']);
        IsoLautShipment::create(['noka' => 'NOKA-DOCUMENT-001']);

        $this->actingAs($isoUser)
            ->get(route('vendor.documents.index'))
            ->assertOk()
            ->assertSee('No. SPB / NOKA');

        $this->actingAs($isoUser)
            ->postJson(route('vendor.documents.data'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonFragment(['identifier' => 'SPB-DOCUMENT-001'])
            ->assertJsonFragment(['identifier' => 'NOKA-DOCUMENT-001']);
    }

    public function test_tso_final_vendor_only_sees_tso_shipments(): void
    {
        $admin = User::factory()->admin()->create();
        $tsoUser = User::factory()->vendor()->create();
        Vendor::create([
            'user_id' => $tsoUser->id,
            'vendor_name' => 'TSO Port to Door Test',
            'position' => 'Port to Door (PTD)',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        TsoShipment::create(['no_rangka' => 'TSO-DOCUMENT-001']);
        Shipment::factory()->create(['no_rangka' => 'DSO-NOT-VISIBLE01']);

        $this->actingAs($tsoUser)
            ->postJson(route('vendor.documents.data'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.identifier', 'TSO-DOCUMENT-001')
            ->assertJsonMissing(['identifier' => 'DSO-NOT-VISIBLE01']);
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
        $response->assertOk()
            ->assertSee('Riwayat Scan')
            ->assertDontSee('<th>Dokumen</th>', false);
    }
}
