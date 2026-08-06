@extends('layouts.vendor')

@section('title', 'Riwayat Scan — Shipment Otomotif')
@section('page-title', 'Riwayat Scan')
@section('breadcrumb')
    <li class="breadcrumb-item active">Riwayat</li>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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
                        @if($canUploadDocuments)
                            <th>Dokumen</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $history)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $history->no_rangka }}</code></td>
                            <td>{{ $history->scan_date->format('d-M-y') }}</td>
                            @if($canUploadDocuments)
                                <td style="min-width: 270px;">
                                    @if($history->document_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.document_disk'))->url($history->document_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mb-1">
                                            <i class="fas fa-image"></i> Lihat
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('vendor.history.document.upload', $history) }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-1 align-items-center">
                                        @csrf
                                        <input type="file" name="document" class="form-control form-control-sm" accept="image/png,image/jpeg" capture="environment" required style="max-width: 170px;">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-upload"></i> {{ $history->document_path ? 'Ganti' : 'Upload' }}
                                        </button>
                                    </form>
                                </td>
                            @endif
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
        columnDefs: [{ orderable: false, targets: {{ $canUploadDocuments ? '[0, 3]' : '[0]' }} }],
        order: [[2, 'desc']]
    });
});
</script>
@endpush
