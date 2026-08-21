<?php

namespace App\Support;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\Shipment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class DashboardSlaAlert
{
    /** @return array{warning: array<int, string>, danger: array<int, string>} */
    public static function dso(?int $month = null, ?int $year = null): array
    {
        $alerts = self::emptyAlerts();

        foreach (self::periodQuery(Shipment::query(), 'terima_do', $month, $year)->get() as $shipment) {
            $target = DsoSla::targetFor($shipment->kota, $shipment->tujuan_pengiriman);

            if ($shipment->terima_do === null || $target === null) {
                continue;
            }

            $stages = $target['stages'];
            $prefix = 'No. Rangka '.($shipment->no_rangka ?: '-');

            if ($shipment->keluar_dari_pdc === null) {
                self::addAlert($alerts, $prefix, 'PDC', $shipment->terima_do->copy()->addDays($stages[0]));
            } elseif ($shipment->at_storage_port === null) {
                self::addAlert($alerts, $prefix, 'AT Storage Port', $shipment->keluar_dari_pdc->copy()->addDays($stages[1]));
            } elseif ($shipment->atd_kapal_loading === null) {
                self::addAlert($alerts, $prefix, 'ATD Kapal', $shipment->at_storage_port->copy()->addDays($stages[2]));
            } elseif ($shipment->ata_storage_port_destination === null) {
                $deadline = $shipment->ata_kapal !== null
                    ? $shipment->ata_kapal->copy()->addDays($stages[4])
                    : $shipment->atd_kapal_loading->copy()->addDays($stages[3] + $stages[4]);
                self::addAlert($alerts, $prefix, 'ATA Storage Port', $deadline);
            }
        }

        return $alerts;
    }

    /** @return array{warning: array<int, string>, danger: array<int, string>} */
    public static function isoLaut(?int $month = null, ?int $year = null): array
    {
        $alerts = self::emptyAlerts();

        foreach (self::periodQuery(IsoLautShipment::query(), 'terima_do', $month, $year)->get() as $shipment) {
            $target = IsoSla::targetFor('iso-laut', $shipment->destination);

            if ($shipment->terima_do === null || $target === null) {
                continue;
            }

            $stages = $target['stages'];
            $prefix = 'No. Rangka '.($shipment->noka ?: '-');

            if ($shipment->keluar_dari_pdc === null) {
                self::addAlert($alerts, $prefix, 'PDC', $shipment->terima_do->copy()->addDays($stages['keluar_dari_pdc']));
            } elseif ($shipment->at_storage_port === null) {
                self::addAlert($alerts, $prefix, 'AT Storage Port', $shipment->keluar_dari_pdc->copy()->addDays($stages['storage_port']));
            } elseif ($shipment->atd_kapal_loading === null) {
                self::addAlert($alerts, $prefix, 'ATD Kapal', $shipment->at_storage_port->copy()->addDays($stages['kapal_loading']));
            } elseif ($shipment->ata_storage_port_destination === null) {
                $deadline = $shipment->ata_kapal !== null
                    ? $shipment->ata_kapal->copy()->addDays($stages['storage_port_destination'])
                    : $shipment->atd_kapal_loading->copy()->addDays($stages['ata_kapal'] + $stages['storage_port_destination']);
                self::addAlert($alerts, $prefix, 'ATA Storage Port', $deadline);
            }
        }

        return $alerts;
    }

    /** @return array{warning: array<int, string>, danger: array<int, string>} */
    public static function isoDarat(?int $month = null, ?int $year = null): array
    {
        $alerts = self::emptyAlerts();

        foreach (self::periodQuery(IsoDaratShipment::query(), 'terima_do', $month, $year)->get() as $shipment) {
            $target = IsoSla::targetFor('iso-darat', $shipment->destination);

            if ($shipment->terima_do === null || $target === null) {
                continue;
            }

            $prefix = 'No. Rangka '.($shipment->no_spb ?: '-')
                .' / Nomor Driver '.($shipment->nomor_driver ?: '-');

            if ($shipment->keluar_dari_pdc === null) {
                self::addAlert($alerts, $prefix, 'Keluar PDC', $shipment->terima_do->copy(), true);
            } elseif (! self::hasMilestone($shipment->at_ptd_dtd)) {
                self::addAlert(
                    $alerts,
                    $prefix,
                    'AT PTD/DTD',
                    $shipment->terima_do->copy()->addDays($target['customer']),
                    true,
                );
            }
        }

        return $alerts;
    }

    /** @param array{warning: array<int, string>, danger: array<int, string>} $alerts */
    private static function addAlert(
        array &$alerts,
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
            $alerts['warning'][] = $isoDarat
                ? "{$prefix} deadline {$milestone} {$time}."
                : "{$prefix} Belum Keluar {$milestone} — deadline {$time}.";

            return;
        }

        $overdueDays = abs($remaining);
        $alerts['danger'][] = $isoDarat
            ? "{$prefix} sudah lewat {$milestone} {$overdueDays} hari."
            : "{$prefix} Belum Keluar {$milestone} lewat {$overdueDays} hari.";
    }

    private static function periodQuery(
        Builder $query,
        string $dateField,
        ?int $month,
        ?int $year,
    ): Builder {
        return $query
            ->whereNotNull($dateField)
            ->when($month !== null, fn (Builder $builder) => $builder->whereMonth($dateField, $month))
            ->when($year !== null, fn (Builder $builder) => $builder->whereYear($dateField, $year));
    }

    /** @return array{warning: array<int, string>, danger: array<int, string>} */
    private static function emptyAlerts(): array
    {
        return ['warning' => [], 'danger' => []];
    }

    private static function hasMilestone(mixed $value): bool
    {
        $value = strtoupper(trim((string) $value));

        return $value !== '' && ! str_contains($value, 'VALUE');
    }
}
