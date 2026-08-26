<?php

namespace App\Support;

use App\Models\SystemSetting;
use InvalidArgumentException;

class IsoSla
{
    /** @var array<int, array<string, array<string, int>>> */
    private static array $customerOverrides = [];

    /**
     * Tahap keluar_dari_pdc pada ISO Laut merupakan target saat shipment
     * masih berstatus Belum Keluar PDC dan bernilai 0 hari untuk semua tujuan.
     * Nilai PtD/Dooring dipindahkan ke Storage Port Destination sehingga
     * tahap PtD/Dooring juga bernilai 0 hari untuk semua tujuan ISO Laut.
     *
     * @return array<string, array<string, array{stages: array<string, int|null>, customer: int}>>
     */
    public static function targets(): array
    {
        $targets = self::baseTargets();

        foreach ($targets as $type => &$typeTargets) {
            foreach (self::customerOverrides($type) as $destination => $customer) {
                if (! isset($typeTargets[$destination])) {
                    continue;
                }

                $typeTargets[$destination]['customer'] = $customer;

                if ($type === 'iso-darat') {
                    $typeTargets[$destination]['stages']['ptd_dooring'] = $customer;
                }
            }
        }

        return $targets;
    }

    /** @return array<string, array<string, array{stages: array<string, int|null>, customer: int}>> */
    private static function baseTargets(): array
    {
        return [
            'iso-laut' => [
                'BALIKPAPAN' => self::target([0, 1, 0, 3, 1, 0], 5),
                'BANJARMASIN' => self::target([0, 0, 0, 3, 0, 0], 4),
                'MAKASSAR' => self::target([0, 1, 0, 2, 0, 0], 5),
                'MANADO' => self::target([0, 1, 0, 2, 0, 0], 3),
                'MEDAN PATIMBAN' => self::target([0, 0, 0, 4, 0, 0], 5),
                'SAMARINDA' => self::target([0, 1, 0, 3, 2, 0], 6),
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

    /** @param array<string, int> $customers */
    public static function setCustomers(string $type, array $customers): void
    {
        if (! in_array($type, ['iso-darat', 'iso-laut'], true)) {
            throw new InvalidArgumentException('Tipe SLA ISO tidak valid.');
        }

        $defaults = self::defaultCustomers($type);
        $values = [];

        foreach ($defaults as $destination => $_default) {
            $customer = filter_var($customers[$destination] ?? null, FILTER_VALIDATE_INT);

            if ($customer === false || $customer < 1 || $customer > 365) {
                throw new InvalidArgumentException("SLA Customer {$destination} tidak valid.");
            }

            $values[$destination] = $customer;
        }

        SystemSetting::query()->updateOrCreate(
            ['setting_key' => self::settingKey($type)],
            ['setting_value' => json_encode($values, JSON_THROW_ON_ERROR)],
        );

        unset(self::$customerOverrides[self::applicationKey()][$type]);
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

    /** @return array<string, int> */
    private static function customerOverrides(string $type): array
    {
        $applicationKey = self::applicationKey();

        if (isset(self::$customerOverrides[$applicationKey][$type])) {
            return self::$customerOverrides[$applicationKey][$type];
        }

        $stored = SystemSetting::query()
            ->where('setting_key', self::settingKey($type))
            ->value('setting_value');

        try {
            $decoded = is_string($stored) ? json_decode($stored, true, flags: JSON_THROW_ON_ERROR) : [];
        } catch (\JsonException) {
            $decoded = [];
        }

        $defaults = self::defaultCustomers($type);
        $overrides = collect(is_array($decoded) ? $decoded : [])
            ->filter(fn (mixed $value, mixed $destination) => isset($defaults[$destination])
                && is_numeric($value)
                && (int) $value >= 1
                && (int) $value <= 365)
            ->map(fn (mixed $value) => (int) $value)
            ->all();

        return self::$customerOverrides[$applicationKey][$type] = $overrides;
    }

    /** @return array<string, int> */
    private static function defaultCustomers(string $type): array
    {
        return collect(self::baseTargets()[$type] ?? [])
            ->map(fn (array $target) => $target['customer'])
            ->all();
    }

    private static function settingKey(string $type): string
    {
        return 'iso_sla_customer_' . str_replace('-', '_', $type);
    }

    private static function applicationKey(): int
    {
        return function_exists('app') ? spl_object_id(app()) : 0;
    }
}
