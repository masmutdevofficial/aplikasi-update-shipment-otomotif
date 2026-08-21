<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Ringkasan Late per Kota</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead><tr><th>Kota</th><th>Total Shipment</th><th>OTD</th><th>Late</th><th>Persentase Late</th></tr></thead>
            <tbody>
                @forelse ($isoLateByCity as $summary)
                    <tr>
                        <td><strong>{{ ucfirst(strtolower($summary['city'])) }}</strong></td>
                        <td>{{ number_format($summary['total']) }}</td>
                        <td><span class="badge badge-success">{{ number_format($summary['otd']) }}</span></td>
                        <td><span class="badge {{ $summary['late'] > 0 ? 'badge-danger' : 'badge-secondary' }}">{{ number_format($summary['late']) }}</span></td>
                        <td>{{ number_format($summary['percentage'], 2, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada shipment ISO Laut yang dapat dievaluasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('admin.dashboard.iso-position-summary')

@include('admin.dashboard.iso-laut-table')

<div class="row">
    <div class="col-6">
        <div class="card card-info">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-anchor"></i> Dwelling Origin</h3></div>
            <div class="card-body p-0 table-responsive dashboard-dwelling-table">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead><tr><th>Kota</th><th>Avg</th><th>Min</th><th>Max</th></tr></thead>
                    <tbody>
                        @forelse ($isoDwellingDetails['origin'] as $row)
                            <tr>
                                <td><strong>{{ ucfirst(strtolower($row['city'])) }}</strong></td>
                                <td>{{ number_format($row['average'], 2, ',', '.') }} hari</td>
                                <td>{{ number_format($row['minimum']) }} hari</td>
                                <td>{{ number_format($row['maximum']) }} hari</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data Dwelling Origin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-flag-checkered"></i> Dwelling Destination</h3></div>
            <div class="card-body p-0 table-responsive dashboard-dwelling-table">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead><tr><th>Kota</th><th>Avg</th><th>Min</th><th>Max</th></tr></thead>
                    <tbody>
                        @forelse ($isoDwellingDetails['destination'] as $row)
                            <tr>
                                <td><strong>{{ ucfirst(strtolower($row['city'])) }}</strong></td>
                                <td>{{ number_format($row['average'], 2, ',', '.') }} hari</td>
                                <td>{{ number_format($row['minimum']) }} hari</td>
                                <td>{{ number_format($row['maximum']) }} hari</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data Dwelling Destination.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.dashboard.iso-latest')
