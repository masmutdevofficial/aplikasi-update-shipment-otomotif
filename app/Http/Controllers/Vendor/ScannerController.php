<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PendingVin;
use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\ShipmentUpdate;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ScannerController extends Controller
{
    public function __construct(
        protected OcrService $ocrService,
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
            'image' => ['required', 'string', 'max:3000000'],
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
            'document_image' => ['nullable', 'string', 'max:3000000'],
        ]);

        $user = auth()->user();
        $vendor = $user->vendor;

        if (! $vendor || ! $vendor->position) {
            return response()->json(['error' => 'Posisi vendor belum ditetapkan.'], 403);
        }

        $isDooringPosition = $vendor->position === 'AT PtD (Dooring)';

        if ($isDooringPosition) {
            $request->validate([
                'document_image' => ['required', 'string', 'max:3000000'],
            ]);
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

            $documentPath = $this->storeDocumentImage($request->input('document_image'), $noRangka);

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
            } catch (\Throwable $e) {
                if ($documentPath) {
                    Storage::disk(config('filesystems.document_disk'))->delete($documentPath);
                }

                throw $e;
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

        $documentPath = $this->storeDocumentImage($request->input('document_image'), $noRangka);

        try {
            DB::transaction(function () use ($shipment, $vendor, $user, $noRangka, $documentPath) {
                // Save shipment update
                ShipmentUpdate::create([
                    'shipment_id' => $shipment->id,
                    'vendor_id' => $vendor->id,
                    'position' => $vendor->position,
                    'scan_date' => today(),
                    'document_path' => $documentPath,
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
        } catch (\Throwable $e) {
            if ($documentPath) {
                Storage::disk(config('filesystems.document_disk'))->delete($documentPath);
            }

            throw $e;
        }

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

    private function storeDocumentImage(?string $encodedImage, string $noRangka): ?string
    {
        if (! $encodedImage) {
            return null;
        }

        if (! preg_match('#^data:image/(png|jpe?g);base64,([A-Za-z0-9+/=\s]+)$#i', $encodedImage, $matches)) {
            throw ValidationException::withMessages([
                'document_image' => 'Foto dokumen harus berupa gambar PNG atau JPEG yang valid.',
            ]);
        }

        $binary = base64_decode($matches[2], true);

        if ($binary === false || strlen($binary) > 2 * 1024 * 1024 || ! getimagesizefromstring($binary)) {
            throw ValidationException::withMessages([
                'document_image' => 'Foto dokumen tidak valid atau ukurannya melebihi 2 MB.',
            ]);
        }

        $extension = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
        $path = "shipment-documents/{$noRangka}/".Str::uuid().".{$extension}";

        Storage::disk(config('filesystems.document_disk'))->put($path, $binary);

        return $path;
    }
}
