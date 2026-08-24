@extends('layouts.admin')

@php
    $editing = isset($shipment);
    $shipmentSection = $type === 'tso' ? 'tso' : 'iso';
@endphp

@section('title', ($editing ? 'Edit ' : 'Tambah ') . $config['label'])
@section('page-title', ($editing ? 'Edit ' : 'Tambah ') . $config['label'])
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.special-shipments.index', $type) }}">{{ $config['short_label'] }}</a></li>
    <li class="breadcrumb-item active">{{ $editing ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
@include('admin.shipments._type-selector')

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas {{ $editing ? 'fa-edit' : 'fa-plus-circle' }}"></i> {{ $editing ? 'Edit' : 'Tambah' }} Data {{ $config['short_label'] }}</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <div class="alert-content">
                <strong><i class="fas fa-sync-alt"></i> Terhubung ke Dashboard {{ $config['short_label'] }}</strong><br>
                Data input manual yang disimpan di halaman ini otomatis tampil pada card Data Shipment {{ $config['short_label'] }}. Kolom lead time, SLA Actual, result, keterlambatan, max arrival, dan progress dihitung oleh sistem.
            </div>
        </div>
        <form method="POST" action="{{ $editing ? route('admin.special-shipments.update', [$type, $shipment->id]) : route('admin.special-shipments.store', $type) }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="row g-3">
                @foreach ($config['fields'] as $field => $fieldConfig)
                    @continue(($fieldConfig['edit_only'] ?? false) && ! $editing)
                    @php
                        $currentValue = old($field, $editing ? $shipment->{$field} : null);
                        $inputType = $fieldConfig['input_type']
                            ?? ($fieldConfig['type'] === 'integer' ? 'number' : $fieldConfig['type']);
                        $storedValueWarning = null;

                        if ($inputType === 'date' && $currentValue instanceof \DateTimeInterface) {
                            $currentValue = $currentValue->format('Y-m-d');
                        } elseif ($inputType === 'date' && filled($currentValue)) {
                            try {
                                $dateText = trim((string) $currentValue);
                                $periodField = $config['performance']['start'];
                                $periodDate = $editing ? $shipment->{$periodField} : null;

                                if ($periodDate instanceof \DateTimeInterface
                                    && preg_match('/^\d{1,2}[-\/]\p{L}{3}$/u', $dateText)) {
                                    $dateText .= '-' . $periodDate->format('Y');
                                }

                                $currentValue = \Carbon\Carbon::parse($dateText)->format('Y-m-d');
                            } catch (\Throwable) {
                                $storedValueWarning = (string) $currentValue;
                                $currentValue = null;
                            }
                        }
                    @endphp
                    <div class="col-md-6">
                        <label for="{{ $field }}" class="form-label">{{ $fieldConfig['label'] }}</label>
                        <input
                            type="{{ $inputType }}"
                            class="form-control @error($field) is-invalid @enderror"
                            id="{{ $field }}"
                            name="{{ $field }}"
                            value="{{ $currentValue }}"
                            @if(isset($fieldConfig['max'])) maxlength="{{ $fieldConfig['max'] }}" @endif
                            @if(isset($fieldConfig['min'])) min="{{ $fieldConfig['min'] }}" @endif
                        >
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($storedValueWarning !== null)
                            <small class="form-text text-warning">
                                Nilai lama “{{ $storedValueWarning }}” bukan tanggal valid. Pilih tanggal yang benar lalu simpan.
                            </small>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
                <a href="{{ route('admin.special-shipments.index', $type) }}" class="btn btn-default"><i class="fas fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
