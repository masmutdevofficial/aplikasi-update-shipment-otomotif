<?php

namespace Tests\Feature\Admin;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\Shipment;
use App\Models\TsoShipment;
use App\Models\User;
use Tests\TestCase;

class ManualShipmentDashboardIntegrationTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_manual_dso_input_is_returned_by_the_dashboard_table(): void
    {
        $data = [
            'lokasi' => 'Jakarta Utara',
            'no_do' => 'DO-MANUAL-DSO-001',
            'type_kendaraan' => 'TERIOS',
            'no_rangka' => 'MHKM1BA3JFK123456',
            'no_engine' => '2NR4G19999',
            'warna' => 'GLITTERING SILVER',
            'asal_pdc' => 'CIBITUNG',
            'kota' => 'MEDAN',
            'tujuan_pengiriman' => 'DEALER CONTOH MEDAN',
            'terima_do' => '2026-08-01',
            'keluar_dari_pdc' => '2026-08-02',
            'nama_kapal' => 'SERASI 5',
            'keberangkatan_kapal' => '2026-08-04',
            'at_storage_port' => '2026-08-03',
            'atd_kapal_loading' => '2026-08-04',
            'ata_kapal' => '2026-08-08',
            'ata_storage_port_destination' => '2026-08-08',
            'at_ptd_dooring' => '2026-08-10',
        ];
        $response = $this->actingAs($this->admin)->post(route('admin.shipments.store'), $data);

        $response->assertRedirect(route('admin.shipments.index'))
            ->assertSessionHas('dashboard_url', route('admin.dashboard', [
                'type' => 'dso',
                'month' => 8,
                'year' => 2026,
            ]));

        $shipment = Shipment::query()->where('no_rangka', $data['no_rangka'])->firstOrFail();
        $this->actingAs($this->admin)
            ->get(route('admin.shipments.edit', $shipment))
            ->assertOk()
            ->assertSee('name="no_do"', false)
            ->assertSee('value="DO-MANUAL-DSO-001"', false)
            ->assertSee('name="at_ptd_dooring"', false)
            ->assertSee('value="2026-08-10"', false);

        $this->actingAs($this->admin)
            ->put(route('admin.shipments.update', $shipment), array_merge($data, [
                'no_do' => 'DO-MANUAL-DSO-EDIT',
                'tujuan_pengiriman' => 'DEALER HASIL EDIT MEDAN',
                'at_ptd_dooring' => '2026-08-11',
            ]))
            ->assertRedirect(route('admin.shipments.index'))
            ->assertSessionHas('dashboard_url');

        $dashboardResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.shipments.data'), $this->datatablePayload('MHKM1BA3JFK123456', 8, 2026));
        $dashboardResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'no_do' => 'DO-MANUAL-DSO-EDIT',
                'no_rangka' => 'MHKM1BA3JFK123456',
                'tujuan_pengiriman' => 'DEALER HASIL EDIT MEDAN',
                'at_ptd_dooring' => '11-Aug-26',
                'sla_actual' => 10,
                'sla_customer' => 10,
                'sla_result' => 'OTD',
                'progress' => 'OTD',
            ]);
        $this->assertDashboardRowContains($dashboardResponse->json('data.0'), [
            'lokasi' => 'Jakarta Utara',
            'no_do' => 'DO-MANUAL-DSO-EDIT',
            'type_kendaraan' => 'TERIOS',
            'no_rangka' => 'MHKM1BA3JFK123456',
            'no_engine' => '2NR4G19999',
            'warna' => 'GLITTERING SILVER',
            'asal_pdc' => 'CIBITUNG',
            'kota' => 'MEDAN',
            'tujuan_pengiriman' => 'DEALER HASIL EDIT MEDAN',
            'terima_do' => '01-Aug-26',
            'keluar_dari_pdc' => '02-Aug-26',
            'nama_kapal' => 'SERASI 5',
            'keberangkatan_kapal' => '04-Aug-26',
            'at_storage_port' => '03-Aug-26',
            'atd_kapal_loading' => '04-Aug-26',
            'ata_kapal' => '08-Aug-26',
            'ata_storage_port_destination' => '08-Aug-26',
            'at_ptd_dooring' => '11-Aug-26',
        ]);
    }

    public function test_manual_tso_input_is_returned_by_the_dashboard_table(): void
    {
        $data = [
            'unit_type' => 'AVANZA',
            'origin' => 'JAKARTA',
            'destination' => 'MAKASSAR',
            'detail_destination' => 'DEALER CONTOH MAKASSAR',
            'no_rangka' => 'MHKM1BA3JFK123457',
            'doc' => 'DOC-TSO-0001',
            'do_date' => '2026-08-01',
            'pu_date' => '2026-08-02',
            'door_to_port' => '2026-08-03',
            'port_to_port' => '2026-08-07',
            'port_to_door' => '2026-08-09',
            'vessel_ptp' => 'SERASI 5',
            'sla_customer' => 9,
        ];
        $response = $this->actingAs($this->admin)->post(route('admin.special-shipments.store', 'tso'), $data);

        $response->assertRedirect(route('admin.special-shipments.index', 'tso'))
            ->assertSessionHas('dashboard_url', route('admin.dashboard', [
                'type' => 'tso',
                'month' => 8,
                'year' => 2026,
            ]));

        $shipment = TsoShipment::query()->where('no_rangka', $data['no_rangka'])->firstOrFail();
        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.edit', ['tso', $shipment->id]))
            ->assertOk()
            ->assertSee('name="detail_destination"', false)
            ->assertSee('value="DEALER CONTOH MAKASSAR"', false)
            ->assertSee('name="port_to_door"', false)
            ->assertSee('value="2026-08-09"', false);

        $this->actingAs($this->admin)
            ->put(route('admin.special-shipments.update', ['tso', $shipment->id]), array_merge($data, [
                'detail_destination' => 'DEALER HASIL EDIT MAKASSAR',
                'doc' => 'DOC-TSO-EDIT',
                'port_to_door' => '2026-08-10',
            ]))
            ->assertRedirect(route('admin.special-shipments.index', 'tso'))
            ->assertSessionHas('dashboard_url');

        $dashboardResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.special-shipments.data', 'tso'), $this->datatablePayload('MHKM1BA3JFK123457', 8, 2026));
        $dashboardResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'unit_type' => 'AVANZA',
                'no_rangka' => 'MHKM1BA3JFK123457',
                'detail_destination' => 'DEALER HASIL EDIT MAKASSAR',
                'doc' => 'DOC-TSO-EDIT',
                'port_to_door' => '10-Aug-26',
                'sla_actual' => 9,
                'sla_result' => 'OTD',
                'progress' => 'OTD',
            ]);
        $this->assertDashboardRowContains($dashboardResponse->json('data.0'), [
            'unit_type' => 'AVANZA',
            'origin' => 'JAKARTA',
            'destination' => 'MAKASSAR',
            'detail_destination' => 'DEALER HASIL EDIT MAKASSAR',
            'no_rangka' => 'MHKM1BA3JFK123457',
            'doc' => 'DOC-TSO-EDIT',
            'do_date' => '01-Aug-26',
            'pu_date' => '02-Aug-26',
            'door_to_port' => '03-Aug-26',
            'port_to_port' => '07-Aug-26',
            'port_to_door' => '10-Aug-26',
            'vessel_ptp' => 'SERASI 5',
            'sla_customer' => 9,
        ]);
    }

    public function test_manual_iso_darat_input_and_driver_are_returned_by_the_dashboard_table(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.special-shipments.store', 'iso-darat'), [
            'source_no' => 1,
            'no_so_booking' => 'SO-D-260801-01',
            'no_quotation' => 'QUO-D-260801-01',
            'no_contract' => 'CTR-D-260801-01',
            'cargo_no_ka' => 'CARGO-D-0001',
            'no_spb' => 'SPB-D-0001',
            'kategori_moda' => 'LT Darat',
            'origin' => 'JAKARTA',
            'destination' => 'BANDUNG',
            'area' => 'JAWA BARAT',
            'terima_do' => '2026-08-01',
            'keluar_dari_pdc' => '2026-08-01',
            'at_ptd_dtd' => '2026-08-02',
            'sla_customer' => 1,
        ]);

        $response->assertSessionHas('dashboard_url', route('admin.dashboard', [
            'type' => 'iso',
            'iso_type' => 'darat',
            'month' => 8,
            'year' => 2026,
        ]));

        $shipment = IsoDaratShipment::query()->where('no_spb', 'SPB-D-0001')->firstOrFail();
        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.edit', ['iso-darat', $shipment->id]))
            ->assertOk()
            ->assertSee('name="no_spb"', false)
            ->assertSee('value="SPB-D-0001"', false)
            ->assertSee('name="at_ptd_dtd"', false)
            ->assertSee('value="2026-08-02"', false);

        $this->actingAs($this->admin)->put(
            route('admin.special-shipments.update', ['iso-darat', $shipment->id]),
            array_merge($shipment->only([
                'source_no', 'no_so_booking', 'no_quotation', 'no_contract', 'cargo_no_ka',
                'no_spb', 'kategori_moda', 'origin', 'destination', 'area', 'sla_customer',
            ]), [
                'nomor_driver' => '0812-0000-1234',
                'area' => 'JAWA BARAT HASIL EDIT',
                'terima_do' => '2026-08-01',
                'keluar_dari_pdc' => '2026-08-01',
                'at_ptd_dtd' => '2026-08-02',
            ]),
        )->assertSessionHas('dashboard_url');

        $dashboardResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.special-shipments.data', 'iso-darat'), $this->datatablePayload('SPB-D-0001', 8, 2026));
        $dashboardResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'no_spb' => 'SPB-D-0001',
                'nomor_driver' => '0812-0000-1234',
                'area' => 'JAWA BARAT HASIL EDIT',
                'at_ptd_dtd' => '02-Aug-26',
                'sla_customer' => 1,
                'sla_actual' => 1,
                'sla_result' => 'OTD',
                'progress' => 'OTD',
            ]);
        $this->assertDashboardRowContains($dashboardResponse->json('data.0'), [
            'source_no' => '1',
            'no_so_booking' => 'SO-D-260801-01',
            'no_quotation' => 'QUO-D-260801-01',
            'no_contract' => 'CTR-D-260801-01',
            'cargo_no_ka' => 'CARGO-D-0001',
            'no_spb' => 'SPB-D-0001',
            'kategori_moda' => 'LT Darat',
            'origin' => 'JAKARTA',
            'destination' => 'BANDUNG',
            'area' => 'JAWA BARAT HASIL EDIT',
            'nomor_driver' => '0812-0000-1234',
            'terima_do' => '01-Aug-26',
            'keluar_dari_pdc' => '01-Aug-26',
            'at_ptd_dtd' => '02-Aug-26',
            'sla_customer' => 1,
        ]);
    }

    public function test_manual_iso_laut_input_is_returned_by_the_dashboard_table(): void
    {
        $data = [
            'source_no' => 1,
            'no_booking_dtp' => 'BKG-DTP-0001',
            'no_booking_ptp' => 'BKG-PTP-0001',
            'no_booking_ptd' => 'BKG-PTD-0001',
            'no_quotation_dtp' => 'QUO-DTP-0001',
            'no_quotation_ptp' => 'QUO-PTP-0001',
            'no_quotation_ptd' => 'QUO-PTD-0001',
            'no_contract_dtp' => 'CTR-DTP-0001',
            'no_contract_ptp' => 'CTR-PTP-0001',
            'no_contract_ptd' => 'CTR-PTD-0001',
            'cargo' => 'CARGO-L-0001',
            'noka' => 'MHKM1BA3JFK123458',
            'kategori_moda' => 'LT Laut',
            'origin' => 'JAKARTA',
            'destination' => 'MAKASSAR',
            'tujuan_pengiriman' => 'DEALER CONTOH MAKASSAR',
            'terima_do' => '2026-08-01',
            'keluar_dari_pdc' => '2026-08-01',
            'jenis_kapal' => 'SERASI 5',
            'at_storage_port' => '2026-08-02',
            'atd_kapal_loading' => '2026-08-02',
            'ata_kapal' => '2026-08-04',
            'ata_storage_port_destination' => '2026-08-04',
            'at_ptd_dtd' => '2026-08-05',
            'sla_customer' => 5,
        ];
        $response = $this->actingAs($this->admin)->post(route('admin.special-shipments.store', 'iso-laut'), $data);

        $response->assertSessionHas('dashboard_url', route('admin.dashboard', [
            'type' => 'iso',
            'iso_type' => 'laut',
            'month' => 8,
            'year' => 2026,
        ]));

        $shipment = IsoLautShipment::query()->where('noka', $data['noka'])->firstOrFail();
        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.edit', ['iso-laut', $shipment->id]))
            ->assertOk()
            ->assertSee('name="tujuan_pengiriman"', false)
            ->assertSee('value="DEALER CONTOH MAKASSAR"', false)
            ->assertSee('name="at_ptd_dtd"', false)
            ->assertSee('value="2026-08-05"', false);

        $this->actingAs($this->admin)
            ->put(route('admin.special-shipments.update', ['iso-laut', $shipment->id]), array_merge($data, [
                'tujuan_pengiriman' => 'DEALER HASIL EDIT MAKASSAR',
                'at_ptd_dtd' => '2026-08-06',
            ]))
            ->assertRedirect(route('admin.special-shipments.index', 'iso-laut'))
            ->assertSessionHas('dashboard_url');

        $dashboardResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.special-shipments.data', 'iso-laut'), $this->datatablePayload('MHKM1BA3JFK123458', 8, 2026));
        $dashboardResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'noka' => 'MHKM1BA3JFK123458',
                'tujuan_pengiriman' => 'DEALER HASIL EDIT MAKASSAR',
                'at_ptd_dtd' => '2026-08-06',
                'sla_customer' => 5,
                'sla_actual' => 5,
                'sla_result' => 'OTD',
                'progress' => 'OTD',
            ]);
        $this->assertDashboardRowContains($dashboardResponse->json('data.0'), [
            'source_no' => '1',
            'no_booking_dtp' => 'BKG-DTP-0001',
            'no_booking_ptp' => 'BKG-PTP-0001',
            'no_booking_ptd' => 'BKG-PTD-0001',
            'no_quotation_dtp' => 'QUO-DTP-0001',
            'no_quotation_ptp' => 'QUO-PTP-0001',
            'no_quotation_ptd' => 'QUO-PTD-0001',
            'no_contract_dtp' => 'CTR-DTP-0001',
            'no_contract_ptp' => 'CTR-PTP-0001',
            'no_contract_ptd' => 'CTR-PTD-0001',
            'cargo' => 'CARGO-L-0001',
            'noka' => 'MHKM1BA3JFK123458',
            'kategori_moda' => 'LT Laut',
            'origin' => 'JAKARTA',
            'destination' => 'MAKASSAR',
            'tujuan_pengiriman' => 'DEALER HASIL EDIT MAKASSAR',
            'terima_do' => '01-Aug-26',
            'keluar_dari_pdc' => '01-Aug-26',
            'jenis_kapal' => 'SERASI 5',
            'at_storage_port' => '02-Aug-26',
            'atd_kapal_loading' => '02-Aug-26',
            'ata_kapal' => '04-Aug-26',
            'ata_storage_port_destination' => '04-Aug-26',
            'at_ptd_dtd' => '2026-08-06',
            'sla_customer' => 5,
        ]);

        $legacyShipment = IsoLautShipment::create([
            'noka' => 'LEGACY-ISO-LAUT-EDIT',
            'terima_do' => '2025-09-01',
            'at_ptd_dtd' => '03-Sep',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.special-shipments.edit', ['iso-laut', $legacyShipment->id]))
            ->assertOk()
            ->assertSee('name="at_ptd_dtd"', false)
            ->assertSee('value="2025-09-03"', false);
    }

    private function datatablePayload(string $search, int $month, int $year): array
    {
        return [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'month' => $month,
            'year' => $year,
            'search' => ['value' => $search, 'regex' => false],
        ];
    }

    private function assertDashboardRowContains(array $row, array $expected): void
    {
        $this->assertSame($expected, array_intersect_key($row, $expected));
    }
}
