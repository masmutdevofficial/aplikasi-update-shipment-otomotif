<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ShipmentDocumentService
{
    public function store(UploadedFile $document, string $storageKey): string
    {
        $extension = $document->extension() === 'png' ? 'png' : 'jpg';
        $storageKey = trim((string) preg_replace('/[^A-Za-z0-9\/_-]+/', '-', $storageKey), '-/');
        $path = "shipment-documents/{$storageKey}/".Str::uuid().".{$extension}";

        if (! Storage::disk(config('filesystems.document_disk'))->put($path, $document->get())) {
            throw new RuntimeException('Penyimpanan dokumen menolak file yang diunggah.');
        }

        return $path;
    }

    public function storeBytes(string $contents, string $storageKey, string $extension = 'jpg'): string
    {
        $extension = $extension === 'png' ? 'png' : 'jpg';
        $storageKey = trim((string) preg_replace('/[^A-Za-z0-9\/_-]+/', '-', $storageKey), '-/');
        $path = "shipment-documents/{$storageKey}/".Str::uuid().".{$extension}";

        if (! Storage::disk(config('filesystems.document_disk'))->put($path, $contents)) {
            throw new RuntimeException('Penyimpanan dokumen menolak file yang diunggah.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(config('filesystems.document_disk'))->delete($path);
        }
    }
}
