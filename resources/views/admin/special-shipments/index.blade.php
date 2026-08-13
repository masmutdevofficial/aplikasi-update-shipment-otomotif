@extends('layouts.admin')

@php($shipmentSection = $type === 'tso' ? 'tso' : 'iso')

@section('title', $config['label'] . ' — Shipment Otomotif')
@section('page-title', $config['label'])
@section('breadcrumb')
    <li class="breadcrumb-item active">{{ $config['short_label'] }}</li>
@endsection

@section('content')
@include('admin.shipments._type-selector')

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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($shipments as $shipment)
                        <tr>
                            <td class="text-center"><input type="checkbox" class="special-select" value="{{ $shipment->id }}" aria-label="Pilih data"></td>
                            @if ($type === 'tso')<td>{{ $loop->iteration }}</td>@endif
                            @foreach ($config['fields'] as $field => $fieldConfig)
                                <td>
                                    @if ($fieldConfig['type'] === 'date')
                                        {{ $shipment->{$field}?->format('d-M-y') ?? '-' }}
                                    @elseif (in_array($field, ['no_rangka', 'noka', 'no_spb'], true))
                                        <code>{{ $shipment->{$field} ?? '-' }}</code>
                                    @else
                                        {{ $shipment->{$field} ?? '-' }}
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.special-shipments.edit', [$type, $shipment->id]) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.special-shipments.destroy', [$type, $shipment->id]) }}" onsubmit="return confirm('Hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
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
    const dataTable = $('#table-special-shipments').DataTable({
        pageLength: 10,
        scrollX: true,
        lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']],
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data {{ $config['short_label'] }}', zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        columnDefs: [{ orderable: false, targets: [0, -1] }],
        order: [[1, 'asc']]
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
