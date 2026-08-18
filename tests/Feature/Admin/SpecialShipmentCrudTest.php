<?php

namespace Tests\Feature\Admin;

use App\Imports\SpecialShipmentImport;
use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\TsoShipment;
use App\Models\User;
use App\Support\SpecialShipmentType;
use App\Support\SpecialShipmentPerformance;
use Tests\TestCase;

class SpecialShipmentCrudTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_each_shipment_type_has_a_separate_index_page(): void
    {
        foreach (['tso', 'iso-darat', 'iso-laut'] as $type) {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.special-shipments.index', $type));

            $response->assertOk();
            $response->assertViewIs('admin.special-shipments.index');
        }
    }

    public function test_special_shipment_datatable_only_returns_requested_page(): void
    {
        for ($index = 1; $index <= 15; $index++) {
            TsoShipment::create(['no_rangka' => 'SERVER-SIDE-' . $index]);
        }

        $response = $this->actingAs($this->admin)->getJson(route('admin.special-shipments.data', [
            'type' => 'tso',
            'draw' => 2,
            'start' => 0,
            'length' => 10,
            'columns' => [
                ['name' => 'id'],
                ['name' => 'row_number'],
                ['name' => 'unit_type'],
            ],
            'order' => [['column' => 2, 'dir' => 'asc']],
            'search' => ['value' => ''],
        ]));

        $response->assertOk()
            ->assertJsonPath('draw', 2)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure(['data' => [[
                'sla_actual',
                'sla_result',
                'delay_percentage',
                'max_arrival',
                'progress',
            ]]);
        $this->assertGreaterThanOrEqual(15, $response->json('recordsTotal'));
    }

    public function test_admin_can_create_update_and_delete_tso_data(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.special-shipments.store', 'tso'), [
                'unit_type' => 'Avanza',
                'no_rangka' => 'TESTVIN1234567890',
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'tso'));

        $shipment = TsoShipment::query()->where('no_rangka', 'TESTVIN1234567890')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.special-shipments.update', ['tso', $shipment->id]), [
                'unit_type' => 'New Avanza',
                'no_rangka' => $shipment->no_rangka,
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'tso'));

        $this->assertSame('New Avanza', $shipment->fresh()->unit_type);

        $this->actingAs($this->admin)
            ->delete(route('admin.special-shipments.destroy', ['tso', $shipment->id]))
            ->assertRedirect(route('admin.special-shipments.index', 'tso'));

        $this->assertDatabaseMissing('tso_shipments', ['id' => $shipment->id]);
    }

    public function test_admin_can_create_iso_darat_and_iso_laut_data(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.special-shipments.store', 'iso-darat'), [
                'no_so_booking' => 'TEST-SO-001',
                'no_spb' => 'TEST-SPB-001',
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'iso-darat'));

        $this->actingAs($this->admin)
            ->post(route('admin.special-shipments.store', 'iso-laut'), [
                'no_booking_dtp' => 'TEST-BOOKING-001',
                'noka' => 'TEST-NOKA-001',
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'iso-laut'));

        $this->assertDatabaseHas('iso_darat_shipments', ['no_spb' => 'TEST-SPB-001']);
        $this->assertDatabaseHas('iso_laut_shipments', ['noka' => 'TEST-NOKA-001']);
    }

    public function test_admin_can_bulk_delete_special_shipments(): void
    {
        $first = IsoDaratShipment::create(['no_spb' => 'VIN-001']);
        $second = IsoDaratShipment::create(['no_spb' => 'VIN-002']);

        $this->actingAs($this->admin)
            ->delete(route('admin.special-shipments.bulk-destroy', 'iso-darat'), [
                'shipment_ids' => [$first->id, $second->id],
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'iso-darat'));

        $this->assertDatabaseMissing('iso_darat_shipments', ['id' => $first->id]);
        $this->assertDatabaseMissing('iso_darat_shipments', ['id' => $second->id]);
    }

    public function test_special_import_adds_and_updates_rows_by_identity(): void
    {
        $import = new SpecialShipmentImport(SpecialShipmentType::get('iso-laut'));
        $header = array_column(SpecialShipmentType::get('iso-laut')['fields'], 'label');
        $firstRow = array_fill(0, count($header), null);
        $firstRow[array_search('NOKA', $header, true)] = 'TEST-NOKA-IMPORT-001';
        $firstRow[array_search('Origin', $header, true)] = 'KARAWANG BARAT';

        $import->collection(collect([$header, $firstRow]));

        $updatedRow = $firstRow;
        $updatedRow[array_search('Origin', $header, true)] = 'KARAWANG TIMUR';
        $import->collection(collect([$header, $updatedRow]));

        $this->assertSame(1, $import->importedCount);
        $this->assertSame(1, $import->updatedCount);
        $this->assertDatabaseHas('iso_laut_shipments', [
            'noka' => 'TEST-NOKA-IMPORT-001',
            'origin' => 'KARAWANG TIMUR',
        ]);
    }

    public function test_template_can_be_downloaded_for_each_special_type(): void
    {
        foreach (['tso', 'iso-darat', 'iso-laut'] as $type) {
            $this->actingAs($this->admin)
                ->get(route('admin.special-shipments.template', $type))
                ->assertOk()
                ->assertHeader('content-disposition');
        }
    }

    public function test_import_infers_year_for_short_dates_in_the_same_row(): void
    {
        $config = SpecialShipmentType::get('iso-darat');
        $headers = array_column($config['fields'], 'label');
        $row = array_fill(0, count($headers), null);
        $row[array_search('NO SPB', $headers, true)] = 'TEST-SPB-DATE-001';
        $row[array_search('Terima DO', $headers, true)] = '2-Sep-25';
        $row[array_search('Keluar dari PDC', $headers, true)] = '02-Sep';
        $row[array_search('AT PTD/DTD', $headers, true)] = '03-Sep';

        (new SpecialShipmentImport($config))->collection(collect([$headers, $row]));

        $this->assertDatabaseHas('iso_darat_shipments', [
            'no_spb' => 'TEST-SPB-DATE-001',
            'terima_do' => '2025-09-02',
            'keluar_dari_pdc' => '2025-09-02',
            'at_ptd_dtd' => '2025-09-03',
        ]);
    }

    public function test_vendor_cannot_access_special_shipment_pages(): void
    {
        $vendor = User::factory()->vendor()->create();

        $this->actingAs($vendor)
            ->get(route('admin.special-shipments.index', 'tso'))
            ->assertForbidden();
    }

    public function test_tso_and_iso_performance_is_calculated_from_their_milestones(): void
    {
        $tso = TsoShipment::create([
            'do_date' => '2026-07-01',
            'pu_date' => '2026-07-02',
            'door_to_port' => '2026-07-03',
            'port_to_port' => '2026-07-06',
            'port_to_door' => '2026-07-12',
            'sla_customer' => 8,
        ]);
        $darat = IsoDaratShipment::create([
            'terima_do' => '2026-07-01',
            'keluar_dari_pdc' => '2026-07-02',
            'at_ptd_dtd' => '2026-07-06',
            'sla_customer' => 5,
        ]);
        $laut = IsoLautShipment::create([
            'terima_do' => '2026-07-01',
            'keluar_dari_pdc' => '2026-07-02',
            'at_storage_port' => '2026-07-02',
            'atd_kapal_loading' => '2026-07-03',
            'ata_kapal' => '2026-07-06',
            'ata_storage_port_destination' => '2026-07-06',
            'at_ptd_dtd' => '2026-07-07',
            'sla_customer' => 6,
        ]);

        $tsoMetrics = SpecialShipmentPerformance::calculate('tso', $tso);
        $daratMetrics = SpecialShipmentPerformance::calculate('iso-darat', $darat);
        $lautMetrics = SpecialShipmentPerformance::calculate('iso-laut', $laut);

        $this->assertSame(11, $tsoMetrics['sla_actual']);
        $this->assertSame('LATE', $tsoMetrics['sla_result']);
        $this->assertSame(37.5, $tsoMetrics['delay_percentage']);
        $this->assertSame(5, $daratMetrics['sla_actual']);
        $this->assertSame('OTD', $daratMetrics['sla_result']);
        $this->assertSame(6, $lautMetrics['sla_actual']);
        $this->assertSame('OTD', $lautMetrics['sla_result']);
    }
}
