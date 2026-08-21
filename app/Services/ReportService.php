<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

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
        ?int $month = null,
        ?int $year = null,
    ): Collection {
        return Shipment::query()
            ->with(['shipmentUpdates.vendor'])
            ->when($search, function ($query, $search) {
                $query->where('no_rangka', 'like', "%{$search}%");
            })
            ->when($month !== null, function ($query) use ($month) {
                $query->whereMonth('terima_do', $month);
            })
            ->when($year !== null, function ($query) use ($year) {
                $query->whereYear('terima_do', $year);
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
            $row['pos_'.md5($position)] = $update?->scan_date?->format('d-M-y') ?? '-';
        }

        $documentPath = $shipment->shipmentUpdates->firstWhere(fn ($u) => $u->document_path);
        $row['document_scan'] = $documentPath
            ? Storage::disk(config('filesystems.document_disk'))->url($documentPath->document_path)
            : '-';

        return $row;
    }
}
