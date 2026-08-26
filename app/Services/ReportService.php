<?php

namespace App\Services;

use App\Models\PendingVin;
use App\Models\Shipment;
use App\Models\ShipmentUpdate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
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
     * @return array<int, string>
     */
    public static function dsoHeadings(): array
    {
        return [
            'Lokasi',
            'No. DO',
            'Type Kendaraan',
            'No. Rangka',
            'No. Engine',
            'Warna',
            'Asal PDC',
            'Kota',
            'Tujuan Pengiriman',
            'Terima DO',
            'Keluar dari PDC',
            'Nama Kapal',
            'Keberangkatan Kapal',
            'AT Storage Port',
            'ATD Kapal (Loading)',
            'ATA Kapal',
            'ATA Storage Port (Destination)',
            'DO Release to Pickup',
            'Storage Port',
            'Dwelling Origin',
            'Kapal (Aboard)',
            'Storage Port (Destination)',
            'Dwelling Destination',
            'SLA Actual',
            'SLA Customer',
            'Result',
            'Keterlambatan (Hari)',
            'Max Arrival',
            'Progress',
            'Dokumen Scan',
        ];
    }

    /**
     * Flatten shipment DSO sesuai urutan kolom laporan dan export.
     */
    public static function flattenShipment(Shipment $shipment): array
    {
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
            'at_storage_port' => $shipment->at_storage_port?->format('d-M-y'),
            'atd_kapal_loading' => $shipment->atd_kapal_loading?->format('d-M-y'),
            'ata_kapal' => $shipment->ata_kapal?->format('d-M-y'),
            'ata_storage_port_destination' => $shipment->ata_storage_port_destination?->format('d-M-y'),
            'lead_time_do_release_pickup' => $shipment->leadTimeDoReleaseToPickup(),
            'lead_time_storage_port' => $shipment->leadTimeStoragePort(),
            'dwelling_origin' => $shipment->dwellingOrigin(),
            'lead_time_kapal_aboard' => $shipment->leadTimeKapalAboard(),
            'lead_time_storage_destination' => $shipment->leadTimeStoragePortDestination(),
            'dwelling_destination' => $shipment->dwellingDestination(),
            'sla_actual' => $shipment->slaActual(),
            'sla_customer' => $shipment->slaCustomer(),
            'sla_result' => $shipment->slaResult(),
            'delay_days' => $shipment->delayDays(),
            'max_arrival' => $shipment->maxArrival()?->format('d-M-y'),
            'progress' => $shipment->shipmentProgress(),
        ];

        $documentPath = $shipment->shipmentUpdates->first(fn ($update) => $update->document_path);
        $row['document_scan'] = $documentPath
            ? Storage::disk(config('filesystems.document_disk'))->url($documentPath->document_path)
            : '-';

        return $row;
    }

    /**
     * Resolve dokumen special shipment dari dokumen scan atau VIN pending
     * berdasarkan field identitas masing-masing tipe shipment.
     *
     * @return SupportCollection<string, string>
     */
    public static function specialDocumentUrls(SupportCollection $shipments, string $identityField): SupportCollection
    {
        $identities = $shipments
            ->pluck($identityField)
            ->map(fn (mixed $identity) => strtoupper(trim((string) $identity)))
            ->filter()
            ->unique()
            ->values();

        if ($identities->isEmpty()) {
            return collect();
        }

        $pendingDocuments = PendingVin::query()
            ->whereNotNull('document_path')
            ->whereIn('no_rangka', $identities)
            ->latest('updated_at')
            ->get()
            ->unique(fn (PendingVin $pending) => strtoupper(trim($pending->no_rangka)))
            ->mapWithKeys(fn (PendingVin $pending) => [
                strtoupper(trim($pending->no_rangka)) => Storage::disk(config('filesystems.document_disk'))->url($pending->document_path),
            ]);
        $shipmentDocuments = ShipmentUpdate::query()
            ->whereNotNull('document_path')
            ->whereHas('shipment', fn ($query) => $query->whereIn('no_rangka', $identities))
            ->with('shipment:id,no_rangka')
            ->latest('updated_at')
            ->get()
            ->unique(fn (ShipmentUpdate $update) => strtoupper(trim($update->shipment->no_rangka)))
            ->mapWithKeys(fn (ShipmentUpdate $update) => [
                strtoupper(trim($update->shipment->no_rangka)) => Storage::disk(config('filesystems.document_disk'))->url($update->document_path),
            ]);

        return $pendingDocuments->merge($shipmentDocuments);
    }

    public static function specialDocumentUrl(SupportCollection $documentUrls, mixed $identity): ?string
    {
        return $documentUrls->get(strtoupper(trim((string) $identity)));
    }
}
