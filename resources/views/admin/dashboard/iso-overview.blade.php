@php
    $isoLabel = $selectedIsoType === 'laut' ? 'ISO Laut' : 'ISO Darat';
    $isoSummaryCards = [
        ['label' => 'Total Shipments', 'value' => number_format($dashboardShipmentTotal), 'icon' => $selectedIsoType === 'laut' ? 'fa-ship' : 'fa-truck', 'theme' => 'blue'],
    ];

    $isoSummaryCards[] = ['label' => 'Shipment Terlambat', 'value' => number_format($specialDelayStats['late']), 'icon' => 'fa-clock', 'theme' => 'red'];
    $isoSummaryCards[] = ['label' => 'Persentase Keterlambatan', 'value' => number_format($specialDelayStats['percentage'], 2, ',', '.') . '%', 'icon' => 'fa-percent', 'theme' => 'purple'];
    $isoSummaryCards[] = ['label' => 'OTD (On Time Delivery)', 'value' => number_format($specialDelayStats['otd_percentage'], 2, ',', '.') . '%', 'icon' => 'fa-check-double', 'theme' => 'green'];

    $isoLautPerformanceCards = [
        'total_received' => ['label' => 'Total Terima DO', 'icon' => 'fa-file-alt', 'theme' => 'blue'],
        'not_departed_pdc' => ['label' => 'Belum Keluar PDC', 'icon' => 'fa-hourglass-half', 'theme' => 'slate', 'alert_stage' => 'not_departed_pdc'],
        'departed_pdc' => ['label' => 'Keluar Dari PDC', 'icon' => 'fa-truck-moving', 'theme' => 'cyan'],
        'storage_port' => ['label' => 'AT Storage Port', 'icon' => 'fa-warehouse', 'theme' => 'orange', 'alert_stage' => 'storage_port'],
        'vessel_loading' => ['label' => 'ATD Kapal (Loading)', 'icon' => 'fa-ship', 'theme' => 'indigo', 'alert_stage' => 'vessel_loading'],
        'vessel_arrived' => ['label' => 'ATA Kapal', 'icon' => 'fa-anchor', 'theme' => 'teal'],
        'destination_storage' => ['label' => 'ATA Storage Port (Destination)', 'icon' => 'fa-warehouse', 'theme' => 'purple', 'alert_stage' => 'destination_storage'],
        'ptd_dtd' => ['label' => 'AT PTD/DTD', 'icon' => 'fa-flag-checkered', 'theme' => 'green'],
    ];
    $isoDaratMilestoneCards = [
        'departed_pdc' => ['label' => 'Keluar PDC', 'icon' => 'fa-truck-moving', 'theme' => 'cyan'],
        'ptd_dtd' => ['label' => 'AT PTD/DTD', 'icon' => 'fa-flag-checkered', 'theme' => 'green'],
    ];
@endphp

<div class="card card-outline card-primary dashboard-overview-panel mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Ringkasan {{ $isoLabel }}</h3>
    </div>
    <div class="card-body">
        <div class="dashboard-metric-grid">
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
                    @php
                        $alertStage = $card['alert_stage'] ?? null;
                        $stageAlerts = $alertStage ? ($dashboardSlaAlerts['stages'][$alertStage] ?? ['warning' => [], 'danger' => []]) : ['warning' => [], 'danger' => []];
                        $stageAlertTotal = count($stageAlerts['warning']) + count($stageAlerts['danger']);
                    @endphp
                    <div
                        class="dashboard-metric-card metric-{{ $card['theme'] }} {{ $stageAlertTotal > 0 ? 'dashboard-alert-trigger' : '' }}"
                        @if ($stageAlertTotal > 0)
                            role="button"
                            tabindex="0"
                            data-alert-stage="{{ $alertStage }}"
                            data-alert-label="{{ $card['label'] }}"
                            title="Klik untuk melihat {{ number_format($stageAlertTotal) }} alert"
                        @endif
                    >
                        <span class="dashboard-metric-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                        <div>
                            <span class="dashboard-metric-label">{{ $card['label'] }}</span>
                            <strong class="dashboard-metric-value">{{ number_format($isoDoPerformance[$key]['count']) }}</strong>
                            <span class="dashboard-metric-meta">{{ number_format($isoDoPerformance[$key]['percentage'], 2, ',', '.') }}% dari Total Terima DO</span>
                            @if ($stageAlertTotal > 0)
                                <span class="dashboard-metric-alert-badge"><i class="fas fa-bell"></i> {{ number_format($stageAlertTotal) }} alert</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="card card-outline card-info dashboard-overview-panel mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> ISO Darat — Delivery Performance</h3>
        </div>
        <div class="card-body">
            <div class="dashboard-metric-grid dashboard-metric-grid-compact">
                @foreach ($isoDaratMilestoneCards as $key => $card)
                    @php
                        $stageAlerts = $dashboardSlaAlerts['stages'][$key] ?? ['warning' => [], 'danger' => []];
                        $stageAlertTotal = count($stageAlerts['warning']) + count($stageAlerts['danger']);
                    @endphp
                    <div
                        class="dashboard-metric-card metric-{{ $card['theme'] }} {{ $stageAlertTotal > 0 ? 'dashboard-alert-trigger' : '' }}"
                        @if ($stageAlertTotal > 0)
                            role="button"
                            tabindex="0"
                            data-alert-stage="{{ $key }}"
                            data-alert-label="{{ $card['label'] }}"
                            title="Klik untuk melihat {{ number_format($stageAlertTotal) }} alert"
                        @endif
                    >
                        <span class="dashboard-metric-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                        <div>
                            <span class="dashboard-metric-label">{{ $card['label'] }}</span>
                            <strong class="dashboard-metric-value">{{ number_format($isoDaratMilestones[$key]['count']) }}</strong>
                            <span class="dashboard-metric-meta">{{ number_format($isoDaratMilestones[$key]['percentage'], 2, ',', '.') }}% dari Total Terima DO</span>
                            @if ($stageAlertTotal > 0)
                                <span class="dashboard-metric-alert-badge"><i class="fas fa-bell"></i> {{ number_format($stageAlertTotal) }} alert</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
