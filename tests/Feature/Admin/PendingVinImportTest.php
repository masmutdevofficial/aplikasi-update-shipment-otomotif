<?php

namespace Tests\Feature\Admin;

use App\Imports\ShipmentImport;
use App\Models\PendingVin;
use App\Models\User;
use App\Models\Vendor;
use App\Support\ShipmentUploadTemplate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendingVinImportTest extends TestCase
{
    public function test_empty_pending_vin_table_is_left_for_datatables_to_render(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.pending-vins.index'))
            ->assertOk()
            ->assertSee('Foto Scan')
            ->assertDontSee('colspan="6"', false)
            ->assertSee("emptyTable: 'Tidak ada VIN pending'", false);
    }

    public function test_admin_can_delete_pending_vin_and_its_r2_document(): void
    {
        Storage::fake('r2');
        config(['filesystems.document_disk' => 'r2']);

        $admin = User::factory()->admin()->create();
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PTD Dooring Test',
            'position' => 'AT PtD (Dooring)',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        $vin = 'MHFAA8GS4N0000002';
        $documentPath = "shipment-documents/{$vin}/proof.png";
        Storage::disk('r2')->put($documentPath, 'test-image');

        $pendingVin = PendingVin::create([
            'no_rangka' => $vin,
            'vendor_id' => $vendor->id,
            'position' => $vendor->position,
            'scan_date' => '2026-08-24',
            'document_path' => $documentPath,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pending-vins.index'))
            ->assertOk()
            ->assertSee('Hapus');

        $this->actingAs($admin)
            ->delete(route('admin.pending-vins.destroy', $pendingVin))
            ->assertRedirect(route('admin.pending-vins.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pending_vins', ['id' => $pendingVin->id]);
        Storage::disk('r2')->assertMissing($documentPath);
    }

    public function test_import_matches_pending_vin_to_new_shipment(): void
    {
        Storage::fake('r2');
        $admin = User::factory()->admin()->create();
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PTD Dooring Test',
            'position' => 'AT PtD (Dooring)',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        $vin = 'MHFAA8GS4N0000001';
        $documentPath = 'shipment-documents/'.$vin.'/proof.png';
        Storage::disk('r2')->put($documentPath, 'test-image');

        PendingVin::create([
            'no_rangka' => $vin,
            'vendor_id' => $vendor->id,
            'position' => $vendor->position,
            'scan_date' => '2026-07-29',
            'document_path' => $documentPath,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
        ]);

        $import = new ShipmentImport($admin->id);
        $headers = ShipmentUploadTemplate::dsoHeadings();
        $row = array_fill(0, count($headers), null);
        $values = [
            'Lokasi' => 'Jakarta',
            'No. DO' => 'DO-001',
            'Type Kendaraan' => 'Avanza',
            'No. Rangka' => $vin,
            'No. Engine' => 'ENG001',
            'Warna' => 'Putih',
            'Asal PDC' => 'PDC Jakarta',
            'Kota' => 'Jakarta',
            'Tujuan Pengiriman' => 'Surabaya',
        ];

        foreach ($values as $header => $value) {
            $row[array_search($header, $headers, true)] = $value;
        }

        $import->collection(collect([
            collect($headers),
            collect($row),
        ]));

        $this->assertSame(1, $import->importedCount);
        $this->assertSame(1, $import->matchedPendingCount);
        $this->assertDatabaseMissing('pending_vins', ['no_rangka' => $vin]);
        $this->assertDatabaseHas('shipment_updates', [
            'position' => 'AT PtD (Dooring)',
            'vendor_id' => $vendor->id,
            'document_path' => $documentPath,
        ]);
    }
}
