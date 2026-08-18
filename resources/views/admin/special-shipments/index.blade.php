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
        ['data' => 'delay_percentage', 'label' => 'Keterlambatan (%)', 'kind' => 'delay'],
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
                <span class="info-box-text">Shipment Selesai Dievaluasi</span>
                <span class="info-box-number">{{ number_format($delayStats['completed']) }}</span>
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
