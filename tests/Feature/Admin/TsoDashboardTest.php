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
            'vessel_ptp' => 'Serasi V',
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
        $response->assertSee('MHKAA1BY9SJ004075');
        $response->assertSee('Serasi V');
    }
}
