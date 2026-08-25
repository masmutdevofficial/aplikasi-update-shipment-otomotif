@php
    $actualTimeFields = [
        'at_storage_port' => 'AT Storage Port',
        'atd_kapal_loading' => 'ATD Kapal (Loading)',
        'ata_kapal' => 'ATA Kapal',
        'ata_storage_port_destination' => 'ATA Storage Port (Destination)',
        'at_ptd_dooring' => 'AT PtD (Dooring)',
    ];
    $automaticScanFields = ['at_storage_port', 'atd_kapal_loading'];
@endphp

<div class="col-12">
    <hr>
    <h4 style="font-size:15px; margin-bottom:4px;">Actual Time (Input)</h4>
    <p class="text-muted small mb-3">AT Storage Port dan ATD Kapal (Loading) tidak wajib diisi admin karena akan tercatat melalui scan vendor. Lead time, dwelling origin/destination, SLA, result, max arrival, progress, dan keterlambatan dalam hari dihitung otomatis.</p>
</div>

@foreach ($actualTimeFields as $field => $label)
    <div class="col-md-6">
        <label for="{{ $field }}" class="form-label fw-semibold">
            {{ $label }}
            @if (in_array($field, $automaticScanFields, true))
                <span class="badge badge-info ml-1">Opsional — scan vendor</span>
            @endif
        </label>
        <input
            type="date"
            class="form-control @error($field) is-invalid @enderror"
            id="{{ $field }}"
            name="{{ $field }}"
            value="{{ old($field, isset($shipment) ? $shipment->{$field}?->format('Y-m-d') : null) }}"
        >
        @error($field)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if (in_array($field, $automaticScanFields, true))
            <small class="form-text text-muted">Boleh dikosongkan; tanggal akan muncul setelah vendor melakukan scan.</small>
        @endif
    </div>
@endforeach
