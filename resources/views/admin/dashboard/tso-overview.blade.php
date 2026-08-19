@php
    $tsoSummaryCards = [
        ['label' => 'Total Shipments', 'value' => number_format($dashboardShipmentTotal), 'icon' => 'fa-truck-loading', 'theme' => 'blue'],
        ['label' => 'Total Vendor', 'value' => number_format($dashboardVendorTotal), 'icon' => 'fa-warehouse', 'theme' => 'green'],
        ['label' => 'Total Users', 'value' => number_format($dashboardUserTotal), 'icon' => 'fa-users', 'theme' => 'cyan'],
        ['label' => 'Scan Sesuai Periode', 'value' => number_format($dashboardScanTotal), 'icon' => 'fa-qrcode', 'theme' => 'orange'],
        ['label' => 'Shipment Dievaluasi', 'value' => number_format($specialDelayStats['evaluated']), 'icon' => 'fa-check-circle', 'theme' => 'teal'],
    ];
@endphp

<div class="card card-outline card-primary dashboard-overview-panel mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Ringkasan TSO</h3>
    </div>
    <div class="card-body">
        <div class="dashboard-metric-grid dashboard-metric-grid-tso">
            @foreach ($tsoSummaryCards as $card)
                <div class="dashboard-metric-card metric-{{ $card['theme'] }}">
                    <span class="dashboard-metric-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                    <div>
                        <span class="dashboard-metric-label">{{ $card['label'] }}</span>
                        <strong class="dashboard-metric-value">{{ $card['value'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
