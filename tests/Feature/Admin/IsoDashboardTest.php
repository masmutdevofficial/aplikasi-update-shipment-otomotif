<?php

namespace Tests\Feature\Admin;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\User;
use Tests\TestCase;

class IsoDashboardTest extends TestCase
{
    public function test_all_iso_business_fields_may_be_null(): void
    {
        $darat = IsoDaratShipment::create([]);
        $laut = IsoLautShipment::create([]);

        $this->assertDatabaseHas('iso_darat_shipments', [
            'id' => $darat->id,
            'source_no' => null,
            'no_so_booking' => null,
            'no_quotation' => null,
            'no_contract' => null,
            'cargo_no_ka' => null,
            'no_spb' => null,
            'kategori_moda' => null,
            'origin' => null,
            'destination' => null,
            'area' => null,
            'nomor_driver' => null,
            'terima_do' => null,
            'keluar_dari_pdc' => null,
            'at_ptd_dtd' => null,
            'sla_customer' => null,
        ]);
        $this->assertDatabaseHas('iso_laut_shipments', [
            'id' => $laut->id,
            'source_no' => null,
            'no_booking_dtp' => null,
            'no_booking_ptp' => null,
            'no_booking_ptd' => null,
            'no_quotation_dtp' => null,
            'no_quotation_ptp' => null,
            'no_quotation_ptd' => null,
            'no_contract_dtp' => null,
            'no_contract_ptp' => null,
            'no_contract_ptd' => null,
            'cargo' => null,
            'noka' => null,
            'kategori_moda' => null,
            'origin' => null,
            'destination' => null,
            'tujuan_pengiriman' => null,
            'terima_do' => null,
            'keluar_dari_pdc' => null,
            'jenis_kapal' => null,
            'at_storage_port' => null,
            'atd_kapal_loading' => null,
            'ata_kapal' => null,
            'ata_storage_port_destination' => null,
            'at_ptd_dtd' => null,
            'sla_customer' => null,
        ]);
    }

    public function test_iso_darat_dashboard_displays_its_columns_and_data(): void
    {
        $admin = User::factory()->admin()->create();
        IsoDaratShipment::create([
            'source_no' => 1,
            'no_so_booking' => '3100551770',
            'no_spb' => 'MHCFTR90TSJ001133',
            'kategori_moda' => 'LT Darat',
            'nomor_driver' => '081234567890',
            'terima_do' => '2025-09-02',
            'keluar_dari_pdc' => '2025-09-02',
            'at_ptd_dtd' => '2025-09-03',
            'sla_customer' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard?type=iso&iso_type=darat');

        $response->assertOk();
        $response->assertSee('Data Shipment ISO Darat');
        $response->assertSee('NO SO / BOOKING');
        $response->assertSee('Nomor Driver');
        $response->assertSee('Keterlambatan (Hari)');
        $response->assertSee('SLA Actual');

        $this->actingAs($admin)
            ->getJson(route('admin.special-shipments.data', ['type' => 'iso-darat', 'length' => 10]))
            ->assertOk()
            ->assertJsonFragment([
                'no_so_booking' => '3100551770',
                'no_spb' => 'MHCFTR90TSJ001133',
                'nomor_driver' => '081234567890',
                'sla_result' => 'OTD',
                'delay_days' => 0,
            ]);
    }

    public function test_iso_laut_dashboard_displays_its_columns_and_data(): void
    {
        $admin = User::factory()->admin()->create();
        IsoLautShipment::create([
            'source_no' => 4257,
            'no_booking_dtp' => '3100553433',
            'noka' => 'MHCPHR54CSJ570508',
            'kategori_moda' => 'LT Laut',
            'at_ptd_dtd' => '#VALUE!',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard?type=iso&iso_type=laut');

        $response->assertOk();
        $response->assertSee('Data Shipment ISO Laut');
        $response->assertSee('NO BOOKING DTP');
        $response->assertSee('Keterlambatan (Hari)');

        $this->actingAs($admin)
            ->getJson(route('admin.special-shipments.data', ['type' => 'iso-laut', 'length' => 10]))
            ->assertOk()
            ->assertJsonFragment([
                'no_booking_dtp' => '3100553433',
                'noka' => 'MHCPHR54CSJ570508',
                'at_ptd_dtd' => '#VALUE!',
            ]);
    }
}
