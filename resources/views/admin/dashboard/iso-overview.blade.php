@php
    $isoLabel = $selectedIsoType === 'laut' ? 'ISO Laut' : 'ISO Darat';
    $isoSummaryCards = [
        ['label' => 'Total Shipments', 'value' => number_format($dashboardShipmentTotal), 'icon' => $selectedIsoType === 'laut' ? 'fa-ship' : 'fa-truck', 'theme' => 'blue'],
        ['label' => 'Total Vendor', 'value' => number_format($dashboardVendorTotal), 'icon' => 'fa-warehouse', 'theme' => 'green'],
        ['label' => 'Total Users', 'value' => number_format($dashboardUserTotal), 'icon' => 'fa-users', 'theme' => 'cyan'],
        ['label' => 'Scan Sesuai Periode', 'value' => number_format($dashboardScanTotal), 'icon' => 'fa-qrcode', 'theme' => 'orange'],
        ['label' => 'Shipment Dievaluasi', 'value' => number_format($specialDelayStats['evaluated']), 'icon' => 'fa-check-circle', 'theme' => 'teal'],
    ];

    if ($selectedIsoType === 'laut') {
        $isoSummaryCards[] = ['label' => 'Shipment Terlambat', 'value' => number_format($specialDelayStats['late']), 'icon' => 'fa-clock', 'theme' => 'red'];
        $isoSummaryCards[] = ['label' => 'Persentase Keterlambatan', 'value' => number_format($specialDelayStats['percentage'], 2, ',', '.') . '%', 'icon' => 'fa-percent', 'theme' => 'purple'];
    }

    $isoLautPerformanceCards = [
        'total_received' => ['label' => 'Total Terima DO', 'icon' => 'fa-file-alt', 'theme' => 'blue'],
        'not_departed_pdc' => ['label' => 'Belum Keluar PDC', 'icon' => 'fa-hourglass-half', 'theme' => 'slate'],
        'departed_pdc' => ['label' => 'Keluar Dari PDC', 'icon' => 'fa-truck-moving', 'theme' => 'cyan'],
        'storage_port' => ['label' => 'AT Storage Port', 'icon' => 'fa-warehouse', 'theme' => 'orange'],
        'vessel_loading' => ['label' => 'ATD Kapal (Loading)', 'icon' => 'fa-ship', 'theme' => 'indigo'],
        'vessel_arrived' => ['label' => 'ATA Kapal', 'icon' => 'fa-anchor', 'theme' => 'teal'],
        'destination_storage' => ['label' => 'ATA Storage Port (Destination)', 'icon' => 'fa-warehouse', 'theme' => 'purple'],
        'ptd_dtd' => ['label' => 'AT PTD/DTD', 'icon' => 'fa-flag-checkered', 'theme' => 'green'],
    ];
@endphp

<div class="card card-outline card-primary dashboard-overview-panel mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Ringkasan {{ $isoLabel }}</h3>
    </div>
    <div class="card-body">
        <div class="dashboard-metric-grid {{ $selectedIsoType === 'darat' ? 'dashboard-metric-grid-tso' : '' }}">
            @foreach ($isoSummaryCards as $card)
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

@if ($selectedIsoType === 'laut')
    <div class="card card-outline card-info dashboard-overview-panel mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> ISO Laut — DO Performance</h3>
        </div>
        <div class="card-body">
            <div class="dashboard-metric-grid">
                @foreach ($isoLautPerformanceCards as $key => $card)
                    <div class="dashboard-metric-card metric-{{ $card['theme'] }}">
                        <span class="dashboard-metric-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                        <div>
                            <span class="dashboard-metric-label">{{ $card['label'] }}</span>
                            <strong class="dashboard-metric-value">{{ number_format($isoDoPerformance[$key]['count']) }}</strong>
                            <span class="dashboard-metric-meta">{{ number_format($isoDoPerformance[$key]['percentage'], 2, ',', '.') }}% dari Total Terima DO</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
