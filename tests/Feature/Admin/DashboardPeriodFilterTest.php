<?php

namespace Tests\Feature\Admin;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\TsoShipment;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DashboardPeriodFilterTest extends TestCase
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

    public function test_dso_dashboard_filters_statistics_and_table_by_month_and_year(): void
    {
        Shipment::create([
            'no_rangka' => 'DSO-MAY-2025-001',
            'kota' => 'BALIKPAPAN',
            'terima_do' => '2025-05-01',
            'at_ptd_dooring' => '2025-05-09',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-JUNE-2025-01',
            'kota' => 'BALIKPAPAN',
            'terima_do' => '2025-06-01',
            'at_ptd_dooring' => '2025-06-15',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/dashboard?type=dso&month=5&year=2025')
            ->assertOk()
            ->assertSee('Semua Bulan')
            ->assertSee('Semua Tahun')
            ->assertSee('DSO')
            ->assertSee('TSO')
            ->assertSee('ISO Darat')
            ->assertSee('ISO Laut')
            ->assertDontSee('id="dashboardType"', false)
            ->assertViewHas('selectedMonth', 5)
            ->assertViewHas('selectedYear', 2025)
            ->assertViewHas('dashboardShipmentTotal', 1)
            ->assertViewHas('delayStats', fn (array $stats) => $stats['completed'] === 1 && $stats['late'] === 0);

        $this->actingAs($this->admin)
            ->postJson(route('admin.shipments.data'), ['month' => 5, 'year' => 2025, 'length' => 10])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonFragment(['no_rangka' => 'DSO-MAY-2025-001'])
            ->assertJsonMissing(['no_rangka' => 'DSO-JUNE-2025-01']);
    }

    public function test_tso_dashboard_filters_statistics_and_table_by_do_date(): void
    {
        TsoShipment::create([
            'no_rangka' => 'TSO-MAY-2025-001',
            'do_date' => '2025-05-01',
            'port_to_door' => '2025-05-05',
            'sla_customer' => 5,
        ]);
        TsoShipment::create([
            'no_rangka' => 'TSO-JUNE-2025-01',
            'do_date' => '2025-06-01',
            'port_to_door' => '2025-06-10',
            'sla_customer' => 5,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/dashboard?type=tso&month=5&year=2025')
            ->assertOk()
            ->assertViewHas('dashboardShipmentTotal', 1)
            ->assertViewHas('specialDelayStats', fn (array $stats) => $stats['completed'] === 1 && $stats['late'] === 0);

        $this->actingAs($this->admin)
            ->postJson(route('admin.special-shipments.data', 'tso'), ['month' => 5, 'year' => 2025, 'length' => 10])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonFragment(['no_rangka' => 'TSO-MAY-2025-001'])
            ->assertJsonMissing(['no_rangka' => 'TSO-JUNE-2025-01']);
    }

    public function test_tso_dashboard_shows_dynamic_destination_positions_summary_and_latest_data(): void
    {
        TsoShipment::create([
            'no_rangka' => 'TSO-POSITION-0001',
            'destination' => 'Balikpapan',
            'do_date' => '2025-05-01',
        ]);
        TsoShipment::create([
            'no_rangka' => 'TSO-POSITION-0002',
            'destination' => 'Balikpapan',
            'do_date' => '2025-05-02',
            'pu_date' => '2025-05-03',
        ]);
        TsoShipment::create([
            'no_rangka' => 'TSO-POSITION-0003',
            'destination' => 'Kendari Baru',
            'do_date' => '2025-05-04',
            'pu_date' => '2025-05-05',
            'door_to_port' => '2025-05-06',
            'port_to_port' => '2025-05-07',
            'port_to_door' => '2025-05-08',
        ]);
        TsoShipment::create([
            'no_rangka' => 'TSO-POSITION-OUTSIDE',
            'destination' => 'Kendari Baru',
            'do_date' => '2025-06-01',
        ]);
        ScanHistory::create([
            'user_id' => $this->admin->id,
            'no_rangka' => 'TSO-POSITION-0003',
            'scan_date' => '2025-05-09',
        ]);
        ScanHistory::create([
            'user_id' => $this->admin->id,
            'no_rangka' => 'NON-TSO-SCAN',
            'scan_date' => '2025-05-10',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/dashboard?type=tso&month=5&year=2025')
            ->assertOk()
            ->assertSee('Ringkasan TSO')
            ->assertSee('Posisi Barang per Kota (Destination)')
            ->assertSee('Kendari Baru')
            ->assertSee('Shipment Terbaru TSO')
            ->assertSee('Scan Terbaru TSO')
            ->assertSee('TSO-POSITION-0003')
            ->assertDontSee('TSO-POSITION-OUTSIDE')
            ->assertDontSee('NON-TSO-SCAN')
            ->assertDontSee('Shipment Terlambat')
            ->assertDontSee('Persentase Keterlambatan')
            ->assertViewHas('dashboardScanTotal', 1)
            ->assertViewHas('tsoPositionSummary', fn (array $summaries) =>
                count($summaries) === 2
                && $summaries[0]['destination'] === 'BALIKPAPAN'
                && $summaries[0]['total'] === 2
                && $summaries[0]['positions']['DO Received']['count'] === 1
                && $summaries[0]['positions']['Pickup']['count'] === 1
                && $summaries[1]['destination'] === 'KENDARI BARU'
                && $summaries[1]['total'] === 1
                && $summaries[1]['positions']['Port to Door']['count'] === 1
            );
    }

    public function test_dso_dashboard_summarizes_current_late_shipments_and_positions_by_city(): void
    {
        Carbon::setTestNow('2025-05-20 12:00:00');

        Shipment::create([
            'no_rangka' => 'DSO-POSITION-0001',
            'kota' => 'PONTIANAK',
            'terima_do' => '2025-05-01',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-POSITION-0002',
            'kota' => 'PONTIANAK',
            'terima_do' => '2025-05-18',
            'keluar_dari_pdc' => '2025-05-19',
            'at_storage_port' => '2025-05-20',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-OUTSIDE-PERIOD',
            'kota' => 'PONTIANAK',
            'terima_do' => '2025-06-01',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard?type=dso&month=5&year=2025')
            ->assertOk()
            ->assertSee('Ringkasan Late per Kota')
            ->assertSee('Dashboard 2 — Posisi Barang per Kota')
            ->assertSee('Dwelling Origin')
            ->assertSee('Keterlambatan (Hari)');

        $response->assertViewHas('delayStats', fn (array $stats) =>
            $stats['evaluated'] === 2
            && $stats['late'] === 1
            && $stats['percentage'] === 50.0
        );
        $response->assertViewHas('dsoLateByCity', fn (array $summaries) =>
            count($summaries) === 1
            && $summaries[0]['city'] === 'PONTIANAK'
            && $summaries[0]['total'] === 2
            && $summaries[0]['late'] === 1
        );
        $response->assertViewHas('dsoPositionSummary', fn (array $summaries) =>
            count($summaries) === 1
            && $summaries[0]['total'] === 2
            && $summaries[0]['positions']['Belum Keluar PDC']['count'] === 1
            && $summaries[0]['positions']['AT Storage Port']['count'] === 1
        );
    }

    public function test_dso_dashboard_lists_dwelling_per_shipment_for_selected_period(): void
    {
        Carbon::setTestNow('2025-05-20 12:00:00');

        Shipment::create([
            'no_rangka' => 'DSO-DWELLING-0001',
            'terima_do' => '2025-05-01',
            'at_storage_port' => '2025-05-05',
            'atd_kapal_loading' => '2025-05-08',
            'ata_storage_port_destination' => '2025-05-10',
            'at_ptd_dooring' => '2025-05-14',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-DWELLING-0002',
            'terima_do' => '2025-05-02',
            'at_storage_port' => '2025-05-18',
            'ata_storage_port_destination' => '2025-05-19',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-DWELLING-OUTSIDE',
            'terima_do' => '2025-06-01',
            'at_storage_port' => '2025-06-01',
            'atd_kapal_loading' => '2025-06-11',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/dashboard?type=dso&month=5&year=2025')
            ->assertOk()
            ->assertSee('Dwelling Origin')
            ->assertSee('Dwelling Destination')
            ->assertDontSee('Referensi SLA Customer DSO')
            ->assertSee('DSO-DWELLING-0001')
            ->assertSee('DSO-DWELLING-0002')
            ->assertDontSee('DSO-DWELLING-OUTSIDE')
            ->assertViewHas('dsoDwellingDetails', fn (array $details) =>
                $details['origin'] === [
                    ['no_rangka' => 'DSO-DWELLING-0001', 'days' => 3],
                    ['no_rangka' => 'DSO-DWELLING-0002', 'days' => 2],
                ]
                && $details['destination'] === [
                    ['no_rangka' => 'DSO-DWELLING-0001', 'days' => 4],
                    ['no_rangka' => 'DSO-DWELLING-0002', 'days' => 1],
                ]
            );
    }

    public function test_dso_dashboard_shows_cumulative_do_performance_counts_and_percentages(): void
    {
        Shipment::create([
            'no_rangka' => 'DSO-FUNNEL-0001',
            'terima_do' => '2025-05-01',
            'keluar_dari_pdc' => '2025-05-02',
            'at_storage_port' => '2025-05-03',
            'atd_kapal_loading' => '2025-05-04',
            'ata_kapal' => '2025-05-05',
            'ata_storage_port_destination' => '2025-05-06',
            'at_ptd_dooring' => '2025-05-07',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-FUNNEL-0002',
            'terima_do' => '2025-05-10',
            'keluar_dari_pdc' => '2025-05-11',
            'at_storage_port' => '2025-05-12',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-FUNNEL-0003',
            'terima_do' => '2025-05-15',
        ]);
        Shipment::create([
            'no_rangka' => 'DSO-FUNNEL-OUTSIDE',
            'terima_do' => '2025-06-01',
            'keluar_dari_pdc' => '2025-06-02',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/dashboard?type=dso&month=5&year=2025')
            ->assertOk()
            ->assertSee('DSO 2 — DO Performance')
            ->assertSee('Total Terima DO')
            ->assertSee('Belum Keluar PDC')
            ->assertSee('AT PtD (Dooring)')
            ->assertSee('33,33% dari Total Terima DO')
            ->assertViewHas('dsoDoPerformance', fn (array $stats) =>
                $stats['total_received'] === ['count' => 3, 'percentage' => 100.0]
                && $stats['not_departed_pdc'] === ['count' => 1, 'percentage' => 33.33]
                && $stats['departed_pdc'] === ['count' => 2, 'percentage' => 66.67]
                && $stats['storage_port'] === ['count' => 2, 'percentage' => 66.67]
                && $stats['vessel_loading'] === ['count' => 1, 'percentage' => 33.33]
                && $stats['vessel_arrived'] === ['count' => 1, 'percentage' => 33.33]
                && $stats['destination_storage'] === ['count' => 1, 'percentage' => 33.33]
                && $stats['ptd_dooring'] === ['count' => 1, 'percentage' => 33.33]
            );
    }

    public function test_both_iso_dashboards_filter_by_terima_do(): void
    {
        foreach ([IsoDaratShipment::class, IsoLautShipment::class] as $model) {
            $model::create([
                'source_no' => 1,
                'terima_do' => '2025-05-01',
                'keluar_dari_pdc' => '2025-05-02',
                'at_ptd_dtd' => '2025-05-03',
                'sla_customer' => 3,
            ]);
            $model::create([
                'source_no' => 2,
                'terima_do' => '2025-06-01',
                'keluar_dari_pdc' => '2025-06-02',
                'at_ptd_dtd' => '2025-06-10',
                'sla_customer' => 3,
            ]);
        }

        foreach (['darat', 'laut'] as $isoType) {
            $type = "iso-{$isoType}";

            $this->actingAs($this->admin)
                ->get("/admin/dashboard?type=iso&iso_type={$isoType}&month=5&year=2025")
                ->assertOk()
                ->assertViewHas('dashboardShipmentTotal', 1)
                ->assertViewHas('specialDelayStats', fn (array $stats) => $stats['completed'] === 1 && $stats['late'] === 0);

            $this->actingAs($this->admin)
                ->postJson(route('admin.special-shipments.data', $type), ['month' => 5, 'year' => 2025, 'length' => 10])
                ->assertOk()
                ->assertJsonPath('recordsTotal', 1)
                ->assertJsonFragment(['source_no' => 1])
                ->assertJsonMissing(['source_no' => 2]);
        }
    }
}
