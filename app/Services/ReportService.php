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
    /** @return array<int, array{data: string, label: string, kind: string, orderable: bool}> */
    public static function dsoColumns(): array
    {
        return [
            ['data' => 'row_number', 'label' => 'No', 'kind' => 'number', 'orderable' => false],
            ['data' => 'lokasi', 'label' => 'Lokasi', 'kind' => 'text', 'orderable' => true],
            ['data' => 'no_do', 'label' => 'No. DO', 'kind' => 'text', 'orderable' => true],
            ['data' => 'type_kendaraan', 'label' => 'Type Kendaraan', 'kind' => 'text', 'orderable' => true],
            ['data' => 'no_rangka', 'label' => 'No. Rangka', 'kind' => 'code', 'orderable' => true],
            ['data' => 'no_engine', 'label' => 'No. Engine', 'kind' => 'text', 'orderable' => true],
            ['data' => 'warna', 'label' => 'Warna', 'kind' => 'text', 'orderable' => true],
            ['data' => 'asal_pdc', 'label' => 'Asal PDC', 'kind' => 'text', 'orderable' => true],
            ['data' => 'kota', 'label' => 'Kota', 'kind' => 'text', 'orderable' => true],
            ['data' => 'tujuan_pengiriman', 'label' => 'Tujuan Pengiriman', 'kind' => 'text', 'orderable' => true],
            ['data' => 'terima_do', 'label' => 'Terima DO', 'kind' => 'text', 'orderable' => true],
            ['data' => 'keluar_dari_pdc', 'label' => 'Keluar dari PDC', 'kind' => 'text', 'orderable' => true],
            ['data' => 'nama_kapal', 'label' => 'Nama Kapal', 'kind' => 'text', 'orderable' => true],
            ['data' => 'keberangkatan_kapal', 'label' => 'Keberangkatan Kapal', 'kind' => 'text', 'orderable' => true],
            ['data' => 'at_storage_port', 'label' => 'AT Storage Port', 'kind' => 'text', 'orderable' => true],
            ['data' => 'atd_kapal_loading', 'label' => 'ATD Kapal (Loading)', 'kind' => 'text', 'orderable' => true],
            ['data' => 'ata_kapal', 'label' => 'ATA Kapal', 'kind' => 'text', 'orderable' => true],
            ['data' => 'ata_storage_port_destination', 'label' => 'ATA Storage Port (Destination)', 'kind' => 'text', 'orderable' => true],
            ['data' => 'lead_time_do_release_pickup', 'label' => 'DO Release to Pickup', 'kind' => 'number', 'orderable' => false],
            ['data' => 'lead_time_storage_port', 'label' => 'Storage Port', 'kind' => 'number', 'orderable' => false],
            ['data' => 'dwelling_origin', 'label' => 'Dwelling Origin', 'kind' => 'number', 'orderable' => false],
            ['data' => 'lead_time_kapal_aboard', 'label' => 'Kapal (Aboard)', 'kind' => 'number', 'orderable' => false],
            ['data' => 'lead_time_storage_destination', 'label' => 'Storage Port (Destination)', 'kind' => 'number', 'orderable' => false],
            ['data' => 'dwelling_destination', 'label' => 'Dwelling Destination', 'kind' => 'number', 'orderable' => false],
            ['data' => 'sla_actual', 'label' => 'SLA Actual', 'kind' => 'number', 'orderable' => false],
            ['data' => 'sla_customer', 'label' => 'SLA Customer', 'kind' => 'number', 'orderable' => false],
            ['data' => 'sla_result', 'label' => 'Result', 'kind' => 'result', 'orderable' => false],
            ['data' => 'delay_days', 'label' => 'Keterlambatan (Hari)', 'kind' => 'delay', 'orderable' => false],
            ['data' => 'max_arrival', 'label' => 'Max Arrival', 'kind' => 'text', 'orderable' => false],
            ['data' => 'progress', 'label' => 'Progress', 'kind' => 'text', 'orderable' => false],
            ['data' => 'document_url', 'label' => 'Dokumen', 'kind' => 'document', 'orderable' => false],
        ];
    }

    /** @return array<int, array{data: string, label: string, kind: string, orderable: bool}> */
    public static function specialColumns(array $config): array
    {
        $columns = [[
            'data' => 'row_number',
            'label' => 'No',
            'kind' => 'number',
            'orderable' => false,
        ]];

        foreach ($config['fields'] as $field => $fieldConfig) {
            $columns[] = [
                'data' => $field,
                'label' => $fieldConfig['label'],
                'kind' => in_array($field, ['no_rangka', 'noka', 'no_spb'], true) ? 'code' : 'text',
                'orderable' => true,
            ];
        }

        foreach ($config['performance']['stages'] as $key => $stage) {
            $columns[] = ['data' => $key, 'label' => $stage['label'], 'kind' => 'number', 'orderable' => false];
        }

        return [
            ...$columns,
            ['data' => 'sla_actual', 'label' => 'SLA Actual', 'kind' => 'number', 'orderable' => false],
            ['data' => 'sla_result', 'label' => 'Result', 'kind' => 'result', 'orderable' => false],
            ['data' => 'delay_days', 'label' => 'Keterlambatan (Hari)', 'kind' => 'delay', 'orderable' => false],
            ['data' => 'max_arrival', 'label' => 'Max Arrival', 'kind' => 'text', 'orderable' => false],
            ['data' => 'progress', 'label' => 'Progress', 'kind' => 'text', 'orderable' => false],
            ['data' => 'document_url', 'label' => 'Dokumen', 'kind' => 'document', 'orderable' => false],
        ];
    }

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
        return collect(self::dsoColumns())
            ->reject(fn (array $column) => $column['data'] === 'row_number')
            ->pluck('label')
            ->all();
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
        $row['document_url'] = $documentPath
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
