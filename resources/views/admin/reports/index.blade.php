@extends('layouts.admin')

@section('title', 'Laporan — Shipment Otomotif')
@section('page-title', 'Laporan Shipment')
@section('breadcrumb')
    <li class="breadcrumb-item active">Laporan</li>
@endsection

@section('content')

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-4">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-4">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-4">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        @if(request()->hasAny(['date_from', 'date_to']))
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                        <a href="{{ route('admin.reports.export', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="table-reports" class="table table-hover table-striped mb-0" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Lokasi</th>
                        <th>No. DO</th>
                        <th>Type</th>
                        <th>No. Rangka</th>
                        <th>No. Engine</th>
                        <th>Warna</th>
                        <th>Asal PDC</th>
                        <th>Kota</th>
                        <th>Tujuan</th>
                        <th>Terima DO</th>
                        <th>Keluar PDC</th>
                        <th>Kapal</th>
                        <th>Keberangkatan</th>
                        @foreach(\App\Models\Vendor::positions() as $pos)
                            <th class="text-center" title="{{ $pos }}">
                                <small>{{ \Illuminate\Support\Str::limit($pos, 15) }}</small>
                            </th>
                        @endforeach
                        <th>Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        @php
                            $updates = $shipment->shipmentUpdates->keyBy('position');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $shipment->lokasi }}</td>
                            <td>{{ $shipment->no_do }}</td>
                            <td>{{ $shipment->type_kendaraan }}</td>
                            <td><code>{{ $shipment->no_rangka }}</code></td>
                            <td>{{ $shipment->no_engine }}</td>
                            <td>{{ $shipment->warna }}</td>
                            <td>{{ $shipment->asal_pdc }}</td>
                            <td>{{ $shipment->kota }}</td>
                            <td>{{ $shipment->tujuan_pengiriman }}</td>
                            <td>{{ $shipment->terima_do->format('d-M-y') }}</td>
                            <td>{{ $shipment->keluar_dari_pdc->format('d-M-y') }}</td>
                            <td>{{ $shipment->nama_kapal }}</td>
                            <td>{{ $shipment->keberangkatan_kapal->format('d-M-y') }}</td>
                            @foreach(\App\Models\Vendor::positions() as $pos)
                                <td class="text-center">
                                    @if($update = $updates->get($pos))
                                        <span class="badge badge-success">{{ $update->scan_date->format('d-M-y') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @php
                                    $docLink = $shipment->shipmentUpdates->first(fn ($u) => $u->document_link);
                                @endphp
                                @if($docLink)
                                    <a href="{{ $docLink->document_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-link"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#table-reports').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']],
        scrollX: true,
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data laporan',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        columnDefs: [
            { orderable: false, targets: [0, -1] }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush
