<?php

declare(strict_types=1);

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OcrService
{
    /**
     * Extract a 17-character VIN from the given raw image binary
     * using OpenAI Vision (chat completions with image input).
     *
     * @param  string  $imageData  Raw binary image data (PNG / JPEG / WEBP)
     * @return string|null         17-character VIN or null on failure
     *
     * @throws \InvalidArgumentException when the image MIME type is not supported
     */
    public function extractVin(string $imageData): ?string
    {
        // Validate MIME type
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);

        $supported = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
        if (! in_array($mimeType, $supported, true)) {
            throw new \InvalidArgumentException('Format gambar tidak valid.');
        }

        // Re-encode binary to base64 data-URL for OpenAI
        $base64DataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

        $model = env('OPENAI_MODEL', 'gpt-5.4-nano');

        $response = OpenAI::chat()->create([
            'model'           => $model,
            'response_format' => ['type' => 'json_object'],
            'max_completion_tokens'      => 100,
            'messages'        => [
                [
                    'role'    => 'system',
                    'content' => 'You are an OCR specialist. Your only task is to read a Vehicle Identification Number (VIN) from an image. A VIN is exactly 17 characters long and contains only uppercase letters (A-Z, excluding I, O, Q) and digits (0-9). Respond exclusively with a JSON object in this exact schema: {"vin": "<17-character VIN string>"}. If you cannot find a valid 17-character VIN, respond with: {"vin": null}. Do not include any explanation or extra fields.',
                ],
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Extract the Vehicle Identification Number (VIN) from this image. Return only the 17-character alphanumeric VIN as JSON.',
                        ],
                        [
                            'type'      => 'image_url',
                            'image_url' => [
                                'url'    => $base64DataUrl,
                                'detail' => 'high',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $content = $response->choices[0]->message->content ?? null;

        if (! $content) {
            return null;
        }

        $decoded = json_decode($content, true);
        $vin     = $decoded['vin'] ?? null;

        if (! $vin || ! is_string($vin)) {
            return null;
        }

        // Normalise: uppercase, strip non-VIN characters
        $vin = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $vin));

        // Validate: exactly 17 chars, no forbidden letters (I, O, Q)
        if (strlen($vin) === 17 && preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
            return $vin;
        }

        // If exactly 17 even with I/O/Q, still return so user can correct
        if (strlen($vin) === 17) {
            return $vin;
        }

        return null;
    }
}
