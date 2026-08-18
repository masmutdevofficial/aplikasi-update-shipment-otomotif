@extends('layouts.admin')

@php($shipmentSection = $type === 'tso' ? 'tso' : 'iso')

@section('title', 'Upload Excel ' . $config['label'])
@section('page-title', 'Upload Excel ' . $config['label'])
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.special-shipments.index', $type) }}">{{ $config['short_label'] }}</a></li>
    <li class="breadcrumb-item active">Upload Excel</li>
@endsection

@section('content')
@include('admin.shipments._type-selector')

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-excel"></i> Upload {{ $config['short_label'] }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.special-shipments.import', $type) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file" class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Format: .xlsx, .xls, atau .csv. Maksimal 5 MB.</div>
                    </div>

                    <div class="alert alert-info">
                        <div class="alert-content">
                            Semua kolom boleh kosong. Gunakan header sesuai template. Data dengan {{ $config['fields'][$config['identity']]['label'] }} yang sama akan diperbarui.
                            Isi <strong>SLA Customer (Days)</strong> bila data ingin dievaluasi otomatis sebagai OTD atau LATE. Lead time, Max Arrival, Progress, dan keterlambatan dalam hari dihitung oleh sistem.
                            <div class="mt-3">
                                <a href="{{ route('admin.special-shipments.template', $type) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Download Template Excel
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success" id="btnUpload"><i class="fas fa-upload"></i> Upload & Import</button>
                        <a href="{{ route('admin.special-shipments.index', $type) }}" class="btn btn-default">Batal</a>
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
    document.querySelector('form').addEventListener('submit', function () {
        const button = document.getElementById('btnUpload');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengimpor...';
    });
});
</script>
@endpush
