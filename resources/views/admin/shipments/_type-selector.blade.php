@php
    $shipmentSection = $shipmentSection ?? 'dso';
    $currentSpecialType = $type ?? 'iso-darat';
@endphp

<div class="card" style="border-left:4px solid var(--primary);">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <label for="shipmentTypeSelector" class="form-label" style="margin-bottom:3px;">Pilih Data Shipment</label>
                <div class="text-muted small">Setiap jenis dibuka pada halaman terpisah agar pemuatan data lebih ringan.</div>
            </div>
            <select id="shipmentTypeSelector" class="form-select" style="width:auto; min-width:220px;" onchange="window.location.href=this.value">
                <option value="{{ route('admin.shipments.index') }}" @selected($shipmentSection === 'dso')>Shipment DSO</option>
                <option value="{{ route('admin.special-shipments.index', 'tso') }}" @selected($shipmentSection === 'tso')>Shipment TSO</option>
                <option value="{{ route('admin.special-shipments.index', 'iso-darat') }}" @selected($shipmentSection === 'iso')>Shipment ISO</option>
            </select>
        </div>

        @if ($shipmentSection === 'iso')
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('admin.special-shipments.index', 'iso-darat') }}" class="btn btn-sm {{ $currentSpecialType === 'iso-darat' ? 'btn-primary' : 'btn-default' }}">
                    <i class="fas fa-truck"></i> ISO Darat
                </a>
                <a href="{{ route('admin.special-shipments.index', 'iso-laut') }}" class="btn btn-sm {{ $currentSpecialType === 'iso-laut' ? 'btn-primary' : 'btn-default' }}">
                    <i class="fas fa-ship"></i> ISO Laut
                </a>
            </div>
        @endif
    </div>
</div>
