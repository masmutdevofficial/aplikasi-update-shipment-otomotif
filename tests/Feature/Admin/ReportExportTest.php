<?php

namespace Tests\Feature\Admin;

use App\Exports\ShipmentExport;
use App\Exports\SpecialShipmentReportExport;
use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\PendingVin;
use App\Models\Shipment;
use App\Models\TsoShipment;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;
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

    public function test_dso_report_uses_performance_columns_without_other_shipment_position_columns(): void
    {
        $shipment = Shipment::factory()->create([
            'kota' => 'Pontianak',
            'tujuan_pengiriman' => 'Pontianak',
            'terima_do' => '2026-08-01',
            'keluar_dari_pdc' => '2026-08-01',
            'at_storage_port' => '2026-08-02',
            'atd_kapal_loading' => '2026-08-03',
            'ata_kapal' => '2026-08-05',
            'ata_storage_port_destination' => '2026-08-06',
            'at_ptd_dooring' => '2026-08-07',
        ]);
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', ['type' => 'dso']));

        $response->assertOk()
            ->assertSee('Dwelling Origin')
            ->assertSee('Dwelling Destination')
            ->assertSee('SLA Actual')
            ->assertSee('SLA Customer')
            ->assertSee('Keterlambatan (Hari)')
            ->assertDontSee('AT PtD (Dooring)')
            ->assertDontSee('AT PTD/DTD')
            ->assertDontSee('Door to Port (DTP)')
            ->assertDontSee('Port to Port (PTP)')
            ->assertDontSee('Port to Door (PTD)');

        $headings = (new ShipmentExport)->headings();
        $row = ReportService::flattenShipment($shipment->load('shipmentUpdates'));

        $this->assertCount(count($headings), $row);
        $this->assertContains('Dwelling Origin', $headings);
        $this->assertContains('SLA Customer', $headings);
        $this->assertNotContains('AT PtD (Dooring)', $headings);
        $this->assertNotContains('AT PTD/DTD', $headings);
        $this->assertNotContains('Door to Port (DTP)', $headings);
        $this->assertNotContains('Port to Port (PTP)', $headings);
        $this->assertNotContains('Port to Door (PTD)', $headings);
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

    public function test_special_reports_and_exports_include_document_column(): void
    {
        foreach (['tso', 'iso-darat', 'iso-laut'] as $type) {
            $this->actingAs($this->admin)
                ->get(route('admin.reports.index', ['type' => $type]))
                ->assertOk()
                ->assertSee('Dokumen');

            $this->assertContains('Dokumen', (new SpecialShipmentReportExport($type))->headings());
        }
    }

    public function test_special_report_resolves_document_from_pending_identity(): void
    {
        Storage::fake('r2');
        config(['filesystems.document_disk' => 'r2']);
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'vendor_name' => 'Vendor Dokumen',
            'position' => 'AT PtD (Dooring)',
        ]);
        $documentPath = 'shipment-documents/REPORTTSODOC00001/document.jpg';
        Storage::disk('r2')->put($documentPath, 'document-content');
        PendingVin::create([
            'no_rangka' => 'REPORTTSODOC00001',
            'vendor_id' => $vendor->id,
            'position' => 'AT PtD (Dooring)',
            'scan_date' => '2026-08-26',
            'document_path' => $documentPath,
        ]);
        TsoShipment::create([
            'no_rangka' => 'REPORTTSODOC00001',
            'do_date' => '2026-08-01',
        ]);
        $documentUrl = Storage::disk('r2')->url($documentPath);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['type' => 'tso']))
            ->assertOk()
            ->assertSee($documentUrl, false);

        $export = new SpecialShipmentReportExport('tso');
        $documentColumn = array_search('Dokumen', $export->headings(), true);

        $this->assertIsInt($documentColumn);
        $this->assertSame($documentUrl, $export->array()[0][$documentColumn]);
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
