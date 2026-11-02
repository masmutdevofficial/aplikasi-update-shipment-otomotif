<?php

namespace App\Services;

use App\Models\PendingVin;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class PendingVinService
{
    public function __construct(
        protected ShipmentScanService $scanService,
    ) {}

    /**
     * Move all matching pending scans into the shipment timeline.
     */
    public function matchForShipment(Shipment $shipment): int
    {
        return DB::transaction(function () use ($shipment) {
            $pendingVins = PendingVin::query()
                ->where('no_rangka', $shipment->no_rangka)
                ->lockForUpdate()
                ->get();

            foreach ($pendingVins as $pending) {
                $this->scanService->record(
                    $shipment,
                    [
                        'position' => $pending->position,
                        'vendor_id' => $pending->vendor_id,
                        'scan_date' => $pending->scan_date,
                        'document_path' => $pending->document_path,
                        'scan_photo_path' => $pending->scan_photo_path,
                        'created_by' => $pending->created_by,
                        'updated_by' => $pending->updated_by,
                    ],
                );

                $pending->delete();
            }

            return $pendingVins->count();
        });
    }
}
