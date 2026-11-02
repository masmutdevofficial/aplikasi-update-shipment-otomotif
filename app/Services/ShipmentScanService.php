<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentUpdate;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class ShipmentScanService
{
    /** @param array<string, mixed> $attributes */
    public function record(Shipment $shipment, array $attributes): ShipmentUpdate
    {
        return DB::transaction(function () use ($shipment, $attributes) {
            // Serialize confirmations for a shipment, including duplicate scans.
            $shipment = Shipment::query()->lockForUpdate()->findOrFail($shipment->id);

            $update = ShipmentUpdate::firstOrCreate(
                [
                    'shipment_id' => $shipment->id,
                    'position' => $attributes['position'],
                ],
                $attributes,
            );

            if ($update->wasRecentlyCreated && $update->position === Vendor::POSITION_KELUAR_PDC) {
                // Persist this milestone so admin forms, filters and dashboards
                // all use the same departure date as the confirmed scan.
                $shipment->update([
                    'keluar_dari_pdc' => $update->scan_date,
                    'updated_by' => $update->updated_by,
                ]);
            }

            return $update;
        });
    }
}
