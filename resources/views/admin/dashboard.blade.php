@extends('layouts.admin')

@php
    $dashboardOptions = [
        'dso' => 'Dashboard DSO',
        'tso' => 'Dashboard TSO',
        'iso' => 'Dashboard ISO',
    ];
    $selectedDashboard = $selectedDashboard ?? strtolower(request()->query('type', 'dso'));
    $selectedIsoType = $selectedIsoType ?? strtolower(request()->query('iso_type', 'darat'));
    $selectedMonth = $selectedMonth ?? null;
    $selectedYear = $selectedYear ?? null;
    $availableYears = $availableYears ?? [(int) now()->year];
    $monthOptions = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    if (! array_key_exists($selectedDashboard, $dashboardOptions)) {
        $selectedDashboard = 'dso';
    }

    if (! in_array($selectedIsoType, ['darat', 'laut'], true)) {
        $selectedIsoType = 'darat';
    }

    $dashboardPerformanceType = match (true) {
        $selectedDashboard === 'tso' => 'tso',
        $selectedDashboard === 'iso' && $selectedIsoType === 'laut' => 'iso-laut',
        $selectedDashboard === 'iso' => 'iso-darat',
        default => null,
    };
    $dashboardSpecialConfig = $dashboardPerformanceType
        ? \App\Support\SpecialShipmentType::get($dashboardPerformanceType)
        : null;
    $dashboardServerColumns = collect();

    if ($dashboardSpecialConfig) {
        if ($dashboardPerformanceType === 'tso') {
            $dashboardServerColumns->push(['data' => 'row_number', 'kind' => 'number', 'orderable' => false]);
        }

        foreach ($dashboardSpecialConfig['fields'] as $field => $fieldConfig) {
            $dashboardServerColumns->push([
                'data' => $field,
                'kind' => in_array($field, ['no_rangka', 'noka', 'no_spb'], true) ? 'code' : 'text',
                'orderable' => true,
            ]);
        }

        foreach ($dashboardSpecialConfig['performance']['stages'] as $key => $stage) {
            $dashboardServerColumns->push(['data' => $key, 'kind' => 'number', 'orderable' => false]);
        }

        $dashboardServerColumns = $dashboardServerColumns->concat([
            ['data' => 'sla_actual', 'kind' => 'number', 'orderable' => false],
            ['data' => 'sla_result', 'kind' => 'result', 'orderable' => false],
            ['data' => 'delay_days', 'kind' => 'delay', 'orderable' => false],
            ['data' => 'max_arrival', 'kind' => 'text', 'orderable' => false],
            ['data' => 'progress', 'kind' => 'text', 'orderable' => false],
        ])->values();
    }
    $dashboardTableSelector = match (true) {
        $selectedDashboard === 'tso' => '#table-tso-shipments',
        $selectedIsoType === 'laut' => '#table-iso-laut',
        default => '#table-iso-darat',
    };
@endphp

@section('title', $dashboardOptions[$selectedDashboard] . ' — Shipment Otomotif')
@section('page-title', $dashboardOptions[$selectedDashboard])
@section('breadcrumb')
    <li class="breadcrumb-item active">{{ $dashboardOptions[$selectedDashboard] }}</li>
@endsection

@section('content')
<div class="card dashboard-selector-card">
    <div class="card-body">
        <div class="dashboard-selector">
            <div class="dashboard-selector-header">
                <span class="dashboard-selector-label">Pilih Dashboard</span>
                <p class="dashboard-selector-description">Pilih dashboard yang ingin ditampilkan.</p>
            </div>

            <nav class="dashboard-tabs" aria-label="Pilihan dashboard">
                <a
                    href="{{ route('admin.dashboard', array_filter(['type' => 'dso', 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                    class="dashboard-tab {{ $selectedDashboard === 'dso' ? 'active' : '' }}"
                    @if ($selectedDashboard === 'dso') aria-current="page" @endif
                ><i class="fas fa-truck"></i> DSO</a>
                <a
                    href="{{ route('admin.dashboard', array_filter(['type' => 'tso', 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                    class="dashboard-tab {{ $selectedDashboard === 'tso' ? 'active' : '' }}"
                    @if ($selectedDashboard === 'tso') aria-current="page" @endif
                ><i class="fas fa-truck-loading"></i> TSO</a>
                <a
                    href="{{ route('admin.dashboard', array_filter(['type' => 'iso', 'iso_type' => 'darat', 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                    class="dashboard-tab {{ $selectedDashboard === 'iso' && $selectedIsoType === 'darat' ? 'active' : '' }}"
                    @if ($selectedDashboard === 'iso' && $selectedIsoType === 'darat') aria-current="page" @endif
                ><i class="fas fa-road"></i> ISO Darat</a>
                <a
                    href="{{ route('admin.dashboard', array_filter(['type' => 'iso', 'iso_type' => 'laut', 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                    class="dashboard-tab {{ $selectedDashboard === 'iso' && $selectedIsoType === 'laut' ? 'active' : '' }}"
                    @if ($selectedDashboard === 'iso' && $selectedIsoType === 'laut') aria-current="page" @endif
                ><i class="fas fa-ship"></i> ISO Laut</a>
            </nav>

            <div class="dashboard-period-row">
                <span class="dashboard-period-label"><i class="fas fa-calendar-alt"></i> Filter Periode</span>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="dashboard-filter-form">
                    <input type="hidden" name="type" value="{{ $selectedDashboard }}">
                    @if ($selectedDashboard === 'iso')
                        <input type="hidden" name="iso_type" value="{{ $selectedIsoType }}">
                    @endif
                    <div class="dashboard-filter-field">
                        <label for="dashboardMonth">Bulan</label>
                        <select id="dashboardMonth" name="month" class="form-select dashboard-period-input">
                            <option value="">Semua Bulan</option>
                            @foreach ($monthOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedMonth === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dashboard-filter-field">
                        <label for="dashboardYear">Tahun</label>
                        <select id="dashboardYear" name="year" class="form-select dashboard-period-input">
                            <option value="">Semua Tahun</option>
                            @foreach ($availableYears as $value)
                                <option value="{{ $value }}" @selected($selectedYear === $value)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan</button>
                    <a
                        href="{{ route('admin.dashboard', array_filter(['type' => $selectedDashboard, 'iso_type' => $selectedDashboard === 'iso' ? $selectedIsoType : null])) }}"
                        class="btn btn-default"
                    >Reset</a>
                </form>
            </div>
        </div>
    </div>
</div>

@if ($selectedDashboard === 'tso')
@include('admin.dashboard.tso-overview')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck-loading"></i> Data Shipment TSO</h3>
    </div>
    <div class="card-body p-0">
        <div class="tso-table-scroll">
            <table id="table-tso-shipments" class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Unit Type</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Detail Destination</th>
                        <th>No Rangka</th>
                        <th>Doc</th>
                        <th>DO Date</th>
                        <th>PU Date</th>
                        <th>Door to Port</th>
                        <th>Port to Port</th>
                        <th>Port to Door</th>
                        <th>Vessel PTP</th>
                        <th>SLA Customer</th>
                        <th>DO to Pickup</th>
                        <th>Door to Port</th>
                        <th>Port to Port</th>
                        <th>Port to Door</th>
                        <th>SLA Actual</th>
                        <th>Result</th>
                        <th title="Maksimal 0 atau SLA Actual - SLA Customer">Keterlambatan (Hari)</th>
                        <th>Max Arrival</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@include('admin.dashboard.tso-position-and-latest')
@elseif ($selectedDashboard === 'iso')
@include('admin.dashboard.iso-overview')
@if ($selectedIsoType === 'darat')
    @include('admin.dashboard.iso-darat-table')
    @include('admin.dashboard.iso-position-summary')
    @include('admin.dashboard.iso-latest')
@else
    @include('admin.dashboard.iso-laut-table')
    @include('admin.dashboard.iso-laut-details')
@endif
@else
{{-- Ringkasan operasional dan performance shipment DSO. --}}
@php
    $dsoSummaryCards = [
        ['label' => 'Total Shipments', 'value' => number_format($dashboardShipmentTotal), 'icon' => 'fa-truck', 'theme' => 'blue'],
        ['label' => 'Total Vendor', 'value' => number_format($dashboardVendorTotal), 'icon' => 'fa-warehouse', 'theme' => 'green'],
        ['label' => 'Total Users', 'value' => number_format($dashboardUserTotal), 'icon' => 'fa-users', 'theme' => 'cyan'],
        ['label' => 'Scan Sesuai Periode', 'value' => number_format($dashboardScanTotal), 'icon' => 'fa-qrcode', 'theme' => 'orange'],
        ['label' => 'Shipment Dievaluasi', 'value' => number_format($delayStats['evaluated']), 'icon' => 'fa-check-circle', 'theme' => 'teal'],
        ['label' => 'Shipment Terlambat', 'value' => number_format($delayStats['late']), 'icon' => 'fa-clock', 'theme' => 'red'],
        ['label' => 'Persentase Keterlambatan', 'value' => number_format($delayStats['percentage'], 2, ',', '.') . '%', 'icon' => 'fa-percent', 'theme' => 'purple'],
        ['label' => 'OTD Performance', 'value' => number_format($delayStats['otd_percentage'], 2, ',', '.') . '%', 'icon' => 'fa-check-double', 'theme' => 'green'],
    ];
    $dsoDoPerformanceCards = [
        'total_received' => ['label' => 'Total Terima DO', 'icon' => 'fa-file-alt', 'theme' => 'blue'],
        'not_departed_pdc' => ['label' => 'Belum Keluar PDC', 'icon' => 'fa-hourglass-half', 'theme' => 'slate'],
        'departed_pdc' => ['label' => 'Keluar Dari PDC', 'icon' => 'fa-truck-moving', 'theme' => 'cyan'],
        'storage_port' => ['label' => 'AT Storage Port', 'icon' => 'fa-warehouse', 'theme' => 'orange'],
        'vessel_loading' => ['label' => 'ATD Kapal (Loading)', 'icon' => 'fa-ship', 'theme' => 'indigo'],
        'vessel_arrived' => ['label' => 'ATA Kapal', 'icon' => 'fa-anchor', 'theme' => 'teal'],
        'destination_storage' => ['label' => 'ATA Storage Port (Destination)', 'icon' => 'fa-warehouse', 'theme' => 'purple'],
        'ptd_dooring' => ['label' => 'AT PtD (Dooring)', 'icon' => 'fa-flag-checkered', 'theme' => 'green'],
    ];
@endphp
<div class="row align-items-stretch dashboard-overview-row">
    <div class="col-12 mb-3">
        <div class="card card-outline card-primary h-100 dashboard-overview-panel">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Ringkasan DSO</h3>
            </div>
            <div class="card-body">
                <div class="dashboard-metric-grid dashboard-metric-grid-summary">
                    @foreach ($dsoSummaryCards as $card)
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
    </div>
    <div class="col-12 mb-3">
        <div class="card card-outline card-info h-100 dashboard-overview-panel">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> DSO 2 — DO Performance</h3>
            </div>
            <div class="card-body">
                <div class="dashboard-metric-grid dashboard-metric-grid-performance">
                    @foreach ($dsoDoPerformanceCards as $key => $card)
                        <div class="dashboard-metric-card metric-{{ $card['theme'] }}">
                            <span class="dashboard-metric-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                            <div>
                                <span class="dashboard-metric-label">{{ $card['label'] }}</span>
                                <strong class="dashboard-metric-value">{{ number_format($dsoDoPerformance[$key]['count']) }}</strong>
                                <span class="dashboard-metric-meta">
                                    {{ number_format($dsoDoPerformance[$key]['percentage'], 2, ',', '.') }}% dari Total Terima DO
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.dashboard.dso-performance-table')

@php
    // Dataset statis sementara untuk visualisasi dwelling pada presentasi.
    $dsoDummyDwellingDetails = [
        'origin' => [
            ['no_rangka' => 'MHKE8FB3JTK260001', 'days' => 1],
            ['no_rangka' => 'MHKAB1BYJTK260002', 'days' => 1],
            ['no_rangka' => 'MHFAB8EMJTK260003', 'days' => 2],
            ['no_rangka' => 'MHFGB8GSJTK260004', 'days' => 1],
            ['no_rangka' => 'MHKGAGFBJTK260005', 'days' => 1],
            ['no_rangka' => 'MHKGAGFBJTK260006', 'days' => 1],
            ['no_rangka' => 'MHKA4GB5JTK260007', 'days' => 2],
            ['no_rangka' => 'MROKB8CDJTK260008', 'days' => 3],
            ['no_rangka' => 'MHKAB1BYJTK260009', 'days' => 1],
            ['no_rangka' => 'MHFAB8EMJTK260010', 'days' => 2],
        ],
        'destination' => [
            ['no_rangka' => 'MHKE8FB3JTK260001', 'days' => 3],
            ['no_rangka' => 'MHKAB1BYJTK260002', 'days' => 4],
            ['no_rangka' => 'MHFAB8EMJTK260003', 'days' => 3],
            ['no_rangka' => 'MHFGB8GSJTK260004', 'days' => 3],
            ['no_rangka' => 'MHKGAGFBJTK260005', 'days' => 4],
            ['no_rangka' => 'MHKGAGFBJTK260006', 'days' => 3],
            ['no_rangka' => 'MHKA4GB5JTK260007', 'days' => 4],
            ['no_rangka' => 'MROKB8CDJTK260008', 'days' => 5],
            ['no_rangka' => 'MHKAB1BYJTK260009', 'days' => 3],
            ['no_rangka' => 'MHFAB8EMJTK260010', 'days' => 4],
        ],
    ];
@endphp

<div class="row">
    <div class="col-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-anchor"></i> Dwelling Origin
                    <span class="badge badge-warning ml-2">Data Demo</span>
                </h3>
            </div>
            <div class="card-body p-0 table-responsive dashboard-dwelling-table">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Rangka</th>
                            <th>Dwelling Origin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dsoDummyDwellingDetails['origin'] as $row)
                            <tr>
                                <td><code>{{ $row['no_rangka'] }}</code></td>
                                <td>{{ number_format($row['days']) }} hari</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-flag-checkered"></i> Dwelling Destination
                    <span class="badge badge-warning ml-2">Data Demo</span>
                </h3>
            </div>
            <div class="card-body p-0 table-responsive dashboard-dwelling-table">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Rangka</th>
                            <th>Dwelling Destination</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dsoDummyDwellingDetails['destination'] as $row)
                            <tr>
                                <td><code>{{ $row['no_rangka'] }}</code></td>
                                <td>{{ number_format($row['days']) }} hari</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-truck"></i> Shipment Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No. Rangka</th>
                            <th>Tujuan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestShipments as $shipment)
                        <tr>
                            <td><code>{{ $shipment->no_rangka }}</code></td>
                            <td>{{ $shipment->tujuan_pengiriman }}</td>
                            <td>{{ $shipment->keberangkatan_kapal?->format('d-M-y') ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted" style="padding:24px;">Belum ada data shipment.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Scan Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>VIN</th>
                            <th>User</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestScans as $scan)
                        <tr>
                            <td><code>{{ $scan->no_rangka }}</code></td>
                            <td>{{ $scan->user->name ?? '-' }}</td>
                            <td>{{ $scan->scan_date->format('d-M-y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted" style="padding:24px;">Belum ada data scan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
.dashboard-selector-card {
    border-left: 4px solid var(--primary);
}

.dashboard-selector {
    display: block;
}

.dashboard-selector-label {
    display: block;
    margin-bottom: 3px;
    color: #343a40;
    font-size: 15px;
    font-weight: 600;
}

.dashboard-selector-description {
    margin: 0 0 16px;
    color: #6c757d;
    font-size: 13px;
}

.dashboard-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    padding-bottom: 12px;
    overflow-x: auto;
    border-bottom: 1px solid #e2e8f0;
}

.dashboard-tab {
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

.dashboard-tab:hover {
    color: #1d4ed8;
    text-decoration: none;
    border-color: #93c5fd;
    background: #eff6ff;
    transform: translateY(-1px);
}

.dashboard-tab.active {
    color: #fff;
    border-color: #2563eb;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 5px 12px rgba(37, 99, 235, .24);
}

.dashboard-period-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    padding-top: 14px;
    overflow-x: auto;
}

.dashboard-period-label {
    flex: 0 0 auto;
    padding-bottom: 9px;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
}

.dashboard-filter-form {
    display: flex;
    flex: 0 0 auto;
    align-items: flex-end;
    gap: 8px;
    min-width: max-content;
}

.dashboard-filter-field label {
    display: block;
    margin-bottom: 4px;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.dashboard-period-input {
    min-width: 145px;
}

.tso-table-scroll,
.dashboard-table-scroll {
    overflow-x: auto;
    width: 100%;
}

.dashboard-data-table th,
.dashboard-data-table td,
.dashboard-position-table th,
.dashboard-position-table td,
#table-tso-shipments th,
#table-tso-shipments td {
    min-width: 120px;
    white-space: nowrap;
}

.shipment-group-header th {
    background: #343a40 !important;
    color: #fff;
    text-align: center;
    vertical-align: middle;
}

.dashboard-data-table th:first-child,
.dashboard-data-table td:first-child,
#table-tso-shipments th:first-child,
#table-tso-shipments td:first-child {
    min-width: 55px;
    text-align: center;
}

.dashboard-overview-panel {
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
}

.dashboard-overview-panel .card-header {
    padding: 15px 18px;
    background: #fff;
}

.dashboard-overview-panel .card-body {
    padding: 16px;
    background: #f8fafc;
}

.dashboard-metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.dashboard-metric-grid-tso {
    grid-template-columns: repeat(5, minmax(0, 1fr));
}

.dashboard-metric-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 104px;
    padding: 14px;
    overflow: hidden;
    color: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 14px rgba(15, 23, 42, .14);
    transition: transform .2s ease, box-shadow .2s ease;
}

.dashboard-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, .2);
}

.dashboard-metric-card::after {
    position: absolute;
    right: -22px;
    bottom: -32px;
    width: 96px;
    height: 96px;
    content: '';
    border-radius: 50%;
    background: rgba(255, 255, 255, .12);
}

.dashboard-metric-icon {
    position: relative;
    z-index: 1;
    display: inline-flex;
    flex: 0 0 42px;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    font-size: 18px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .2);
}

.dashboard-metric-card > div {
    position: relative;
    z-index: 1;
    min-width: 0;
}

.dashboard-metric-label,
.dashboard-metric-value,
.dashboard-metric-meta {
    display: block;
}

.dashboard-metric-label {
    margin-bottom: 3px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.25;
    opacity: .9;
}

.dashboard-metric-value {
    font-size: 25px;
    line-height: 1.1;
}

.dashboard-metric-meta {
    margin-top: 4px;
    font-size: 11px;
    line-height: 1.25;
    opacity: .82;
}

.metric-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
.metric-green { background: linear-gradient(135deg, #16a34a, #15803d); }
.metric-cyan { background: linear-gradient(135deg, #0891b2, #0e7490); }
.metric-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
.metric-teal { background: linear-gradient(135deg, #0d9488, #0f766e); }
.metric-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
.metric-purple { background: linear-gradient(135deg, #9333ea, #7e22ce); }
.metric-indigo { background: linear-gradient(135deg, #4f46e5, #4338ca); }
.metric-slate { background: linear-gradient(135deg, #64748b, #475569); }

.dashboard-dwelling-table {
    max-height: 360px;
    overflow-y: auto;
}

.dashboard-dwelling-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fff;
}

@media (max-width: 576px) {
    .dashboard-period-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 6px;
    }

    .dashboard-filter-form {
        width: max-content;
    }

    .dashboard-metric-grid {
        grid-template-columns: 1fr;
    }

}

@media (min-width: 577px) and (max-width: 991px) {
    .dashboard-metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@endpush

@if (in_array($selectedDashboard, ['tso', 'iso'], true))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableSelector = @json($dashboardTableSelector);
    const serverColumns = @json($dashboardServerColumns);
    const renderDelayDays = (value) => {
        if (value === null || value === undefined || value === '-') {
            return '<span class="badge badge-secondary">-</span>';
        }

        const days = Number.parseInt(value, 10);
        const badgeClass = days > 0 ? 'badge-danger' : 'badge-success';

        return `<span class="badge ${badgeClass}" title="SLA Actual - SLA Customer">${value}</span>`;
    };

    $(tableSelector).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: @json(route('admin.special-shipments.data', $dashboardPerformanceType)),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            data: function (payload) {
                payload.month = @json($selectedMonth);
                payload.year = @json($selectedYear);
            }
        },
        pageLength: 10,
        scrollX: true,
        lengthMenu: [[10, 25, 50, 100], ['10', '25', '50', '100']],
        columns: serverColumns.map((column) => ({
            data: column.data,
            name: column.data,
            orderable: column.orderable,
            searchable: column.orderable,
            render: column.kind === 'code'
                ? ((value) => `<code>${value}</code>`)
                : (column.kind === 'result'
                    ? ((value) => `<span class="badge ${value === 'OTD' ? 'badge-success' : (value === 'LATE' ? 'badge-danger' : 'badge-secondary')}">${value}</span>`)
                    : (column.kind === 'delay'
                        ? renderDelayDays
                        : undefined))
        })),
        language: {
            processing: 'Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data shipment',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        order: [[{{ $dashboardPerformanceType === 'tso' ? 1 : 0 }}, 'asc']]
    });
});
</script>
@endpush
@endif
