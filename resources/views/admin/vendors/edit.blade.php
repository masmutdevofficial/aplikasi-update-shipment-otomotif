@extends('layouts.admin')

@section('title', 'Edit Vendor — Shipment Otomotif')
@section('page-title')
Edit Vendor: {{ $vendor->vendor_name }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendor</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Vendor: {{ $vendor->vendor_name }}</h3>
            </div>
            <div class="card-body">
                {{-- Read-only user info --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">User Account</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text"
                               class="form-control"
                               value="{{ $vendor->user->name ?? '-' }} ({{ $vendor->user->email ?? '-' }})"
                               disabled>
                    </div>
                    <small class="text-muted">User account tidak dapat diubah.</small>
                </div>

                <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="vendor_name" class="form-label fw-semibold">Nama Vendor <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                            <input type="text"
                                   class="form-control @error('vendor_name') is-invalid @enderror"
                                   id="vendor_name"
                                   name="vendor_name"
                                   value="{{ old('vendor_name', $vendor->vendor_name) }}"
                                   required>
                        </div>
                        @error('vendor_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="position" class="form-label fw-semibold">Posisi <span class="text-danger">*</span></label>
                        <select class="form-select @error('position') is-invalid @enderror"
                                id="position"
                                name="position"
                                required>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ old('position', $vendor->position) === $pos ? 'selected' : '' }}>
                                    {{ $pos }}
                                </option>
                            @endforeach
                        </select>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Simpan Perubahan
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
