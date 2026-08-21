<?php

namespace Tests\Feature\Admin;

use App\Models\TsoShipment;
use App\Models\User;
use Tests\TestCase;

class TsoDashboardTest extends TestCase
{
    public function test_all_tso_shipment_fields_may_be_null(): void
    {
        $shipment = TsoShipment::create([]);

        $this->assertDatabaseHas('tso_shipments', [
            'id' => $shipment->id,
            'unit_type' => null,
            'origin' => null,
            'destination' => null,
            'detail_destination' => null,
            'no_rangka' => null,
            'doc' => null,
            'do_date' => null,
            'pu_date' => null,
            'door_to_port' => null,
            'port_to_port' => null,
            'port_to_door' => null,
            'vessel_ptp' => null,
            'sla_customer' => null,
        ]);
    }

    public function test_tso_dashboard_displays_tso_shipment_columns_and_data(): void
    {
        $admin = User::factory()->admin()->create();
        TsoShipment::create([
            'unit_type' => 'New Avanza 1.3 E M/T',
            'origin' => 'PDC Sunter Lake',
            'destination' => 'Medan',
            'detail_destination' => 'TSO-Medan Amplas',
            'no_rangka' => 'MHKAA1BY9SJ004075',
            'do_date' => '2025-11-12',
            'pu_date' => '2025-11-13',
            'door_to_port' => '2025-11-13',
            'port_to_port' => '2025-11-17',
            'port_to_door' => '2025-11-21',
            'vessel_ptp' => 'Serasi V',
            'sla_customer' => 8,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard?type=tso');

        $response->assertOk();
        $response->assertSeeInOrder([
            'Unit Type',
            'Origin',
            'Destination',
            'Detail Destination',
            'No Rangka',
            'Doc',
            'DO Date',
            'PU Date',
            'Door to Port',
            'Port to Port',
            'Port to Door',
            'Vessel PTP',
        ]);
        $response->assertSee('Keterlambatan (Hari)');
        $response->assertSee('SLA Actual');
        $response->assertSeeInOrder(['Posisi Barang per Kota (Destination)', 'Data Shipment TSO']);

        $this->actingAs($admin)
            ->getJson(route('admin.special-shipments.data', ['type' => 'tso', 'length' => 10]))
            ->assertOk()
            ->assertJsonFragment([
                'no_rangka' => 'MHKAA1BY9SJ004075',
                'vessel_ptp' => 'Serasi V',
                'sla_result' => 'LATE',
                'delay_days' => 1,
            ]);
    }

    public function test_tso_dashboard_shows_dd_performance_info_cards(): void
    {
        $admin = User::factory()->admin()->create();

        TsoShipment::create(['do_date' => '2025-11-01']);
        TsoShipment::create(['do_date' => '2025-11-02', 'pu_date' => '2025-11-03']);
        TsoShipment::create([
            'do_date' => '2025-11-03',
            'pu_date' => '2025-11-04',
            'door_to_port' => '2025-11-05',
            'port_to_port' => '2025-11-06',
        ]);
        TsoShipment::create([
            'do_date' => '2025-11-04',
            'pu_date' => '2025-11-05',
            'door_to_port' => '2025-11-06',
            'port_to_port' => '2025-11-07',
            'port_to_door' => '2025-11-08',
        ]);
        TsoShipment::create([]);

        $this->actingAs($admin)
            ->get('/admin/dashboard?type=tso&month=11&year=2025')
            ->assertOk()
            ->assertSee('TSO 2 — DD Performance')
            ->assertSee('TOTAL SHIPMENT / Terima DO')
            ->assertSee('dashboard-metric-card-featured')
            ->assertSee('DTP (Delivery To Port)')
            ->assertSee('PTP (Port To Port)')
            ->assertSee('PTD (Port To Door)')
            ->assertDontSee('Total Vendor')
            ->assertViewHas('tsoDoPerformance', fn (array $stats) =>
                $stats['total_received'] === ['count' => 4, 'percentage' => 100.0]
                && $stats['not_departed_pdc'] === ['count' => 1, 'percentage' => 25.0]
                && $stats['dtp'] === ['count' => 1, 'percentage' => 25.0]
                && $stats['ptp'] === ['count' => 1, 'percentage' => 25.0]
                && $stats['ptd'] === ['count' => 1, 'percentage' => 25.0]
            );
    }
}
