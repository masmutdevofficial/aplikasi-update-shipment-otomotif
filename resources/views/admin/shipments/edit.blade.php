@extends('layouts.admin')

@section('title', 'Edit Shipment — Shipment Otomotif')
@section('page-title', 'Edit Shipment')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Shipment: {{ $shipment->no_rangka }}</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.shipments.update', $shipment) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        {{-- Lokasi --}}
                        <div class="col-md-6">
                            <label for="lokasi" class="form-label fw-semibold">Lokasi <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('lokasi') is-invalid @enderror"
                                   id="lokasi"
                                   name="lokasi"
                                   value="{{ old('lokasi', $shipment->lokasi) }}"
                                   maxlength="50"
                                   required>
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- No. DO --}}
                        <div class="col-md-6">
                            <label for="no_do" class="form-label fw-semibold">No. DO <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('no_do') is-invalid @enderror"
                                   id="no_do"
                                   name="no_do"
                                   value="{{ old('no_do', $shipment->no_do) }}"
                                   maxlength="50"
                                   required>
                            @error('no_do')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Type Kendaraan --}}
                        <div class="col-md-6">
                            <label for="type_kendaraan" class="form-label fw-semibold">Type Kendaraan <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('type_kendaraan') is-invalid @enderror"
                                   id="type_kendaraan"
                                   name="type_kendaraan"
                                   value="{{ old('type_kendaraan', $shipment->type_kendaraan) }}"
                                   maxlength="50"
                                   required>
                            @error('type_kendaraan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- No. Rangka (VIN) --}}
                        <div class="col-md-6">
                            <label for="no_rangka" class="form-label fw-semibold">No. Rangka (VIN) <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('no_rangka') is-invalid @enderror"
                                   id="no_rangka"
                                   name="no_rangka"
                                   value="{{ old('no_rangka', $shipment->no_rangka) }}"
                                   minlength="17"
                                   maxlength="17"
                                   required>
                            @error('no_rangka')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Tepat 17 karakter.</small>
                        </div>

                        {{-- No. Engine --}}
                        <div class="col-md-6">
                            <label for="no_engine" class="form-label fw-semibold">No. Engine <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('no_engine') is-invalid @enderror"
                                   id="no_engine"
                                   name="no_engine"
                                   value="{{ old('no_engine', $shipment->no_engine) }}"
                                   maxlength="50"
                                   required>
                            @error('no_engine')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Warna --}}
                        <div class="col-md-6">
                            <label for="warna" class="form-label fw-semibold">Warna <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('warna') is-invalid @enderror"
                                   id="warna"
                                   name="warna"
                                   value="{{ old('warna', $shipment->warna) }}"
                                   maxlength="50"
                                   required>
                            @error('warna')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Asal PDC --}}
                        <div class="col-md-6">
                            <label for="asal_pdc" class="form-label fw-semibold">Asal PDC <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('asal_pdc') is-invalid @enderror"
                                   id="asal_pdc"
                                   name="asal_pdc"
                                   value="{{ old('asal_pdc', $shipment->asal_pdc) }}"
                                   maxlength="100"
                                   required>
                            @error('asal_pdc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kota --}}
                        <div class="col-md-6">
                            <label for="kota" class="form-label fw-semibold">Kota <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('kota') is-invalid @enderror"
                                   id="kota"
                                   name="kota"
                                   value="{{ old('kota', $shipment->kota) }}"
                                   maxlength="100"
                                   required>
                            @error('kota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tujuan Pengiriman --}}
                        <div class="col-md-6">
                            <label for="tujuan_pengiriman" class="form-label fw-semibold">Tujuan Pengiriman <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('tujuan_pengiriman') is-invalid @enderror"
                                   id="tujuan_pengiriman"
                                   name="tujuan_pengiriman"
                                   value="{{ old('tujuan_pengiriman', $shipment->tujuan_pengiriman) }}"
                                   maxlength="100"
                                   required>
                            @error('tujuan_pengiriman')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Terima DO --}}
                        <div class="col-md-6">
                            <label for="terima_do" class="form-label fw-semibold">Tanggal Terima DO <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('terima_do') is-invalid @enderror"
                                   id="terima_do"
                                   name="terima_do"
                                   value="{{ old('terima_do', $shipment->terima_do?->format('Y-m-d')) }}"
                                   required>
                            @error('terima_do')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keluar dari PDC --}}
                        <div class="col-md-6">
                            <label for="keluar_dari_pdc" class="form-label fw-semibold">Tanggal Keluar dari PDC <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('keluar_dari_pdc') is-invalid @enderror"
                                   id="keluar_dari_pdc"
                                   name="keluar_dari_pdc"
                                   value="{{ old('keluar_dari_pdc', $shipment->keluar_dari_pdc?->format('Y-m-d')) }}"
                                   required>
                            @error('keluar_dari_pdc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nama Kapal --}}
                        <div class="col-md-6">
                            <label for="nama_kapal" class="form-label fw-semibold">Nama Kapal <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('nama_kapal') is-invalid @enderror"
                                   id="nama_kapal"
                                   name="nama_kapal"
                                   value="{{ old('nama_kapal', $shipment->nama_kapal) }}"
                                   maxlength="100"
                                   required>
                            @error('nama_kapal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keberangkatan Kapal --}}
                        <div class="col-md-6">
                            <label for="keberangkatan_kapal" class="form-label fw-semibold">Tanggal Keberangkatan Kapal <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('keberangkatan_kapal') is-invalid @enderror"
                                   id="keberangkatan_kapal"
                                   name="keberangkatan_kapal"
                                   value="{{ old('keberangkatan_kapal', $shipment->keberangkatan_kapal?->format('Y-m-d')) }}"
                                   required>
                            @error('keberangkatan_kapal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Simpan Perubahan
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
