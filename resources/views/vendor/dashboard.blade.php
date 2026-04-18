@extends('layouts.vendor')

@section('title', 'Dashboard Vendor — Shipment Otomotif')
@section('page-title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

@if(!$vendor || !$vendor->position)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center" style="padding:40px 20px;">
                    <i class="fas fa-info-circle" style="font-size:2.5rem;color:var(--warning);"></i>
                    <h5 class="mt-3 fw-semibold">Posisi Belum Ditetapkan</h5>
                    <p class="text-muted">
                        Fitur scan VIN dan riwayat akan tersedia setelah posisi vendor ditetapkan oleh administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row mb-4">
        <div class="col-4">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-map-marker-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Posisi Anda</span>
                    <span class="info-box-number" style="font-size:15px;">{{ $vendor->position }}</span>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Scan Hari Ini</span>
                    <span class="info-box-number">{{ $todayScans }}</span>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-history"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Scan</span>
                    <span class="info-box-number">{{ $totalScans }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-body text-center" style="padding:40px 20px;">
                    <i class="fas fa-qrcode" style="font-size:2.5rem;color:var(--primary);"></i>
                    <h5 class="mt-3 fw-semibold">Scan VIN Kendaraan</h5>
                    <p class="text-muted">Gunakan kamera untuk scan nomor rangka kendaraan.</p>
                    <a href="{{ route('vendor.scanner') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-camera"></i> Mulai Scan
                    </a>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-history"></i> Scan Terakhir</h6>
                    @if($recentScans->isEmpty())
                        <p class="text-muted text-center py-3">Belum ada riwayat scan.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentScans as $scan)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <code>{{ $scan->no_rangka }}</code>
                                        <small class="text-muted">{{ $scan->scan_date->format('d-M-y') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('vendor.history') }}" class="btn btn-outline-secondary btn-sm mt-2 w-100">
                            Lihat Semua Riwayat
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
