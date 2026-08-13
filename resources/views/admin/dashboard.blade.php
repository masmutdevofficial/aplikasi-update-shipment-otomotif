@extends('layouts.admin')

@php
    $dashboardOptions = [
        'dso' => 'Dashboard DSO',
        'tso' => 'Dashboard TSO',
        'iso' => 'Dashboard ISO',
    ];
    $selectedDashboard = strtolower(request()->query('type', 'dso'));
    $selectedIsoType = strtolower(request()->query('iso_type', 'darat'));

    if (! array_key_exists($selectedDashboard, $dashboardOptions)) {
        $selectedDashboard = 'dso';
    }

    if (! in_array($selectedIsoType, ['darat', 'laut'], true)) {
        $selectedIsoType = 'darat';
    }

    $tsoShipments = $selectedDashboard === 'tso'
        ? \App\Models\TsoShipment::query()->oldest()->get()
        : collect();
    $isoDaratShipments = $selectedDashboard === 'iso' && $selectedIsoType === 'darat'
        ? \App\Models\IsoDaratShipment::query()->orderBy('source_no')->get()
        : collect();
    $isoLautShipments = $selectedDashboard === 'iso' && $selectedIsoType === 'laut'
        ? \App\Models\IsoLautShipment::query()->orderBy('source_no')->get()
        : collect();
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

@if ($selectedDashboard === 'tso')
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
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tsoShipments as $shipment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $shipment->unit_type ?? '-' }}</td>
                            <td>{{ $shipment->origin ?? '-' }}</td>
                            <td>{{ $shipment->destination ?? '-' }}</td>
                            <td>{{ $shipment->detail_destination ?? '-' }}</td>
                            <td><code>{{ $shipment->no_rangka ?? '-' }}</code></td>
                            <td>
                                @if ($shipment->doc)
                                    <a href="{{ asset('storage/' . $shipment->doc) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-alt"></i> Lihat
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $shipment->do_date?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->pu_date?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->door_to_port?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->port_to_port?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->port_to_door?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->vessel_ptp ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@elseif ($selectedDashboard === 'iso')
<div class="iso-mode-selector" role="group" aria-label="Pilih jenis shipment ISO">
    <a
        href="{{ route('admin.dashboard', ['type' => 'iso', 'iso_type' => 'darat']) }}"
        class="btn {{ $selectedIsoType === 'darat' ? 'btn-primary' : 'btn-default' }}"
    >
        <i class="fas fa-truck"></i> ISO Darat
    </a>
    <a
        href="{{ route('admin.dashboard', ['type' => 'iso', 'iso_type' => 'laut']) }}"
        class="btn {{ $selectedIsoType === 'laut' ? 'btn-primary' : 'btn-default' }}"
    >
        <i class="fas fa-ship"></i> ISO Laut
    </a>
</div>

@if ($selectedIsoType === 'darat')
    @include('admin.dashboard.iso-darat-table')
@else
    @include('admin.dashboard.iso-laut-table')
@endif
@else
{{-- Dashboard DSO tetap menggunakan tampilan awal. --}}
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
@endif
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

.tso-table-scroll,
.dashboard-table-scroll {
    overflow-x: auto;
    width: 100%;
}

.dashboard-data-table th,
.dashboard-data-table td,
#table-tso-shipments th,
#table-tso-shipments td {
    min-width: 120px;
    white-space: nowrap;
}

.dashboard-data-table th:first-child,
.dashboard-data-table td:first-child,
#table-tso-shipments th:first-child,
#table-tso-shipments td:first-child {
    min-width: 55px;
    text-align: center;
}

.iso-mode-selector {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
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

@if (in_array($selectedDashboard, ['tso', 'iso'], true))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableSelector = @json(
        $selectedDashboard === 'tso'
            ? '#table-tso-shipments'
            : ($selectedIsoType === 'darat' ? '#table-iso-darat' : '#table-iso-laut')
    );
    const dateTargets = @json(
        $selectedDashboard === 'tso'
            ? [7, 8, 9, 10, 11]
            : ($selectedIsoType === 'darat' ? [10, 11, 12] : [16, 17, 19, 20, 21, 22])
    );

    $(tableSelector).DataTable({
        pageLength: 10,
        scrollX: true,
        lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data shipment',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        columnDefs: [
            { orderable: false, targets: [0] },
            { type: 'date', targets: dateTargets }
        ],
        order: [[0, 'asc']]
    });
});
</script>
@endpush
@endif
