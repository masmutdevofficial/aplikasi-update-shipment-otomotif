@extends('layouts.admin')

@php
    $dashboardOptions = [
        'dso' => 'Dashboard DSO',
        'tso' => 'Dashboard TSO',
        'iso' => 'Dashboard ISO',
    ];
    $selectedDashboard = strtolower(request()->query('type', 'dso'));

    if (! array_key_exists($selectedDashboard, $dashboardOptions)) {
        $selectedDashboard = 'dso';
    }
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
            <div>
                <label for="dashboardType" class="dashboard-selector-label">Pilih Dashboard</label>
                <p class="dashboard-selector-description">Pilih dashboard yang ingin ditampilkan.</p>
            </div>

            <form method="GET" action="{{ route('admin.dashboard') }}">
                <select
                    id="dashboardType"
                    name="type"
                    class="form-select dashboard-selector-input"
                    aria-label="Pilih dashboard"
                    onchange="this.form.submit()"
                >
                    @foreach ($dashboardOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedDashboard === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <noscript>
                    <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
                </noscript>
            </form>
        </div>
    </div>
</div>

{{-- Konten awal dibuat sama untuk DSO, TSO, dan ISO. --}}
<div class="row">
    <div class="col-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-truck"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Shipments</span>
                <span class="info-box-number">{{ \App\Models\Shipment::count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-warehouse"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Vendor</span>
                <span class="info-box-number">{{ \App\Models\Vendor::count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">{{ \App\Models\User::count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-qrcode"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Scan Hari Ini</span>
                <span class="info-box-number">{{ \App\Models\ScanHistory::whereDate('scan_date', today())->count() }}</span>
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
                        @forelse(\App\Models\Shipment::latest()->take(5)->get() as $shipment)
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
                        @forelse(\App\Models\ScanHistory::with('user')->latest('scan_date')->take(5)->get() as $scan)
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
@endsection

@push('styles')
.dashboard-selector-card {
    border-left: 4px solid var(--primary);
}

.dashboard-selector {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.dashboard-selector-label {
    display: block;
    margin-bottom: 3px;
    color: #343a40;
    font-size: 15px;
    font-weight: 600;
}

.dashboard-selector-description {
    margin: 0;
    color: #6c757d;
    font-size: 13px;
}

.dashboard-selector form {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dashboard-selector-input {
    min-width: 220px;
}

@media (max-width: 576px) {
    .dashboard-selector {
        align-items: stretch;
        flex-direction: column;
    }

    .dashboard-selector form,
    .dashboard-selector-input {
        width: 100%;
    }
}
@endpush
