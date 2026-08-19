<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Posisi Barang per Kota (Destination) — {{ $selectedIsoType === 'laut' ? 'ISO Laut' : 'ISO Darat' }}</h3>
    </div>
    <div class="card-body p-0 dashboard-table-scroll">
        <table class="table table-sm table-bordered table-hover mb-0 dashboard-position-table">
            <thead>
                <tr>
                    <th rowspan="2">Destination</th>
                    <th rowspan="2">Total DO</th>
                    @foreach ($isoPositions as $position)
                        <th colspan="2" class="text-center">{{ $position }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($isoPositions as $position)
                        <th>Jumlah</th>
                        <th>Persentase</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($isoPositionSummary as $summary)
                    <tr>
                        <td><strong>{{ ucfirst(strtolower($summary['destination'])) }}</strong></td>
                        <td>{{ number_format($summary['total']) }}</td>
                        @foreach ($isoPositions as $position)
                            <td>{{ number_format($summary['positions'][$position]['count']) }}</td>
                            <td>{{ number_format($summary['positions'][$position]['percentage'], 0) }}%</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 2 + (count($isoPositions) * 2) }}" class="text-center text-muted py-3">Belum ada data posisi berdasarkan destination.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
