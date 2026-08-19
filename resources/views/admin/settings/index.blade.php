@extends('layouts.admin')

@section('title', 'Settings — Shipment Otomotif')
@section('page-title', 'Settings')
@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield"></i> Akses Web Vendor</h3>
            </div>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <p class="text-muted">
                        Atur apakah pengguna level vendor dapat login dan menggunakan aplikasi. Admin dan superadmin tetap dapat mengakses aplikasi pada kedua mode.
                    </p>

                    <div class="vendor-mode-options">
                        <label class="vendor-mode-option {{ $vendorAccessMode === \App\Support\VendorAccess::MODE_ACTIVE ? 'is-selected is-active' : '' }}">
                            <input
                                type="radio"
                                name="vendor_access_mode"
                                value="{{ \App\Support\VendorAccess::MODE_ACTIVE }}"
                                @checked(old('vendor_access_mode', $vendorAccessMode) === \App\Support\VendorAccess::MODE_ACTIVE)
                            >
                            <span class="vendor-mode-icon bg-success"><i class="fas fa-check-circle"></i></span>
                            <span>
                                <strong>Aktif</strong>
                                <small>Vendor dapat login dan menggunakan seluruh menu vendor.</small>
                            </span>
                        </label>

                        <label class="vendor-mode-option {{ $vendorAccessMode === \App\Support\VendorAccess::MODE_MAINTENANCE ? 'is-selected is-maintenance' : '' }}">
                            <input
                                type="radio"
                                name="vendor_access_mode"
                                value="{{ \App\Support\VendorAccess::MODE_MAINTENANCE }}"
                                @checked(old('vendor_access_mode', $vendorAccessMode) === \App\Support\VendorAccess::MODE_MAINTENANCE)
                            >
                            <span class="vendor-mode-icon bg-warning"><i class="fas fa-tools"></i></span>
                            <span>
                                <strong>Maintenance</strong>
                                <small>Login vendor ditolak dan vendor yang sudah login akan otomatis logout.</small>
                            </span>
                        </label>
                    </div>

                    @if ($vendorAccessMode === \App\Support\VendorAccess::MODE_MAINTENANCE)
                        <div class="alert alert-warning mt-3 mb-0">
                            <div class="alert-content">
                                <strong>Portal vendor sedang Maintenance.</strong><br>
                                Vendor tidak dapat login atau mengakses aplikasi hingga mode diubah kembali menjadi Aktif.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
.vendor-mode-options {
    display: grid;
    gap: 12px;
    margin-top: 18px;
}
.vendor-mode-option {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    transition: border-color .15s, background-color .15s;
}
.vendor-mode-option:has(input:checked) {
    border-color: #007bff;
    background: #f0f7ff;
}
.vendor-mode-option input { margin: 0; }
.vendor-mode-option strong { display: block; font-size: 16px; }
.vendor-mode-option small { display: block; margin-top: 2px; color: #6c757d; }
.vendor-mode-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    color: #fff;
    flex-shrink: 0;
}
@endpush
