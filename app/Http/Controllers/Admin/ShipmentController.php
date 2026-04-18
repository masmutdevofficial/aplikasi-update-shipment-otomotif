<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShipmentRequest;
use App\Http\Requests\Admin\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Http\Request;

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
}
