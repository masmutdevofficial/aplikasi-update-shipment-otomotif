<?php

namespace App\Models;

use App\Support\DsoSla;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lokasi',
        'no_do',
        'type_kendaraan',
        'no_rangka',
        'no_engine',
        'warna',
        'asal_pdc',
        'kota',
        'tujuan_pengiriman',
        'terima_do',
        'keluar_dari_pdc',
        'nama_kapal',
        'keberangkatan_kapal',
        'at_storage_port',
        'atd_kapal_loading',
        'ata_kapal',
        'ata_storage_port_destination',
        'at_ptd_dooring',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'terima_do' => 'date',
            'keluar_dari_pdc' => 'date',
            'keberangkatan_kapal' => 'date',
            'at_storage_port' => 'date',
            'atd_kapal_loading' => 'date',
            'ata_kapal' => 'date',
            'ata_storage_port_destination' => 'date',
            'at_ptd_dooring' => 'date',
        ];
    }

    public function leadTimeDoReleaseToPickup(): ?int
    {
        return $this->daysBetween($this->terima_do, $this->keluar_dari_pdc);
    }

    public function leadTimeStoragePort(): ?int
    {
        return $this->daysBetween($this->keluar_dari_pdc, $this->at_storage_port);
    }

    public function leadTimeKapalLoading(): ?int
    {
        return $this->daysBetween($this->at_storage_port, $this->atd_kapal_loading);
    }

    public function leadTimeKapalAboard(): ?int
    {
        return $this->daysBetween($this->atd_kapal_loading, $this->ata_kapal);
    }

    public function leadTimeStoragePortDestination(): ?int
    {
        return $this->daysBetween($this->ata_kapal, $this->ata_storage_port_destination);
    }

    public function leadTimePtdDooring(): ?int
    {
        return $this->daysBetween($this->ata_storage_port_destination, $this->at_ptd_dooring);
    }

    public function dwellingOrigin(): ?int
    {
        return $this->daysUntilMilestoneOrToday($this->at_storage_port, $this->atd_kapal_loading);
    }

    public function dwellingDestination(): ?int
    {
        return $this->daysUntilMilestoneOrToday($this->ata_storage_port_destination, $this->at_ptd_dooring);
    }

    public function slaActual(): ?int
    {
        if ($this->terima_do === null) {
            return null;
        }

        return $this->daysBetween(
            $this->terima_do,
            $this->at_ptd_dooring ?? now()->startOfDay(),
        );
    }

    public function slaCustomer(): ?int
    {
        $target = DsoSla::targetFor($this->kota, $this->tujuan_pengiriman);

        return $target['total'] ?? null;
    }

    public function slaResult(): string
    {
        $actual = $this->slaActual();
        $customer = $this->slaCustomer();

        if ($actual === null || $customer === null) {
            return 'IN PROGRESS';
        }

        return $actual <= $customer ? 'OTD' : 'LATE';
    }

    public function delayDays(): ?int
    {
        $actual = $this->slaActual();
        $customer = $this->slaCustomer();

        if ($actual === null || $customer === null) {
            return null;
        }

        return max(0, $actual - $customer);
    }

    public function maxArrival(): ?CarbonInterface
    {
        $customer = $this->slaCustomer();

        return $this->terima_do && $customer !== null
            ? $this->terima_do->copy()->addDays($customer)
            : null;
    }

    public function shipmentProgress(): string
    {
        return match (true) {
            $this->at_ptd_dooring !== null => $this->slaResult(),
            $this->ata_storage_port_destination !== null => 'Storage Port (Destination)',
            $this->ata_kapal !== null => 'Kapal (Aboard)',
            $this->atd_kapal_loading !== null => 'Kapal (Loading)',
            $this->at_storage_port !== null => 'Storage Port',
            $this->keluar_dari_pdc !== null => 'Pickup',
            $this->terima_do !== null => 'DO Received',
            default => 'Pending',
        };
    }

    public function currentPosition(): string
    {
        return match (true) {
            $this->at_ptd_dooring !== null => 'AT PtD (Dooring)',
            $this->ata_storage_port_destination !== null => 'ATA Storage Port (Destination)',
            $this->ata_kapal !== null => 'ATA Kapal',
            $this->atd_kapal_loading !== null => 'ATD Kapal (Loading)',
            $this->at_storage_port !== null => 'AT Storage Port',
            $this->keluar_dari_pdc !== null => 'Keluar dari PDC',
            default => 'Belum Keluar PDC',
        };
    }

    private function daysBetween(?CarbonInterface $from, ?CarbonInterface $to): ?int
    {
        if ($from === null || $to === null) {
            return null;
        }

        return max(0, (int) $from->diffInDays($to, false));
    }

    private function daysUntilMilestoneOrToday(?CarbonInterface $from, ?CarbonInterface $milestone): ?int
    {
        if ($from === null) {
            return null;
        }

        return $this->daysBetween($from, $milestone ?? now()->startOfDay());
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function shipmentUpdates(): HasMany
    {
        return $this->hasMany(ShipmentUpdate::class);
    }
}
