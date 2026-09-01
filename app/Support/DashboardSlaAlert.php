<?php

namespace App\Support;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\Shipment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class DashboardSlaAlert
{
    /** @return array{warning: array<int, string>, danger: array<int, string>, stages: array<string, array{warning: array<int, string>, danger: array<int, string>}>} */
    public static function dso(?int $month = null, ?int $year = null, ?int $day = null): array
    {
        $alerts = self::emptyAlerts([
            'not_departed_pdc',
            'departed_pdc',
            'storage_port',
            'vessel_loading',
            'vessel_arrived',
            'destination_storage',
        ]);

        foreach (self::periodQuery(Shipment::query(), 'terima_do', $month, $year, $day)->get() as $shipment) {
            if ($shipment->isDoHold() || trim((string) $shipment->kota) === '') {
                continue;
            }

            $target = DsoSla::targetFor($shipment->kota, $shipment->tujuan_pengiriman);

            if ($shipment->terima_do === null || $target === null) {
                continue;
            }

            $stages = $target['stages'];
            $prefix = 'No. Rangka '.($shipment->no_rangka ?: '-');

            switch ($shipment->currentPosition()) {
                case 'Belum Keluar PDC':
                    self::addAlert($alerts, 'not_departed_pdc', $prefix, 'PDC', $shipment->terima_do->copy()->addDays($stages[0]));
                    break;
                case 'Keluar dari PDC':
                    self::addAlert($alerts, 'departed_pdc', $prefix, 'AT Storage Port', $shipment->keluar_dari_pdc->copy()->addDays($stages[1]));
                    break;
                case 'AT Storage Port':
                    self::addAlert($alerts, 'storage_port', $prefix, 'ATD Kapal', $shipment->at_storage_port->copy()->addDays($stages[2]));
                    break;
                case 'ATD Kapal (Loading)':
                    self::addAlert(
                        $alerts,
                        'vessel_loading',
                        $prefix,
                        'ATA Storage Port',
                        $shipment->atd_kapal_loading->copy()->addDays($stages[3] + $stages[4]),
                    );
                    break;
                case 'ATA Kapal':
                    self::addAlert($alerts, 'vessel_arrived', $prefix, 'ATA Storage Port', $shipment->ata_kapal->copy()->addDays($stages[4]));
                    break;
                case 'ATA Storage Port (Destination)':
                    self::addAlert(
                        $alerts,
                        'destination_storage',
                        $prefix,
                        'AT PtD (Dooring)',
                        $shipment->ata_storage_port_destination->copy()->addDays($stages[5]),
                    );
                    break;
            }
        }

        return $alerts;
    }

    /** @return array{warning: array<int, string>, danger: array<int, string>, stages: array<string, array{warning: array<int, string>, danger: array<int, string>}>} */
    public static function isoLaut(?int $month = null, ?int $year = null, ?int $day = null): array
    {
        $alerts = self::emptyAlerts([
            'not_departed_pdc',
            'departed_pdc',
            'storage_port',
            'vessel_loading',
            'vessel_arrived',
            'destination_storage',
        ]);

        foreach (self::periodQuery(IsoLautShipment::query(), 'terima_do', $month, $year, $day)->get() as $shipment) {
            $target = IsoSla::targetFor('iso-laut', $shipment->destination);

            if ($shipment->terima_do === null || $target === null) {
                continue;
            }

            $stages = $target['stages'];
            $prefix = 'No. Rangka '.($shipment->noka ?: '-');

            switch (IsoDashboard::currentPosition('iso-laut', $shipment)) {
                case 'DO Received':
                    self::addAlert($alerts, 'not_departed_pdc', $prefix, 'PDC', $shipment->terima_do->copy()->addDays($stages['keluar_dari_pdc']));
                    break;
                case 'Pickup':
                    self::addAlert($alerts, 'departed_pdc', $prefix, 'AT Storage Port', $shipment->keluar_dari_pdc->copy()->addDays($stages['storage_port']));
                    break;
                case 'Storage Port':
                    self::addAlert($alerts, 'storage_port', $prefix, 'ATD Kapal', $shipment->at_storage_port->copy()->addDays($stages['kapal_loading']));
                    break;
                case 'Kapal (Loading)':
                    self::addAlert(
                        $alerts,
                        'vessel_loading',
                        $prefix,
                        'ATA Storage Port',
                        $shipment->atd_kapal_loading->copy()->addDays($stages['ata_kapal'] + $stages['storage_port_destination']),
                    );
                    break;
                case 'Kapal (Aboard)':
                    self::addAlert(
                        $alerts,
                        'vessel_arrived',
                        $prefix,
                        'ATA Storage Port',
                        $shipment->ata_kapal->copy()->addDays($stages['storage_port_destination']),
                    );
                    break;
                case 'Storage Port (Destination)':
                    self::addAlert(
                        $alerts,
                        'destination_storage',
                        $prefix,
                        'AT PTD/DTD',
                        $shipment->ata_storage_port_destination->copy()->addDays($stages['ptd_dooring']),
                    );
                    break;
            }
        }

        return $alerts;
    }

    /** @return array{warning: array<int, string>, danger: array<int, string>, stages: array<string, array{warning: array<int, string>, danger: array<int, string>}>} */
    public static function isoDarat(?int $month = null, ?int $year = null, ?int $day = null): array
    {
        $alerts = self::emptyAlerts(['departed_pdc', 'ptd_dtd']);

        foreach (self::periodQuery(IsoDaratShipment::query(), 'terima_do', $month, $year, $day)->get() as $shipment) {
            $target = IsoSla::targetFor('iso-darat', $shipment->destination);

            if ($shipment->terima_do === null || $target === null) {
                continue;
            }

            $prefix = 'No. Rangka '.($shipment->no_spb ?: '-')
                .' / Nomor Driver '.($shipment->nomor_driver ?: '-');

            if ($shipment->keluar_dari_pdc === null) {
                self::addAlert($alerts, 'departed_pdc', $prefix, 'Keluar PDC', $shipment->terima_do->copy(), true);
            } elseif (! self::hasMilestone($shipment->at_ptd_dtd)) {
                self::addAlert(
                    $alerts,
                    'ptd_dtd',
                    $prefix,
                    'AT PTD/DTD',
                    $shipment->terima_do->copy()->addDays($target['customer']),
                    true,
                );
            }
        }

        return $alerts;
    }

    /** @param array{warning: array<int, string>, danger: array<int, string>, stages: array<string, array{warning: array<int, string>, danger: array<int, string>}>} $alerts */
    private static function addAlert(
        array &$alerts,
        string $stageKey,
        string $prefix,
        string $milestone,
        CarbonInterface $deadline,
        bool $isoDarat = false,
    ): void {
        $remaining = (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);

        if ($remaining > 1) {
            return;
        }

        if ($remaining >= 0) {
            $time = $remaining === 0 ? 'hari ini' : '1 hari lagi';
            $message = $isoDarat
                ? "{$prefix} deadline {$milestone} {$time}."
                : "{$prefix} Belum Keluar {$milestone} — deadline {$time}.";
            $alerts['warning'][] = $message;
            $alerts['stages'][$stageKey]['warning'][] = $message;

            return;
        }

        $overdueDays = abs($remaining);
        $message = $isoDarat
            ? "{$prefix} sudah lewat {$milestone} {$overdueDays} hari."
            : "{$prefix} Belum Keluar {$milestone} lewat {$overdueDays} hari.";
        $alerts['danger'][] = $message;
        $alerts['stages'][$stageKey]['danger'][] = $message;
    }

    private static function periodQuery(
        Builder $query,
        string $dateField,
        ?int $month,
        ?int $year,
        ?int $day = null,
    ): Builder {
        return $query
            ->whereNotNull($dateField)
            ->when($day !== null, fn (Builder $builder) => $builder->whereDay($dateField, $day))
            ->when($month !== null, fn (Builder $builder) => $builder->whereMonth($dateField, $month))
            ->when($year !== null, fn (Builder $builder) => $builder->whereYear($dateField, $year));
    }

    /** @param array<int, string> $stageKeys */
    private static function emptyAlerts(array $stageKeys): array
    {
        return [
            'warning' => [],
            'danger' => [],
            'stages' => array_fill_keys($stageKeys, ['warning' => [], 'danger' => []]),
        ];
    }

    private static function hasMilestone(mixed $value): bool
    {
        $value = strtoupper(trim((string) $value));

        return $value !== '' && ! str_contains($value, 'VALUE');
    }
}
