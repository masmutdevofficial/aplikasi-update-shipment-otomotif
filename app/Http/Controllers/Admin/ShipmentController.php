<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ShipmentTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShipmentRequest;
use App\Http\Requests\Admin\UpdateShipmentRequest;
use App\Imports\ShipmentImport;
use App\Models\Shipment;
use App\Services\ShipmentService;
use App\Support\DsoSla;
use App\Support\ShipmentDashboard;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
    ) {}

    public function index(Request $request)
    {
        $delayStats = DsoSla::delayStatistics();
        $slaDestinations = DsoSla::destinations();

        return view('admin.shipments.index', compact('delayStats', 'slaDestinations'));
    }

    public function data(Request $request)
    {
        $columns = [
            'lokasi',
            'no_do',
            'type_kendaraan',
            'no_rangka',
            'no_engine',
            'warna',
            'asal_pdc',
            'kota',
            'tujuan_pengiriman',
            'terima_do',
            'keluar_dari_pdc',
            'nama_kapal',
            'keberangkatan_kapal',
            'at_storage_port',
            'atd_kapal_loading',
            'ata_kapal',
            'ata_storage_port_destination',
            'at_ptd_dooring',
        ];
        $month = $this->validMonth($request->input('month'));
        $year = $this->validYear($request->input('year'));
        $query = Shipment::query()
            ->when($month !== null, fn ($builder) => $builder->whereMonth('terima_do', $month))
            ->when($year !== null, fn ($builder) => $builder->whereYear('terima_do', $year));
        $recordsTotal = (clone $query)->count();
        $search = trim((string) $request->input('search.value', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($columns, $search) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $recordsFiltered = (clone $query)->count();
        $orderColumn = (string) $request->input('columns.'.(int) $request->input('order.0.column', 2).'.name', 'lokasi');
        $orderColumn = in_array($orderColumn, $columns, true) ? $orderColumn : 'lokasi';
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(10, (int) $request->input('length', 10)));
        $shipments = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $shipments->map(fn (Shipment $shipment, int $index) => [
            'id' => $shipment->id,
            'row_number' => $start + $index + 1,
            'lokasi' => e($shipment->lokasi ?? '-'),
            'no_do' => e($shipment->no_do ?? '-'),
            'type_kendaraan' => e($shipment->type_kendaraan ?? '-'),
            'no_rangka' => e($shipment->no_rangka ?? '-'),
            'no_engine' => e($shipment->no_engine ?? '-'),
            'warna' => e($shipment->warna ?? '-'),
            'asal_pdc' => e($shipment->asal_pdc ?? '-'),
            'kota' => e($shipment->kota ?? '-'),
            'tujuan_pengiriman' => e($shipment->tujuan_pengiriman ?? '-'),
            'terima_do' => $shipment->terima_do?->format('d-M-y') ?? '-',
            'keluar_dari_pdc' => $this->displayDoHoldDate($shipment, 'keluar_dari_pdc'),
            'nama_kapal' => $shipment->isDoHold() ? 'DO HOLD' : e($shipment->nama_kapal ?? '-'),
            'keberangkatan_kapal' => $this->displayDoHoldDate($shipment, 'keberangkatan_kapal'),
            'at_storage_port' => $this->displayDoHoldDate($shipment, 'at_storage_port'),
            'atd_kapal_loading' => $this->displayDoHoldDate($shipment, 'atd_kapal_loading'),
            'ata_kapal' => $this->displayDoHoldDate($shipment, 'ata_kapal'),
            'ata_storage_port_destination' => $this->displayDoHoldDate($shipment, 'ata_storage_port_destination'),
            'at_ptd_dooring' => $this->displayDoHoldDate($shipment, 'at_ptd_dooring'),
            'lead_time_do_release_pickup' => $shipment->leadTimeDoReleaseToPickup() ?? '-',
            'lead_time_storage_port' => $shipment->leadTimeStoragePort() ?? '-',
            'dwelling_origin' => $shipment->dwellingOrigin() ?? '-',
            'lead_time_kapal_aboard' => $shipment->leadTimeKapalAboard() ?? '-',
            'lead_time_storage_destination' => $shipment->leadTimeStoragePortDestination() ?? '-',
            'dwelling_destination' => $shipment->dwellingDestination() ?? '-',
            'sla_actual' => $shipment->slaActual() ?? '-',
            'sla_customer' => $shipment->slaCustomer() ?? '-',
            'sla_result' => $shipment->slaResult(),
            'delay_days' => $shipment->delayDays() ?? '-',
            'max_arrival' => $shipment->maxArrival()?->format('d-M-y') ?? '-',
            'progress' => e($shipment->shipmentProgress()),
            'edit_url' => route('admin.shipments.edit', $shipment),
            'delete_url' => route('admin.shipments.destroy', $shipment),
        ]);

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    private function displayDoHoldDate(Shipment $shipment, string $field): string
    {
        if ($shipment->isDoHold()) {
            return 'DO HOLD';
        }

        return $shipment->{$field}?->format('d-M-y') ?? '-';
    }

    private function validMonth(mixed $value): ?int
    {
        $month = filter_var($value, FILTER_VALIDATE_INT);

        return $month !== false && $month >= 1 && $month <= 12 ? $month : null;
    }

    private function validYear(mixed $value): ?int
    {
        $year = filter_var($value, FILTER_VALIDATE_INT);

        return $year !== false && $year >= 2000 && $year <= 2100 ? $year : null;
    }

    public function create()
    {
        return view('admin.shipments.create');
    }

    public function store(StoreShipmentRequest $request)
    {
        $shipment = $this->shipmentService->createShipment(
            data: $request->validated(),
            createdBy: auth()->id(),
        );

        return redirect()->route('admin.shipments.index')
            ->with([
                'success' => 'Data shipment berhasil ditambahkan dan otomatis tersedia di Dashboard DSO.',
                'dashboard_url' => ShipmentDashboard::url('dso', $shipment->terima_do),
                'dashboard_label' => ShipmentDashboard::label('dso'),
            ]);
    }

    public function edit(Shipment $shipment)
    {
        return view('admin.shipments.edit', compact('shipment'));
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment)
    {
        $shipment = $this->shipmentService->updateShipment(
            shipment: $shipment,
            data: $request->validated(),
            updatedBy: auth()->id(),
        );

        return redirect()->route('admin.shipments.index')
            ->with([
                'success' => 'Data shipment berhasil diperbarui dan perubahan otomatis tersedia di Dashboard DSO.',
                'dashboard_url' => ShipmentDashboard::url('dso', $shipment->terima_do),
                'dashboard_label' => ShipmentDashboard::label('dso'),
            ]);
    }

    public function destroy(Shipment $shipment)
    {
        $this->shipmentService->deleteShipment($shipment);

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Data shipment berhasil dihapus.');
    }

    /**
     * Remove the selected shipments.
     */
    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['required', 'uuid', 'distinct', 'exists:shipments,id'],
        ]);

        $deletedCount = $this->shipmentService->deleteShipments($data['shipment_ids']);

        return redirect()->route('admin.shipments.index')
            ->with('success', "{$deletedCount} data shipment berhasil dihapus.");
    }

    public function showImport()
    {
        return view('admin.shipments.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File wajib berformat .xlsx dari Master Template DSO.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $import = new ShipmentImport(createdBy: auth()->id());

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'file' => 'File tidak dapat dibaca. Download ulang Master Template DSO dan simpan tetap dalam format .xlsx.',
            ]);
        }

        if ($import->invalidTemplate) {
            return back()->withErrors(['file' => $import->errors[0]['pesan']]);
        }

        $failCount = count($import->errors);

        $message = "Import selesai: {$import->importedCount} data berhasil diimpor";

        if ($import->updatedCount > 0) {
            $message .= ", {$import->updatedCount} data diperbarui";
        }

        if ($import->skippedCount > 0) {
            $message .= ", {$import->skippedCount} di-skip (VIN sudah terdaftar)";
        }

        if ($import->matchedPendingCount > 0) {
            $message .= ", {$import->matchedPendingCount} VIN pending berhasil dicocokkan";
        }

        if ($failCount > 0) {
            $errorMessages = collect($import->errors)->map(function ($e) {
                return "Baris {$e['baris']}: {$e['pesan']}";
            })->join('<br>');

            return redirect()->route('admin.shipments.index')
                ->with('warning', $message.".<br><strong>{$failCount} baris gagal:</strong><br>{$errorMessages}");
        }

        return redirect()->route('admin.shipments.index')
            ->with('success', $message.'.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ShipmentTemplateExport, 'Master_Upload_DSO.xlsx');
    }
}
