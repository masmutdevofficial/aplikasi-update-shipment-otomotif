<?php

namespace App\Imports;

use App\Models\Shipment;
use App\Services\PendingVinService;
use App\Support\ShipmentUploadTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ShipmentImport implements ToCollection
{
    public int $importedCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public int $matchedPendingCount = 0;

    public bool $invalidTemplate = false;

    /** @var array<array{baris: int, pesan: string}> */
    public array $errors = [];

    private string $createdBy;

    private const REQUIRED_CREATE_FIELDS = [
        'lokasi' => 'Lokasi',
        'type_kendaraan' => 'Type Kendaraan',
        'no_rangka' => 'No. Rangka (VIN)',
        'no_engine' => 'No. Engine',
        'warna' => 'Warna',
        'asal_pdc' => 'Asal PDC',
        'kota' => 'Kota',
        'tujuan_pengiriman' => 'Tujuan Pengiriman',
    ];

    private const DO_HOLD_FIELDS = [
        'keluar_dari_pdc',
        'nama_kapal',
        'keberangkatan_kapal',
        'at_storage_port',
        'atd_kapal_loading',
        'ata_kapal',
        'ata_storage_port_destination',
        'at_ptd_dooring',
    ];

    public function __construct(string $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        $header = $rows->first();
        $headerValues = $header instanceof Collection ? $header->toArray() : (array) $header;

        if ($header === null || ! ShipmentUploadTemplate::headerMatches(
            $headerValues,
            ShipmentUploadTemplate::dsoHeadings(),
        )) {
            $this->invalidTemplate = true;
            $this->errors[] = [
                'baris' => 1,
                'pesan' => ShipmentUploadTemplate::invalidHeaderMessage('DSO'),
            ];

            return;
        }

        $headerColumns = array_flip(array_keys(ShipmentUploadTemplate::dsoFields()));

        for ($index = 1; $index < $rows->count(); $index++) {
            $rowNum = $index + 1;
            $data = $this->rowToData($rows->get($index)->toArray(), $headerColumns);

            if (! $this->hasMappedValue($data)) {
                continue;
            }

            $this->importRow($data, $rowNum);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function rowToData(array $row, array $headerColumns): array
    {
        $data = [];

        foreach ($headerColumns as $field => $index) {
            $data[$field] = $row[$index] ?? null;
        }

        return $data;
    }

    private function importRow(array $data, int $rowNum): void
    {
        $lokasi = $this->value($data, 'lokasi');
        $no_do = $this->value($data, 'no_do');
        $type_kendaraan = $this->value($data, 'type_kendaraan');
        $no_rangka = strtoupper(trim((string) $this->value($data, 'no_rangka')));
        $no_engine = $this->value($data, 'no_engine');
        $warna = $this->value($data, 'warna');
        $asal_pdc = $this->value($data, 'asal_pdc');
        $kota = $this->value($data, 'kota');
        $tujuan_pengiriman = $this->value($data, 'tujuan_pengiriman');
        $terima_do_raw = $this->value($data, 'terima_do');
        $keluar_dari_pdc_raw = $this->value($data, 'keluar_dari_pdc');
        $nama_kapal = $this->value($data, 'nama_kapal');
        $keberangkatan_raw = $this->value($data, 'keberangkatan_kapal');
        $actualDateInputs = [
            'at_storage_port' => ['AT Storage Port', $this->value($data, 'at_storage_port')],
            'atd_kapal_loading' => ['ATD Kapal (Loading)', $this->value($data, 'atd_kapal_loading')],
            'ata_kapal' => ['ATA Kapal', $this->value($data, 'ata_kapal')],
            'ata_storage_port_destination' => ['ATA Storage Port (Destination)', $this->value($data, 'ata_storage_port_destination')],
            'at_ptd_dooring' => ['AT PtD (Dooring)', $this->value($data, 'at_ptd_dooring')],
        ];
        $doHoldInputs = array_map(fn (string $field) => $this->value($data, $field), self::DO_HOLD_FIELDS);
        $doHoldStatusProvided = collect($doHoldInputs)->contains(fn (mixed $value) => ! $this->isBlank($value));
        $isDoHold = collect($doHoldInputs)->contains(fn (mixed $value) => $this->isDoHoldValue($value));

        if ($no_rangka === '') {
            $this->errors[] = ['baris' => $rowNum, 'pesan' => 'Kolom No. Rangka (VIN) wajib diisi'];

            return;
        }

        if (! preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $no_rangka)) {
            $this->errors[] = [
                'baris' => $rowNum,
                'pesan' => "No. Rangka \"{$no_rangka}\" harus tepat 17 karakter huruf/angka (tidak boleh I, O, atau Q)",
            ];

            return;
        }

        $terima_do = $this->parseOptionalDate($terima_do_raw, 'Terima DO', $rowNum);
        $keluar_dari_pdc = $this->parseOptionalDateOrHold($keluar_dari_pdc_raw, 'Keluar dari PDC', $rowNum);
        $keberangkatan_kapal = $this->parseOptionalDateOrHold($keberangkatan_raw, 'Keberangkatan Kapal', $rowNum);
        $actualDates = [];

        foreach ($actualDateInputs as $field => [$label, $value]) {
            $actualDates[$field] = $this->parseOptionalDateOrHold($value, $label, $rowNum);
        }

        if ($terima_do === false || $keluar_dari_pdc === false || $keberangkatan_kapal === false || in_array(false, $actualDates, true)) {
            return;
        }

        $hasKapalData = $nama_kapal !== null || $keberangkatan_kapal !== null;
        $shipment = Shipment::where('no_rangka', $no_rangka)->first();

        if ($shipment) {
            $updates = [
                'updated_by' => $this->createdBy,
            ];

            if ($doHoldStatusProvided) {
                $updates['do_hold'] = $isDoHold;
            }

            $this->setIfPresent($updates, 'no_do', $no_do);
            $this->setIfPresent($updates, 'terima_do', $terima_do);
            $this->setIfPresent($updates, 'keluar_dari_pdc', $keluar_dari_pdc);
            $this->setIfPresent($updates, 'nama_kapal', $nama_kapal);
            $this->setIfPresent($updates, 'keberangkatan_kapal', $keberangkatan_kapal);

            foreach ($actualDates as $field => $value) {
                $this->setIfPresent($updates, $field, $value);
            }

            if (count($updates) > 1) {
                $shipment->update($updates);
                $this->updatedCount++;
            } else {
                $this->skippedCount++;
            }

            $this->matchedPendingCount += app(PendingVinService::class)->matchForShipment($shipment->fresh());

            return;
        }

        $rowErrors = $this->validateCreateFields([
            'lokasi' => $lokasi,
            'type_kendaraan' => $type_kendaraan,
            'no_rangka' => $no_rangka,
            'no_engine' => $no_engine,
            'warna' => $warna,
            'asal_pdc' => $asal_pdc,
            'kota' => $kota,
            'tujuan_pengiriman' => $tujuan_pengiriman,
        ]);

        if (! empty($rowErrors)) {
            foreach ($rowErrors as $msg) {
                $this->errors[] = ['baris' => $rowNum, 'pesan' => $msg];
            }

            return;
        }

        $shipment = Shipment::create([
            'lokasi' => trim((string) $lokasi),
            'no_do' => $no_do !== null ? trim((string) $no_do) : null,
            'type_kendaraan' => trim((string) $type_kendaraan),
            'no_rangka' => $no_rangka,
            'no_engine' => trim((string) $no_engine),
            'warna' => trim((string) $warna),
            'asal_pdc' => trim((string) $asal_pdc),
            'kota' => trim((string) $kota),
            'tujuan_pengiriman' => trim((string) $tujuan_pengiriman),
            'terima_do' => $terima_do,
            'keluar_dari_pdc' => $keluar_dari_pdc,
            'nama_kapal' => $hasKapalData && $nama_kapal !== null ? trim((string) $nama_kapal) : null,
            'keberangkatan_kapal' => $keberangkatan_kapal,
            'at_storage_port' => $actualDates['at_storage_port'],
            'atd_kapal_loading' => $actualDates['atd_kapal_loading'],
            'ata_kapal' => $actualDates['ata_kapal'],
            'ata_storage_port_destination' => $actualDates['ata_storage_port_destination'],
            'at_ptd_dooring' => $actualDates['at_ptd_dooring'],
            'do_hold' => $isDoHold,
            'created_by' => $this->createdBy,
            'updated_by' => $this->createdBy,
        ]);

        $this->matchedPendingCount += app(PendingVinService::class)->matchForShipment($shipment);

        $this->importedCount++;
    }

    /**
     * @return array<int, string>
     */
    private function validateCreateFields(array $data): array
    {
        $errors = [];

        foreach (self::REQUIRED_CREATE_FIELDS as $field => $label) {
            if ($this->isBlank($data[$field] ?? null)) {
                $errors[] = "Kolom {$label} wajib diisi";
            }
        }

        return $errors;
    }

    private function value(array $data, string $field): mixed
    {
        return array_key_exists($field, $data) && ! $this->isBlank($data[$field])
            ? $data[$field]
            : null;
    }

    private function hasMappedValue(array $data): bool
    {
        foreach ($data as $value) {
            if (! $this->isBlank($value)) {
                return true;
            }
        }

        return false;
    }

    private function isBlank(mixed $value): bool
    {
        if ($value instanceof \DateTimeInterface) {
            return false;
        }

        if (is_object($value)) {
            return false;
        }

        return $value === null || trim((string) $value) === '';
    }

    private function setIfPresent(array &$updates, string $field, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        $updates[$field] = is_string($value) ? trim($value) : $value;
    }

    private function isDoHoldValue(mixed $value): bool
    {
        return ! is_object($value) && strtoupper(trim((string) $value)) === 'DO HOLD';
    }

    private function parseOptionalDateOrHold(mixed $value, string $label, int $rowNum): string|false|null
    {
        if ($this->isDoHoldValue($value)) {
            return null;
        }

        return $this->parseOptionalDate($value, $label, $rowNum);
    }

    private function parseOptionalDate(mixed $value, string $label, int $rowNum): string|false|null
    {
        if ($this->isBlank($value)) {
            return null;
        }

        $date = $this->parseDate($value);

        if (! $date) {
            $this->errors[] = [
                'baris' => $rowNum,
                'pesan' => "Format tanggal {$label} tidak dikenali. Gunakan format seperti 2026-04-01 atau 01/04/2026",
            ];

            return false;
        }

        return $date;
    }

    /**
     * Parse tanggal dari serial number Excel atau string berbagai format.
     */
    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        // Excel menyimpan tanggal sebagai serial number (angka > 1000)
        if (is_numeric($value) && (int) $value > 1000) {
            try {
                return Date::excelToDateTimeObject((float) $value)
                    ->format('Y-m-d');
            } catch (\Exception) {
                // lanjut ke parsing string
            }
        }

        $str = trim((string) $value);

        $formats = [
            'Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y',
            'd M Y', 'd-M-Y', 'd-M-y', 'j-M-y', 'j-M-Y',
            'Y/m/d', 'd/m/y',
        ];

        foreach ($formats as $format) {
            try {
                return $this->formatParsedDate(Carbon::createFromFormat($format, $str));
            } catch (\Exception) {
                continue;
            }
        }

        try {
            return $this->formatParsedDate(Carbon::parse($str));
        } catch (\Exception) {
            return null;
        }
    }

    private function formatParsedDate(Carbon $date): string
    {
        if ((int) $date->format('Y') < 100) {
            $date->addYears(2000);
        }

        return $date->format('Y-m-d');
    }
}
