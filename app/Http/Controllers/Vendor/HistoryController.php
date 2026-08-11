<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PendingVin;
use App\Models\ScanHistory;
use App\Models\ShipmentUpdate;
use App\Services\ShipmentDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        $vendor = $user->vendor;
        $histories = ScanHistory::where('user_id', $user->id)
            ->latest('scan_date')
            ->get();

        $documentPaths = ShipmentUpdate::query()
            ->where('vendor_id', $vendor->id)
            ->where('position', $vendor->position)
            ->whereHas('shipment', fn ($query) => $query->whereIn('no_rangka', $histories->pluck('no_rangka')))
            ->with('shipment:id,no_rangka')
            ->get()
            ->mapWithKeys(fn (ShipmentUpdate $update) => [$update->shipment->no_rangka => $update->document_path]);

        $pendingDocumentPaths = PendingVin::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('no_rangka', $histories->pluck('no_rangka'))
            ->pluck('document_path', 'no_rangka');

        $histories->each(function (ScanHistory $history) use ($documentPaths, $pendingDocumentPaths) {
            $history->document_path = $documentPaths->get($history->no_rangka) ?? $pendingDocumentPaths->get($history->no_rangka);
        });

        return view('vendor.history.index', [
            'histories' => $histories,
            'canUploadDocuments' => $vendor->position === 'AT PtD (Dooring)',
        ]);
    }

    public function uploadDocument(Request $request, ScanHistory $history, ShipmentDocumentService $documentService)
    {
        $user = auth()->user();
        $vendor = $user->vendor;

        abort_unless($history->user_id === $user->id, 404);
        abort_unless($vendor && $vendor->position === 'AT PtD (Dooring)', 403);

        $request->validate([
            'document' => ['bail', 'required', 'file', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ], [
            'document.required' => 'Pilih foto dokumen terlebih dahulu.',
            'document.uploaded' => 'Upload foto gagal. Pastikan koneksi stabil, lalu gunakan foto PNG/JPEG dengan ukuran maksimal 5 MB.',
            'document.image' => 'Dokumen harus berupa gambar PNG atau JPEG.',
            'document.mimes' => 'Dokumen harus berformat PNG atau JPEG.',
            'document.max' => 'Ukuran dokumen maksimal 5 MB. Silakan kompres foto lalu coba lagi.',
        ]);

        try {
            $newPath = $documentService->store($request->file('document'), $history->no_rangka);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('vendor.history')
                ->with('error', 'Foto gagal disimpan ke server. Dokumen sebelumnya tetap aman. Silakan coba lagi.');
        }

        try {
            $oldPath = DB::transaction(function () use ($history, $vendor, $user, $newPath) {
                $update = ShipmentUpdate::query()
                    ->where('vendor_id', $vendor->id)
                    ->where('position', $vendor->position)
                    ->whereHas('shipment', fn ($query) => $query->where('no_rangka', $history->no_rangka))
                    ->lockForUpdate()
                    ->first();

                if ($update) {
                    $oldPath = $update->document_path;
                    $update->update(['document_path' => $newPath, 'updated_by' => $user->id]);

                    return $oldPath;
                }

                $pending = PendingVin::query()
                    ->where('no_rangka', $history->no_rangka)
                    ->where('vendor_id', $vendor->id)
                    ->where('position', $vendor->position)
                    ->lockForUpdate()
                    ->first();

                if (! $pending) {
                    abort(404);
                }

                $oldPath = $pending->document_path;
                $pending->update(['document_path' => $newPath, 'updated_by' => $user->id]);

                return $oldPath;
            });
        } catch (\Throwable $exception) {
            $documentService->delete($newPath);
            report($exception);

            return redirect()->route('vendor.history')
                ->with('error', 'Foto gagal diperbarui. Dokumen sebelumnya tetap aman. Silakan coba lagi.');
        }

        if ($oldPath && $oldPath !== $newPath) {
            $documentService->delete($oldPath);
        }

        return redirect()->route('vendor.history')
            ->with('success', 'Foto dokumen untuk VIN '.$history->no_rangka.' berhasil diunggah.');
    }
}
