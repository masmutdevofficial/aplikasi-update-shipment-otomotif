<?php

namespace App\Support;

use Carbon\CarbonInterface;

class ShipmentDashboard
{
    /**
     * Build the dashboard URL that displays a shipment type and, when
     * available, the same period as the manually entered record.
     */
    public static function url(string $type, ?CarbonInterface $periodDate = null): string
    {
        $parameters = match ($type) {
            'dso' => ['type' => 'dso'],
            'tso' => ['type' => 'tso'],
            'iso-darat' => ['type' => 'iso', 'iso_type' => 'darat'],
            'iso-laut' => ['type' => 'iso', 'iso_type' => 'laut'],
            default => ['type' => 'dso'],
        };

        if ($periodDate !== null) {
            $parameters['month'] = $periodDate->month;
            $parameters['year'] = $periodDate->year;
        }

        return route('admin.dashboard', $parameters);
    }

    public static function label(string $type): string
    {
        return match ($type) {
            'tso' => 'Lihat Dashboard TSO',
            'iso-darat' => 'Lihat Dashboard ISO Darat',
            'iso-laut' => 'Lihat Dashboard ISO Laut',
            default => 'Lihat Dashboard DSO',
        };
    }
}
