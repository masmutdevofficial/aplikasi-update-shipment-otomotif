<?php

namespace Tests\Feature\Vendor;

use App\Imports\ShipmentImport;
use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use App\Services\OcrService;
use App\Services\ShipmentService;
use App\Support\ShipmentUploadTemplate;
use Database\Seeders\KeluarPdcSeeder;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

class KeluarPdcScanTest extends TestCase
{
    private User $operator;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-01 00:30:00', 'Asia/Jakarta'));
        $this->admin = User::factory()->admin()->create();
        $this->seed(KeluarPdcSeeder::class);
        $this->operator = User::query()->where('email', 'vendor.keluarpdc@gmail.com')->firstOrFail();
    }

    public function test_seeded_operator_can_login_and_scan_then_admin_sees_departure_date(): void
    {
        $shipment = $this->shipment();

        $this->post('/login', [
            'email' => $this->operator->email,
            'password' => 'Vendor@Pdc2026!',
        ])->assertRedirect(route('vendor.dashboard'));

        $this->get(route('vendor.scanner'))->assertOk()->assertSee('Keluar dari PDC');
        $this->mock(OcrService::class)
            ->shouldReceive('extractVin')->once()->andReturn($shipment->no_rangka);
        $this->postJson(route('vendor.scanner.scan'), [
            'image' => 'data:image/jpeg;base64,'.base64_encode('test-scan'),
        ])->assertOk()->assertJsonPath('vin', $shipment->no_rangka);

        // The backend must use the account's position and Jakarta date,
        // regardless of extra fields submitted by a client.
        $this->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $shipment->no_rangka,
            'position' => 'ATA Kapal',
            'scan_date' => '2020-01-01',
            'keluar_dari_pdc' => '2020-01-01',
        ])->assertOk()
            ->assertJsonPath('data.position', Vendor::POSITION_KELUAR_PDC)
            ->assertJsonPath('data.scan_date', '01-Nov-26');

        $shipment->refresh();
        $this->assertSame('2026-11-01', $shipment->keluar_dari_pdc->toDateString());
        $this->assertSame($this->operator->id, $shipment->updated_by);
        $this->assertSame('Keluar dari PDC', $shipment->currentPosition());
        $this->assertDatabaseHas('shipment_updates', [
            'shipment_id' => $shipment->id,
            'vendor_id' => $this->operator->vendor->id,
            'position' => Vendor::POSITION_KELUAR_PDC,
            'created_by' => $this->operator->id,
        ]);
        $this->assertDatabaseHas('scan_histories', [
            'user_id' => $this->operator->id,
            'no_rangka' => $shipment->no_rangka,
        ]);
        $this->get(route('admin.shipments.index'))->assertForbidden();

        $this->actingAs($this->admin)
            ->postJson(route('admin.shipments.data'), [
                'search' => ['value' => $shipment->no_rangka],
            ])->assertOk()->assertJsonPath('data.0.keluar_dari_pdc', '01-Nov-26');

        $this->get(route('admin.shipments.edit', $shipment))
            ->assertOk()->assertSee('value="2026-11-01"', false);

        $this->postJson(route('admin.reports.data'), [
            'type' => 'dso',
            'date_field' => 'keluar_dari_pdc',
            'date' => '2026-11-01',
        ])->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.keluar_dari_pdc', '01-Nov-26')
            ->assertJsonPath('data.0.progress', 'Pickup');

        $this->get(route('admin.dashboard', ['type' => 'dso', 'month' => 11, 'year' => 2026]))
            ->assertOk()
            ->assertViewHas('dsoDoPerformance', fn (array $stats) => $stats['departed_pdc']['count'] === 1);
    }

    public function test_confirmed_pdc_scan_replaces_imported_departure_date_only_once(): void
    {
        $shipment = $this->shipment(['keluar_dari_pdc' => '2026-10-30']);

        $this->actingAs($this->operator)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $shipment->no_rangka,
        ])->assertOk();

        $this->travelTo(Carbon::parse('2026-11-02 10:00:00', 'Asia/Jakarta'));
        $this->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $shipment->no_rangka,
        ])->assertStatus(409);

        $this->assertSame('2026-11-01', $shipment->fresh()->keluar_dari_pdc->toDateString());
        $this->assertDatabaseCount('shipment_updates', 1);
        $this->assertDatabaseCount('scan_histories', 1);
    }

    public function test_other_positions_cannot_set_the_pdc_departure_date(): void
    {
        $shipment = $this->shipment();
        $storageUser = User::factory()->vendor()->create();
        Vendor::create([
            'user_id' => $storageUser->id,
            'vendor_name' => 'Storage Port',
            'position' => 'AT Storage Port',
        ]);

        $this->actingAs($storageUser)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $shipment->no_rangka,
            'position' => Vendor::POSITION_KELUAR_PDC,
            'keluar_dari_pdc' => '2026-11-01',
        ])->assertOk()->assertJsonPath('data.position', 'AT Storage Port');

        $this->assertNull($shipment->fresh()->keluar_dari_pdc);
    }

    public function test_pending_pdc_scan_uses_original_scan_date_when_shipment_is_imported(): void
    {
        $vin = 'MHFAA8GS4N0000002';
        $this->actingAs($this->operator)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $vin,
        ])->assertNotFound()->assertJsonPath('pending_allowed', true);
        $this->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $vin,
            'save_as_pending' => true,
        ])->assertCreated()->assertJsonPath('pending', true);
        $this->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $vin,
            'save_as_pending' => true,
        ])->assertStatus(409);

        $this->travelTo(Carbon::parse('2026-11-03 10:00:00', 'Asia/Jakarta'));
        $headers = ShipmentUploadTemplate::dsoHeadings();
        $values = [
            'Lokasi' => 'Jakarta',
            'No. Rangka' => $vin,
            'Type Kendaraan' => 'Avanza',
            'No. Engine' => 'ENG-PDC-001',
            'Warna' => 'Putih',
            'Asal PDC' => 'Sunter',
            'Kota' => 'PONTIANAK',
            'Tujuan Pengiriman' => 'PTD',
            'Terima DO' => '2026-11-01',
            'Keluar dari PDC' => '2026-11-02',
        ];
        $row = array_map(fn (string $header) => $values[$header] ?? null, $headers);
        $import = new ShipmentImport($this->admin->id);
        $import->collection(collect([collect($headers), collect($row)]));

        $this->assertSame([], $import->errors);
        $this->assertSame(1, $import->matchedPendingCount);
        $shipment = Shipment::query()->where('no_rangka', $vin)->firstOrFail();
        $this->assertSame('2026-11-01', $shipment->keluar_dari_pdc->toDateString());
        $this->assertSame($this->operator->id, $shipment->updated_by);
        $this->assertDatabaseMissing('pending_vins', ['no_rangka' => $vin]);
        $this->assertDatabaseCount('shipment_updates', 1);
        $this->assertDatabaseCount('scan_histories', 1);

        $this->actingAs($this->admin)->postJson(route('admin.shipments.data'), [])
            ->assertOk()->assertJsonPath('data.0.keluar_dari_pdc', '01-Nov-26');
    }

    public function test_pending_pdc_scan_also_populates_manually_created_shipment(): void
    {
        $vin = 'MHFAA8GS4N0000003';
        $this->actingAs($this->operator)->postJson(route('vendor.scanner.confirm'), [
            'no_rangka' => $vin,
            'save_as_pending' => true,
        ])->assertCreated();

        $this->travelTo(Carbon::parse('2026-11-03 10:00:00', 'Asia/Jakarta'));
        $shipment = app(ShipmentService::class)->createShipment(
            Shipment::factory()->make(['no_rangka' => $vin, 'keluar_dari_pdc' => null])->getAttributes(),
            $this->admin->id,
        );

        $this->assertSame('2026-11-01', $shipment->fresh()->keluar_dari_pdc->toDateString());
        $this->assertDatabaseMissing('pending_vins', ['no_rangka' => $vin]);
    }

    public function test_failed_history_write_rolls_back_the_scan_and_departure_date(): void
    {
        $shipment = $this->shipment();
        ScanHistory::creating(function () {
            throw new RuntimeException('Simulated history write failure');
        });

        try {
            $this->actingAs($this->operator)->postJson(route('vendor.scanner.confirm'), [
                'no_rangka' => $shipment->no_rangka,
            ])->assertStatus(500);
        } finally {
            ScanHistory::flushEventListeners();
        }

        $this->assertNull($shipment->fresh()->keluar_dari_pdc);
        $this->assertSame($this->admin->id, $shipment->fresh()->updated_by);
        $this->assertDatabaseCount('shipment_updates', 0);
        $this->assertDatabaseCount('scan_histories', 0);
    }

    public function test_admin_can_assign_the_pdc_position_from_vendor_management(): void
    {
        $user = User::factory()->vendor()->create();
        $this->actingAs($this->admin)->post(route('admin.vendors.store'), [
            'user_id' => $user->id,
            'vendor_name' => 'Petugas PDC',
            'position' => Vendor::POSITION_KELUAR_PDC,
        ])->assertRedirect(route('admin.vendors.index'))->assertSessionHasNoErrors();

        $this->assertSame(Vendor::POSITION_KELUAR_PDC, $user->vendor->position);
    }

    private function shipment(array $attributes = []): Shipment
    {
        return Shipment::factory()->create([
            'no_rangka' => 'MHFAA8GS4N0000001',
            'kota' => 'PONTIANAK',
            'tujuan_pengiriman' => 'PTD',
            'terima_do' => '2026-11-01',
            'keluar_dari_pdc' => null,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
            ...$attributes,
        ]);
    }
}
