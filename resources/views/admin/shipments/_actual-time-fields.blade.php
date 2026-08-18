@php
    $actualTimeFields = [
        'at_storage_port' => 'AT Storage Port',
        'atd_kapal_loading' => 'ATD Kapal (Loading)',
        'ata_kapal' => 'ATA Kapal',
        'ata_storage_port_destination' => 'ATA Storage Port (Destination)',
        'at_ptd_dooring' => 'AT PtD (Dooring)',
    ];
@endphp

<div class="col-12">
    <hr>
    <h4 style="font-size:15px; margin-bottom:4px;">Actual Time (Input)</h4>
    <p class="text-muted small mb-3">Lead time, SLA, result, max arrival, progress, dan persentase keterlambatan dihitung otomatis.</p>
</div>

@foreach ($actualTimeFields as $field => $label)
    <div class="col-md-6">
        <label for="{{ $field }}" class="form-label fw-semibold">{{ $label }}</label>
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
    </div>
@endforeach
