<?php

namespace Tests\Feature\Admin;

use App\Models\Shipment;
use App\Models\PendingVin;
use App\Models\User;
use App\Models\Vendor;
use Tests\TestCase;

class ShipmentCrudTest extends TestCase
{

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function validShipmentData(array $overrides = []): array
    {
        return array_merge([
            'lokasi' => 'Jakarta',
            'no_do' => 'DO-001',
            'type_kendaraan' => 'Avanza',
            'no_rangka' => 'MHFAA8GS4N0000001',
            'no_engine' => 'ENG001',
            'warna' => 'Putih',
            'asal_pdc' => 'PDC Jakarta',
            'kota' => 'Jakarta',
            'tujuan_pengiriman' => 'Surabaya',
            'terima_do' => '2026-04-01',
            'keluar_dari_pdc' => '2026-04-02',
            'nama_kapal' => 'KM Laut Jaya',
            'keberangkatan_kapal' => '2026-04-03',
        ], $overrides);
    }

    public function test_admin_can_view_shipments_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shipments.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.shipments.index');
    }

    public function test_shipment_datatable_only_returns_requested_page(): void
    {
        Shipment::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)->getJson(route('admin.shipments.data', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                ['name' => 'id'],
                ['name' => 'row_number'],
                ['name' => 'lokasi'],
            ],
            'order' => [['column' => 2, 'dir' => 'asc']],
            'search' => ['value' => ''],
        ]));

        $response->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonCount(10, 'data');
        $this->assertGreaterThanOrEqual(15, $response->json('recordsTotal'));
    }

    public function test_admin_can_create_shipment(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.shipments.store'),
            $this->validShipmentData()
        );

        $response->assertRedirect(route('admin.shipments.index'));
        $this->assertDatabaseHas('shipments', ['no_rangka' => 'MHFAA8GS4N0000001']);
    }

    public function test_manual_shipment_creation_matches_pending_vin(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'vendor_name' => 'PT Vendor Pending',
            'position' => 'AT Storage Port',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        PendingVin::create([
            'no_rangka' => 'MHFAA8GS4N0000001',
            'vendor_id' => $vendor->id,
            'position' => $vendor->position,
            'scan_date' => '2026-08-06',
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.shipments.store'), $this->validShipmentData())
            ->assertRedirect(route('admin.shipments.index'));

        $shipment = Shipment::where('no_rangka', 'MHFAA8GS4N0000001')->firstOrFail();

        $this->assertDatabaseMissing('pending_vins', ['no_rangka' => $shipment->no_rangka]);
        $this->assertDatabaseHas('shipment_updates', [
            'shipment_id' => $shipment->id,
            'vendor_id' => $vendor->id,
            'position' => 'AT Storage Port',
        ]);
    }

    public function test_vin_must_be_exactly_17_characters(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.shipments.store'),
            $this->validShipmentData(['no_rangka' => 'SHORT123'])
        );

        $response->assertSessionHasErrors('no_rangka');
    }

    public function test_vin_must_match_valid_pattern(): void
    {
        // VIN cannot contain I, O, Q
        $response = $this->actingAs($this->admin)->post(
            route('admin.shipments.store'),
            $this->validShipmentData(['no_rangka' => 'MHFAA8GI4N0000001'])
        );

        $response->assertSessionHasErrors('no_rangka');
    }

    public function test_vin_must_be_unique(): void
    {
        Shipment::factory()->create(['no_rangka' => 'MHFAA8GS4N0000001']);

        $response = $this->actingAs($this->admin)->post(
            route('admin.shipments.store'),
            $this->validShipmentData()
        );

        $response->assertSessionHasErrors('no_rangka');
    }

    public function test_admin_can_update_shipment(): void
    {
        $shipment = Shipment::factory()->create(['created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.shipments.update', $shipment),
            $this->validShipmentData([
                'no_rangka' => $shipment->no_rangka,
                'warna' => 'Hitam',
            ])
        );

        $response->assertRedirect(route('admin.shipments.index'));
        $this->assertEquals('Hitam', $shipment->fresh()->warna);
    }

    public function test_admin_can_delete_shipment(): void
    {
        $shipment = Shipment::factory()->create(['created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.shipments.destroy', $shipment));

        $response->assertRedirect(route('admin.shipments.index'));
        $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
    }

    public function test_admin_can_bulk_delete_selected_shipments(): void
    {
        $shipments = Shipment::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.shipments.bulk-destroy'), [
            'shipment_ids' => $shipments->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('admin.shipments.index'));
        $this->assertDatabaseMissing('shipments', ['id' => $shipments[0]->id]);
        $this->assertDatabaseMissing('shipments', ['id' => $shipments[1]->id]);
    }

    public function test_vendor_cannot_access_shipments(): void
    {
        $vendor = User::factory()->vendor()->create();

        $response = $this->actingAs($vendor)->get(route('admin.shipments.index'));
        $response->assertStatus(403);
    }

    public function test_do_and_shipment_dates_are_optional(): void
    {
        $data = $this->validShipmentData([
            'no_do' => '',
            'terima_do' => '',
            'keluar_dari_pdc' => '',
            'nama_kapal' => '',
            'keberangkatan_kapal' => '',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.shipments.store'), $data);

        $response->assertRedirect(route('admin.shipments.index'));
        $this->assertDatabaseHas('shipments', [
            'no_rangka' => 'MHFAA8GS4N0000001',
            'no_do' => null,
            'terima_do' => null,
            'keluar_dari_pdc' => null,
            'nama_kapal' => null,
            'keberangkatan_kapal' => null,
        ]);
    }
}
