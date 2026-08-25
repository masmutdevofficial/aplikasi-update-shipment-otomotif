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
    <div class="col-12 col-lg-9">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Wajib Gunakan Master Template DSO</h3>
            </div>
            <div class="card-body">
                <p class="mb-2">Sistem hanya menerima file <strong>.xlsx</strong> dengan susunan header dari master template resmi.</p>
                <ol class="pl-3 mb-3">
                    <li>Download master template melalui tombol di bawah.</li>
                    <li>Ganti atau hapus <strong>baris contoh data</strong>, lalu isi data mulai baris berikutnya.</li>
                    <li>Jangan mengubah nama, urutan, menambah, atau menghapus header kolom.</li>
                    <li>Isi tanggal dengan format <code>YYYY-MM-DD</code> dan simpan tetap sebagai <strong>.xlsx</strong>.</li>
                    <li>Upload file yang sudah diisi melalui form pada halaman ini.</li>
                </ol>
                <a href="{{ route('admin.shipments.template') }}" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i> Download Master Template DSO
                </a>
            </div>
        </div>

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
                               accept=".xlsx"
                               required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Wajib menggunakan <strong>Master Template DSO (.xlsx)</strong> &bull; Ukuran maksimal: <strong>5 MB</strong></div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Pastikan baris contoh sudah <strong>diganti atau dihapus</strong>. File dengan header yang berbeda dari master template akan ditolak.
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
