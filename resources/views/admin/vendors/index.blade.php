@extends('layouts.admin')

@section('title', 'Kelola Vendor — Shipment Otomotif')
@section('page-title', 'Kelola Vendor')
@section('breadcrumb')
    <li class="breadcrumb-item active">Vendor</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Daftar vendor dan posisi pengiriman</p>
    <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Vendor
    </a>
</div>

{{-- Table --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="table-vendors" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama Vendor</th>
                        <th>User Account</th>
                        <th>Email</th>
                        <th>Posisi</th>
                        <th>Status User</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendors as $vendor)
                        <tr>
                            <td class="fw-semibold">{{ $vendor->vendor_name }}</td>
                            <td><code>{{ $vendor->user->username ?? '-' }}</code></td>
                            <td>{{ $vendor->user->email ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $vendor->position }}</span></td>
                            <td>
                                @if($vendor->user && $vendor->user->is_active)
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                @else
                                    <span class="badge" style="background:var(--secondary)"><i class="fas fa-times-circle"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.vendors.edit', $vendor) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.vendors.destroy', $vendor) }}"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger"
                                                title="Hapus"
                                                onclick="return confirm('Yakin ingin menghapus vendor {{ $vendor->vendor_name }}?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
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
    $('#table-vendors').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], ['10', '25', '50', 'Semua']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data vendor',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        columnDefs: [{ orderable: false, targets: -1 }]
    });
});
</script>
@endpush
