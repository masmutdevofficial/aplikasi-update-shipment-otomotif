@extends('layouts.admin')

@section('title', 'Kelola Users — Shipment Otomotif')
@section('page-title', 'Kelola Users')
@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">
        @if(auth()->user()->isAdmin())
            Kelola akun vendor
        @else
            Kelola semua akun pengguna
        @endif
    </p>
    <div class="d-flex align-items-center">
        <form id="bulk-delete-form" method="POST" action="{{ route('admin.users.bulk-destroy') }}" class="mr-2 d-none">
            @csrf
            @method('DELETE')
            <span id="selected-users-count" class="text-muted mr-2"></span>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Hapus Terpilih
            </button>
        </form>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah User
        </a>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="table-users" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <input type="checkbox" id="select-all-users" aria-label="Pilih semua user">
                        </th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="text-center">
                                @if($user->id !== auth()->id())
                                    <input type="checkbox" class="user-select" value="{{ $user->id }}" aria-label="Pilih {{ $user->name }}">
                                @endif
                            </td>
                            <td>{{ $user->name }}</td>
                            <td><code>{{ $user->username }}</code></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @switch($user->level)
                                    @case('superadmin')
                                        <span class="badge badge-danger">Superadmin</span>
                                        @break
                                    @case('admin')
                                        <span class="badge badge-primary">Admin</span>
                                        @break
                                    @case('vendor')
                                        <span class="badge badge-success">Vendor</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                @else
                                    <span class="badge" style="background:var(--secondary)"><i class="fas fa-times-circle"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('admin.users.toggle-status', $user) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}"
                                                    title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user {{ $user->name }}?')">
                                                    <i class="fas fa-{{ $user->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('admin.users.destroy', $user) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-outline-danger"
                                                    title="Hapus"
                                                    onclick="return confirm('Yakin ingin menghapus user {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
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
    const usersTable = $('#table-users').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], ['10', '25', '50', 'Semua']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data user',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        columnDefs: [{ orderable: false, targets: [0, -1] }]
    });

    const selectAll = document.getElementById('select-all-users');
    const bulkDeleteForm = document.getElementById('bulk-delete-form');
    const selectedCount = document.getElementById('selected-users-count');

    function getUserCheckboxes() {
        return Array.from($(usersTable.rows().nodes()).find('.user-select'));
    }

    function updateBulkDeleteState() {
        const checkboxes = getUserCheckboxes();
        const selected = checkboxes.filter((checkbox) => checkbox.checked);

        bulkDeleteForm.classList.toggle('d-none', selected.length === 0);
        selectedCount.textContent = `${selected.length} user dipilih`;
        selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
    }

    selectAll.addEventListener('change', function () {
        getUserCheckboxes().forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
        updateBulkDeleteState();
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('user-select')) {
            updateBulkDeleteState();
        }
    });

    $('#table-users').on('draw.dt', updateBulkDeleteState);

    bulkDeleteForm.addEventListener('submit', function (event) {
        const selected = getUserCheckboxes().filter((checkbox) => checkbox.checked);

        if (selected.length === 0) {
            event.preventDefault();
            return;
        }

        if (!confirm(`Yakin ingin menghapus ${selected.length} user terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
            event.preventDefault();
            return;
        }

        selected.forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = checkbox.value;
            bulkDeleteForm.appendChild(input);
        });
    });
});
</script>
@endpush
