<?php

namespace App\Services;

use App\Models\Shipment;
use Illuminate\Pagination\LengthAwarePaginator;

class ShipmentService
{
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
        $data['created_by'] = $createdBy;
        $data['updated_by'] = $createdBy;

        return Shipment::create($data);
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
}
