<?php

namespace Tests\Feature\Admin;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\Shipment;
use App\Models\TsoShipment;
use App\Models\User;
use Tests\TestCase;

class DashboardPeriodFilterTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
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
