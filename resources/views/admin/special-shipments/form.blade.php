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
        <form method="POST" action="{{ $editing ? route('admin.special-shipments.update', [$type, $shipment->id]) : route('admin.special-shipments.store', $type) }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="row g-3">
                @foreach ($config['fields'] as $field => $fieldConfig)
                    @php
                        $currentValue = old($field, $editing ? $shipment->{$field} : null);
                        if ($fieldConfig['type'] === 'date' && $currentValue instanceof \DateTimeInterface) {
                            $currentValue = $currentValue->format('Y-m-d');
                        }
                    @endphp
                    <div class="col-md-6">
                        <label for="{{ $field }}" class="form-label">{{ $fieldConfig['label'] }}</label>
                        <input
                            type="{{ $fieldConfig['type'] === 'integer' ? 'number' : $fieldConfig['type'] }}"
                            class="form-control @error($field) is-invalid @enderror"
                            id="{{ $field }}"
                            name="{{ $field }}"
                            value="{{ $currentValue }}"
                            @if(isset($fieldConfig['max'])) maxlength="{{ $fieldConfig['max'] }}" @endif
                        >
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
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
