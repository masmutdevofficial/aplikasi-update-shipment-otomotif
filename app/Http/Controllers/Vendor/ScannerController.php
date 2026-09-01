<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PendingVin;
use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\ShipmentUpdate;
use App\Services\OcrService;
use App\Services\ShipmentDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScannerController extends Controller
{
    public function __construct(
        protected OcrService $ocrService,
        protected ShipmentDocumentService $documentService,
    ) {}

    public function index()
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor || ! $vendor->position) {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Posisi vendor belum ditetapkan.');
        }

        return view('vendor.scanner.index', ['vendor' => $vendor]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            // Base64 menambah ukuran sekitar 33%; 14 juta karakter cukup
            // untuk gambar biner 10 MB sekaligus membatasi payload berlebih.
            'image' => ['required', 'string', 'max:14000000'],
        ], [
            'image.max' => 'Ukuran gambar scan maksimal 10 MB.',
        ]);

        $user = auth()->user();
        $vendor = $user->vendor;

        if (! $vendor || ! $vendor->position) {
            return response()->json(['error' => 'Posisi vendor belum ditetapkan.'], 403);
        }

        // Decode base64 image
        $imageData = $request->input('image');
        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
        $imageData = base64_decode($imageData);

        if (! $imageData) {
            return response()->json(['error' => 'Gagal memproses gambar.'], 422);
        }

        if (strlen($imageData) > 10 * 1024 * 1024) {
            return response()->json(['error' => 'Ukuran gambar scan maksimal 10 MB.'], 422);
        }

        // OCR processing via OpenAI Vision
        try {
            $vinResult = $this->ocrService->extractVin($imageData);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Layanan OCR tidak tersedia saat ini: '.$e->getMessage(),
            ], 503);
        }

        if (! $vinResult) {
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
            'save_as_pending' => ['nullable', 'boolean'],
            'scan_image' => ['nullable', 'string', 'max:14000000'],
        ]);

        $user = auth()->user();
        $vendor = $user->vendor;

        if (! $vendor || ! $vendor->position) {
            return response()->json(['error' => 'Posisi vendor belum ditetapkan.'], 403);
        }

        $noRangka = strtoupper($request->input('no_rangka'));

        // Find shipment by VIN
        $shipment = Shipment::where('no_rangka', $noRangka)->first();

        if (! $shipment) {
            if (! $request->boolean('save_as_pending')) {
                return response()->json([
                    'error' => 'No. Rangka tidak ditemukan di data shipment.',
                    'pending_allowed' => true,
                    'warning' => 'Periksa kembali VIN. Jika sudah benar, simpan sebagai VIN pending agar otomatis dicocokkan saat shipment diimpor.',
                ], 404);
            }

            $existingPending = PendingVin::where('no_rangka', $noRangka)
                ->where('position', $vendor->position)
                ->first();

            if ($existingPending) {
                return response()->json([
                    'error' => 'VIN pending untuk posisi '.$vendor->position.' sudah tercatat pada '.$existingPending->scan_date->format('d-M-y').'.',
                ], 409);
            }

            $documentPath = null;

            if ($request->filled('scan_image')) {
                [$imageBytes, $extension] = $this->decodeScanImage($request->string('scan_image')->toString());
                $documentPath = $this->documentService->storeBytes($imageBytes, "pending-vins/{$noRangka}", $extension);
            }

            try {
                DB::transaction(function () use ($vendor, $user, $noRangka, $documentPath) {
                    PendingVin::create([
                        'no_rangka' => $noRangka,
                        'vendor_id' => $vendor->id,
                        'position' => $vendor->position,
                        'scan_date' => today(),
                        'document_path' => $documentPath,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    ScanHistory::create([
                        'user_id' => $user->id,
                        'no_rangka' => $noRangka,
                        'scan_date' => today(),
                    ]);
                });
            } catch (\Throwable $exception) {
                $this->documentService->delete($documentPath);

                throw $exception;
            }

            return response()->json([
                'success' => true,
                'pending' => true,
                'message' => 'VIN belum ada di shipment dan telah disimpan sebagai pending.',
                'data' => [
                    'no_rangka' => $noRangka,
                    'position' => $vendor->position,
                    'scan_date' => today()->format('d-M-y'),
                ],
            ], 201);
        }

        // Check if this position already has an update for this shipment
        $existing = ShipmentUpdate::where('shipment_id', $shipment->id)
            ->where('position', $vendor->position)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Data posisi '.$vendor->position.' untuk VIN ini sudah tercatat pada '.$existing->scan_date->format('d-M-y').'.',
            ], 409);
        }

        DB::transaction(function () use ($shipment, $vendor, $user, $noRangka) {
            ShipmentUpdate::create([
                'shipment_id' => $shipment->id,
                'vendor_id' => $vendor->id,
                'position' => $vendor->position,
                'scan_date' => today(),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            ScanHistory::create([
                'user_id' => $user->id,
                'no_rangka' => $noRangka,
                'scan_date' => today(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Scan berhasil disimpan untuk posisi '.$vendor->position.'.',
            'data' => [
                'no_rangka' => $noRangka,
                'position' => $vendor->position,
                'scan_date' => today()->format('d-M-y'),
            ],
        ]);
    }

    /** @return array{0: string, 1: string} */
    private function decodeScanImage(string $dataUrl): array
    {
        if (! preg_match('#^data:image/(jpeg|png|webp);base64,(.+)$#s', $dataUrl, $matches)) {
            throw ValidationException::withMessages([
                'scan_image' => 'Format foto scan tidak valid.',
            ]);
        }

        $contents = base64_decode($matches[2], true);

        if ($contents === false || $contents === '' || strlen($contents) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'scan_image' => 'Foto scan tidak valid atau melebihi ukuran maksimal 10 MB.',
            ]);
        }

        return [$contents, $matches[1] === 'png' ? 'png' : 'jpg'];
    }

}
