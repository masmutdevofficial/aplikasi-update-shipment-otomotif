@php
    $isIsoLaut = $selectedIsoType === 'laut';
    $identityLabel = $isIsoLaut ? 'NOKA' : 'NO SPB';
@endphp

<div class="row">
    <div class="col-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas {{ $isIsoLaut ? 'fa-ship' : 'fa-truck' }}"></i> Shipment Terbaru {{ $isIsoLaut ? 'ISO Laut' : 'ISO Darat' }}</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ $identityLabel }}</th>
                            <th>Destination</th>
                            <th>Terima DO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestShipments as $shipment)
                            <tr>
                                <td><code>{{ $isIsoLaut ? ($shipment->noka ?? '-') : ($shipment->no_spb ?? '-') }}</code></td>
                                <td>{{ $shipment->destination ?? '-' }}</td>
                                <td>{{ $shipment->terima_do?->format('d-M-y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data shipment {{ $isIsoLaut ? 'ISO Laut' : 'ISO Darat' }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Scan Terbaru {{ $isIsoLaut ? 'ISO Laut' : 'ISO Darat' }}</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead><tr><th>{{ $identityLabel }}</th><th>User</th><th>Tanggal</th></tr></thead>
                    <tbody>
                        @forelse ($latestScans as $scan)
                            <tr>
                                <td><code>{{ $scan->no_rangka }}</code></td>
                                <td>{{ $scan->user->name ?? '-' }}</td>
                                <td>{{ $scan->scan_date->format('d-M-y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada scan shipment {{ $isIsoLaut ? 'ISO Laut' : 'ISO Darat' }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
