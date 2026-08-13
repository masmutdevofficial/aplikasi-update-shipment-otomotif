<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck"></i> Data Shipment ISO Darat</h3>
    </div>
    <div class="card-body p-0">
        <div class="dashboard-table-scroll">
            <table id="table-iso-darat" class="table table-striped table-hover dashboard-data-table mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NO SO / BOOKING</th>
                        <th>NO Quotation</th>
                        <th>NO Contract</th>
                        <th>CARGO / NO KA</th>
                        <th>NO SPB</th>
                        <th>Kategori MODA</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Area</th>
                        <th>Terima DO</th>
                        <th>Keluar dari PDC</th>
                        <th>AT PTD/DTD</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($isoDaratShipments as $shipment)
                        <tr>
                            <td>{{ $shipment->source_no ?? '-' }}</td>
                            <td>{{ $shipment->no_so_booking ?? '-' }}</td>
                            <td>{{ $shipment->no_quotation ?? '-' }}</td>
                            <td>{{ $shipment->no_contract ?? '-' }}</td>
                            <td>{{ $shipment->cargo_no_ka ?? '-' }}</td>
                            <td><code>{{ $shipment->no_spb ?? '-' }}</code></td>
                            <td>{{ $shipment->kategori_moda ?? '-' }}</td>
                            <td>{{ $shipment->origin ?? '-' }}</td>
                            <td>{{ $shipment->destination ?? '-' }}</td>
                            <td>{{ $shipment->area ?? '-' }}</td>
                            <td>{{ $shipment->terima_do?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->keluar_dari_pdc?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->at_ptd_dtd?->format('d-M-y') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
