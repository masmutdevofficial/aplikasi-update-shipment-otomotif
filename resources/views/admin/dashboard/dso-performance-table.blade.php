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
                        <th class="text-center">{{ $position }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($dsoPositions as $position)
                        <th>Jumlah</th>
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
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 2 + count($dsoPositions) }}" class="text-center text-muted py-3">Belum ada data posisi barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('admin.dashboard._dso-performance-card')

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
        ajax: {
            url: '{{ route('admin.shipments.data') }}',
            data: function (payload) {
                payload.month = @json($selectedMonth);
                payload.year = @json($selectedYear);
            }
        },
        scrollX: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], ['10', '25', '50', '100']],
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
                render: renderDelayDays
            },
            { data: 'max_arrival', name: 'max_arrival', orderable: false, searchable: false },
            { data: 'progress', name: 'progress', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            processing: 'Memuat data shipment DSO...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data shipment DSO pada periode ini',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        }
    });
});
</script>
@endpush
