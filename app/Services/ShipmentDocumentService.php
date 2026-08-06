<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShipmentDocumentService
{
    public function store(UploadedFile $document, string $noRangka): string
    {
        $extension = $document->extension() === 'png' ? 'png' : 'jpg';
        $path = "shipment-documents/{$noRangka}/".Str::uuid().".{$extension}";

        Storage::disk(config('filesystems.document_disk'))->put($path, $document->get());

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(config('filesystems.document_disk'))->delete($path);
        }
    }
}
