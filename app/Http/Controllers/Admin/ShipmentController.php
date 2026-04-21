<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ShipmentTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShipmentRequest;
use App\Http\Requests\Admin\UpdateShipmentRequest;
use App\Imports\ShipmentImport;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
    ) {}

    public function index(Request $request)
    {
        $shipments = $this->shipmentService->getAllShipments();

        return view('admin.shipments.index', compact('shipments'));
    }

    public function create()
    {
        return view('admin.shipments.create');
    }

    public function store(StoreShipmentRequest $request)
    {
        $this->shipmentService->createShipment(
            data: $request->validated(),
            createdBy: auth()->id(),
        );

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Data shipment berhasil ditambahkan.');
    }

    public function edit(Shipment $shipment)
    {
        return view('admin.shipments.edit', compact('shipment'));
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment)
    {
        $this->shipmentService->updateShipment(
            shipment: $shipment,
            data: $request->validated(),
            updatedBy: auth()->id(),
        );

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Data shipment berhasil diperbarui.');
    }

    public function destroy(Shipment $shipment)
    {
        $this->shipmentService->deleteShipment($shipment);

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Data shipment berhasil dihapus.');
    }

    public function showImport()
    {
        return view('admin.shipments.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $import = new ShipmentImport(createdBy: auth()->id());

        Excel::import($import, $request->file('file'));

        $failCount = count($import->errors);

        $message = "Import selesai: {$import->importedCount} data berhasil diimpor";

        if ($import->skippedCount > 0) {
            $message .= ", {$import->skippedCount} di-skip (VIN sudah terdaftar)";
        }

        if ($failCount > 0) {
            $errorMessages = collect($import->errors)->map(function ($e) {
                return "Baris {$e['baris']}: {$e['pesan']}";
            })->join('<br>');

            return redirect()->route('admin.shipments.index')
                ->with('warning', $message . ".<br><strong>{$failCount} baris gagal:</strong><br>{$errorMessages}");
        }

        return redirect()->route('admin.shipments.index')
            ->with('success', $message . '.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ShipmentTemplateExport(), 'Format_Upload.xlsx');
    }
}
