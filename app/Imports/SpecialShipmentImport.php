<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SpecialShipmentImport implements ToCollection
{
    public int $importedCount = 0;
    public int $updatedCount = 0;

    /** @var array<int, array{baris: int, pesan: string}> */
    public array $errors = [];

    public function __construct(
        private readonly array $config,
    ) {}

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        $headerIndex = $this->findHeaderRow($rows);

        if ($headerIndex === null) {
            $this->errors[] = ['baris' => 1, 'pesan' => 'Header kolom tidak ditemukan atau tidak sesuai template.'];
            return;
        }

        $columns = $this->mapHeader($rows->get($headerIndex)->toArray());

        for ($index = $headerIndex + 1; $index < $rows->count(); $index++) {
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

    private function findHeaderRow(Collection $rows): ?int
    {
        foreach ($rows as $index => $row) {
            if (count($this->mapHeader($row->toArray())) >= 1) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return array<string, int>
     */
    private function mapHeader(array $row): array
    {
        $knownHeaders = [];

        foreach ($this->config['fields'] as $field => $fieldConfig) {
            $knownHeaders[$this->normalizeKey($field)] = $field;
            $knownHeaders[$this->normalizeKey($fieldConfig['label'])] = $field;
        }

        $columns = [];

        foreach ($row as $index => $header) {
            $normalized = $this->normalizeKey((string) $header);

            if (isset($knownHeaders[$normalized])) {
                $columns[$knownHeaders[$normalized]] = $index;
            }
        }

        return $columns;
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
            } elseif ($fieldConfig['type'] === 'integer') {
                $data[$field] = is_numeric($data[$field]) ? (int) $data[$field] : null;
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

    private function normalizeKey(string $value): string
    {
        return trim(preg_replace('/_+/', '_', preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($value)))), '_');
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || (!is_object($value) && trim((string) $value) === '');
    }
}
