@extends('layouts.admin')

@section('title', 'Tambah Vendor — Shipment Otomotif')
@section('page-title', 'Tambah Vendor Baru')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendor</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-warehouse"></i> Form Tambah Vendor</h3>
            </div>
            <div class="card-body">
                @if($availableUsers->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Tidak ada user vendor yang tersedia. Pastikan sudah membuat user dengan level <strong>vendor</strong>
                        yang belum terhubung ke data vendor manapun.
                        <a href="{{ route('admin.users.create') }}" class="alert-link">Tambah user baru</a>.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.vendors.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-semibold">User Account <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror"
                                id="user_id"
                                name="user_id"
                                required
                                onchange="autoFillName()">
                            <option value="">— Pilih User Vendor —</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        {{ old('user_id') === $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="vendor_name" class="form-label fw-semibold">Nama Vendor <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                            <input type="text"
                                   class="form-control @error('vendor_name') is-invalid @enderror"
                                   id="vendor_name"
                                   name="vendor_name"
                                   value="{{ old('vendor_name') }}"
                                   placeholder="Nama vendor"
                                   required>
                        </div>
                        @error('vendor_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Otomatis terisi dari nama user, bisa diubah.</small>
                    </div>

                    <div class="mb-3">
                        <label for="position" class="form-label fw-semibold">Posisi <span class="text-danger">*</span></label>
                        <select class="form-select @error('position') is-invalid @enderror"
                                id="position"
                                name="position"
                                required>
                            <option value="">— Pilih Posisi —</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ old('position') === $pos ? 'selected' : '' }}>
                                    {{ $pos }}
                                </option>
                            @endforeach
                        </select>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" {{ $availableUsers->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-check"></i> Simpan
                        </button>
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-default">
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
    function autoFillName() {
        const select = document.getElementById('user_id');
        const nameInput = document.getElementById('vendor_name');
        const selected = select.options[select.selectedIndex];

        if (selected && selected.dataset.name) {
            nameInput.value = selected.dataset.name;
        }
    }

    // Auto-fill on page load if value is pre-selected
    if (document.getElementById('user_id').value) {
        autoFillName();
    }
</script>
@endpush
