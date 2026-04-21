<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\OcrService;
use App\Models\Shipment;
use App\Models\ShipmentUpdate;
use App\Models\ScanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    public function __construct(
        protected OcrService $ocrService,
    ) {}

    public function index()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor || !$vendor->position) {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Posisi vendor belum ditetapkan.');
        }

        return view('vendor.scanner.index', ['vendor' => $vendor]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'image' => ['required', 'string', 'max:3000000'],
        ]);

        $user = auth()->user();
        $vendor = $user->vendor;

        if (!$vendor || !$vendor->position) {
            return response()->json(['error' => 'Posisi vendor belum ditetapkan.'], 403);
        }

        // Decode base64 image
        $imageData = $request->input('image');
        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
        $imageData = base64_decode($imageData);

        if (!$imageData) {
            return response()->json(['error' => 'Gagal memproses gambar.'], 422);
        }

        // OCR processing via OpenAI Vision
        try {
            $vinResult = $this->ocrService->extractVin($imageData);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Layanan OCR tidak tersedia saat ini: ' . $e->getMessage(),
            ], 503);
        }

        if (!$vinResult) {
            return response()->json([
                'error' => 'Tidak dapat membaca VIN dari gambar. Pastikan gambar jelas dan coba lagi.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'vin' => $vinResult,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'no_rangka' => ['required', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/'],
        ]);

        $user = auth()->user();
        $vendor = $user->vendor;

        if (!$vendor || !$vendor->position) {
            return response()->json(['error' => 'Posisi vendor belum ditetapkan.'], 403);
        }

        $isDooringPosition = $vendor->position === 'AT PtD (Dooring)';

        if ($isDooringPosition) {
            $request->validate([
                'document_link' => ['nullable', 'url', 'max:500'],
            ]);
        }

        $noRangka = strtoupper($request->input('no_rangka'));

        // Find shipment by VIN
        $shipment = Shipment::where('no_rangka', $noRangka)->first();

        if (!$shipment) {
            return response()->json([
                'error' => 'No. Rangka tidak ditemukan di data shipment.',
            ], 404);
        }

        // Check if this position already has an update for this shipment
        $existing = ShipmentUpdate::where('shipment_id', $shipment->id)
            ->where('position', $vendor->position)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Data posisi ' . $vendor->position . ' untuk VIN ini sudah tercatat pada ' . $existing->scan_date->format('d-M-y') . '.',
            ], 409);
        }

        DB::transaction(function () use ($shipment, $vendor, $user, $noRangka, $request, $isDooringPosition) {
            // Save shipment update
            ShipmentUpdate::create([
                'shipment_id' => $shipment->id,
                'vendor_id' => $vendor->id,
                'position' => $vendor->position,
                'scan_date' => today(),
                'document_link' => $isDooringPosition ? $request->input('document_link') : null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Save scan history
            ScanHistory::create([
                'user_id' => $user->id,
                'no_rangka' => $noRangka,
                'scan_date' => today(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Scan berhasil disimpan untuk posisi ' . $vendor->position . '.',
            'data' => [
                'no_rangka' => $noRangka,
                'position' => $vendor->position,
                'scan_date' => today()->format('d-M-y'),
            ],
        ]);
    }
}
