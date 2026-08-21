@php
    $tsoPerformanceCards = [
        'not_departed_pdc' => ['label' => 'Belum Keluar PDC', 'icon' => 'fa-hourglass-half', 'theme' => 'slate'],
        'dtp' => ['label' => 'DTP (Delivery To Port)', 'icon' => 'fa-truck-moving', 'theme' => 'cyan'],
        'ptp' => ['label' => 'PTP (Port To Port)', 'icon' => 'fa-ship', 'theme' => 'orange'],
        'ptd' => ['label' => 'PTD (Port To Door)', 'icon' => 'fa-truck-loading', 'theme' => 'green'],
    ];
@endphp

<div class="card card-outline card-primary dashboard-overview-panel mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck-loading"></i> TSO 2 — DD Performance</h3>
    </div>
    <div class="card-body">
        <div class="dashboard-metric-grid mb-3">
            <div class="dashboard-metric-card dashboard-metric-card-featured metric-blue">
                <span class="dashboard-metric-icon"><i class="fas fa-file-alt"></i></span>
                <div>
                    <span class="dashboard-metric-label">TOTAL SHIPMENT / Terima DO</span>
                    <strong class="dashboard-metric-value">{{ number_format($tsoDoPerformance['total_received']['count']) }}</strong>
                    <span class="dashboard-metric-meta">{{ number_format($tsoDoPerformance['total_received']['percentage'], 2, ',', '.') }}% dari Total Terima DO</span>
                </div>
            </div>
        </div>
        <hr class="my-3">
        <div class="dashboard-metric-grid">
            @foreach ($tsoPerformanceCards as $key => $card)
                <div class="dashboard-metric-card metric-{{ $card['theme'] }}">
                    <span class="dashboard-metric-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                    <div>
                        <span class="dashboard-metric-label">{{ $card['label'] }}</span>
                        <strong class="dashboard-metric-value">{{ number_format($tsoDoPerformance[$key]['count']) }}</strong>
                        <span class="dashboard-metric-meta">{{ number_format($tsoDoPerformance[$key]['percentage'], 2, ',', '.') }}% dari Total Terima DO</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
