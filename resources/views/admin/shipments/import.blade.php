@extends('layouts.admin')

@section('title', 'Upload Excel Shipment — Shipment Otomotif')
@section('page-title', 'Upload Data Shipment via Excel')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></li>
    <li class="breadcrumb-item active">Upload Excel</li>
@endsection

@section('content')
@include('admin.shipments._type-selector', ['shipmentSection' => 'dso'])

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-excel"></i> Form Upload Excel</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.shipments.import') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="file" class="form-label fw-semibold">
                            Pilih File Excel <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               class="form-control @error('file') is-invalid @enderror"
                               id="file"
                               name="file"
                               accept=".xlsx,.xls,.csv"
                               required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format yang didukung: <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong> &bull; Ukuran maksimal: <strong>5 MB</strong></div>
                    </div>

                    {{-- Info Box --}}
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Ketentuan Upload</h6>
                        <ul class="mb-2 ps-3">
                            <li>File harus memiliki <strong>header kolom</strong> sesuai format template atau manifest kapal.</li>
                            <li>Kolom <strong>No. Rangka (VIN)</strong> harus tepat 17 karakter alfanumerik.</li>
                            <li><strong>No. DO</strong>, tanggal kapal, dan seluruh kolom <strong>Actual Time</strong> boleh kosong jika belum tersedia.</li>
                            <li>Kolom Actual Time yang didukung: <strong>AT Storage Port</strong>, <strong>ATD Kapal (Loading)</strong>, <strong>ATA Kapal</strong>, <strong>ATA Storage Port (Destination)</strong>, dan <strong>AT PtD (Dooring)</strong>.</li>
                            <li>Lead time, SLA Actual, SLA Customer, Result, Max Arrival, Progress, dan persentase keterlambatan dihitung otomatis oleh sistem.</li>
                            <li>Data yang No. Rangka-nya sudah terdaftar akan <strong>diperbarui otomatis</strong> jika file berisi data DO, tanggal, kapal, atau ATD terbaru.</li>
                            <li>File manifest kapal dengan kop/header di tengah file akan dideteksi otomatis selama memiliki kolom <strong>No. Rangka</strong>.</li>
                            <li>Format tanggal: <code>YYYY-MM-DD</code> atau <code>DD/MM/YYYY</code>.</li>
                        </ul>
                        <a href="{{ route('admin.shipments.template') }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-download"></i> Download Format_Upload.xlsx
                        </a>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success" id="btnUpload">
                            <i class="fas fa-upload"></i> Upload & Import
                        </button>
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const btn  = document.getElementById('btnUpload');
    form.addEventListener('submit', function () {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengimpor...';
    });
});
</script>
@endpush
