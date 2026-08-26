@php
    $shipmentSection = $shipmentSection ?? 'dso';
    $currentSpecialType = $type ?? 'iso-darat';
    $shipmentSelectorMode = $shipmentSelectorMode ?? 'index';
    $dsoSelectorRoute = $shipmentSelectorMode === 'import'
        ? route('admin.shipments.import.form')
        : route('admin.shipments.index');
    $specialSelectorRoute = static fn (string $shipmentType) => $shipmentSelectorMode === 'import'
        ? route('admin.special-shipments.import.form', $shipmentType)
        : route('admin.special-shipments.index', $shipmentType);
@endphp

<div class="card shipment-selector-card">
    <div class="card-body">
        <div class="shipment-selector">
            <div class="shipment-selector-header">
                <span class="shipment-selector-label">Pilih Shipment</span>
                <p class="shipment-selector-description">Pilih data shipment yang ingin ditampilkan.</p>
            </div>

            <nav class="shipment-tabs" aria-label="Pilihan data shipment">
                <a
                    href="{{ $dsoSelectorRoute }}"
                    class="shipment-tab {{ $shipmentSection === 'dso' ? 'active' : '' }}"
                    @if ($shipmentSection === 'dso') aria-current="page" @endif
                ><i class="fas fa-truck"></i> DSO</a>
                <a
                    href="{{ $specialSelectorRoute('tso') }}"
                    class="shipment-tab {{ $shipmentSection === 'tso' ? 'active' : '' }}"
                    @if ($shipmentSection === 'tso') aria-current="page" @endif
                ><i class="fas fa-truck-loading"></i> TSO</a>
                <a
                    href="{{ $specialSelectorRoute('iso-darat') }}"
                    class="shipment-tab {{ $shipmentSection === 'iso' && $currentSpecialType === 'iso-darat' ? 'active' : '' }}"
                    @if ($shipmentSection === 'iso' && $currentSpecialType === 'iso-darat') aria-current="page" @endif
                ><i class="fas fa-road"></i> ISO Darat</a>
                <a
                    href="{{ $specialSelectorRoute('iso-laut') }}"
                    class="shipment-tab {{ $shipmentSection === 'iso' && $currentSpecialType === 'iso-laut' ? 'active' : '' }}"
                    @if ($shipmentSection === 'iso' && $currentSpecialType === 'iso-laut') aria-current="page" @endif
                ><i class="fas fa-ship"></i> ISO Laut</a>
            </nav>
        </div>
    </div>
</div>

@once
    @push('styles')
.shipment-selector-card {
    border-left: 4px solid var(--primary);
}

.shipment-selector {
    display: block;
}

.shipment-selector-label {
    display: block;
    margin-bottom: 3px;
    color: #343a40;
    font-size: 15px;
    font-weight: 600;
}

.shipment-selector-description {
    margin: 0 0 16px;
    color: #6c757d;
    font-size: 13px;
}

.shipment-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    padding-bottom: 12px;
    overflow-x: auto;
    border-bottom: 1px solid #e2e8f0;
}

.shipment-tab {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 7px;
    padding: 10px 16px;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
    transition: color .2s ease, border-color .2s ease, background .2s ease, transform .2s ease;
}

.shipment-tab:hover {
    color: #1d4ed8;
    text-decoration: none;
    border-color: #93c5fd;
    background: #eff6ff;
    transform: translateY(-1px);
}

.shipment-tab.active {
    color: #fff;
    border-color: #2563eb;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 5px 12px rgba(37, 99, 235, .24);
}
    @endpush
@endonce
