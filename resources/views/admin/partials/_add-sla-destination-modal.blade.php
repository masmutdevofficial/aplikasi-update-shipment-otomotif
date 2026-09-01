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

<div class="modal fade sla-destination-modal" id="{{ $slaAddModalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $slaAddModalId }}Label" aria-hidden="true" hidden>
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
.sla-destination-modal {
    position: fixed;
    inset: 0;
    z-index: 1060;
    display: none;
    overflow-x: hidden;
    overflow-y: auto;
    padding: 24px 12px;
    background: rgba(0, 0, 0, .5);
}
.sla-destination-modal.show {
    display: block;
}
.sla-destination-modal .modal-dialog {
    width: 100%;
    max-width: 800px;
    margin: 28px auto;
}
.sla-destination-modal .modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    color: #212529;
    background: #fff;
    border: 1px solid rgba(0, 0, 0, .2);
    border-radius: 6px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, .28);
}
.sla-destination-modal .modal-header,
.sla-destination-modal .modal-footer {
    display: flex;
    align-items: center;
    padding: 16px;
}
.sla-destination-modal .modal-header {
    justify-content: space-between;
    border-bottom: 1px solid #dee2e6;
}
.sla-destination-modal .modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}
.sla-destination-modal .modal-body {
    position: relative;
    padding: 16px;
}
.sla-destination-modal .modal-footer {
    justify-content: flex-end;
    gap: 8px;
    border-top: 1px solid #dee2e6;
}
.sla-destination-modal .close {
    padding: 4px 8px;
    color: #000;
    font-size: 24px;
    line-height: 1;
    background: transparent;
    border: 0;
    opacity: .55;
    cursor: pointer;
}
.sla-destination-modal .close:hover {
    opacity: .85;
}
body.sla-modal-open {
    overflow: hidden;
}
.sla-destination-stage-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}
.sla-customer-modal-field {
    grid-column: 1 / -1;
}
@media (max-width: 576px) {
    .sla-destination-modal {
        padding: 8px;
    }
    .sla-destination-modal .modal-dialog {
        margin: 8px auto;
    }
    .sla-destination-stage-grid { grid-template-columns: 1fr; }
    .sla-customer-modal-field { grid-column: auto; }
}
    @endpush

    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let previouslyFocused = null;

    function openModal(modal) {
        if (!modal) return;

        previouslyFocused = document.activeElement;
        modal.hidden = false;
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('sla-modal-open');

        window.requestAnimationFrame(function () {
            const firstInput = modal.querySelector('input:not([type="hidden"]), button, select, textarea');
            if (firstInput) firstInput.focus();
        });
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove('show');
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('sla-modal-open');

        if (previouslyFocused) previouslyFocused.focus();
    }

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-toggle="modal"][data-target]');

        if (trigger) {
            event.preventDefault();
            openModal(document.querySelector(trigger.getAttribute('data-target')));
            return;
        }

        const dismiss = event.target.closest('[data-dismiss="modal"]');

        if (dismiss) {
            closeModal(dismiss.closest('.sla-destination-modal'));
            return;
        }

        if (event.target.classList.contains('sla-destination-modal')) {
            closeModal(event.target);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        closeModal(document.querySelector('.sla-destination-modal.show'));
    });

    window.SlaDestinationModal = {
        open: function (id) {
            openModal(document.getElementById(id));
        },
    };
});
</script>
    @endpush
@endonce

@if ($addSlaErrors->any())
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.SlaDestinationModal.open('{{ $slaAddModalId }}');
});
</script>
    @endpush
@endif
