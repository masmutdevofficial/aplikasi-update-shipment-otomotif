<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Posisi Barang per Kota (Destination)</h3>
    </div>
    <div class="card-body p-0 dashboard-table-scroll">
        <table class="table table-sm table-bordered table-hover mb-0 dashboard-position-table">
            <thead>
                <tr>
                    <th rowspan="2">Destination</th>
                    <th rowspan="2">Total DO</th>
                    @foreach ($tsoPositions as $position)
                        <th class="text-center">{{ $position }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($tsoPositions as $position)
                        <th>Jumlah</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($tsoPositionSummary as $summary)
                    <tr>
                        <td><strong>{{ ucfirst(strtolower($summary['destination'])) }}</strong></td>
                        <td>{{ number_format($summary['total']) }}</td>
                        @foreach ($tsoPositions as $position)
                            <td>{{ number_format($summary['positions'][$position]['count']) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 2 + count($tsoPositions) }}" class="text-center text-muted py-3">Belum ada data posisi TSO berdasarkan destination.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('admin.dashboard._tso-data-table')

<div class="row">
    <div class="col-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-truck-loading"></i> Shipment Terbaru TSO</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Rangka</th>
                            <th>Destination</th>
                            <th>DO Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestShipments as $shipment)
                            <tr>
                                <td><code>{{ $shipment->no_rangka ?? '-' }}</code></td>
                                <td>{{ $shipment->destination ?? '-' }}</td>
                                <td>{{ $shipment->do_date?->format('d-M-y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data shipment TSO.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Scan Terbaru TSO</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>VIN</th>
                            <th>User</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestScans as $scan)
                            <tr>
                                <td><code>{{ $scan->no_rangka }}</code></td>
                                <td>{{ $scan->user->name ?? '-' }}</td>
                                <td>{{ $scan->scan_date->format('d-M-y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada scan shipment TSO.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
