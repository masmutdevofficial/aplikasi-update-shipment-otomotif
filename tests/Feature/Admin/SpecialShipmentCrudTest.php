<?php

namespace Tests\Feature\Admin;

use App\Exports\SpecialShipmentTemplateExport;
use App\Imports\SpecialShipmentImport;
use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\TsoShipment;
use App\Models\User;
use App\Support\IsoSla;
use App\Support\SpecialShipmentPerformance;
use App\Support\SpecialShipmentType;
use App\Support\ShipmentUploadTemplate;
use Carbon\Carbon;
use Tests\TestCase;

class SpecialShipmentCrudTest extends TestCase
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

    public function test_each_shipment_type_has_a_separate_index_page(): void
    {
        foreach (['tso', 'iso-darat', 'iso-laut'] as $type) {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.special-shipments.index', $type));

            $response->assertOk();
            $response->assertViewIs('admin.special-shipments.index');
        }
    }

    public function test_iso_pages_show_editable_customer_sla_reference_cards(): void
    {
        foreach (['iso-darat', 'iso-laut'] as $type) {
            $this->actingAs($this->admin)
                ->get(route('admin.special-shipments.index', $type))
                ->assertOk()
                ->assertSee('Referensi SLA Customer')
                ->assertSee('Simpan Referensi SLA')
                ->assertSee('name="sla_customer[', false)
                ->assertSee('name="sla_stages[', false);
        }

        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.index', 'tso'))
            ->assertOk()
            ->assertDontSee('Referensi SLA Customer');
    }

    public function test_admin_can_update_iso_customer_sla_and_use_it_in_performance(): void
    {
        $customers = collect(IsoSla::targets()['iso-darat'])
            ->map(fn (array $target) => $target['customer'])
            ->all();
        $customers['PADANG'] = 9;
        $daratStages = collect(IsoSla::targets()['iso-darat'])
            ->map(fn (array $target) => ['ptd_dooring' => $target['stages']['ptd_dooring']])
            ->all();
        $daratStages['PADANG']['ptd_dooring'] = 7;

        $this->actingAs($this->admin)
            ->put(route('admin.special-shipments.sla-customer.update', 'iso-darat'), [
                'sla_customer' => $customers,
                'sla_stages' => $daratStages,
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'iso-darat'))
            ->assertSessionHas('success');

        $shipment = IsoDaratShipment::create([
            'destination' => 'Padang',
            'terima_do' => '2026-07-01',
            'at_ptd_dtd' => '2026-07-10',
            'sla_customer' => 99,
        ]);
        $metrics = SpecialShipmentPerformance::calculate('iso-darat', $shipment);

        $this->assertSame(9, IsoSla::customerFor('iso-darat', 'Padang'));
        $this->assertSame(7, IsoSla::targetFor('iso-darat', 'Padang')['stages']['ptd_dooring']);
        $this->assertSame(9, $metrics['sla_customer']);
        $this->assertSame('OTD', $metrics['sla_result']);
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => 'iso_sla_customer_iso_darat',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => 'iso_sla_stages_iso_darat',
        ]);

        $lautCustomers = collect(IsoSla::targets()['iso-laut'])
            ->map(fn (array $target) => $target['customer'])
            ->all();
        $lautCustomers['SAMARINDA'] = 10;
        $lautStages = collect(IsoSla::targets()['iso-laut'])
            ->map(fn (array $target) => $target['stages'])
            ->all();
        $lautStages['SAMARINDA']['storage_port'] = 8;

        $this->actingAs($this->admin)
            ->put(route('admin.special-shipments.sla-customer.update', 'iso-laut'), [
                'sla_customer' => $lautCustomers,
                'sla_stages' => $lautStages,
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'iso-laut'));

        $lautShipment = IsoLautShipment::create([
            'destination' => 'Samarinda',
            'terima_do' => '2026-07-01',
            'at_ptd_dtd' => '2026-07-11',
        ]);
        $lautMetrics = SpecialShipmentPerformance::calculate('iso-laut', $lautShipment);

        $this->assertSame(10, $lautMetrics['sla_customer']);
        $this->assertSame(8, IsoSla::targetFor('iso-laut', 'Samarinda')['stages']['storage_port']);
        $this->assertSame('OTD', $lautMetrics['sla_result']);
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => 'iso_sla_customer_iso_laut',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => 'iso_sla_stages_iso_laut',
        ]);
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
                'delay_days',
                'max_arrival',
                'progress',
            ]]]);
        $this->assertGreaterThanOrEqual(15, $response->json('recordsTotal'));
    }

    public function test_iso_laut_datatable_accepts_its_large_payload_via_post(): void
    {
        IsoLautShipment::create([
            'source_no' => 1,
            'no_booking_dtp' => 'POST-DATATABLE-001',
            'noka' => 'POST-NOKA-001',
        ]);

        $config = SpecialShipmentType::get('iso-laut');
        $fieldNames = array_keys($config['fields']);
        $columns = collect(array_merge(
            ['id'],
            $fieldNames,
            array_keys($config['performance']['stages']),
            ['sla_actual', 'sla_result', 'delay_days', 'max_arrival', 'progress', 'actions'],
        ))
            ->map(fn (string $column) => [
                'data' => $column,
                'name' => $column,
                'searchable' => in_array($column, $fieldNames, true),
                'orderable' => in_array($column, $fieldNames, true),
                'search' => ['value' => '', 'regex' => false],
            ])
            ->values()
            ->all();

        $response = $this->actingAs($this->admin)->postJson(
            route('admin.special-shipments.data', 'iso-laut'),
            [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => $columns,
                'order' => [['column' => 1, 'dir' => 'asc']],
                'search' => ['value' => '', 'regex' => false],
            ],
        );

        $response->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonFragment([
                'no_booking_dtp' => 'POST-DATATABLE-001',
                'noka' => 'POST-NOKA-001',
            ]);
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

    public function test_iso_laut_storage_port_and_vessel_loading_are_optional_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.create', 'iso-laut'))
            ->assertOk()
            ->assertSee('Opsional — scan vendor')
            ->assertSee('Boleh dikosongkan; tanggal akan muncul setelah vendor melakukan scan.');

        $this->actingAs($this->admin)
            ->post(route('admin.special-shipments.store', 'iso-laut'), [
                'noka' => 'OPTIONAL-SCAN-NOKA',
                'terima_do' => '2026-08-24',
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'iso-laut'))
            ->assertSessionDoesntHaveErrors(['at_storage_port', 'atd_kapal_loading']);

        $this->assertDatabaseHas('iso_laut_shipments', [
            'noka' => 'OPTIONAL-SCAN-NOKA',
            'at_storage_port' => null,
            'atd_kapal_loading' => null,
        ]);
    }

    public function test_admin_can_add_iso_darat_driver_number_manually_after_upload(): void
    {
        $shipment = IsoDaratShipment::create(['no_spb' => 'DRIVER-SPB-001']);

        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.create', 'iso-darat'))
            ->assertOk()
            ->assertDontSee('Nomor Driver');

        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.edit', ['iso-darat', $shipment->id]))
            ->assertOk()
            ->assertSee('Nomor Driver');

        $this->actingAs($this->admin)
            ->put(route('admin.special-shipments.update', ['iso-darat', $shipment->id]), [
                'nomor_driver' => '0812-3456-7890',
            ])
            ->assertRedirect(route('admin.special-shipments.index', 'iso-darat'));

        $this->assertDatabaseHas('iso_darat_shipments', [
            'id' => $shipment->id,
            'nomor_driver' => '0812-3456-7890',
        ]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.special-shipments.data', ['type' => 'iso-darat', 'length' => 10]))
            ->assertOk()
            ->assertJsonFragment(['nomor_driver' => '0812-3456-7890']);
    }

    public function test_iso_darat_driver_number_is_not_part_of_upload_template_or_import(): void
    {
        $config = SpecialShipmentType::get('iso-darat');
        $this->assertNotContains('Nomor Driver', (new SpecialShipmentTemplateExport('iso-darat', $config))->headings());

        $shipment = IsoDaratShipment::create([
            'no_spb' => 'DRIVER-IMPORT-001',
            'nomor_driver' => '081111111111',
        ]);

        $import = new SpecialShipmentImport($config);
        $import->collection(collect([
            ['NO SPB', 'Nomor Driver'],
            ['DRIVER-IMPORT-001', '089999999999'],
        ]));

        $this->assertTrue($import->invalidTemplate);
        $this->assertSame('081111111111', $shipment->fresh()->nomor_driver);
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
        $header = ShipmentUploadTemplate::specialHeadings(SpecialShipmentType::get('iso-laut'));
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

    public function test_iso_laut_import_normalizes_excel_serial_at_ptd_dtd(): void
    {
        $config = SpecialShipmentType::get('iso-laut');
        $headers = ShipmentUploadTemplate::specialHeadings($config);
        $row = array_fill(0, count($headers), null);
        $row[array_search('NOKA', $headers, true)] = 'TEST-NOKA-EXCEL-DATE-001';
        $row[array_search('Terima DO', $headers, true)] = 46175;
        $row[array_search('AT PTD/DTD', $headers, true)] = 46182;

        (new SpecialShipmentImport($config))->collection(collect([$headers, $row]));

        $this->assertDatabaseHas('iso_laut_shipments', [
            'noka' => 'TEST-NOKA-EXCEL-DATE-001',
            'at_ptd_dtd' => '2026-06-09',
        ]);
        $this->assertSame(
            '2026-06-02',
            IsoLautShipment::query()
                ->where('noka', 'TEST-NOKA-EXCEL-DATE-001')
                ->firstOrFail()
                ->terima_do
                ->format('Y-m-d'),
        );
    }

    public function test_iso_laut_existing_excel_serial_is_read_as_a_date(): void
    {
        $shipment = IsoLautShipment::create([
            'noka' => 'TEST-NOKA-LEGACY-SERIAL-001',
            'terima_do' => '2026-06-02',
            'at_ptd_dtd' => '46182',
            'sla_customer' => 7,
        ]);

        $this->assertSame('2026-06-09', $shipment->fresh()->at_ptd_dtd);

        $metrics = SpecialShipmentPerformance::calculate('iso-laut', $shipment->fresh());
        $this->assertSame(7, $metrics['sla_actual']);
        $this->assertSame('OTD', $metrics['sla_result']);

        $this->actingAs($this->admin)
            ->getJson(route('admin.special-shipments.data', [
                'type' => 'iso-laut',
                'search' => ['value' => 'TEST-NOKA-LEGACY-SERIAL-001'],
                'length' => 10,
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'noka' => 'TEST-NOKA-LEGACY-SERIAL-001',
                'at_ptd_dtd' => '2026-06-09',
                'sla_actual' => 7,
                'sla_result' => 'OTD',
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
        $headers = ShipmentUploadTemplate::specialHeadings($config);
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
        $this->assertSame(3, $tsoMetrics['delay_days']);
        $this->assertSame(5, $daratMetrics['sla_actual']);
        $this->assertSame('OTD', $daratMetrics['sla_result']);
        $this->assertSame(6, $lautMetrics['sla_actual']);
        $this->assertSame('OTD', $lautMetrics['sla_result']);
    }

    public function test_iso_sla_matrix_overrides_uploaded_customer_sla_for_known_destinations(): void
    {
        $darat = IsoDaratShipment::create([
            'destination' => 'Bandung',
            'terima_do' => '2026-07-01',
            'at_ptd_dtd' => '2026-07-03',
            'sla_customer' => 99,
        ]);
        $laut = IsoLautShipment::create([
            'destination' => 'Medan (Patimban)',
            'terima_do' => '2026-07-01',
            'at_ptd_dtd' => '2026-07-06',
            'sla_customer' => 99,
        ]);

        $daratMetrics = SpecialShipmentPerformance::calculate('iso-darat', $darat);
        $lautMetrics = SpecialShipmentPerformance::calculate('iso-laut', $laut);

        $this->assertSame(1, $daratMetrics['sla_customer']);
        $this->assertSame('LATE', $daratMetrics['sla_result']);
        $this->assertSame(1, $daratMetrics['delay_days']);
        $this->assertSame(5, $lautMetrics['sla_customer']);
        $this->assertSame('OTD', $lautMetrics['sla_result']);
        $this->assertSame(0, $lautMetrics['delay_days']);
    }

    public function test_iso_sla_matrix_contains_the_provided_stage_and_customer_targets(): void
    {
        foreach (IsoSla::targets()['iso-laut'] as $target) {
            $this->assertSame(0, $target['stages']['keluar_dari_pdc']);
            $this->assertSame(0, $target['stages']['ptd_dooring']);
        }

        $lautCustomers = collect(IsoSla::targets()['iso-laut'])
            ->map(fn (array $target) => $target['customer'])
            ->all();
        $daratCustomers = collect(IsoSla::targets()['iso-darat'])
            ->map(fn (array $target) => $target['customer'])
            ->all();

        $this->assertSame([
            'BALIKPAPAN' => 5,
            'BANJARMASIN' => 4,
            'MAKASSAR' => 5,
            'MANADO' => 3,
            'MEDAN PATIMBAN' => 5,
            'SAMARINDA' => 6,
        ], $lautCustomers);
        $this->assertSame([
            'BANDUNG' => 1,
            'BEKASI' => 3,
            'CILEGON' => 2,
            'CIREBON' => 1,
            'DKI JAKARTA' => 3,
            'LAMPUNG' => 3,
            'MALANG' => 3,
            'MEDAN' => 4,
            'PADANG' => 5,
            'PALEMBANG' => 3,
            'PEKALONGAN' => 2,
            'PEKANBARU' => 4,
            'SEMARANG' => 3,
            'SOLO MAGELANG' => 2,
            'SURABAYA' => 3,
            'TANGERANG' => 3,
            'YOGYAKARTA' => 2,
        ], $daratCustomers);
        $this->assertSame([
            'keluar_dari_pdc' => 0,
            'storage_port' => 1,
            'kapal_loading' => 0,
            'ata_kapal' => 3,
            'storage_port_destination' => 1,
            'ptd_dooring' => 0,
        ], IsoSla::targetFor('iso-laut', 'Balikpapan')['stages']);

        $this->assertSame(6, IsoSla::customerFor('iso-laut', 'Samarinda'));
        $this->assertSame(2, IsoSla::customerFor('iso-darat', 'Solo/Magelang'));
        $this->assertSame(3, IsoSla::customerFor('iso-darat', 'DKI Jakarta'));
        $this->assertSame([
            'keluar_dari_pdc' => null,
            'storage_port' => null,
            'kapal_loading' => null,
            'ata_kapal' => null,
            'storage_port_destination' => null,
            'ptd_dooring' => 1,
        ], IsoSla::targetFor('iso-darat', 'Bandung')['stages']);
    }

    public function test_iso_datatable_returns_customer_sla_from_matrix(): void
    {
        IsoDaratShipment::create([
            'no_spb' => 'MATRIX-SPB-001',
            'destination' => 'Padang',
            'sla_customer' => 99,
        ]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.special-shipments.data', ['type' => 'iso-darat', 'length' => 10]))
            ->assertOk()
            ->assertJsonFragment([
                'no_spb' => 'MATRIX-SPB-001',
                'sla_customer' => 5,
            ]);
    }

    public function test_ongoing_special_shipment_uses_today_for_delay(): void
    {
        Carbon::setTestNow('2026-07-15 12:00:00');
        $tso = TsoShipment::create([
            'do_date' => '2026-07-01',
            'port_to_door' => null,
            'sla_customer' => 8,
        ]);
        $isoLaut = IsoLautShipment::create([
            'terima_do' => '2026-07-01',
            'at_storage_port' => '2026-07-05',
            'atd_kapal_loading' => '2026-07-06',
            'ata_storage_port_destination' => '2026-07-11',
            'at_ptd_dtd' => null,
            'sla_customer' => 8,
        ]);

        $tsoMetrics = SpecialShipmentPerformance::calculate('tso', $tso);
        $isoMetrics = SpecialShipmentPerformance::calculate('iso-laut', $isoLaut);

        $this->assertSame(14, $tsoMetrics['sla_actual']);
        $this->assertSame('LATE', $tsoMetrics['sla_result']);
        $this->assertSame(6, $tsoMetrics['delay_days']);
        $this->assertSame(1, $isoMetrics['lead_time_loading']);
        $this->assertSame(4, $isoMetrics['lead_time_ptd_dtd']);
        $this->assertSame(6, $isoMetrics['delay_days']);
    }
}
