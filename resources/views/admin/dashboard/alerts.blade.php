@extends('layouts.admin')

@php
    $dashboardLabel = match (true) {
        $selectedDashboard === 'tso' => 'TSO',
        $selectedDashboard === 'iso' && $selectedIsoType === 'laut' => 'ISO Laut',
        $selectedDashboard === 'iso' => 'ISO Darat',
        default => 'DSO',
    };
    $dashboardQuery = array_filter([
        'type' => $selectedDashboard,
        'iso_type' => $selectedDashboard === 'iso' ? $selectedIsoType : null,
        'start_date' => $selectedStartDate,
        'end_date' => $selectedEndDate,
    ], static fn ($value) => $value !== null && $value !== '');
    $periodLabel = match (true) {
        $selectedStartDate !== null && $selectedEndDate !== null => \Carbon\Carbon::parse($selectedStartDate)->format('d/m/Y').' – '.\Carbon\Carbon::parse($selectedEndDate)->format('d/m/Y'),
        $selectedStartDate !== null => 'Mulai '.\Carbon\Carbon::parse($selectedStartDate)->format('d/m/Y'),
        $selectedEndDate !== null => 'Sampai '.\Carbon\Carbon::parse($selectedEndDate)->format('d/m/Y'),
        default => 'Semua Periode',
    };
    $totalAlerts = count($dashboardSlaAlerts['warning']) + count($dashboardSlaAlerts['danger']);
    $warningAlertTotal = count($dashboardSlaAlerts['warning']);
    $dangerAlertTotal = count($dashboardSlaAlerts['danger']);
@endphp

@section('title', 'Semua Alert '.$dashboardLabel.' — Shipment Otomotif')
@section('page-title', 'Semua Alert '.$dashboardLabel)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard', $dashboardQuery) }}">Dashboard {{ $dashboardLabel }}</a></li>
    <li class="breadcrumb-item active">Semua Alert</li>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="h5 mb-1"><i class="fas fa-bell text-warning"></i> Alert SLA {{ $dashboardLabel }}</h3>
            <div class="text-muted">Periode: {{ $periodLabel }} · Total {{ number_format($totalAlerts) }} alert</div>
        </div>
        <a href="{{ route('admin.dashboard', $dashboardQuery) }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

@if ($totalAlerts === 0)
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Tidak ada alert SLA untuk dashboard dan periode ini.
    </div>
@else
    <div class="row dashboard-alert-summary-row">
        <div class="col-md-4">
            <div class="info-box dashboard-alert-summary-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-bell"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Alert</span>
                    <span class="info-box-number">{{ number_format($totalAlerts) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box dashboard-alert-summary-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Mendekati Deadline</span>
                    <span class="info-box-number">{{ number_format($warningAlertTotal) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box dashboard-alert-summary-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Melewati SLA</span>
                    <span class="info-box-number">{{ number_format($dangerAlertTotal) }}</span>
                </div>
            </div>
        </div>
    </div>

    @include('admin.dashboard._sla-alerts', ['showAllDashboardSlaAlerts' => true])
@endif
@endsection

@push('styles')
.dashboard-alert-summary-row {
    margin-bottom: 2px;
}

.dashboard-alert-summary-box {
    min-height: 88px;
}

.dashboard-alert-summary-box .info-box-number {
    font-size: 24px;
}

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('.dashboard-alert-table').each(function () {
        $(this).DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']],
            ordering: false,
            autoWidth: false,
            language: {
                search: 'Cari alert:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ alert',
                infoEmpty: 'Tidak ada alert',
                infoFiltered: '(difilter dari _MAX_ alert)',
                zeroRecords: 'Tidak ada alert yang cocok',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' }
            }
        });
    });
});
</script>
@endpush
