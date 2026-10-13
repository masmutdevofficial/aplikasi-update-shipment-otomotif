<?php

namespace Tests\Feature\Admin;

use App\Exports\ShipmentExport;
use App\Exports\SpecialShipmentReportExport;
use App\Models\Shipment;
use App\Models\ShipmentUpdate;
use App\Models\TsoShipment;
use App\Models\User;
use App\Models\Vendor;
use App\Support\ReportDateFilter;
use App\Support\SpecialShipmentType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Tests\TestCase;

class ReportDateFilterTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_date_selector_precedes_month_and_preserves_filters_in_export_and_tabs(): void
    {
        TsoShipment::create(['no_rangka' => 'DATE-YEAR', 'pu_date' => '2018-05-01']);
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'type' => 'tso', 'date_field' => 'pu_date', 'month' => 5, 'year' => 2018,
        ]));

        $response->assertOk()
            ->assertViewHas('selectedDateField', 'pu_date')
            ->assertViewHas('availableYears', fn ($years) => in_array(2018, $years, true))
            ->assertSeeInOrder(['id="reportDateField"', 'id="reportMonth"'], false)
            ->assertSee('value="pu_date" selected', false)
            ->assertSee(route('admin.reports.export', [
                'type' => 'tso', 'date_field' => 'pu_date', 'month' => 5, 'year' => 2018,
            ]))
            ->assertSee(route('admin.reports.index', [
                'type' => 'iso', 'iso_type' => 'laut', 'date_field' => 'terima_do', 'month' => 5, 'year' => 2018,
            ]));

        foreach (['dso', 'tso', 'iso-darat', 'iso-laut'] as $type) {
            $response = $this->get(route('admin.reports.index', ['type' => $type]))->assertOk();
            foreach (ReportDateFilter::options($type) as $field => $label) {
                $response->assertSee('value="'.$field.'"', false)->assertSee($label);
            }
        }
    }

    public function test_every_operational_date_column_filters_table_and_export_by_its_own_period(): void
    {
        foreach (['dso', 'tso', 'iso-darat', 'iso-laut'] as $type) {
            $config = $type === 'dso' ? null : SpecialShipmentType::get($type);
            $identity = $config['identity'] ?? 'no_rangka';
            $start = $config['performance']['start'] ?? 'terima_do';

            foreach (ReportDateFilter::options($type) as $field => $label) {
                if ($field === 'max_arrival') {
                    continue;
                }
                // The start date is outside May when filtering another milestone.
                $match = $this->shipment($type, [$identity => 'DATE-MATCH', $start => '2025-01-01', $field => '2025-05-03']);
                $other = $this->shipment($type, [$identity => 'DATE-OTHER', $start => '2025-05-01', $field => '2025-06-03']);
                $wrongYear = $this->shipment($type, [$identity => 'DATE-YEAR', $field => '2024-05-03']);
                $missing = $this->shipment($type, [$identity => 'DATE-NULL', $field => null]);

                $this->actingAs($this->admin)->postJson(route('admin.reports.data'), [
                    'type' => $type, 'date_field' => $field, 'month' => 5, 'year' => 2025,
                ])->assertOk()->assertJsonPath('recordsFiltered', 1)
                    ->assertJsonPath('data.0.'.$identity, 'DATE-MATCH');

                $export = $type === 'dso'
                    ? new ShipmentExport(month: 5, year: 2025, dateField: $field)
                    : new SpecialShipmentReportExport($type, 5, 2025, $field);
                $rows = $export->array();
                $this->assertCount(1, $rows, $type.' '.$label);
                $this->assertContains('DATE-MATCH', $rows[0]);

                foreach ([$match, $other, $wrongYear, $missing] as $shipment) {
                    $shipment->delete();
                }
            }
        }
    }

    public function test_dso_filter_uses_vendor_scan_instead_of_the_superseded_master_date(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::create(['user_id' => $user->id, 'vendor_name' => 'Filter Vendor', 'position' => 'AT Storage Port']);
        $shipment = Shipment::factory()->create(['no_rangka' => 'DATE-SCAN', 'at_storage_port' => '2025-05-03']);
        ShipmentUpdate::create([
            'shipment_id' => $shipment->id, 'vendor_id' => $vendor->id,
            'position' => 'AT Storage Port', 'scan_date' => '2024-06-04',
        ]);

        foreach ([[6, 2024, 1], [5, 2025, 0]] as [$month, $year, $count]) {
            $this->actingAs($this->admin)->postJson(route('admin.reports.data'), [
                'type' => 'dso', 'date_field' => 'at_storage_port', 'month' => $month, 'year' => $year,
            ])->assertOk()->assertJsonPath('recordsFiltered', $count);
            $this->assertCount($count, (new ShipmentExport(month: $month, year: $year, dateField: 'at_storage_port'))->array());
        }
        $this->get(route('admin.reports.index', ['date_field' => 'at_storage_port']))
            ->assertOk()->assertViewHas('availableYears', fn ($years) => in_array(2024, $years, true));
        $this->assertSame('2025-05-03', $shipment->fresh()->at_storage_port->format('Y-m-d'));
    }

    public function test_max_arrival_filters_by_calculated_deadline_for_every_report_type(): void
    {
        foreach (['dso', 'tso', 'iso-darat', 'iso-laut'] as $type) {
            $config = $type === 'dso' ? null : SpecialShipmentType::get($type);
            $identity = $config['identity'] ?? 'no_rangka';
            $start = $config['performance']['start'] ?? 'terima_do';
            $attributes = $type === 'dso' ? ['kota' => 'Pontianak', 'tujuan_pengiriman' => 'Pontianak'] : ['sla_customer' => 5];
            $match = $this->shipment($type, [$identity => 'DATE-DEADLINE', $start => '2025-01-31', ...$attributes]);
            $missing = $this->shipment($type, [$identity => 'DATE-MISSING']);

            $this->actingAs($this->admin)->postJson(route('admin.reports.data'), [
                'type' => $type, 'date_field' => 'max_arrival', 'month' => 2, 'year' => 2025,
            ])->assertOk()->assertJsonPath('recordsFiltered', 1)
                ->assertJsonPath('data.0.'.$identity, 'DATE-DEADLINE');
            $export = $type === 'dso'
                ? new ShipmentExport(month: 2, year: 2025, dateField: 'max_arrival')
                : new SpecialShipmentReportExport($type, 2, 2025, 'max_arrival');
            $this->assertCount(1, $export->array());
            $match->delete();
            $missing->delete();
        }
    }

    public function test_iso_laut_legacy_dates_are_parsed_consistently_and_invalid_values_are_ignored(): void
    {
        $model = SpecialShipmentType::get('iso-laut')['model'];
        foreach (['03-May', (string) ExcelDate::PHPToExcel(new \DateTime('2025-05-03')), '#VALUE!'] as $index => $date) {
            $model::create(['noka' => 'DATE-LEGACY-'.$index, 'terima_do' => '2025-01-01', 'at_ptd_dtd' => $date]);
        }
        $this->actingAs($this->admin)->postJson(route('admin.reports.data'), [
            'type' => 'iso', 'iso_type' => 'laut', 'date_field' => 'at_ptd_dtd', 'month' => 5, 'year' => 2025,
        ])->assertOk()->assertJsonPath('recordsFiltered', 2);
        $this->assertCount(2, (new SpecialShipmentReportExport('iso-laut', 5, 2025, 'at_ptd_dtd'))->array());
    }

    public function test_invalid_or_incompatible_date_fields_fall_back_to_the_default(): void
    {
        TsoShipment::create(['no_rangka' => 'DATE-DEFAULT', 'do_date' => '2025-05-01']);
        foreach (['no_rangka', 'at_storage_port', 'unknown', 'do_date) OR 1=1 --', ['do_date']] as $field) {
            $this->actingAs($this->admin)->get(route('admin.reports.index', ['type' => 'tso', 'date_field' => $field]))
                ->assertOk()->assertViewHas('selectedDateField', 'do_date');
            $this->postJson(route('admin.reports.data'), [
                'type' => 'tso', 'date_field' => $field, 'month' => 5, 'year' => 2025,
            ])->assertOk()->assertJsonPath('recordsFiltered', 1);
        }
    }

    public function test_export_endpoint_passes_the_selected_date_field_to_the_export(): void
    {
        Excel::fake();
        TsoShipment::create(['no_rangka' => 'DATE-EXPORT', 'do_date' => '2025-01-01', 'pu_date' => '2025-05-01']);
        Shipment::factory()->create(['no_rangka' => 'DATE-EXPORT-DSO', 'terima_do' => '2025-01-01', 'keluar_dari_pdc' => '2025-05-01']);
        foreach (['tso' => 'pu_date', 'dso' => 'keluar_dari_pdc'] as $type => $field) {
            $this->actingAs($this->admin)->get(route('admin.reports.export', [
                'type' => $type, 'date_field' => $field, 'month' => 5, 'year' => 2025,
            ]))->assertOk();
            Excel::assertDownloaded('laporan_shipment_'.$type.'_'.now()->format('Ymd_His').'.xlsx',
                fn ($export) => count($export->array()) === 1);
        }
    }

    private function shipment(string $type, array $attributes)
    {
        if ($type === 'dso') {
            return Shipment::factory()->create([
                'terima_do' => null, 'keluar_dari_pdc' => null, 'keberangkatan_kapal' => null,
                ...$attributes,
            ]);
        }

        $model = SpecialShipmentType::get($type)['model'];

        return $model::create($attributes);
    }
}
