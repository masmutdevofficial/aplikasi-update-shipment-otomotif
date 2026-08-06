<?php

namespace App\Services;

use App\Models\Shipment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    public function __construct(
        protected PendingVinService $pendingVinService,
    ) {}

    public function getShipments(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Shipment::query()
            ->when($search, function ($query, $search) {
                $query->where('no_rangka', 'like', "%{$search}%")
                      ->orWhere('no_do', 'like', "%{$search}%")
                      ->orWhere('lokasi', 'like', "%{$search}%")
                      ->orWhere('type_kendaraan', 'like', "%{$search}%")
                      ->orWhere('nama_kapal', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all shipments without pagination (for DataTables).
     */
    public function getAllShipments(): \Illuminate\Database\Eloquent\Collection
    {
        return Shipment::query()->latest()->get();
    }

    public function createShipment(array $data, string $createdBy): Shipment
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $data['created_by'] = $createdBy;
            $data['updated_by'] = $createdBy;

            $shipment = Shipment::create($data);
            $this->pendingVinService->matchForShipment($shipment);

            return $shipment;
        });
    }

    public function updateShipment(Shipment $shipment, array $data, string $updatedBy): Shipment
    {
        $data['updated_by'] = $updatedBy;

        $shipment->update($data);

        return $shipment;
    }

    public function deleteShipment(Shipment $shipment): void
    {
        $shipment->delete();
    }

    /**
     * Delete the shipments identified by their UUIDs.
     *
     * @param array<int, string> $shipmentIds
     */
    public function deleteShipments(array $shipmentIds): int
    {
        return Shipment::whereIn('id', $shipmentIds)->delete();
    }

}
