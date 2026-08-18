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

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-line"></i> Performance Shipment DSO</h3>
    </div>
    <div class="card-body p-0">
        <div class="dashboard-table-scroll">
            <table id="table-dashboard-dso" class="table table-striped table-hover mb-0 dashboard-data-table">
                <thead>
                    <tr class="shipment-group-header" data-dt-order="disable">
                        <th colspan="14"></th>
                        <th colspan="5">Actual Time (Input)</th>
                        <th colspan="6">Actual Lead Time (Days)</th>
                        <th colspan="6">SLA &amp; Performance</th>
                    </tr>
                    <tr>
                        <th>No</th>
                        <th>Lokasi</th>
                        <th>No DO</th>
                        <th>Type Kendaraan</th>
                        <th>No Rangka</th>
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
                        <th>Kapal (Loading)</th>
                        <th>Kapal (Aboard)</th>
                        <th>Storage Port (Destination)</th>
                        <th>PtD (Dooring)</th>
                        <th>SLA Actual</th>
                        <th>SLA Cust</th>
                        <th>Result</th>
                        <th title="(SLA Actual - SLA Customer) / SLA Customer × 100%">Persentase Keterlambatan</th>
                        <th>Max Arrival</th>
                        <th>Progress</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-stopwatch"></i> Referensi SLA Customer DSO</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>Destination</th>
                    <th>Keluar dari PDC</th>
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
                    <tr>
                        <td><strong>{{ ucfirst(strtolower($destination)) }}</strong></td>
                        @foreach ($target['stages'] as $days)
                            <td>{{ $days }}</td>
                        @endforeach
                        <td><strong>{{ $target['total'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const renderDelayPercentage = (value) => {
        if (value === null || value === undefined || value === '-') {
            return '<span class="badge badge-secondary">-</span>';
        }

        const percentage = Number.parseFloat(value);
        const badgeClass = percentage > 0 ? 'badge-danger' : 'badge-success';

        return `<span class="badge ${badgeClass}" title="(SLA Actual - SLA Customer) / SLA Customer × 100%">${value}</span>`;
    };

    $('#table-dashboard-dso').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        pageLength: 10,
        ajax: {
            url: @json(route('admin.shipments.data')),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            data: function (payload) {
                payload.month = @json($selectedMonth);
                payload.year = @json($selectedYear);
            }
        },
        columns: [
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
            { data: 'lead_time_do_release_pickup', orderable: false, searchable: false },
            { data: 'lead_time_storage_port', orderable: false, searchable: false },
            { data: 'lead_time_kapal_loading', orderable: false, searchable: false },
            { data: 'lead_time_kapal_aboard', orderable: false, searchable: false },
            { data: 'lead_time_storage_destination', orderable: false, searchable: false },
            { data: 'lead_time_ptd_dooring', orderable: false, searchable: false },
            { data: 'sla_actual', orderable: false, searchable: false },
            { data: 'sla_customer', orderable: false, searchable: false },
            {
                data: 'sla_result', orderable: false, searchable: false,
                render: (value) => `<span class="badge ${value === 'OTD' ? 'badge-success' : (value === 'LATE' ? 'badge-danger' : 'badge-secondary')}">${value}</span>`
            },
            {
                data: 'delay_percentage', orderable: false, searchable: false,
                render: renderDelayPercentage
            },
            { data: 'max_arrival', orderable: false, searchable: false },
            { data: 'progress', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            processing: 'Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data shipment DSO',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        }
    });
});
</script>
@endpush
