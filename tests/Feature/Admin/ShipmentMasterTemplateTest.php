<?php

namespace Tests\Feature\Admin;

use App\Exports\ShipmentTemplateExport;
use App\Exports\SpecialShipmentTemplateExport;
use App\Models\User;
use App\Support\SpecialShipmentType;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ShipmentMasterTemplateTest extends TestCase
{
    public function test_upload_pages_require_and_link_the_official_master_template(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.shipments.import.form'))
            ->assertOk()
            ->assertSee('Wajib Gunakan Master Template DSO')
            ->assertSee(route('admin.shipments.template'), false)
            ->assertSee('accept=".xlsx"', false);

        foreach (['tso', 'iso-darat', 'iso-laut'] as $type) {
            $config = SpecialShipmentType::get($type);

            $this->actingAs($admin)
                ->get(route('admin.special-shipments.import.form', $type))
                ->assertOk()
                ->assertSee('Wajib Gunakan Master Template ' . $config['short_label'])
                ->assertSee(route('admin.special-shipments.template', $type), false)
                ->assertSee('accept=".xlsx"', false);
        }
    }

    public function test_downloaded_dso_master_template_example_can_be_uploaded_without_errors(): void
    {
        $admin = User::factory()->admin()->create();
        $file = $this->workbook(
            'Master_Upload_DSO.xlsx',
            new ShipmentTemplateExport(),
        );

        $this->actingAs($admin)
            ->post(route('admin.shipments.import'), ['file' => $file])
            ->assertRedirect(route('admin.shipments.index'))
            ->assertSessionHas('success')
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('shipments', [
            'no_rangka' => 'MHKM1BA3JFK123456',
            'no_do' => 'DO-DSO-001',
            'at_ptd_dooring' => '2026-08-06',
        ]);
    }

    public function test_each_special_master_template_example_can_be_uploaded_without_errors(): void
    {
        $admin = User::factory()->admin()->create();
        $expectations = [
            'tso' => ['table' => 'tso_shipments', 'field' => 'no_rangka', 'value' => 'MHKM1BA3JFK123457'],
            'iso-darat' => ['table' => 'iso_darat_shipments', 'field' => 'no_spb', 'value' => 'SPB-ISO-D-001'],
            'iso-laut' => ['table' => 'iso_laut_shipments', 'field' => 'noka', 'value' => 'MHKM1BA3JFK123458'],
        ];

        foreach ($expectations as $type => $expected) {
            $config = SpecialShipmentType::get($type);
            $file = $this->workbook(
                'Master_Upload_' . str_replace(' ', '_', $config['short_label']) . '.xlsx',
                new SpecialShipmentTemplateExport($type, $config),
            );

            $this->actingAs($admin)
                ->post(route('admin.special-shipments.import', $type), ['file' => $file])
                ->assertRedirect(route('admin.special-shipments.index', $type))
                ->assertSessionHas('success')
                ->assertSessionDoesntHaveErrors();

            $this->assertDatabaseHas($expected['table'], [
                $expected['field'] => $expected['value'],
            ]);
        }
    }

    public function test_upload_rejects_workbook_with_modified_headers(): void
    {
        $admin = User::factory()->admin()->create();
        $badExport = new class implements FromArray {
            public function array(): array
            {
                return [
                    ['No. Rangka', 'Lokasi'],
                    ['MHKM1BA3JFK123459', 'Jakarta'],
                ];
            }
        };

        $this->actingAs($admin)
            ->from(route('admin.shipments.import.form'))
            ->post(route('admin.shipments.import'), [
                'file' => $this->workbook('Master_Upload_DSO.xlsx', $badExport),
            ])
            ->assertRedirect(route('admin.shipments.import.form'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('shipments', ['no_rangka' => 'MHKM1BA3JFK123459']);
    }

    private function workbook(string $name, object $export): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            Excel::raw($export, ExcelFormat::XLSX),
        );
    }
}
