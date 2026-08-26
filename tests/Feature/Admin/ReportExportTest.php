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

        $response = $this->actingAs($this->admin)->postJson(route('admin.reports.data'), [
            'type' => 'dso',
            'draw' => 1,
            'start' => 0,
            'length' => 25,
            'search' => ['value' => 'MHFAA8GS4N0000001'],
        ]);

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.no_rangka', 'MHFAA8GS4N0000001');
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
            ->postJson(route('admin.reports.data'), [
                'type' => 'tso',
                'draw' => 1,
                'start' => 0,
                'length' => 25,
            ])
            ->assertOk()
            ->assertJsonPath('data.0.document_url', $documentUrl);

        $export = new SpecialShipmentReportExport('tso');
        $documentColumn = array_search('Dokumen', $export->headings(), true);

        $this->assertIsInt($documentColumn);
        $this->assertSame($documentUrl, $export->array()[0][$documentColumn]);
    }

    public function test_reports_are_separated_by_type_and_period(): void
    {
        Shipment::factory()->create(['no_rangka' => 'REPORT-DSO-001', 'terima_do' => '2025-05-01']);
        TsoShipment::create(['no_rangka' => 'REPORT-TSO-MAY', 'do_date' => '2025-05-01']);
        TsoShipment::create(['no_rangka' => 'REPORT-TSO-JUNE', 'do_date' => '2025-06-01']);
        IsoDaratShipment::create(['no_spb' => 'REPORT-ISO-DARAT', 'terima_do' => '2025-05-01']);
        IsoLautShipment::create(['noka' => 'REPORT-ISO-LAUT', 'terima_do' => '2025-05-01']);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['type' => 'tso', 'month' => 5, 'year' => 2025]))
            ->assertOk()
            ->assertSee('Laporan Shipment TSO')
            ->assertViewHas('selectedReport', 'tso')
            ->assertViewHas('selectedMonth', 5)
            ->assertViewHas('selectedYear', 2025);

        $this->actingAs($this->admin)
            ->postJson(route('admin.reports.data'), [
                'type' => 'tso',
                'month' => 5,
                'year' => 2025,
                'draw' => 1,
                'start' => 0,
                'length' => 25,
            ])
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.no_rangka', 'REPORT-TSO-MAY')
            ->assertJsonMissing(['no_rangka' => 'REPORT-TSO-JUNE'])
            ->assertJsonMissing(['no_rangka' => 'REPORT-DSO-001']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.reports.data'), [
                'type' => 'iso',
                'iso_type' => 'darat',
                'draw' => 1,
                'start' => 0,
                'length' => 25,
            ])
            ->assertOk()
            ->assertJsonPath('data.0.no_spb', 'REPORT-ISO-DARAT')
            ->assertJsonMissing(['noka' => 'REPORT-ISO-LAUT']);
    }

    public function test_report_datatable_only_fetches_the_requested_page(): void
    {
        foreach (range(1, 30) as $number) {
            TsoShipment::create([
                'no_rangka' => sprintf('REPORT-PAGE-%05d', $number),
                'do_date' => '2025-05-01',
            ]);
        }

        $response = $this->actingAs($this->admin)->postJson(route('admin.reports.data'), [
            'type' => 'tso',
            'draw' => 7,
            'start' => 10,
            'length' => 10,
        ]);

        $response->assertOk()
            ->assertJsonPath('draw', 7)
            ->assertJsonPath('recordsTotal', 30)
            ->assertJsonPath('recordsFiltered', 30)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.row_number', 11);
    }

    public function test_vendor_cannot_access_reports(): void
    {
        $vendor = User::factory()->vendor()->create();
        $this->actingAs($vendor)
            ->get(route('admin.reports.index'))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->postJson(route('admin.reports.data'), ['type' => 'dso'])
            ->assertForbidden();
    }
}
