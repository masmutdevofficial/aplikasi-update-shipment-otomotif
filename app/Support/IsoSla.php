<?php

namespace App\Support;

class IsoSla
{
    /**
     * Tahap keluar_dari_pdc pada ISO Laut merupakan target saat shipment
     * masih berstatus Belum Keluar PDC dan bernilai 0 hari untuk semua tujuan.
     *
     * @return array<string, array<string, array{stages: array<string, int|null>, customer: int}>>
     */
    public static function targets(): array
    {
        return [
            'iso-laut' => [
                'BALIKPAPAN' => self::target([0, 1, 0, 3, 0, 1], 5),
                'BANJARMASIN' => self::target([0, 0, 0, 3, 0, 0], 4),
                'MAKASSAR' => self::target([0, 1, 0, 2, 0, 0], 5),
                'MANADO' => self::target([0, 1, 0, 2, 0, 0], 3),
                'MEDAN PATIMBAN' => self::target([0, 0, 0, 4, 0, 0], 5),
                'SAMARINDA' => self::target([0, 1, 0, 3, 0, 2], 6),
            ],
            'iso-darat' => [
                'BANDUNG' => self::daratTarget(1),
                'BEKASI' => self::daratTarget(3),
                'CILEGON' => self::daratTarget(2),
                'CIREBON' => self::daratTarget(1),
                'DKI JAKARTA' => self::daratTarget(3),
                'LAMPUNG' => self::daratTarget(3),
                'MALANG' => self::daratTarget(3),
                'MEDAN' => self::daratTarget(4),
                'PADANG' => self::daratTarget(5),
                'PALEMBANG' => self::daratTarget(3),
                'PEKALONGAN' => self::daratTarget(2),
                'PEKANBARU' => self::daratTarget(4),
                'SEMARANG' => self::daratTarget(3),
                'SOLO MAGELANG' => self::daratTarget(2),
                'SURABAYA' => self::daratTarget(3),
                'TANGERANG' => self::daratTarget(3),
                'YOGYAKARTA' => self::daratTarget(2),
            ],
        ];
    }

    /** @return array{stages: array<string, int|null>, customer: int}|null */
    public static function targetFor(string $type, ?string $destination): ?array
    {
        $destination = self::normalize($destination);

        if ($destination === '') {
            return null;
        }

        $targets = self::targets()[$type] ?? [];

        if (isset($targets[$destination])) {
            return $targets[$destination];
        }

        foreach ($targets as $city => $target) {
            if (str_contains($destination, $city)) {
                return $target;
            }
        }

        return null;
    }

    public static function customerFor(string $type, ?string $destination): ?int
    {
        return self::targetFor($type, $destination)['customer'] ?? null;
    }

    /** @param array<int, int|null> $values */
    private static function target(array $values, int $customer): array
    {
        return [
            'stages' => array_combine([
                'keluar_dari_pdc',
                'storage_port',
                'kapal_loading',
                'ata_kapal',
                'storage_port_destination',
                'ptd_dooring',
            ], $values),
            'customer' => $customer,
        ];
    }

    private static function daratTarget(int $customer): array
    {
        return self::target([null, null, null, null, null, $customer], $customer);
    }

    private static function normalize(?string $value): string
    {
        return trim((string) preg_replace('/[^A-Z0-9]+/', ' ', strtoupper(trim((string) $value))));
    }
}
