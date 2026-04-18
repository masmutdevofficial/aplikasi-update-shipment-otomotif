<?php

namespace Tests\Feature\Admin;

use App\Models\Shipment;
use App\Models\User;
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

    public function test_admin_can_create_shipment(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.shipments.store'),
            $this->validShipmentData()
        );

        $response->assertRedirect(route('admin.shipments.index'));
        $this->assertDatabaseHas('shipments', ['no_rangka' => 'MHFAA8GS4N0000001']);
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

    public function test_vendor_cannot_access_shipments(): void
    {
        $vendor = User::factory()->vendor()->create();

        $response = $this->actingAs($vendor)->get(route('admin.shipments.index'));
        $response->assertStatus(403);
    }

    public function test_dates_are_required(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.shipments.store'),
            $this->validShipmentData(['terima_do' => '', 'keluar_dari_pdc' => ''])
        );

        $response->assertSessionHasErrors(['terima_do', 'keluar_dari_pdc']);
    }
}
