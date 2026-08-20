<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-line"></i> Performance Shipment DSO
            <span class="badge badge-warning ml-2">Data Demo</span>
        </h3>
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
    // Dataset statis sementara untuk kebutuhan presentasi; tidak mengambil data dari database.
    const dummyShipments = [
        {
            row_number: 1, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260801', type_kendaraan: 'Rush 1.5 G AT',
            no_rangka: 'MHKE8FB3JTK260001', no_engine: '2NRX260001', warna: 'White', asal_pdc: 'Jakarta',
            kota: 'Balikpapan', tujuan_pengiriman: 'Auto2000 Balikpapan', terima_do: '03-Aug-26',
            keluar_dari_pdc: '04-Aug-26', nama_kapal: 'KM Dharma Ferry VII', keberangkatan_kapal: '06-Aug-26',
            at_storage_port: '05-Aug-26', atd_kapal_loading: '06-Aug-26', ata_kapal: '09-Aug-26',
            ata_storage_port_destination: '09-Aug-26', at_ptd_dooring: '12-Aug-26',
            lead_time_do_release_pickup: 1, lead_time_storage_port: 1, dwelling_origin: 1,
            lead_time_kapal_aboard: 3, lead_time_storage_destination: 0, dwelling_destination: 3,
            sla_actual: 9, sla_customer: 10, sla_result: 'OTD', delay_days: 0,
            max_arrival: '13-Aug-26', progress: 'OTD'
        },
        {
            row_number: 2, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260802', type_kendaraan: 'Avanza 1.5 G CVT',
            no_rangka: 'MHKAB1BYJTK260002', no_engine: '2NRX260002', warna: 'Silver Metallic', asal_pdc: 'Jakarta',
            kota: 'Samarinda', tujuan_pengiriman: 'Auto2000 Samarinda', terima_do: '04-Aug-26',
            keluar_dari_pdc: '05-Aug-26', nama_kapal: 'KM Kirana IX', keberangkatan_kapal: '07-Aug-26',
            at_storage_port: '06-Aug-26', atd_kapal_loading: '07-Aug-26', ata_kapal: '10-Aug-26',
            ata_storage_port_destination: '10-Aug-26', at_ptd_dooring: '14-Aug-26',
            lead_time_do_release_pickup: 1, lead_time_storage_port: 1, dwelling_origin: 1,
            lead_time_kapal_aboard: 3, lead_time_storage_destination: 0, dwelling_destination: 4,
            sla_actual: 10, sla_customer: 11, sla_result: 'OTD', delay_days: 0,
            max_arrival: '15-Aug-26', progress: 'OTD'
        },
        {
            row_number: 3, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260803', type_kendaraan: 'Innova Zenix G HEV',
            no_rangka: 'MHFAB8EMJTK260003', no_engine: 'M20A260003', warna: 'Black Mica', asal_pdc: 'Jakarta',
            kota: 'Banjarmasin', tujuan_pengiriman: 'Auto2000 Banjarmasin', terima_do: '05-Aug-26',
            keluar_dari_pdc: '07-Aug-26', nama_kapal: 'KM Niki Sejahtera', keberangkatan_kapal: '10-Aug-26',
            at_storage_port: '08-Aug-26', atd_kapal_loading: '10-Aug-26', ata_kapal: '13-Aug-26',
            ata_storage_port_destination: '13-Aug-26', at_ptd_dooring: '16-Aug-26',
            lead_time_do_release_pickup: 2, lead_time_storage_port: 1, dwelling_origin: 2,
            lead_time_kapal_aboard: 3, lead_time_storage_destination: 0, dwelling_destination: 3,
            sla_actual: 11, sla_customer: 9, sla_result: 'LATE', delay_days: 2,
            max_arrival: '14-Aug-26', progress: 'LATE'
        },
        {
            row_number: 4, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260804', type_kendaraan: 'Fortuner 2.4 VRZ AT',
            no_rangka: 'MHFGB8GSJTK260004', no_engine: '2GDX260004', warna: 'Phantom Brown', asal_pdc: 'Jakarta',
            kota: 'Medan', tujuan_pengiriman: 'Auto2000 Medan Amplas', terima_do: '06-Aug-26',
            keluar_dari_pdc: '07-Aug-26', nama_kapal: 'KM Swarna Bahtera', keberangkatan_kapal: '09-Aug-26',
            at_storage_port: '08-Aug-26', atd_kapal_loading: '09-Aug-26', ata_kapal: '13-Aug-26',
            ata_storage_port_destination: '13-Aug-26', at_ptd_dooring: '16-Aug-26',
            lead_time_do_release_pickup: 1, lead_time_storage_port: 1, dwelling_origin: 1,
            lead_time_kapal_aboard: 4, lead_time_storage_destination: 0, dwelling_destination: 3,
            sla_actual: 10, sla_customer: 10, sla_result: 'OTD', delay_days: 0,
            max_arrival: '16-Aug-26', progress: 'OTD'
        },
        {
            row_number: 5, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260805', type_kendaraan: 'Yaris Cross S HEV',
            no_rangka: 'MHKGAGFBJTK260005', no_engine: '2NRV260005', warna: 'Scarlet Red', asal_pdc: 'Jakarta',
            kota: 'Makassar', tujuan_pengiriman: 'Auto2000 Urip Sumoharjo', terima_do: '07-Aug-26',
            keluar_dari_pdc: '08-Aug-26', nama_kapal: 'KM Dharma Kencana VII', keberangkatan_kapal: '10-Aug-26',
            at_storage_port: '09-Aug-26', atd_kapal_loading: '10-Aug-26', ata_kapal: '12-Aug-26',
            ata_storage_port_destination: '12-Aug-26', at_ptd_dooring: '16-Aug-26',
            lead_time_do_release_pickup: 1, lead_time_storage_port: 1, dwelling_origin: 1,
            lead_time_kapal_aboard: 2, lead_time_storage_destination: 0, dwelling_destination: 4,
            sla_actual: 9, sla_customer: 10, sla_result: 'OTD', delay_days: 0,
            max_arrival: '17-Aug-26', progress: 'OTD'
        },
        {
            row_number: 6, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260806', type_kendaraan: 'Raize 1.0T GR CVT',
            no_rangka: 'MHKGAGFBJTK260006', no_engine: '1KRX260006', warna: 'Turquoise MM', asal_pdc: 'Jakarta',
            kota: 'Pontianak', tujuan_pengiriman: 'Auto2000 Pontianak', terima_do: '08-Aug-26',
            keluar_dari_pdc: '10-Aug-26', nama_kapal: 'KM Satya Kencana III', keberangkatan_kapal: '12-Aug-26',
            at_storage_port: '11-Aug-26', atd_kapal_loading: '12-Aug-26', ata_kapal: '15-Aug-26',
            ata_storage_port_destination: '15-Aug-26', at_ptd_dooring: '18-Aug-26',
            lead_time_do_release_pickup: 2, lead_time_storage_port: 1, dwelling_origin: 1,
            lead_time_kapal_aboard: 3, lead_time_storage_destination: 0, dwelling_destination: 3,
            sla_actual: 10, sla_customer: 8, sla_result: 'LATE', delay_days: 2,
            max_arrival: '16-Aug-26', progress: 'LATE'
        },
        {
            row_number: 7, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260807', type_kendaraan: 'Agya 1.2 GR CVT',
            no_rangka: 'MHKA4GB5JTK260007', no_engine: 'WAUX260007', warna: 'Yellow', asal_pdc: 'Jakarta',
            kota: 'Gorontalo', tujuan_pengiriman: 'Hasjrat Toyota Gorontalo', terima_do: '01-Aug-26',
            keluar_dari_pdc: '03-Aug-26', nama_kapal: 'KM Mutiara Sentosa II', keberangkatan_kapal: '06-Aug-26',
            at_storage_port: '04-Aug-26', atd_kapal_loading: '06-Aug-26', ata_kapal: '10-Aug-26',
            ata_storage_port_destination: '10-Aug-26', at_ptd_dooring: '14-Aug-26',
            lead_time_do_release_pickup: 2, lead_time_storage_port: 1, dwelling_origin: 2,
            lead_time_kapal_aboard: 4, lead_time_storage_destination: 0, dwelling_destination: 4,
            sla_actual: 13, sla_customer: 14, sla_result: 'OTD', delay_days: 0,
            max_arrival: '15-Aug-26', progress: 'OTD'
        },
        {
            row_number: 8, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260808', type_kendaraan: 'Hilux Double Cabin 2.4',
            no_rangka: 'MROKB8CDJTK260008', no_engine: '2GDX260008', warna: 'Super White', asal_pdc: 'Jakarta',
            kota: 'Manado', tujuan_pengiriman: 'Hasjrat Toyota Manado', terima_do: '01-Aug-26',
            keluar_dari_pdc: '03-Aug-26', nama_kapal: 'KM Dorolonda', keberangkatan_kapal: '07-Aug-26',
            at_storage_port: '04-Aug-26', atd_kapal_loading: '07-Aug-26', ata_kapal: '14-Aug-26',
            ata_storage_port_destination: '14-Aug-26', at_ptd_dooring: '19-Aug-26',
            lead_time_do_release_pickup: 2, lead_time_storage_port: 1, dwelling_origin: 3,
            lead_time_kapal_aboard: 7, lead_time_storage_destination: 0, dwelling_destination: 5,
            sla_actual: 18, sla_customer: 20, sla_result: 'OTD', delay_days: 0,
            max_arrival: '21-Aug-26', progress: 'OTD'
        },
        {
            row_number: 9, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260809', type_kendaraan: 'Veloz 1.5 Q CVT TSS',
            no_rangka: 'MHKAB1BYJTK260009', no_engine: '2NRX260009', warna: 'Platinum White', asal_pdc: 'Jakarta',
            kota: 'Balikpapan', tujuan_pengiriman: 'Auto2000 MT Haryono', terima_do: '09-Aug-26',
            keluar_dari_pdc: '11-Aug-26', nama_kapal: 'KM Kirana IX', keberangkatan_kapal: '13-Aug-26',
            at_storage_port: '12-Aug-26', atd_kapal_loading: '13-Aug-26', ata_kapal: '16-Aug-26',
            ata_storage_port_destination: '16-Aug-26', at_ptd_dooring: '19-Aug-26',
            lead_time_do_release_pickup: 2, lead_time_storage_port: 1, dwelling_origin: 1,
            lead_time_kapal_aboard: 3, lead_time_storage_destination: 0, dwelling_destination: 3,
            sla_actual: 10, sla_customer: 10, sla_result: 'OTD', delay_days: 0,
            max_arrival: '19-Aug-26', progress: 'OTD'
        },
        {
            row_number: 10, lokasi: 'PDC Jakarta', no_do: 'DO-DSO-260810', type_kendaraan: 'Corolla Cross HEV GR',
            no_rangka: 'MHFAB8EMJTK260010', no_engine: 'M20A260010', warna: 'Metal Stream', asal_pdc: 'Jakarta',
            kota: 'Makassar', tujuan_pengiriman: 'Auto2000 Alauddin', terima_do: '06-Aug-26',
            keluar_dari_pdc: '08-Aug-26', nama_kapal: 'KM Dharma Rucitra VII', keberangkatan_kapal: '11-Aug-26',
            at_storage_port: '09-Aug-26', atd_kapal_loading: '11-Aug-26', ata_kapal: '14-Aug-26',
            ata_storage_port_destination: '14-Aug-26', at_ptd_dooring: '18-Aug-26',
            lead_time_do_release_pickup: 2, lead_time_storage_port: 1, dwelling_origin: 2,
            lead_time_kapal_aboard: 3, lead_time_storage_destination: 0, dwelling_destination: 4,
            sla_actual: 12, sla_customer: 10, sla_result: 'LATE', delay_days: 2,
            max_arrival: '16-Aug-26', progress: 'LATE'
        }
    ];

    const renderDelayDays = (value) => {
        if (value === null || value === undefined || value === '-') {
            return '<span class="badge badge-secondary">-</span>';
        }

        const days = Number.parseInt(value, 10);
        const badgeClass = days > 0 ? 'badge-danger' : 'badge-success';

        return `<span class="badge ${badgeClass}" title="SLA Actual - SLA Customer">${value}</span>`;
    };

    $('#table-dashboard-dso').DataTable({
        data: dummyShipments,
        processing: false,
        serverSide: false,
        scrollX: true,
        pageLength: 10,
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
