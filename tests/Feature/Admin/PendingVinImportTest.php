<?php

namespace Tests\Feature\Admin;

use App\Imports\ShipmentImport;
use App\Models\PendingVin;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendingVinImportTest extends TestCase
{
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
        $import->collection(collect([
            collect(['Lokasi', 'No. DO', 'Type Kendaraan', 'No. Rangka', 'No. Engine', 'Warna', 'Asal PDC', 'Kota', 'Tujuan Pengiriman']),
            collect(['Jakarta', 'DO-001', 'Avanza', $vin, 'ENG001', 'Putih', 'PDC Jakarta', 'Jakarta', 'Surabaya']),
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
