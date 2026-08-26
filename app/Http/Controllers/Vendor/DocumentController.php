<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\PendingVin;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\ShipmentUpdate;
use App\Models\TsoShipment;
use App\Services\ShipmentDocumentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $sources = $this->authorizedSources();

        return view('vendor.documents.index', [
            'identityLabel' => count($sources) === 2 ? 'No. SPB / NOKA' : $sources[0]['label'],
        ]);
    }

    public function data(Request $request)
    {
        $sources = $this->authorizedSources();
        $union = null;

        foreach ($sources as $source) {
            $query = $source['model']::query()->selectRaw(
                '? as shipment_type, ? as documentable_type, id as documentable_id, '.$source['identity'].' as identifier',
                [$source['type'], $source['model']],
            );
            $union = $union === null ? $query : $union->unionAll($query);
        }

        $query = DB::query()->fromSub($union, 'document_shipments')->whereNotNull('identifier');
        $recordsTotal = (clone $query)->count();
        $search = trim((string) $request->input('search.value', ''));

        if ($search !== '') {
            $query->where('identifier', 'like', "%{$search}%");
        }

        $recordsFiltered = (clone $query)->count();
        $direction = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(10, (int) $request->input('length', 25)));
        $rows = $query->orderBy('identifier', $direction)->skip($start)->take($length)->get();
        $documents = ShipmentDocument::query()
            ->whereIn('documentable_id', $rows->pluck('documentable_id'))
            ->get()
            ->keyBy(fn (ShipmentDocument $document) => $document->documentable_type.'|'.$document->documentable_id);
        $legacyDsoDocuments = ShipmentUpdate::query()
            ->where('position', 'AT PtD (Dooring)')
            ->whereNotNull('document_path')
            ->whereIn('shipment_id', $rows->where('shipment_type', 'dso')->pluck('documentable_id'))
            ->pluck('document_path', 'shipment_id');
        $legacyPendingDocuments = PendingVin::query()
            ->whereNotNull('document_path')
            ->whereIn('no_rangka', $rows->pluck('identifier'))
            ->pluck('document_path', 'no_rangka');

        $data = $rows->map(function (object $row, int $index) use ($start, $documents, $legacyDsoDocuments, $legacyPendingDocuments) {
            $document = $documents->get($row->documentable_type.'|'.$row->documentable_id);
            $documentPath = $document?->document_path
                ?? ($row->shipment_type === 'dso' ? $legacyDsoDocuments->get($row->documentable_id) : null)
                ?? $legacyPendingDocuments->get($row->identifier);

            return [
                'row_number' => $start + $index + 1,
                'identifier' => $row->identifier,
                'document_url' => $documentPath
                    ? Storage::disk(config('filesystems.document_disk'))->url($documentPath)
                    : null,
                'upload_url' => route('vendor.documents.upload', [$row->shipment_type, $row->documentable_id]),
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function upload(
        Request $request,
        string $shipmentType,
        string $shipment,
        ShipmentDocumentService $documentService,
    ) {
        $source = collect($this->authorizedSources())->firstWhere('type', $shipmentType);
        abort_unless($source !== null, 403);

        /** @var Model $shipmentModel */
        $shipmentModel = $source['model']::query()->findOrFail($shipment);
        $identifier = (string) $shipmentModel->{$source['identity']};
        $request->validate([
            'document' => ['bail', 'required', 'file', 'image', 'mimes:jpeg,jpg,png', 'max:10240'],
        ], [
            'document.required' => 'Pilih foto dokumen terlebih dahulu.',
            'document.uploaded' => 'Upload foto gagal. Pastikan koneksi stabil, lalu gunakan foto PNG/JPEG dengan ukuran maksimal 10 MB.',
            'document.image' => 'Dokumen harus berupa gambar PNG atau JPEG.',
            'document.mimes' => 'Dokumen harus berformat PNG atau JPEG.',
            'document.max' => 'Ukuran dokumen maksimal 10 MB. Silakan kompres foto lalu coba lagi.',
        ]);

        try {
            $newPath = $documentService->store($request->file('document'), $shipmentType.'/'.$shipmentModel->getKey());
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('vendor.documents.index')
                ->with('error', 'Foto gagal disimpan ke server. Dokumen sebelumnya tetap aman. Silakan coba lagi.');
        }

        $user = auth()->user();
        $vendor = $user->vendor;

        try {
            $oldPath = DB::transaction(function () use ($shipmentModel, $vendor, $user, $identifier, $newPath) {
                $document = ShipmentDocument::query()
                    ->where('documentable_type', $shipmentModel::class)
                    ->where('documentable_id', $shipmentModel->getKey())
                    ->lockForUpdate()
                    ->first();
                $oldPath = $document?->document_path;

                ShipmentDocument::query()->updateOrCreate(
                    [
                        'documentable_type' => $shipmentModel::class,
                        'documentable_id' => $shipmentModel->getKey(),
                    ],
                    [
                        'vendor_id' => $vendor->id,
                        'identifier' => $identifier,
                        'document_path' => $newPath,
                        'created_by' => $document?->created_by ?? $user->id,
                        'updated_by' => $user->id,
                    ],
                );

                return $oldPath;
            });
        } catch (\Throwable $exception) {
            $documentService->delete($newPath);
            report($exception);

            return redirect()->route('vendor.documents.index')
                ->with('error', 'Foto gagal diperbarui. Dokumen sebelumnya tetap aman. Silakan coba lagi.');
        }

        if ($oldPath && $oldPath !== $newPath) {
            $documentService->delete($oldPath);
        }

        return redirect()->route('vendor.documents.index')
            ->with('success', "Foto dokumen untuk {$identifier} berhasil diunggah.");
    }

    /** @return array<int, array{type: string, model: class-string<Model>, identity: string, label: string}> */
    private function authorizedSources(): array
    {
        $position = auth()->user()->vendor?->position;
        $sources = match ($position) {
            'AT PtD (Dooring)' => [
                ['type' => 'dso', 'model' => Shipment::class, 'identity' => 'no_rangka', 'label' => 'No. Rangka'],
            ],
            'AT PTD/DTD' => [
                ['type' => 'iso-darat', 'model' => IsoDaratShipment::class, 'identity' => 'no_spb', 'label' => 'No. SPB'],
                ['type' => 'iso-laut', 'model' => IsoLautShipment::class, 'identity' => 'noka', 'label' => 'NOKA'],
            ],
            'Port to Door (PTD)' => [
                ['type' => 'tso', 'model' => TsoShipment::class, 'identity' => 'no_rangka', 'label' => 'No. Rangka'],
            ],
            default => [],
        };

        abort_if($sources === [], 403);

        return $sources;
    }
}
