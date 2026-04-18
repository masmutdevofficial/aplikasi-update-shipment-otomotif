<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportService
{
    public function getReport(
        ?string $search = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        return Shipment::query()
            ->with(['shipmentUpdates.vendor'])
            ->when($search, function ($query, $search) {
                $query->where('no_rangka', 'like', "%{$search}%");
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getReportForExport(
        ?string $search = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Collection {
        return Shipment::query()
            ->with(['shipmentUpdates.vendor'])
            ->when($search, function ($query, $search) {
                $query->where('no_rangka', 'like', "%{$search}%");
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->get();
    }

    /**
     * Flatten shipment data with vendor position dates for display/export.
     */
    public static function flattenShipment(Shipment $shipment): array
    {
        $positions = Vendor::positions();
        $updates = $shipment->shipmentUpdates->keyBy('position');

        $row = [
            'lokasi' => $shipment->lokasi,
            'no_do' => $shipment->no_do,
            'type_kendaraan' => $shipment->type_kendaraan,
            'no_rangka' => $shipment->no_rangka,
            'no_engine' => $shipment->no_engine,
            'warna' => $shipment->warna,
            'asal_pdc' => $shipment->asal_pdc,
            'kota' => $shipment->kota,
            'tujuan_pengiriman' => $shipment->tujuan_pengiriman,
            'terima_do' => $shipment->terima_do?->format('d-M-y'),
            'keluar_dari_pdc' => $shipment->keluar_dari_pdc?->format('d-M-y'),
            'nama_kapal' => $shipment->nama_kapal,
            'keberangkatan_kapal' => $shipment->keberangkatan_kapal?->format('d-M-y'),
        ];

        // Add vendor position dates
        foreach ($positions as $position) {
            $update = $updates->get($position);
            $row['pos_' . md5($position)] = $update?->scan_date?->format('d-M-y') ?? '-';
        }

        // Document link (take from any update that has one)
        $docLink = $shipment->shipmentUpdates->firstWhere(fn ($u) => $u->document_link);
        $row['document_link'] = $docLink?->document_link ?? '-';

        return $row;
    }
}
