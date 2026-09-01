@php
    $showAllDashboardSlaAlerts = $showAllDashboardSlaAlerts ?? false;
    $dashboardAlertLimit = $showAllDashboardSlaAlerts ? null : 10;
    $dashboardAlertDetailQuery = array_filter([
        'type' => $selectedDashboard,
        'iso_type' => $selectedDashboard === 'iso' ? $selectedIsoType : null,
        'day' => $selectedDay,
        'month' => $selectedMonth,
        'year' => $selectedYear,
    ], static fn ($value) => $value !== null && $value !== '');
@endphp

@if ($dashboardSlaAlerts['warning'] !== [])
    <div class="card card-warning" id="warning-alerts">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Deadline Shipment Mendekat</h3>
            <span class="badge badge-light dashboard-alert-count">{{ number_format(count($dashboardSlaAlerts['warning'])) }} data</span>
        </div>
        <div class="card-body {{ $showAllDashboardSlaAlerts ? 'p-0' : 'dashboard-alert-body' }}">
            @if ($showAllDashboardSlaAlerts)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 dashboard-alert-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:70px;">No</th>
                                <th>Detail Alert</th>
                                <th style="width:190px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dashboardSlaAlerts['warning'] as $message)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="dashboard-alert-message">{{ $message }}</td>
                                    <td><span class="badge badge-warning dashboard-alert-status"><i class="fas fa-clock"></i> Mendekati Deadline</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
            <div class="dashboard-alert-list">
                @foreach (array_slice($dashboardSlaAlerts['warning'], 0, $dashboardAlertLimit) as $message)
                    <div class="dashboard-alert-item dashboard-alert-item-warning">
                        <span class="dashboard-alert-number">{{ $loop->iteration }}</span>
                        <span class="dashboard-alert-item-message">{{ $message }}</span>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
        @if (! $showAllDashboardSlaAlerts && count($dashboardSlaAlerts['warning']) > 10)
            <div class="card-footer text-right">
                <a href="{{ route('admin.dashboard.alerts', $dashboardAlertDetailQuery) }}#warning-alerts" class="btn btn-warning btn-sm">
                    <i class="fas fa-list"></i> Lihat Semua ({{ number_format(count($dashboardSlaAlerts['warning'])) }})
                </a>
            </div>
        @endif
    </div>
@endif

@if ($showAllDashboardSlaAlerts && $dashboardSlaAlerts['danger'] !== [])
    <div class="card card-danger" id="danger-alerts">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-times-circle"></i> Shipment Melewati SLA</h3>
            <span class="badge badge-light dashboard-alert-count">{{ number_format(count($dashboardSlaAlerts['danger'])) }} data</span>
        </div>
        <div class="card-body {{ $showAllDashboardSlaAlerts ? 'p-0' : 'dashboard-alert-body' }}">
            @if ($showAllDashboardSlaAlerts)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 dashboard-alert-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:70px;">No</th>
                                <th>Detail Alert</th>
                                <th style="width:190px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dashboardSlaAlerts['danger'] as $message)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="dashboard-alert-message">{{ $message }}</td>
                                    <td><span class="badge badge-danger dashboard-alert-status"><i class="fas fa-exclamation-circle"></i> Melewati SLA</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
            <div class="dashboard-alert-list">
                @foreach (array_slice($dashboardSlaAlerts['danger'], 0, $dashboardAlertLimit) as $message)
                    <div class="dashboard-alert-item dashboard-alert-item-danger">
                        <span class="dashboard-alert-number">{{ $loop->iteration }}</span>
                        <span class="dashboard-alert-item-message">{{ $message }}</span>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
        @if (! $showAllDashboardSlaAlerts && count($dashboardSlaAlerts['danger']) > 10)
            <div class="card-footer text-right">
                <a href="{{ route('admin.dashboard.alerts', $dashboardAlertDetailQuery) }}#danger-alerts" class="btn btn-danger btn-sm">
                    <i class="fas fa-list"></i> Lihat Semua ({{ number_format(count($dashboardSlaAlerts['danger'])) }})
                </a>
            </div>
        @endif
    </div>
@endif

@once
    @push('styles')
.dashboard-alert-count {
    padding: 5px 9px;
    color: #343a40;
    font-size: 11px;
    font-weight: 700;
}

.dashboard-alert-body {
    padding: 12px;
}

.dashboard-alert-list {
    display: grid;
    gap: 7px;
}

.dashboard-alert-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    line-height: 1.45;
    border: 1px solid transparent;
    border-radius: 7px;
}

.dashboard-alert-item-warning {
    border-color: #f8df8a;
    background: #fffaf0;
}

.dashboard-alert-item-danger {
    border-color: #f2b8bd;
    background: #fff5f5;
}

.dashboard-alert-number {
    display: inline-flex;
    flex: 0 0 24px;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    color: #495057;
    font-size: 11px;
    font-weight: 700;
    border-radius: 50%;
    background: rgba(255, 255, 255, .8);
    box-shadow: 0 1px 3px rgba(15, 23, 42, .12);
}

.dashboard-alert-item-message,
.dashboard-alert-message {
    overflow-wrap: anywhere;
}

.dashboard-alert-table thead th {
    color: #495057;
    font-size: 12px;
    white-space: nowrap;
    background: #f8fafc;
}

.dashboard-alert-table td {
    vertical-align: middle;
}

.dashboard-alert-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 9px;
    white-space: nowrap;
}
    @endpush
@endonce
