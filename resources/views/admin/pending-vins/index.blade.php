@extends('layouts.admin')

@section('title', 'VIN Pending — Shipment Otomotif')
@section('page-title', 'VIN Pending')
@section('breadcrumb')
    <li class="breadcrumb-item active">VIN Pending</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="table-pending-vins" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Rangka (VIN)</th>
                        <th>Posisi</th>
                        <th>Vendor</th>
                        <th>Tanggal Scan</th>
                        <th>Dokumen Scan</th>
                        <th>Foto Scan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingVins as $pending)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $pending->no_rangka }}</code></td>
                            <td><span class="badge badge-info">{{ $pending->position }}</span></td>
                            <td>{{ $pending->vendor->vendor_name }}</td>
                            <td>{{ $pending->scan_date->format('d-M-y') }}</td>
                            <td>
                                @if($pending->document_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.document_disk'))->url($pending->document_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($pending->scan_photo_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.document_disk'))->url($pending->scan_photo_path) }}" target="_blank" rel="noopener" title="Buka foto scan ukuran penuh">
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.document_disk'))->url($pending->scan_photo_path) }}"
                                            alt="Foto scan VIN {{ $pending->no_rangka }}"
                                            style="width:96px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;"
                                            loading="lazy"
                                        >
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.pending-vins.destroy', $pending) }}" method="POST" onsubmit="return confirm('Hapus VIN pending {{ $pending->no_rangka }} beserta dokumen dan foto scannya? Data yang dihapus tidak dapat dikembalikan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus VIN pending">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </form>
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
    $('#table-pending-vins').DataTable({
        pageLength: 25,
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada VIN pending',
            emptyTable: 'Tidak ada VIN pending', infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        columnDefs: [{ orderable: false, targets: [0, -1] }],
        order: [[4, 'desc']]
    });
});
</script>
@endpush
