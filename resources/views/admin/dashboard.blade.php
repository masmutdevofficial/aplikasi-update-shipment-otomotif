@extends('layouts.admin')

@section('title', 'Dashboard — Shipment Otomotif')
@section('page-title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
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
                            <td>{{ $shipment->keberangkatan_kapal->format('d-M-y') }}</td>
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
