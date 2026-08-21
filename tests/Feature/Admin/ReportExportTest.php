<?php

namespace Tests\Feature\Admin;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\Shipment;
use App\Models\TsoShipment;
use App\Models\User;
use Tests\TestCase;

class ReportExportTest extends TestCase
{

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_reports(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
        $response->assertSee('Pilih Dashboard');
        $response->assertSee('Filter Periode');
        $response->assertSee('ISO Darat');
        $response->assertSee('ISO Laut');
    }

    public function test_admin_can_filter_reports_by_search(): void
    {
        Shipment::factory()->create([
            'no_rangka' => 'MHFAA8GS4N0000001',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'search' => 'MHFAA8GS4N0000001',
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_excel(): void
    {
        // Skip if maatwebsite/excel is not available in test environment
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $this->markTestSkipped('Maatwebsite Excel package not available in test environment.');
        }

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export'));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_each_special_report_type(): void
    {
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $this->markTestSkipped('Maatwebsite Excel package not available in test environment.');
        }

        foreach (['tso', 'iso-darat', 'iso-laut'] as $type) {
            $this->actingAs($this->admin)
                ->get(route('admin.reports.export', ['type' => $type, 'month' => 5, 'year' => 2025]))
                ->assertOk()
                ->assertHeader('content-disposition');
        }
    }

    public function test_reports_are_separated_by_type_and_period(): void
    {
        Shipment::create(['no_rangka' => 'REPORT-DSO-001', 'terima_do' => '2025-05-01']);
        TsoShipment::create(['no_rangka' => 'REPORT-TSO-MAY', 'do_date' => '2025-05-01']);
        TsoShipment::create(['no_rangka' => 'REPORT-TSO-JUNE', 'do_date' => '2025-06-01']);
        IsoDaratShipment::create(['no_spb' => 'REPORT-ISO-DARAT', 'terima_do' => '2025-05-01']);
        IsoLautShipment::create(['noka' => 'REPORT-ISO-LAUT', 'terima_do' => '2025-05-01']);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['type' => 'tso', 'month' => 5, 'year' => 2025]))
            ->assertOk()
            ->assertSee('Laporan Shipment TSO')
            ->assertSee('REPORT-TSO-MAY')
            ->assertDontSee('REPORT-TSO-JUNE')
            ->assertDontSee('REPORT-DSO-001')
            ->assertViewHas('selectedReport', 'tso')
            ->assertViewHas('selectedMonth', 5)
            ->assertViewHas('selectedYear', 2025);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['type' => 'iso', 'iso_type' => 'darat']))
            ->assertOk()
            ->assertSee('REPORT-ISO-DARAT')
            ->assertDontSee('REPORT-ISO-LAUT');
    }

    public function test_vendor_cannot_access_reports(): void
    {
        $vendor = User::factory()->vendor()->create();
        $response = $this->actingAs($vendor)->get(route('admin.reports.index'));
        $response->assertStatus(403);
    }
}
