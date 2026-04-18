@extends('layouts.vendor')

@section('title', 'Riwayat Scan — Shipment Otomotif')
@section('page-title', 'Riwayat Scan')
@section('breadcrumb')
    <li class="breadcrumb-item active">Riwayat</li>
@endsection

@section('content')

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="table-history" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Rangka (VIN)</th>
                        <th>Tanggal Scan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $history)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $history->no_rangka }}</code></td>
                            <td>{{ $history->scan_date->format('d-M-y') }}</td>
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
    $('#table-history').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], ['10', '25', '50', 'Semua']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Belum ada riwayat scan',
            emptyTable: 'Belum ada riwayat scan',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        columnDefs: [{ orderable: false, targets: 0 }],
        order: [[2, 'desc']]
    });
});
</script>
@endpush
