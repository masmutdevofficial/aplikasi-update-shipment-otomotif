@extends('layouts.admin')

@section('title', 'Kelola Shipments — Shipment Otomotif')
@section('page-title', 'Data Keberangkatan Unit')
@section('breadcrumb')
    <li class="breadcrumb-item active">Shipments</li>
@endsection

@section('content')
@include('admin.shipments._type-selector', ['shipmentSection' => 'dso'])

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
                    <tr class="shipment-group-header" data-dt-order="disable">
                        <th colspan="15"></th>
                        <th colspan="5">Actual Time (Input)</th>
                        <th colspan="6">Actual Lead Time (Days)</th>
                        <th colspan="6">SLA &amp; Performance</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <input type="checkbox" class="select-all-shipments" aria-label="Pilih semua shipment">
                        </th>
                        <th>No</th>
                        <th>Lokasi</th>
                        <th>No. DO</th>
                        <th>Type Kendaraan</th>
                        <th>No. Rangka</th>
                        <th>No. Engine</th>
                        <th>Warna</th>
                        <th>Asal PDC</th>
                        <th>Kota</th>
                        <th>Tujuan Pengiriman</th>
                        <th>Terima DO</th>
                        <th>Keluar dari PDC</th>
                        <th>Nama Kapal</th>
                        <th>Keberangkatan Kapal</th>
                        <th>AT Storage Port</th>
                        <th>ATD Kapal (Loading)</th>
                        <th>ATA Kapal</th>
                        <th>ATA Storage Port (Destination)</th>
                        <th>AT PtD (Dooring)</th>
                        <th>DO Release to Pickup</th>
                        <th>Storage Port</th>
                        <th title="Dihitung dari tanggal masing-masing shipment">Dwelling Origin (per Shipment)</th>
                        <th>Kapal (Aboard)</th>
                        <th>Storage Port (Destination)</th>
                        <th title="Dihitung dari tanggal masing-masing shipment">Dwelling Destination (per Shipment)</th>
                        <th>SLA Actual</th>
                        <th>SLA Cust</th>
                        <th>Result</th>
                        <th>Keterlambatan (Hari)</th>
                        <th>Max Arrival</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card card-info">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title"><i class="fas fa-stopwatch"></i> Referensi SLA Customer DSO</h3>
        <button type="button" class="btn btn-sm btn-light ml-auto" data-toggle="modal" data-target="#addDsoSlaDestinationModal">
            <i class="fas fa-plus"></i> Tambah Destination
        </button>
    </div>
    <form method="POST" action="{{ route('admin.shipments.sla-customer.update') }}">
        @csrf
        @method('PUT')
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-striped mb-0 sla-reference-table">
            <thead>
                <tr>
                    <th class="sla-lock-column"><i class="fas fa-lock" title="Status kunci baris"></i></th>
                    <th>Destination</th>
                    <th>Belum Keluar PDC</th>
                    <th>Storage Port</th>
                    <th>Kapal (Loading)</th>
                    <th>ATA Kapal</th>
                    <th>Storage Port (Destination)</th>
                    <th>PtD (Dooring)</th>
                    <th>SLA Customer</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($slaDestinations as $destination => $target)
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
                        <td><strong>{{ ucfirst(strtolower($destination)) }}</strong></td>
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
                        <td>
                            <div class="input-group input-group-sm" style="min-width:145px;">
                                <input
                                    type="number"
                                    name="sla_customer[{{ $destination }}]"
                                    value="{{ old("sla_customer.{$destination}", $target['total']) }}"
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
            <small class="text-muted">Perubahan tahapan dan SLA Customer berlaku untuk tabel shipment, dashboard, laporan, result OTD/LATE, dan perhitungan keterlambatan DSO.</small>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Referensi SLA
            </button>
        </div>
    </form>
</div>

@include('admin.partials._add-sla-destination-modal', [
    'slaAddModalId' => 'addDsoSlaDestinationModal',
    'slaAddTitle' => 'Tambah Destination SLA DSO',
    'slaAddAction' => route('admin.shipments.sla-destination.store'),
])
@include('admin.partials._sla-reference-lock-controls')
@endsection

@push('styles')
.shipment-group-header th {
    background: #343a40 !important;
    color: #fff;
    text-align: center;
    vertical-align: middle;
}

#table-shipments th,
#table-shipments td {
    white-space: nowrap;
}
@endpush

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
            { data: 'no_engine', name: 'no_engine' },
            { data: 'warna', name: 'warna' },
            { data: 'asal_pdc', name: 'asal_pdc' },
            { data: 'kota', name: 'kota' },
            { data: 'tujuan_pengiriman', name: 'tujuan_pengiriman' },
            { data: 'terima_do', name: 'terima_do' },
            { data: 'keluar_dari_pdc', name: 'keluar_dari_pdc' },
            { data: 'nama_kapal', name: 'nama_kapal' },
            { data: 'keberangkatan_kapal', name: 'keberangkatan_kapal' },
            { data: 'at_storage_port', name: 'at_storage_port' },
            { data: 'atd_kapal_loading', name: 'atd_kapal_loading' },
            { data: 'ata_kapal', name: 'ata_kapal' },
            { data: 'ata_storage_port_destination', name: 'ata_storage_port_destination' },
            { data: 'at_ptd_dooring', name: 'at_ptd_dooring' },
            { data: 'lead_time_do_release_pickup', name: 'lead_time_do_release_pickup', orderable: false, searchable: false },
            { data: 'lead_time_storage_port', name: 'lead_time_storage_port', orderable: false, searchable: false },
            { data: 'dwelling_origin', name: 'dwelling_origin', orderable: false, searchable: false },
            { data: 'lead_time_kapal_aboard', name: 'lead_time_kapal_aboard', orderable: false, searchable: false },
            { data: 'lead_time_storage_destination', name: 'lead_time_storage_destination', orderable: false, searchable: false },
            { data: 'dwelling_destination', name: 'dwelling_destination', orderable: false, searchable: false },
            { data: 'sla_actual', name: 'sla_actual', orderable: false, searchable: false },
            { data: 'sla_customer', name: 'sla_customer', orderable: false, searchable: false },
            {
                data: 'sla_result', name: 'sla_result', orderable: false, searchable: false,
                render: (value) => `<span class="badge ${value === 'OTD' ? 'badge-success' : (value === 'LATE' ? 'badge-danger' : 'badge-secondary')}">${value}</span>`
            },
            {
                data: 'delay_days', name: 'delay_days', orderable: false, searchable: false,
                render: (value) => `<span class="${parseFloat(value) > 0 ? 'text-danger fw-bold' : ''}">${value}</span>`
            },
            { data: 'max_arrival', name: 'max_arrival', orderable: false, searchable: false },
            { data: 'progress', name: 'progress', orderable: false, searchable: false },
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
