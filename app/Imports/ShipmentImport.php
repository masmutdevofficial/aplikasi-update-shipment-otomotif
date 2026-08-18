<?php

namespace App\Imports;

use App\Models\Shipment;
use App\Services\PendingVinService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ShipmentImport implements ToCollection
{
    public int $importedCount = 0;
    public int $updatedCount  = 0;
    public int $skippedCount  = 0;
    public int $matchedPendingCount = 0;

    /** @var array<array{baris: int, pesan: string}> */
    public array $errors = [];

    private string $createdBy;

    /**
     * Pemetaan nama kolom Excel ke nama field internal.
     * Mendukung template bawaan dan manifest yang punya header di tengah file.
     */
    private const COLUMN_MAP = [
        'lokasi'              => ['lokasi'],
        'no_do'               => ['no_do', 'nodo', 'nomor_do', 'no do', 'nomor do'],
        'type_kendaraan'      => ['type_kendaraan', 'tipe_kendaraan', 'jenis_kendaraan', 'type kendaraan'],
        'no_rangka'           => ['no_rangka', 'no rangka', 'nomor_rangka', 'vin'],
        'no_engine'           => ['no_engine', 'no engine', 'no_engine_', 'nomor_engine', 'no mesin'],
        'warna'               => ['warna', 'warna kendaraan'],
        'asal_pdc'            => ['asal_pdc', 'asal pdc', 'pdc asal'],
        'kota'                => ['kota', 'kota tujuan'],
        'tujuan_pengiriman'   => ['tujuan_pengiriman', 'tujuan pengiriman', 'tujuan', 'dealer'],
        'terima_do'           => ['terima_do', 'terima do', 'tgl_terima_do', 'tanggal_terima_do', 'tanggal terima do'],
        'keluar_dari_pdc'     => ['keluar_dari_pdc', 'keluar dari pdc', 'keluar pdc', 'tgl_keluar_pdc', 'tanggal keluar pdc'],
        'nama_kapal'          => ['nama_kapal', 'nama kapal', 'jenis_kapal', 'jenis kapal', 'kapal'],
        'keberangkatan_kapal' => [
            'keberangkatan_kapal', 'keberangkatan kapal', 'tgl_keberangkatan',
            'tanggal_atd', 'tanggal atd', 'atd', 'etd', 'departure',
            'tanggal keberangkatan kapal',
        ],
        'at_storage_port' => ['at_storage_port', 'at storage port'],
        'atd_kapal_loading' => ['atd_kapal_loading', 'atd kapal loading', 'atd kapal (loading)'],
        'ata_kapal' => ['ata_kapal', 'ata kapal'],
        'ata_storage_port_destination' => [
            'ata_storage_port_destination', 'ata storage port destination',
            'ata storage port (destination)',
        ],
        'at_ptd_dooring' => ['at_ptd_dooring', 'at ptd dooring', 'at ptd (dooring)', 'at ptd'],
    ];

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

    public function __construct(string $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        $headerIndex = $this->findHeaderRow($rows);

        if ($headerIndex === null) {
            $this->errors[] = [
                'baris' => 1,
                'pesan' => 'Header kolom tidak ditemukan. Pastikan file memiliki kolom No. Rangka/VIN.',
            ];
            return;
        }

        $headerColumns = $this->buildHeaderColumns($rows->get($headerIndex)->toArray());

        for ($index = $headerIndex + 1; $index < $rows->count(); $index++) {
            $rowNum = $index + 1;
            $data = $this->rowToData($rows->get($index)->toArray(), $headerColumns);

            if (!$this->hasMappedValue($data)) {
                continue;
            }

            $this->importRow($data, $rowNum);
        }
    }

    /**
     * Cari baris header. Manifest kapal punya kop surat, jadi header tidak selalu baris pertama.
     */
    private function findHeaderRow(Collection $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $headerColumns = $this->buildHeaderColumns($row->toArray());

            if (!isset($headerColumns['no_rangka'])) {
                continue;
            }

            if (count($headerColumns) >= 3) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return array<string, int>
     */
    private function buildHeaderColumns(array $row): array
    {
        $normalizedHeaders = [];

        foreach ($row as $index => $value) {
            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            $normalizedHeaders[$this->normalizeKey((string) $value)] = $index;
        }

        $columns = [];

        foreach (self::COLUMN_MAP as $field => $candidates) {
            foreach ($candidates as $candidate) {
                $normalized = $this->normalizeKey($candidate);

                if (array_key_exists($normalized, $normalizedHeaders)) {
                    $columns[$field] = $normalizedHeaders[$normalized];
                    break;
                }
            }
        }

        return $columns;
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
        $lokasi              = $this->value($data, 'lokasi');
        $no_do               = $this->value($data, 'no_do');
        $type_kendaraan      = $this->value($data, 'type_kendaraan');
        $no_rangka           = strtoupper(trim((string) $this->value($data, 'no_rangka')));
        $no_engine           = $this->value($data, 'no_engine');
        $warna               = $this->value($data, 'warna');
        $asal_pdc            = $this->value($data, 'asal_pdc');
        $kota                = $this->value($data, 'kota');
        $tujuan_pengiriman   = $this->value($data, 'tujuan_pengiriman');
        $terima_do_raw       = $this->value($data, 'terima_do');
        $keluar_dari_pdc_raw = $this->value($data, 'keluar_dari_pdc');
        $nama_kapal          = $this->value($data, 'nama_kapal');
        $keberangkatan_raw   = $this->value($data, 'keberangkatan_kapal');
        $actualDateInputs = [
            'at_storage_port' => ['AT Storage Port', $this->value($data, 'at_storage_port')],
            'atd_kapal_loading' => ['ATD Kapal (Loading)', $this->value($data, 'atd_kapal_loading')],
            'ata_kapal' => ['ATA Kapal', $this->value($data, 'ata_kapal')],
            'ata_storage_port_destination' => ['ATA Storage Port (Destination)', $this->value($data, 'ata_storage_port_destination')],
            'at_ptd_dooring' => ['AT PtD (Dooring)', $this->value($data, 'at_ptd_dooring')],
        ];

        if ($no_rangka === '') {
            $this->errors[] = ['baris' => $rowNum, 'pesan' => 'Kolom No. Rangka (VIN) wajib diisi'];
            return;
        }

        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $no_rangka)) {
            $this->errors[] = [
                'baris' => $rowNum,
                'pesan' => "No. Rangka \"{$no_rangka}\" harus tepat 17 karakter huruf/angka (tidak boleh I, O, atau Q)",
            ];
            return;
        }

        $terima_do = $this->parseOptionalDate($terima_do_raw, 'Terima DO', $rowNum);
        $keluar_dari_pdc = $this->parseOptionalDate($keluar_dari_pdc_raw, 'Keluar dari PDC', $rowNum);
        $keberangkatan_kapal = $this->parseOptionalDate($keberangkatan_raw, 'Keberangkatan Kapal', $rowNum);
        $actualDates = [];

        foreach ($actualDateInputs as $field => [$label, $value]) {
            $actualDates[$field] = $this->parseOptionalDate($value, $label, $rowNum);
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

        if (!empty($rowErrors)) {
            foreach ($rowErrors as $msg) {
                $this->errors[] = ['baris' => $rowNum, 'pesan' => $msg];
            }
            return;
        }

        $shipment = Shipment::create([
            'lokasi'              => trim((string) $lokasi),
            'no_do'               => $no_do !== null ? trim((string) $no_do) : null,
            'type_kendaraan'      => trim((string) $type_kendaraan),
            'no_rangka'           => $no_rangka,
            'no_engine'           => trim((string) $no_engine),
            'warna'               => trim((string) $warna),
            'asal_pdc'            => trim((string) $asal_pdc),
            'kota'                => trim((string) $kota),
            'tujuan_pengiriman'   => trim((string) $tujuan_pengiriman),
            'terima_do'           => $terima_do,
            'keluar_dari_pdc'     => $keluar_dari_pdc,
            'nama_kapal'          => $hasKapalData && $nama_kapal !== null ? trim((string) $nama_kapal) : null,
            'keberangkatan_kapal' => $keberangkatan_kapal,
            'at_storage_port' => $actualDates['at_storage_port'],
            'atd_kapal_loading' => $actualDates['atd_kapal_loading'],
            'ata_kapal' => $actualDates['ata_kapal'],
            'ata_storage_port_destination' => $actualDates['ata_storage_port_destination'],
            'at_ptd_dooring' => $actualDates['at_ptd_dooring'],
            'created_by'          => $this->createdBy,
            'updated_by'          => $this->createdBy,
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
        return array_key_exists($field, $data) && !$this->isBlank($data[$field])
            ? $data[$field]
            : null;
    }

    private function hasMappedValue(array $data): bool
    {
        foreach ($data as $value) {
            if (!$this->isBlank($value)) {
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

    /**
     * @return string|false|null
     */
    private function parseOptionalDate(mixed $value, string $label, int $rowNum): string|false|null
    {
        if ($this->isBlank($value)) {
            return null;
        }

        $date = $this->parseDate($value);

        if (!$date) {
            $this->errors[] = [
                'baris' => $rowNum,
                'pesan' => "Format tanggal {$label} tidak dikenali. Gunakan format seperti 2026-04-01 atau 01/04/2026",
            ];
            return false;
        }

        return $date;
    }

    private function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);

        return trim(preg_replace('/_+/', '_', $key), '_');
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
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
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
