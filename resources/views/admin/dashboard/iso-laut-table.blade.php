<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-ship"></i> Data Shipment ISO Laut</h3>
    </div>
    <div class="card-body p-0">
        <div class="dashboard-table-scroll">
            <table id="table-iso-laut" class="table table-striped table-hover dashboard-data-table mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NO BOOKING DTP</th>
                        <th>NO BOOKING PTP</th>
                        <th>NO BOOKING PTD</th>
                        <th>NO Quotation DTP</th>
                        <th>NO Quotation PTP</th>
                        <th>NO Quotation PTD</th>
                        <th>NO Contract DTP</th>
                        <th>NO Contract PTP</th>
                        <th>NO Contract PTD</th>
                        <th>CARGO</th>
                        <th>NOKA</th>
                        <th>Kategori MODA</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Tujuan Pengiriman</th>
                        <th>Terima DO</th>
                        <th>Keluar dari PDC</th>
                        <th>Jenis Kapal</th>
                        <th>AT Storage Port</th>
                        <th>ATD Kapal (Loading)</th>
                        <th>ATA Kapal</th>
                        <th>ATA Storage Port (Destination)</th>
                        <th>AT PTD/DTD</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($isoLautShipments as $shipment)
                        <tr>
                            <td>{{ $shipment->source_no ?? '-' }}</td>
                            <td>{{ $shipment->no_booking_dtp ?? '-' }}</td>
                            <td>{{ $shipment->no_booking_ptp ?? '-' }}</td>
                            <td>{{ $shipment->no_booking_ptd ?? '-' }}</td>
                            <td>{{ $shipment->no_quotation_dtp ?? '-' }}</td>
                            <td>{{ $shipment->no_quotation_ptp ?? '-' }}</td>
                            <td>{{ $shipment->no_quotation_ptd ?? '-' }}</td>
                            <td>{{ $shipment->no_contract_dtp ?? '-' }}</td>
                            <td>{{ $shipment->no_contract_ptp ?? '-' }}</td>
                            <td>{{ $shipment->no_contract_ptd ?? '-' }}</td>
                            <td>{{ $shipment->cargo ?? '-' }}</td>
                            <td><code>{{ $shipment->noka ?? '-' }}</code></td>
                            <td>{{ $shipment->kategori_moda ?? '-' }}</td>
                            <td>{{ $shipment->origin ?? '-' }}</td>
                            <td>{{ $shipment->destination ?? '-' }}</td>
                            <td>{{ $shipment->tujuan_pengiriman ?? '-' }}</td>
                            <td>{{ $shipment->terima_do?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->keluar_dari_pdc?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->jenis_kapal ?? '-' }}</td>
                            <td>{{ $shipment->at_storage_port?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->atd_kapal_loading?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->ata_kapal?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->ata_storage_port_destination?->format('d-M-y') ?? '-' }}</td>
                            <td>{{ $shipment->at_ptd_dtd ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
