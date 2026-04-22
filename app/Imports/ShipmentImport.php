<?php

namespace App\Imports;

use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ShipmentImport implements ToCollection, WithHeadingRow
{
    public int $importedCount = 0;
    public int $updatedCount  = 0;
    public int $skippedCount  = 0;

    /** @var array<array{baris: int, pesan: string}> */
    public array $errors = [];

    private string $createdBy;

    /**
     * Pemetaan nama kolom Excel → nama field internal.
     * Mendukung berbagai variasi header (template bawaan, format user, dll.)
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
    ];

    public function __construct(string $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // baris Excel (baris 1 = header)
            $data   = $row->toArray();

            // Skip baris kosong
            if (empty(array_filter($data))) {
                continue;
            }

            // Resolusi nilai dari nama kolom yang fleksibel
            $lokasi              = $this->resolveCol($data, 'lokasi');
            $no_do               = $this->resolveCol($data, 'no_do');
            $type_kendaraan      = $this->resolveCol($data, 'type_kendaraan');
            $no_rangka           = strtoupper(trim((string) $this->resolveCol($data, 'no_rangka')));
            $no_engine           = $this->resolveCol($data, 'no_engine');
            $warna               = $this->resolveCol($data, 'warna');
            $asal_pdc            = $this->resolveCol($data, 'asal_pdc');
            $kota                = $this->resolveCol($data, 'kota');
            $tujuan_pengiriman   = $this->resolveCol($data, 'tujuan_pengiriman');
            $terima_do_raw       = $this->resolveCol($data, 'terima_do');
            $keluar_dari_pdc_raw = $this->resolveCol($data, 'keluar_dari_pdc');
            $nama_kapal          = $this->resolveCol($data, 'nama_kapal');
            $keberangkatan_raw   = $this->resolveCol($data, 'keberangkatan_kapal');

            // Validasi manual dengan pesan Bahasa Indonesia
            $rowErrors = [];

            if (empty($lokasi)) {
                $rowErrors[] = 'Kolom Lokasi wajib diisi';
            }
            if (empty($no_do)) {
                $rowErrors[] = 'Kolom No. DO wajib diisi';
            }
            if (empty($type_kendaraan)) {
                $rowErrors[] = 'Kolom Type Kendaraan wajib diisi';
            }
            if (empty($no_rangka)) {
                $rowErrors[] = 'Kolom No. Rangka (VIN) wajib diisi';
            } elseif (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $no_rangka)) {
                $rowErrors[] = "No. Rangka \"{$no_rangka}\" harus tepat 17 karakter huruf/angka (tidak boleh I, O, atau Q)";
            }
            if (empty($no_engine)) {
                $rowErrors[] = 'Kolom No. Engine wajib diisi';
            }
            if (empty($warna)) {
                $rowErrors[] = 'Kolom Warna wajib diisi';
            }
            if (empty($asal_pdc)) {
                $rowErrors[] = 'Kolom Asal PDC wajib diisi';
            }
            if (empty($kota)) {
                $rowErrors[] = 'Kolom Kota wajib diisi';
            }
            if (empty($tujuan_pengiriman)) {
                $rowErrors[] = 'Kolom Tujuan Pengiriman wajib diisi';
            }
            if (empty($terima_do_raw)) {
                $rowErrors[] = 'Kolom Tanggal Terima DO wajib diisi';
            }
            if (empty($keluar_dari_pdc_raw)) {
                $rowErrors[] = 'Kolom Tanggal Keluar dari PDC wajib diisi';
            }
            if (!empty($rowErrors)) {
                foreach ($rowErrors as $msg) {
                    $this->errors[] = ['baris' => $rowNum, 'pesan' => $msg];
                }
                continue;
            }

            // Parse tanggal wajib
            $terima_do       = $this->parseDate($terima_do_raw);
            $keluar_dari_pdc = $this->parseDate($keluar_dari_pdc_raw);

            if (!$terima_do) {
                $this->errors[] = ['baris' => $rowNum, 'pesan' => 'Format tanggal Terima DO tidak dikenali. Gunakan format seperti 2026-04-01 atau 01/04/2026'];
                continue;
            }
            if (!$keluar_dari_pdc) {
                $this->errors[] = ['baris' => $rowNum, 'pesan' => 'Format tanggal Keluar dari PDC tidak dikenali. Gunakan format seperti 2026-04-01 atau 01/04/2026'];
                continue;
            }

            // --- Mode deteksi berdasarkan keberadaan data kapal ---
            $hasKapalData = !empty($nama_kapal) || !empty($keberangkatan_raw);

            if ($hasKapalData) {
                // MODE UPDATE KAPAL: tambahkan data kapal ke shipment yang sudah ada
                $shipment = Shipment::where('no_rangka', $no_rangka)->first();

                if (!$shipment) {
                    $this->errors[] = [
                        'baris' => $rowNum,
                        'pesan' => "No. Rangka \"{$no_rangka}\" belum terdaftar. Upload data kapal hanya untuk shipment yang sudah ada.",
                    ];
                    continue;
                }

                // Parse tanggal keberangkatan
                $keberangkatan_kapal = $this->parseDate($keberangkatan_raw);
                if (!empty($keberangkatan_raw) && !$keberangkatan_kapal) {
                    $this->errors[] = ['baris' => $rowNum, 'pesan' => 'Format tanggal Keberangkatan Kapal tidak dikenali. Gunakan format seperti 2026-04-01 atau 01/04/2026'];
                    continue;
                }

                $shipment->update([
                    'nama_kapal'          => !empty($nama_kapal) ? trim((string) $nama_kapal) : $shipment->nama_kapal,
                    'keberangkatan_kapal' => $keberangkatan_kapal ?? $shipment->keberangkatan_kapal,
                    'updated_by'          => $this->createdBy,
                ]);

                $this->updatedCount++;
            } else {
                // MODE SHIPMENT BARU: buat entri baru, data kapal boleh kosong
                if (Shipment::where('no_rangka', $no_rangka)->exists()) {
                    $this->skippedCount++;
                    continue;
                }

                Shipment::create([
                    'lokasi'              => trim((string) $lokasi),
                    'no_do'               => trim((string) $no_do),
                    'type_kendaraan'      => trim((string) $type_kendaraan),
                    'no_rangka'           => $no_rangka,
                    'no_engine'           => trim((string) $no_engine),
                    'warna'               => trim((string) $warna),
                    'asal_pdc'            => trim((string) $asal_pdc),
                    'kota'                => trim((string) $kota),
                    'tujuan_pengiriman'   => trim((string) $tujuan_pengiriman),
                    'terima_do'           => $terima_do,
                    'keluar_dari_pdc'     => $keluar_dari_pdc,
                    'nama_kapal'          => null,
                    'keberangkatan_kapal' => null,
                    'created_by'          => $this->createdBy,
                    'updated_by'          => $this->createdBy,
                ]);

                $this->importedCount++;
            }
        }
    }

    /**
     * Cari nilai dari row berdasarkan berbagai kemungkinan nama kolom.
     * maatwebsite/excel mengubah header → lowercase + underscore (tanpa karakter khusus).
     */
    private function resolveCol(array $row, string $field): mixed
    {
        $candidates = self::COLUMN_MAP[$field] ?? [$field];

        foreach ($candidates as $key) {
            // Normalisasi: lowercase, spasi → underscore, hapus karakter selain huruf/angka/_
            $normalized = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', strtolower($key)));

            if (isset($row[$normalized]) && $row[$normalized] !== '') {
                return $row[$normalized];
            }
            // Coba key asli juga
            if (isset($row[$key]) && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    /**
     * Parse tanggal dari serial number Excel atau string berbagai format.
     */
    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
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

        // Format yang didukung
        $formats = [
            'Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y',
            'd M Y', 'd-M-Y', 'd-M-y', 'j-M-y', 'j-M-Y',
            'Y/m/d', 'd/m/y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $str)->format('Y-m-d');
            } catch (\Exception) {
                continue;
            }
        }

        // Fallback
        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
