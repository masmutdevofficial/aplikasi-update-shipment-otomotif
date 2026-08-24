<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendingVin;
use App\Services\ShipmentDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class PendingVinController extends Controller
{
    public function __construct(
        private readonly ShipmentDocumentService $documentService,
    ) {}

    public function index(): View
    {
        $pendingVins = PendingVin::with('vendor.user')
            ->latest('scan_date')
            ->latest()
            ->get();

        return view('admin.pending-vins.index', compact('pendingVins'));
    }

    public function destroy(PendingVin $pendingVin): RedirectResponse
    {
        try {
            // Hapus objek R2 lebih dulu agar kegagalan storage tidak menyisakan
            // gambar tanpa referensi setelah record database terhapus.
            $this->documentService->delete($pendingVin->document_path);
            $pendingVin->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'VIN pending gagal dihapus. Data dan gambar tetap dipertahankan, silakan coba lagi.');
        }

        return redirect()
            ->route('admin.pending-vins.index')
            ->with('success', 'VIN pending dan gambar dokumennya berhasil dihapus.');
    }
}
