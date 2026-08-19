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
                        <th title="Dihitung per shipment: ATD Kapal (Loading) - AT Storage Port; gunakan hari ini bila belum loading">Dwelling Origin (per Shipment)</th>
                        <th>Kapal (Aboard)</th>
                        <th>Storage Port (Destination)</th>
                        <th title="Dihitung per shipment: AT PtD (Dooring) - ATA Storage Port (Destination); gunakan hari ini bila belum dooring">Dwelling Destination (per Shipment)</th>
                        <th>SLA Actual</th>
                        <th>SLA Cust</th>
                        <th>Result</th>
                        <th title="Maksimal 0 atau SLA Actual - SLA Customer">Keterlambatan (Hari)</th>
                        <th>Max Arrival</th>
                        <th>Progress</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Ringkasan Late per Kota</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>Kota</th>
                    <th>Total Shipment</th>
                    <th>OTD</th>
                    <th>Late</th>
                    <th>Persentase Late</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dsoLateByCity as $summary)
                    <tr>
                        <td><strong>{{ ucfirst(strtolower($summary['city'])) }}</strong></td>
                        <td>{{ number_format($summary['total']) }}</td>
                        <td><span class="badge badge-success">{{ number_format($summary['otd']) }}</span></td>
                        <td><span class="badge {{ $summary['late'] > 0 ? 'badge-danger' : 'badge-secondary' }}">{{ number_format($summary['late']) }}</span></td>
                        <td>{{ number_format($summary['percentage'], 2) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada shipment yang dapat dievaluasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Dashboard 2 — Posisi Barang per Kota</h3>
    </div>
    <div class="card-body p-0 dashboard-table-scroll">
        <table class="table table-sm table-bordered table-hover mb-0 dashboard-position-table">
            <thead>
                <tr>
                    <th rowspan="2">Kota</th>
                    <th rowspan="2">Terima DO</th>
                    @foreach ($dsoPositions as $position)
                        <th colspan="2" class="text-center">{{ $position }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($dsoPositions as $position)
                        <th>Jumlah</th>
                        <th>Persentase</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($dsoPositionSummary as $summary)
                    <tr>
                        <td><strong>{{ ucfirst(strtolower($summary['city'])) }}</strong></td>
                        <td>{{ number_format($summary['total']) }}</td>
                        @foreach ($dsoPositions as $position)
                            <td>{{ number_format($summary['positions'][$position]['count']) }}</td>
                            <td>{{ number_format($summary['positions'][$position]['percentage'], 0) }}%</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 2 + (count($dsoPositions) * 2) }}" class="text-center text-muted py-3">Belum ada data posisi barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const renderDelayDays = (value) => {
        if (value === null || value === undefined || value === '-') {
            return '<span class="badge badge-secondary">-</span>';
        }

        const days = Number.parseInt(value, 10);
        const badgeClass = days > 0 ? 'badge-danger' : 'badge-success';

        return `<span class="badge ${badgeClass}" title="SLA Actual - SLA Customer">${value}</span>`;
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
            { data: 'dwelling_origin', orderable: false, searchable: false },
            { data: 'lead_time_kapal_aboard', orderable: false, searchable: false },
            { data: 'lead_time_storage_destination', orderable: false, searchable: false },
            { data: 'dwelling_destination', orderable: false, searchable: false },
            { data: 'sla_actual', orderable: false, searchable: false },
            { data: 'sla_customer', orderable: false, searchable: false },
            {
                data: 'sla_result', orderable: false, searchable: false,
                render: (value) => `<span class="badge ${value === 'OTD' ? 'badge-success' : (value === 'LATE' ? 'badge-danger' : 'badge-secondary')}">${value}</span>`
            },
            {
                data: 'delay_days', orderable: false, searchable: false,
                render: renderDelayDays
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
