<?php

namespace Tests\Feature\Admin;

use App\Models\Shipment;
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

    public function test_report_validates_date_range(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'date_from' => '2026-04-10',
            'date_to' => '2026-04-01',
        ]));

        $response->assertSessionHasErrors('date_to');
    }

    public function test_vendor_cannot_access_reports(): void
    {
        $vendor = User::factory()->vendor()->create();
        $response = $this->actingAs($vendor)->get(route('admin.reports.index'));
        $response->assertStatus(403);
    }
}
