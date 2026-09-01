@extends('layouts.admin')

@php
    $shipmentSection = $type === 'tso' ? 'tso' : 'iso';
    $specialTableColumns = collect($config['fields'])->map(function ($fieldConfig, $field) {
        return [
            'data' => $field,
            'name' => $field,
            'code' => in_array($field, ['no_rangka', 'noka', 'no_spb'], true),
        ];
    })->values();
    $performanceColumns = collect($config['performance']['stages'])->map(function ($stage, $key) {
        return ['data' => $key, 'label' => $stage['label'], 'kind' => 'number'];
    })->values()->concat([
        ['data' => 'sla_actual', 'label' => 'SLA Actual', 'kind' => 'number'],
        ['data' => 'sla_result', 'label' => 'Result', 'kind' => 'result'],
        ['data' => 'delay_days', 'label' => 'Keterlambatan (Hari)', 'kind' => 'delay'],
        ['data' => 'max_arrival', 'label' => 'Max Arrival', 'kind' => 'date'],
        ['data' => 'progress', 'label' => 'Progress', 'kind' => 'text'],
    ])->values();
@endphp

@section('title', $config['label'] . ' — Shipment Otomotif')
@section('page-title', $config['label'])
@section('breadcrumb')
    <li class="breadcrumb-item active">{{ $config['short_label'] }}</li>
@endsection

@section('content')
@include('admin.shipments._type-selector')

<div class="row">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Shipment Dievaluasi</span>
                <span class="info-box-number">{{ number_format($delayStats['evaluated']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Shipment Terlambat</span>
                <span class="info-box-number">{{ number_format($delayStats['late']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Persentase Keterlambatan</span>
                <span class="info-box-number">{{ number_format($delayStats['percentage'], 2) }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 flex-wrap mb-3">
    <form id="bulk-delete-special-form" method="POST" action="{{ route('admin.special-shipments.bulk-destroy', $type) }}" class="d-none">
        @csrf
        @method('DELETE')
        <span id="selected-special-count" class="text-muted me-2"></span>
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus Terpilih</button>
    </form>
    <a href="{{ route('admin.special-shipments.import.form', $type) }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Upload Excel
    </a>
    <a href="{{ route('admin.special-shipments.create', $type) }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Data
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="special-shipment-table-scroll">
            <table id="table-special-shipments" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center"><input type="checkbox" id="selectAllSpecial" aria-label="Pilih semua"></th>
                        @if ($type === 'tso')<th>No</th>@endif
                        @foreach ($config['fields'] as $fieldConfig)
                            <th>{{ $fieldConfig['label'] }}</th>
                        @endforeach
                        @foreach ($performanceColumns as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@if (str_starts_with($type, 'iso-'))
<div class="card card-info">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title"><i class="fas fa-stopwatch"></i> Referensi SLA Customer {{ $config['short_label'] }}</h3>
        <button type="button" class="btn btn-sm btn-light ml-auto" data-toggle="modal" data-target="#addIsoSlaDestinationModal">
            <i class="fas fa-plus"></i> Tambah Destination
        </button>
    </div>
    <form method="POST" action="{{ route('admin.special-shipments.sla-customer.update', $type) }}">
        @csrf
        @method('PUT')
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-striped mb-0 sla-reference-table">
                <thead>
                    <tr>
                        <th class="sla-lock-column"><i class="fas fa-lock" title="Status kunci baris"></i></th>
                        <th>Destination</th>
                        @if ($type === 'iso-laut')
                            <th>Belum Keluar PDC</th>
                            <th>Storage Port</th>
                            <th>Kapal (Loading)</th>
                            <th>ATA Kapal</th>
                            <th>Storage Port (Destination)</th>
                            <th>PtD (Dooring)</th>
                        @else
                            <th>PTD/DTD</th>
                        @endif
                        <th style="min-width:160px;">SLA Customer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slaTargets as $destination => $target)
                        <tr class="sla-reference-row" data-locked="true">
                            <td class="sla-lock-column">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-warning sla-row-lock-toggle"
                                    title="Buka kunci untuk mengubah data"
                                    aria-label="Buka kunci baris {{ $destination }}"
                                    aria-pressed="false"
                                ><i class="fas fa-lock"></i></button>
                            </td>
                            <td><strong>{{ ucwords(strtolower($destination)) }}</strong></td>
                            @if ($type === 'iso-laut')
                                @foreach ($target['stages'] as $stage => $days)
                                    <td>
                                        <input
                                            type="number"
                                            name="sla_stages[{{ $destination }}][{{ $stage }}]"
                                            value="{{ old("sla_stages.{$destination}.{$stage}", $days) }}"
                                            class="form-control form-control-sm sla-reference-input @error("sla_stages.{$destination}.{$stage}") is-invalid @enderror"
                                            min="0"
                                            max="365"
                                            required
                                            readonly
                                            aria-label="Tahapan SLA {{ $destination }}"
                                            style="min-width:75px;"
                                        >
                                        @error("sla_stages.{$destination}.{$stage}")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                @endforeach
                            @else
                                <td>
                                    <input
                                        type="number"
                                        name="sla_stages[{{ $destination }}][ptd_dooring]"
                                        value="{{ old("sla_stages.{$destination}.ptd_dooring", $target['stages']['ptd_dooring']) }}"
                                        class="form-control form-control-sm sla-reference-input @error("sla_stages.{$destination}.ptd_dooring") is-invalid @enderror"
                                        min="0"
                                        max="365"
                                        required
                                        readonly
                                        aria-label="PTD/DTD {{ $destination }}"
                                        style="min-width:75px;"
                                    >
                                    @error("sla_stages.{$destination}.ptd_dooring")
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </td>
                            @endif
                            <td>
                                <div class="input-group input-group-sm">
                                    <input
                                        type="number"
                                        name="sla_customer[{{ $destination }}]"
                                        value="{{ old("sla_customer.{$destination}", $target['customer']) }}"
                                        class="form-control sla-reference-input @error("sla_customer.{$destination}") is-invalid @enderror"
                                        min="1"
                                        max="365"
                                        required
                                        readonly
                                        aria-label="SLA Customer {{ $destination }}"
                                    >
                                    <div class="input-group-append"><span class="input-group-text">hari</span></div>
                                </div>
                                @error("sla_customer.{$destination}")
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">Perubahan tahapan dan SLA Customer berlaku untuk tabel shipment, dashboard, laporan, dan perhitungan keterlambatan {{ $config['short_label'] }}.</small>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Referensi SLA
            </button>
        </div>
    </form>
</div>

@include('admin.partials._add-sla-destination-modal', [
    'slaAddModalId' => 'addIsoSlaDestinationModal',
    'slaAddTitle' => 'Tambah Destination SLA '.$config['short_label'],
    'slaAddAction' => route('admin.special-shipments.sla-destination.store', $type),
])
@include('admin.partials._sla-reference-lock-controls')
@endif
@endsection

@push('styles')
.special-shipment-table-scroll { width: 100%; overflow-x: auto; }
#table-special-shipments th, #table-special-shipments td { min-width: 120px; white-space: nowrap; }
#table-special-shipments th:first-child, #table-special-shipments td:first-child { min-width: 45px; width: 45px; }
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fieldColumns = @json($specialTableColumns);
    const performanceColumns = @json($performanceColumns);
    const columns = [
        { data: 'id', name: 'id', orderable: false, searchable: false, render: (id) => `<input type="checkbox" class="special-select" value="${id}" aria-label="Pilih data">` },
        @if ($type === 'tso')
        { data: 'row_number', name: 'row_number', orderable: false, searchable: false },
        @endif
        ...fieldColumns.map((column) => ({
            data: column.data,
            name: column.name,
            render: column.code ? ((value) => `<code>${value}</code>`) : undefined
        })),
        ...performanceColumns.map((column) => ({
            data: column.data,
            name: column.data,
            orderable: false,
            searchable: false,
            render: column.kind === 'result'
                ? ((value) => `<span class="badge ${value === 'OTD' ? 'badge-success' : (value === 'LATE' ? 'badge-danger' : 'badge-secondary')}">${value}</span>`)
                : (column.kind === 'delay'
                    ? ((value) => `<span class="${parseFloat(value) > 0 ? 'text-danger font-weight-bold' : ''}">${value}</span>`)
                    : undefined)
        })),
        {
            data: null, name: 'actions', orderable: false, searchable: false,
            render: (data) => `
                <div class="d-flex gap-1">
                    <a href="${data.edit_url}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="${data.delete_url}" onsubmit="return confirm('Hapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                </div>`
        }
    ];

    const dataTable = $('#table-special-shipments').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.special-shipments.data', $type) }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        },
        pageLength: 10,
        scrollX: true,
        lengthMenu: [[10, 25, 50, 100], ['10', '25', '50', '100']],
        columns: columns,
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data {{ $config['short_label'] }}', zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        order: [[{{ $type === 'tso' ? 2 : 1 }}, 'asc']]
    });

    const selectAll = document.getElementById('selectAllSpecial');
    const bulkForm = document.getElementById('bulk-delete-special-form');
    const selectedCount = document.getElementById('selected-special-count');
    const checkboxes = () => Array.from($(dataTable.rows().nodes()).find('.special-select'));

    function updateSelection() {
        const selected = checkboxes().filter((checkbox) => checkbox.checked);
        bulkForm.classList.toggle('d-none', selected.length === 0);
        selectedCount.textContent = `${selected.length} data dipilih`;
        selectAll.checked = selected.length > 0 && selected.length === checkboxes().length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes().length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes().forEach((checkbox) => checkbox.checked = this.checked);
        updateSelection();
    });
    document.addEventListener('change', (event) => {
        if (event.target.classList.contains('special-select')) updateSelection();
    });
    $('#table-special-shipments').on('draw.dt', updateSelection);
    bulkForm.addEventListener('submit', function (event) {
        const selected = checkboxes().filter((checkbox) => checkbox.checked);
        if (!selected.length || !confirm(`Hapus ${selected.length} data terpilih?`)) {
            event.preventDefault();
            return;
        }
        selected.forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'shipment_ids[]'; input.value = checkbox.value;
            bulkForm.appendChild(input);
        });
    });
});
</script>
@endpush
