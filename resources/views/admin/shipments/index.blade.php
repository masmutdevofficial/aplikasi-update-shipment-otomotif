@extends('layouts.admin')

@section('title', 'Kelola Shipments — Shipment Otomotif')
@section('page-title', 'Data Keberangkatan Unit')
@section('breadcrumb')
    <li class="breadcrumb-item active">Shipments</li>
@endsection

@section('content')
@include('admin.shipments._type-selector', ['shipmentSection' => 'dso'])

<div class="d-flex justify-content-between align-items-center mb-3">
    <span></span>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        <form id="bulk-delete-shipments-form" method="POST" action="{{ route('admin.shipments.bulk-destroy') }}" class="d-none">
            @csrf
            @method('DELETE')
            <span id="selected-shipments-count" class="text-muted mr-2"></span>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Hapus Terpilih
            </button>
        </form>
        <a href="{{ route('admin.shipments.import.form') }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Upload Excel
        </a>
        <a href="{{ route('admin.shipments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Shipment
        </a>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="shipment-table-scroll">
            <table id="table-shipments" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <input type="checkbox" class="select-all-shipments" aria-label="Pilih semua shipment">
                        </th>
                        <th>No</th>
                        <th>Lokasi</th>
                        <th>No. DO</th>
                        <th>Type Kendaraan</th>
                        <th>No. Rangka</th>
                        <th>Warna</th>
                        <th>Asal PDC</th>
                        <th>Tujuan</th>
                        <th>Terima DO</th>
                        <th>Keluar PDC</th>
                        <th>Nama Kapal</th>
                        <th>Keberangkatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const shipmentsTable = $('#table-shipments').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.shipments.data') }}',
        pageLength: 10,
        scrollX: true,
        lengthMenu: [[10, 25, 50, 100], ['10', '25', '50', '100']],
        columns: [
            { data: 'id', name: 'id', orderable: false, searchable: false, render: (id) => `<input type="checkbox" class="shipment-select" value="${id}" aria-label="Pilih shipment">` },
            { data: 'row_number', name: 'row_number', orderable: false, searchable: false },
            { data: 'lokasi', name: 'lokasi' },
            { data: 'no_do', name: 'no_do' },
            { data: 'type_kendaraan', name: 'type_kendaraan' },
            { data: 'no_rangka', name: 'no_rangka', render: (value) => `<code>${value}</code>` },
            { data: 'warna', name: 'warna' },
            { data: 'asal_pdc', name: 'asal_pdc' },
            { data: 'tujuan_pengiriman', name: 'tujuan_pengiriman' },
            { data: 'terima_do', name: 'terima_do' },
            { data: 'keluar_dari_pdc', name: 'keluar_dari_pdc' },
            { data: 'nama_kapal', name: 'nama_kapal' },
            { data: 'keberangkatan_kapal', name: 'keberangkatan_kapal' },
            {
                data: null, name: 'actions', orderable: false, searchable: false,
                render: (data) => `
                    <div class="d-flex gap-1">
                        <a href="${data.edit_url}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="${data.delete_url}" onsubmit="return confirm('Hapus data shipment ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>`
            }
        ],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data shipment',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        order: [[2, 'asc']]
    });

    const tableContainer = $(shipmentsTable.table().container());
    const selectAll = tableContainer.find('.select-all-shipments');
    const bulkDeleteForm = document.getElementById('bulk-delete-shipments-form');
    const selectedCount = document.getElementById('selected-shipments-count');

    function getShipmentCheckboxes() {
        return Array.from($(shipmentsTable.rows().nodes()).find('.shipment-select'));
    }

    function updateBulkDeleteState() {
        const checkboxes = getShipmentCheckboxes();
        const selected = checkboxes.filter((checkbox) => checkbox.checked);

        bulkDeleteForm.classList.toggle('d-none', selected.length === 0);
        selectedCount.textContent = `${selected.length} shipment dipilih`;
        selectAll.prop('checked', checkboxes.length > 0 && selected.length === checkboxes.length);
        selectAll.prop('indeterminate', selected.length > 0 && selected.length < checkboxes.length);
    }

    tableContainer.on('change', '.select-all-shipments', function () {
        getShipmentCheckboxes().forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteState();
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('shipment-select')) {
            updateBulkDeleteState();
        }
    });

    $('#table-shipments').on('draw.dt', updateBulkDeleteState);

    bulkDeleteForm.addEventListener('submit', function (event) {
        const selected = getShipmentCheckboxes().filter((checkbox) => checkbox.checked);

        if (selected.length === 0 || !confirm(`Yakin ingin menghapus ${selected.length} shipment terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
            event.preventDefault();
            return;
        }

        selected.forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'shipment_ids[]';
            input.value = checkbox.value;
            bulkDeleteForm.appendChild(input);
        });
    });
});
</script>
@endpush
