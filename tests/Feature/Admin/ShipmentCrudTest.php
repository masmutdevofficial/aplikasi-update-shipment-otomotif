<?php

namespace Tests\Feature\Admin;

use App\Imports\ShipmentImport;
use App\Models\PendingVin;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use App\Support\DsoSla;
use App\Support\ShipmentUploadTemplate;
use Carbon\Carbon;
use Tests\TestCase;

class ShipmentCrudTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
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
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure(['data' => [[
                'at_storage_port',
                'atd_kapal_loading',
                'ata_kapal',
                'ata_storage_port_destination',
                'at_ptd_dooring',
                'sla_actual',
                'sla_customer',
                'sla_result',
                'dwelling_origin',
                'dwelling_destination',
                'delay_days',
                'max_arrival',
                'progress',
            ]]]);
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

    public function test_dso_storage_port_and_vessel_loading_are_optional_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.shipments.create'))
            ->assertOk()
            ->assertSee('Opsional — scan vendor')
            ->assertSee('Boleh dikosongkan; tanggal akan muncul setelah vendor melakukan scan.');

        $this->actingAs($this->admin)
            ->post(route('admin.shipments.store'), $this->validShipmentData([
                'no_rangka' => 'MHFAA8GS4N0000003',
            ]))
            ->assertRedirect(route('admin.shipments.index'))
            ->assertSessionDoesntHaveErrors(['at_storage_port', 'atd_kapal_loading']);

        $this->assertDatabaseHas('shipments', [
            'no_rangka' => 'MHFAA8GS4N0000003',
            'at_storage_port' => null,
            'atd_kapal_loading' => null,
        ]);
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

    public function test_dso_lead_time_and_sla_are_calculated_automatically(): void
    {
        $shipment = Shipment::factory()->create([
            'kota' => 'PONTIANAK',
            'tujuan_pengiriman' => 'PONTIANAK',
            'terima_do' => '2026-07-01',
            'keluar_dari_pdc' => '2026-07-02',
            'at_storage_port' => '2026-07-02',
            'atd_kapal_loading' => '2026-07-03',
            'ata_kapal' => '2026-07-06',
            'ata_storage_port_destination' => '2026-07-06',
            'at_ptd_dooring' => '2026-07-06',
        ]);

        $this->assertSame(1, $shipment->leadTimeDoReleaseToPickup());
        $this->assertSame(0, $shipment->leadTimeStoragePort());
        $this->assertSame(1, $shipment->leadTimeKapalLoading());
        $this->assertSame(1, $shipment->dwellingOrigin());
        $this->assertSame(3, $shipment->leadTimeKapalAboard());
        $this->assertSame(0, $shipment->leadTimeStoragePortDestination());
        $this->assertSame(0, $shipment->leadTimePtdDooring());
        $this->assertSame(0, $shipment->dwellingDestination());
        $this->assertSame(5, $shipment->slaActual());
        $this->assertSame(8, $shipment->slaCustomer());
        $this->assertSame('OTD', $shipment->slaResult());
        $this->assertSame(0, $shipment->delayDays());
        $this->assertSame('2026-07-09', $shipment->maxArrival()?->format('Y-m-d'));
        $this->assertSame('OTD', $shipment->shipmentProgress());
    }

    public function test_dso_delay_days_are_calculated_against_customer_sla(): void
    {
        $shipment = Shipment::factory()->create([
            'kota' => 'PONTIANAK',
            'tujuan_pengiriman' => 'PONTIANAK',
            'terima_do' => '2026-07-01',
            'at_ptd_dooring' => '2026-07-12',
        ]);

        $this->assertSame(11, $shipment->slaActual());
        $this->assertSame(8, $shipment->slaCustomer());
        $this->assertSame('LATE', $shipment->slaResult());
        $this->assertSame(3, $shipment->delayDays());

        $this->actingAs($this->admin)
            ->getJson(route('admin.shipments.data', [
                'length' => 10,
                'search' => ['value' => $shipment->no_rangka],
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'no_rangka' => $shipment->no_rangka,
                'delay_days' => 3,
            ]);
    }

    public function test_dso_ongoing_shipment_uses_today_for_delay_and_dwelling(): void
    {
        Carbon::setTestNow('2026-07-15 12:00:00');

        $shipment = Shipment::factory()->create([
            'kota' => 'PONTIANAK',
            'tujuan_pengiriman' => 'PONTIANAK',
            'terima_do' => '2026-07-01',
            'at_storage_port' => '2026-07-05',
            'atd_kapal_loading' => '2026-07-06',
            'ata_kapal' => '2026-07-10',
            'ata_storage_port_destination' => '2026-07-11',
            'at_ptd_dooring' => null,
        ]);

        $this->assertSame(14, $shipment->slaActual());
        $this->assertSame(8, $shipment->slaCustomer());
        $this->assertSame('LATE', $shipment->slaResult());
        $this->assertSame(6, $shipment->delayDays());
        $this->assertSame(1, $shipment->dwellingOrigin());
        $this->assertSame(4, $shipment->dwellingDestination());
    }

    public function test_dso_dwelling_is_calculated_per_shipment_not_per_city(): void
    {
        $firstShipment = Shipment::factory()->create([
            'kota' => 'PONTIANAK',
            'at_storage_port' => '2026-07-01',
            'atd_kapal_loading' => '2026-07-02',
            'ata_storage_port_destination' => '2026-07-05',
            'at_ptd_dooring' => '2026-07-07',
        ]);
        $secondShipment = Shipment::factory()->create([
            'kota' => 'PONTIANAK',
            'at_storage_port' => '2026-07-01',
            'atd_kapal_loading' => '2026-07-04',
            'ata_storage_port_destination' => '2026-07-05',
            'at_ptd_dooring' => '2026-07-10',
        ]);

        $this->assertSame(1, $firstShipment->dwellingOrigin());
        $this->assertSame(2, $firstShipment->dwellingDestination());
        $this->assertSame(3, $secondShipment->dwellingOrigin());
        $this->assertSame(5, $secondShipment->dwellingDestination());

        $response = $this->actingAs($this->admin)->getJson(route('admin.shipments.data', [
            'length' => 10,
            'search' => ['value' => 'PONTIANAK'],
        ]));

        $response->assertOk();
        $rows = collect($response->json('data'))->keyBy('no_rangka');

        $this->assertSame(1, $rows[$firstShipment->no_rangka]['dwelling_origin']);
        $this->assertSame(2, $rows[$firstShipment->no_rangka]['dwelling_destination']);
        $this->assertSame(3, $rows[$secondShipment->no_rangka]['dwelling_origin']);
        $this->assertSame(5, $rows[$secondShipment->no_rangka]['dwelling_destination']);
    }

    public function test_dso_index_shows_aggregate_late_percentage(): void
    {
        Shipment::factory()->create([
            'kota' => 'PONTIANAK',
            'terima_do' => '2026-07-01',
            'at_ptd_dooring' => '2026-07-06',
        ]);
        Shipment::factory()->create([
            'kota' => 'PONTIANAK',
            'terima_do' => '2026-07-01',
            'at_ptd_dooring' => '2026-07-12',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.shipments.index'));

        $response->assertOk();
        $response->assertViewHas('delayStats', fn (array $stats) => $stats['completed'] >= 2
            && $stats['late'] >= 1
            && $stats['percentage'] > 0
        );
    }

    public function test_dso_excel_import_accepts_actual_time_columns(): void
    {
        $import = new ShipmentImport($this->admin->id);
        $headers = ShipmentUploadTemplate::dsoHeadings();
        $row = array_fill(0, count($headers), null);
        $values = [
            'Lokasi' => 'D720',
            'Type Kendaraan' => 'SIGRA',
            'No. Rangka' => 'MHKS6GJ6JTJ200820',
            'No. Engine' => '3NR5A03703',
            'Warna' => 'BRONZE METALLIC',
            'Asal PDC' => 'KARAWANG',
            'Kota' => 'PONTIANAK',
            'Tujuan Pengiriman' => 'PONTIANAK',
            'Terima DO' => '01-Jul-26',
            'Keluar dari PDC' => '02-Jul-26',
            'AT Storage Port' => '02-Jul-26',
            'ATD Kapal (Loading)' => '03-Jul-26',
            'ATA Kapal' => '06-Jul-26',
            'ATA Storage Port (Destination)' => '06-Jul-26',
            'AT PtD (Dooring)' => '06-Jul-26',
        ];

        foreach ($values as $header => $value) {
            $row[array_search($header, $headers, true)] = $value;
        }

        $import->collection(collect([
            collect($headers),
            collect($row),
        ]));

        $this->assertSame(1, $import->importedCount);
        $this->assertDatabaseHas('shipments', [
            'no_rangka' => 'MHKS6GJ6JTJ200820',
            'at_storage_port' => '2026-07-02',
            'atd_kapal_loading' => '2026-07-03',
            'ata_kapal' => '2026-07-06',
            'ata_storage_port_destination' => '2026-07-06',
            'at_ptd_dooring' => '2026-07-06',
        ]);
    }

    public function test_dso_excel_import_accepts_do_hold_in_milestone_columns(): void
    {
        $import = new ShipmentImport($this->admin->id);
        $headers = ShipmentUploadTemplate::dsoHeadings();
        $row = array_fill(0, count($headers), null);
        $values = [
            'Lokasi' => 'D720',
            'Type Kendaraan' => 'SIGRA',
            'No. Rangka' => 'MHKS6GJ6JTJ200821',
            'No. Engine' => '3NR5A03704',
            'Warna' => 'PUTIH',
            'Asal PDC' => 'KARAWANG',
            'Kota' => 'PONTIANAK',
            'Tujuan Pengiriman' => 'PONTIANAK',
            'Terima DO' => '01-Jul-26',
            'Keluar dari PDC' => 'DO HOLD',
            'Nama Kapal' => 'DO HOLD',
            'Keberangkatan Kapal' => 'DO HOLD',
            'AT Storage Port' => 'DO HOLD',
            'ATD Kapal (Loading)' => 'DO HOLD',
            'ATA Kapal' => 'DO HOLD',
            'ATA Storage Port (Destination)' => 'DO HOLD',
            'AT PtD (Dooring)' => 'DO HOLD',
        ];

        foreach ($values as $header => $value) {
            $row[array_search($header, $headers, true)] = $value;
        }

        $import->collection(collect([collect($headers), collect($row)]));

        $this->assertSame(1, $import->importedCount);
        $this->assertSame([], $import->errors);
        $this->assertDatabaseHas('shipments', [
            'no_rangka' => 'MHKS6GJ6JTJ200821',
            'do_hold' => true,
            'keluar_dari_pdc' => null,
            'at_ptd_dooring' => null,
        ]);

        $shipment = Shipment::query()->where('no_rangka', 'MHKS6GJ6JTJ200821')->firstOrFail();
        $this->assertTrue($shipment->isDoHold());
        $this->assertNull($shipment->slaActual());
        $this->assertSame('DO HOLD', $shipment->slaResult());
        $this->assertSame('DO HOLD', $shipment->shipmentProgress());
        $this->assertSame(1, DsoSla::doHoldStatistics()['total']);

        $this->actingAs($this->admin)
            ->getJson(route('admin.shipments.data', [
                'length' => 10,
                'search' => ['value' => 'MHKS6GJ6JTJ200821'],
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'keluar_dari_pdc' => 'DO HOLD',
                'nama_kapal' => 'DO HOLD',
                'keberangkatan_kapal' => 'DO HOLD',
                'at_storage_port' => 'DO HOLD',
                'atd_kapal_loading' => 'DO HOLD',
                'ata_kapal' => 'DO HOLD',
                'ata_storage_port_destination' => 'DO HOLD',
                'at_ptd_dooring' => 'DO HOLD',
                'sla_result' => 'DO HOLD',
                'progress' => 'DO HOLD',
            ]);
    }
}
