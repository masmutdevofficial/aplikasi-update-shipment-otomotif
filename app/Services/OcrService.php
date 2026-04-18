<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrService
{
    public function extractVin(string $imageData): ?string
    {
        // Validate MIME type of raw image data
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);
        if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/webp', 'image/gif'])) {
            throw new \InvalidArgumentException('Format gambar tidak valid.');
        }

        // Save temp file
        $tempPath = storage_path('app/private/ocr_' . uniqid() . '.png');

        try {
            // Preprocess image with Intervention Image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageData);

            // Enhance for OCR: grayscale, increase contrast, resize
            $image->greyscale()
                  ->contrast(30)
                  ->sharpen(15);

            $image->save($tempPath);

            // Run Tesseract OCR
            $ocr = new TesseractOCR($tempPath);
            $ocr->allowlist(range('A', 'Z'), range('0', '9'));
            $ocr->psm(7); // Single line mode

            $rawText = $ocr->run();

            // Clean result: remove spaces, special chars
            $cleaned = preg_replace('/[^A-Z0-9]/', '', strtoupper($rawText));

            // Try to find a 17-character VIN sequence
            if (strlen($cleaned) === 17) {
                return $cleaned;
            }

            // Try to extract 17-char substring
            if (strlen($cleaned) > 17) {
                // VIN cannot contain I, O, Q
                $pattern = '/[A-HJ-NPR-Z0-9]{17}/';
                if (preg_match($pattern, $cleaned, $matches)) {
                    return $matches[0];
                }
            }

            return null;
        } finally {
            // Clean up temp file
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
