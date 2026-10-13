<?php

namespace Tests\Feature\Admin;

use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\ShipmentUpdate;
use App\Models\TsoShipment;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ReportService;
use App\Support\SpecialShipmentType;
use Tests\TestCase;

class ReportColumnSearchTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_every_report_header_has_a_column_search_input(): void
    {
        foreach (['dso', 'tso', 'iso-darat', 'iso-laut'] as $type) {
            $response = $this->get(route('admin.reports.index', ['type' => $type]))->assertOk();
            $document = new \DOMDocument;
            @$document->loadHTML($response->getContent());
            $xpath = new \DOMXPath($document);
            $inputs = $xpath->query('//table[@id="table-reports"]/thead/tr[@data-dt-order="disable"]/th/input');
            $this->assertCount(count($this->columns($type)), $inputs);
            foreach ($this->columns($type) as $index => $column) {
                $this->assertSame('Cari kolom '.$column['label'], $inputs[$index]->getAttribute('aria-label'));
            }
        }
    }

    public function test_column_search_is_scoped_to_its_column_and_combines_with_other_searches(): void
    {
        foreach ([
            ['no_rangka' => 'COL-MATCH', 'origin' => 'Jakarta', 'destination' => 'Bali', 'do_date' => '2025-05-01'],
            ['no_rangka' => 'COL-OTHER', 'origin' => 'Jakarta', 'destination' => 'Bandung', 'do_date' => '2025-05-01'],
            ['no_rangka' => 'COL-JUNE', 'origin' => 'Jakarta', 'destination' => 'Bali', 'do_date' => '2025-06-01'],
            ['no_rangka' => 'COL-SCOPED', 'origin' => 'Surabaya', 'destination' => 'Bali', 'unit_type' => 'Jakarta', 'do_date' => '2025-05-01'],
        ] as $attributes) {
            TsoShipment::create($attributes);
        }

        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'month' => 5, 'year' => 2025,
            'columns' => $this->filters('tso', ['origin' => 'jakarta', 'destination' => 'bali']),
        ])->assertOk()->assertJsonPath('recordsTotal', 3)->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.no_rangka', 'COL-MATCH');

        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'search' => ['value' => 'MATCH'], 'date_field' => 'do_date', 'date' => '2025-05-01',
            'columns' => $this->filters('tso', ['destination' => 'bali']),
        ])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.no_rangka', 'COL-MATCH');

        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'month' => 5, 'year' => 2025,
            'columns' => $this->filters('tso', ['origin' => 'Jakarta', 'destination' => '']),
        ])->assertOk()->assertJsonPath('recordsFiltered', 2);
    }

    public function test_dates_are_searched_in_the_displayed_format_and_nulls_as_a_dash(): void
    {
        TsoShipment::create(['no_rangka' => 'COL-DATE', 'pu_date' => '2025-05-03']);
        TsoShipment::create(['no_rangka' => 'COL-MISSING', 'pu_date' => null]);
        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'columns' => $this->filters('tso', ['pu_date' => '03-may-25']),
        ])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.no_rangka', 'COL-DATE');
        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'columns' => $this->filters('tso', ['pu_date' => '-']),
        ])->assertOk()->assertJsonPath('recordsFiltered', 2)->assertJsonFragment(['no_rangka' => 'COL-MISSING']);
    }

    public function test_dso_date_search_uses_vendor_scans_and_leaves_master_dates_unchanged(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'vendor_name' => 'Search Vendor', 'position' => 'AT Storage Port']);
        $shipment = Shipment::factory()->create(['no_rangka' => 'COL-SCAN', 'at_storage_port' => '2025-06-04']);
        ShipmentUpdate::create([
            'shipment_id' => $shipment->id, 'vendor_id' => $vendor->id,
            'position' => 'AT Storage Port', 'scan_date' => '2025-05-03',
        ]);
        $this->postJson(route('admin.reports.data'), [
            'type' => 'dso', 'columns' => $this->filters('dso', ['at_storage_port' => '03-May-25']),
        ])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.no_rangka', 'COL-SCAN');
        $this->postJson(route('admin.reports.data'), [
            'type' => 'dso', 'columns' => $this->filters('dso', ['at_storage_port' => '04-Jun-25']),
        ])->assertOk()->assertJsonPath('recordsFiltered', 0);
        $this->assertSame('2025-06-04', $shipment->fresh()->at_storage_port->format('Y-m-d'));
    }

    public function test_computed_performance_columns_are_searchable_together(): void
    {
        TsoShipment::create([
            'no_rangka' => 'COL-LATE', 'do_date' => '2025-05-01', 'pu_date' => '2025-05-03',
            'port_to_door' => '2025-05-10', 'sla_customer' => 5,
        ]);
        TsoShipment::create([
            'no_rangka' => 'COL-OTD', 'do_date' => '2025-05-01', 'port_to_door' => '2025-05-03', 'sla_customer' => 5,
        ]);
        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'columns' => $this->filters('tso', [
                'sla_result' => 'late', 'progress' => 'late', 'lead_time_pickup' => '2',
                'sla_actual' => '9', 'sla_customer' => '5', 'delay_days' => '4', 'max_arrival' => '06-May-25',
            ]),
        ])->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.no_rangka', 'COL-LATE');
    }

    public function test_search_is_applied_before_pagination_and_counts_all_matches(): void
    {
        foreach (range(1, 30) as $number) {
            TsoShipment::create([
                'no_rangka' => sprintf('COL-PAGE-%03d', $number), 'do_date' => '2025-05-01',
                'port_to_door' => $number <= 15 ? '2025-05-10' : '2025-05-03', 'sla_customer' => 5,
            ]);
        }
        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'start' => 10, 'length' => 10,
            'columns' => $this->filters('tso', ['sla_result' => 'LATE']),
        ])->assertOk()->assertJsonPath('recordsTotal', 30)->assertJsonPath('recordsFiltered', 15)
            ->assertJsonCount(5, 'data')->assertJsonPath('data.0.row_number', 11);
    }

    public function test_searching_row_numbers_preserves_the_matching_number(): void
    {
        foreach (['A', 'B', 'C'] as $unit) {
            TsoShipment::create(['no_rangka' => 'COL-NO-'.$unit, 'unit_type' => $unit]);
        }
        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'columns' => $this->filters('tso', ['row_number' => '2']),
            'order' => [['column' => 1, 'dir' => 'asc']],
        ])->assertOk()->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.row_number', 2)->assertJsonPath('data.0.no_rangka', 'COL-NO-B');
    }

    public function test_document_presence_and_type_specific_columns_are_searchable_for_all_reports(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'vendor_name' => 'Document Search Vendor', 'position' => 'AT PtD (Dooring)']);
        foreach (['dso', 'tso', 'iso-darat', 'iso-laut'] as $type) {
            $config = $type === 'dso' ? null : SpecialShipmentType::get($type);
            $model = $config['model'] ?? Shipment::class;
            $identity = $config['identity'] ?? 'no_rangka';
            $present = $type === 'dso'
                ? Shipment::factory()->create([$identity => 'COL-DOC'])
                : $model::create([$identity => 'COL-DOC']);
            $missing = $type === 'dso'
                ? Shipment::factory()->create([$identity => 'COL-NO-DOC'])
                : $model::create([$identity => 'COL-NO-DOC']);
            ShipmentDocument::create([
                'documentable_type' => $model, 'documentable_id' => $present->id,
                'vendor_id' => $vendor->id,
                'identifier' => 'COL-DOC', 'document_path' => 'shipment-documents/column-search.jpg',
            ]);
            foreach (['Ada' => 'COL-DOC', 'Kosong' => 'COL-NO-DOC'] as $term => $expected) {
                $this->postJson(route('admin.reports.data'), [
                    'type' => $type, 'columns' => $this->filters($type, ['document_url' => $term]),
                ])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.'.$identity, $expected);
            }
            $present->delete();
            $missing->delete();
        }
    }

    public function test_search_values_are_literal_and_client_column_names_cannot_change_the_query(): void
    {
        TsoShipment::create(['no_rangka' => 'COL-LITERAL', 'origin' => '100%_ Jakarta']);
        TsoShipment::create(['no_rangka' => 'COL-OTHER', 'origin' => '100XX Jakarta']);
        $columns = $this->filters('tso', ['origin' => '100%_']);
        $columns[2]['name'] = 'origin) OR 1=1 --';
        $this->postJson(route('admin.reports.data'), ['type' => 'tso', 'columns' => $columns])
            ->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.no_rangka', 'COL-LITERAL');
        $this->postJson(route('admin.reports.data'), [
            'type' => 'tso', 'columns' => $this->filters('tso', ['origin' => "' OR 1=1 --"]),
        ])->assertOk()->assertJsonPath('recordsFiltered', 0);
    }

    public function test_invalid_column_search_values_are_rejected(): void
    {
        foreach ([str_repeat('x', 101), ['nested']] as $value) {
            $columns = $this->filters('tso', []);
            $columns[1]['search']['value'] = $value;
            $this->postJson(route('admin.reports.data'), ['type' => 'tso', 'columns' => $columns])
                ->assertUnprocessable()->assertJsonValidationErrors('columns.1.search.value');
        }
    }

    private function columns(string $type): array
    {
        return $type === 'dso' ? ReportService::dsoColumns() : ReportService::specialColumns(SpecialShipmentType::get($type));
    }

    private function filters(string $type, array $values): array
    {
        return array_map(fn (array $column) => [
            'data' => $column['data'], 'name' => $column['data'], 'searchable' => true,
            'search' => ['value' => $values[$column['data']] ?? '', 'regex' => false],
        ], $this->columns($type));
    }
}
