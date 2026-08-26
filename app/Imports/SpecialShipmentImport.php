<?php

namespace App\Imports;

use App\Support\ShipmentUploadTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SpecialShipmentImport implements ToCollection
{
    public int $importedCount = 0;
    public int $updatedCount = 0;
    public bool $invalidTemplate = false;

    /** @var array<int, array{baris: int, pesan: string}> */
    public array $errors = [];

    public function __construct(
        private readonly array $config,
    ) {}

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        $header = $rows->first();
        $headerValues = $header instanceof Collection ? $header->toArray() : (array) $header;

        if ($header === null || !ShipmentUploadTemplate::headerMatches(
            $headerValues,
            ShipmentUploadTemplate::specialHeadings($this->config),
        )) {
            $this->invalidTemplate = true;
            $this->errors[] = [
                'baris' => 1,
                'pesan' => ShipmentUploadTemplate::invalidHeaderMessage($this->config['short_label']),
            ];
            return;
        }

        $columns = array_flip(array_keys(ShipmentUploadTemplate::specialImportableFields($this->config)));

        for ($index = 1; $index < $rows->count(); $index++) {
            $data = [];

            foreach ($columns as $field => $columnIndex) {
                $value = $rows->get($index)[$columnIndex] ?? null;
                $data[$field] = $this->isBlank($value) ? null : $value;
            }

            if (!collect($data)->contains(fn ($value) => !$this->isBlank($value))) {
                continue;
            }

            if (!$this->normalizeRow($data, $index + 1)) {
                continue;
            }

            $this->persist($data);
        }
    }

    private function normalizeRow(array &$data, int $rowNumber): bool
    {
        $fallbackYear = null;

        foreach ($this->config['fields'] as $field => $fieldConfig) {
            if (!array_key_exists($field, $data) || $data[$field] === null) {
                continue;
            }

            if ($fieldConfig['type'] === 'date') {
                $parsed = $this->parseDate($data[$field], $fallbackYear);

                if ($parsed === null) {
                    $this->errors[] = [
                        'baris' => $rowNumber,
                        'pesan' => "Format tanggal {$fieldConfig['label']} tidak dikenali.",
                    ];
                    return false;
                }

                $data[$field] = $parsed;
                $fallbackYear ??= (int) substr($parsed, 0, 4);
            } elseif (($fieldConfig['input_type'] ?? null) === 'date') {
                // ISO Laut stores AT PTD/DTD as text so legacy values such as
                // #VALUE! remain readable. Valid dates and Excel serial dates
                // should still be normalized before they are persisted.
                $parsed = $this->parseDate($data[$field], $fallbackYear);

                if ($parsed !== null) {
                    $data[$field] = $parsed;
                    $fallbackYear ??= (int) substr($parsed, 0, 4);
                } else {
                    $data[$field] = trim((string) $data[$field]);
                }
            } elseif ($fieldConfig['type'] === 'integer') {
                $data[$field] = is_numeric($data[$field]) ? (int) $data[$field] : null;

                if ($data[$field] !== null && isset($fieldConfig['min']) && $data[$field] < $fieldConfig['min']) {
                    $this->errors[] = [
                        'baris' => $rowNumber,
                        'pesan' => "Nilai {$fieldConfig['label']} minimal {$fieldConfig['min']}.",
                    ];
                    return false;
                }
            } else {
                $data[$field] = trim((string) $data[$field]);
            }
        }

        return true;
    }

    private function persist(array $data): void
    {
        $model = $this->config['model'];
        $identity = $this->config['identity'];
        $identityValue = $data[$identity] ?? null;

        if (!$this->isBlank($identityValue)) {
            $existing = $model::query()->where($identity, $identityValue)->first();

            if ($existing) {
                $existing->update($data);
                $this->updatedCount++;
                return;
            }
        }

        $model::create($data);
        $this->importedCount++;
    }

    private function parseDate(mixed $value, ?int $fallbackYear = null): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value) && (float) $value > 1000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim((string) $value);

        if ($fallbackYear !== null && preg_match('/^\d{1,2}[-\/]\p{L}{3}$/u', $text)) {
            foreach (['d-M-Y', 'j-M-Y'] as $format) {
                try {
                    return Carbon::createFromFormat($format, "{$text}-{$fallbackYear}")->format('Y-m-d');
                } catch (\Throwable) {
                    // Coba format berikutnya.
                }
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd-M-y', 'd-M-Y', 'j-M-y', 'j-M-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $text)->format('Y-m-d');
            } catch (\Throwable) {
                // Coba format berikutnya.
            }
        }

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || (!is_object($value) && trim((string) $value) === '');
    }
}
