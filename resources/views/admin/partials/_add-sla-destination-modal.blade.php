@php
    $addSlaErrors = $errors->getBag('addSlaDestination');
    $slaStageFields = [
        'keluar_dari_pdc' => 'Belum Keluar PDC',
        'storage_port' => 'Storage Port',
        'kapal_loading' => 'Kapal (Loading)',
        'ata_kapal' => 'ATA Kapal',
        'storage_port_destination' => 'Storage Port (Destination)',
        'ptd_dooring' => 'PtD (Dooring)',
    ];
@endphp

<div class="modal fade" id="{{ $slaAddModalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $slaAddModalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $slaAddAction }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $slaAddModalId }}Label">
                        <i class="fas fa-map-marker-alt text-info"></i> {{ $slaAddTitle }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="{{ $slaAddModalId }}Destination">Nama Destination</label>
                        <input
                            type="text"
                            id="{{ $slaAddModalId }}Destination"
                            name="destination"
                            value="{{ old('destination') }}"
                            class="form-control {{ $addSlaErrors->has('destination') ? 'is-invalid' : '' }}"
                            maxlength="100"
                            placeholder="Contoh: Kendari"
                            required
                            autofocus
                        >
                        @if ($addSlaErrors->has('destination'))
                            <div class="invalid-feedback">{{ $addSlaErrors->first('destination') }}</div>
                        @endif
                    </div>

                    <div class="sla-destination-stage-grid">
                        @foreach ($slaStageFields as $stage => $label)
                            <div class="form-group mb-0">
                                <label for="{{ $slaAddModalId }}-{{ $stage }}">{{ $label }}</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        id="{{ $slaAddModalId }}-{{ $stage }}"
                                        name="sla_stages[{{ $stage }}]"
                                        value="{{ old("sla_stages.{$stage}", 0) }}"
                                        class="form-control {{ $addSlaErrors->has("sla_stages.{$stage}") ? 'is-invalid' : '' }}"
                                        min="0"
                                        max="365"
                                        required
                                    >
                                    <div class="input-group-append"><span class="input-group-text">hari</span></div>
                                </div>
                                @if ($addSlaErrors->has("sla_stages.{$stage}"))
                                    <small class="text-danger">{{ $addSlaErrors->first("sla_stages.{$stage}") }}</small>
                                @endif
                            </div>
                        @endforeach

                        <div class="form-group mb-0 sla-customer-modal-field">
                            <label for="{{ $slaAddModalId }}Customer">SLA Customer</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    id="{{ $slaAddModalId }}Customer"
                                    name="sla_customer"
                                    value="{{ old('sla_customer') }}"
                                    class="form-control {{ $addSlaErrors->has('sla_customer') ? 'is-invalid' : '' }}"
                                    min="1"
                                    max="365"
                                    required
                                >
                                <div class="input-group-append"><span class="input-group-text">hari</span></div>
                            </div>
                            @if ($addSlaErrors->has('sla_customer'))
                                <small class="text-danger">{{ $addSlaErrors->first('sla_customer') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-plus"></i> Tambah Destination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
    @push('styles')
.sla-destination-stage-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}
.sla-customer-modal-field {
    grid-column: 1 / -1;
}
@media (max-width: 576px) {
    .sla-destination-stage-grid { grid-template-columns: 1fr; }
    .sla-customer-modal-field { grid-column: auto; }
}
    @endpush
@endonce

@if ($addSlaErrors->any())
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#{{ $slaAddModalId }}').modal('show');
});
</script>
    @endpush
@endif
