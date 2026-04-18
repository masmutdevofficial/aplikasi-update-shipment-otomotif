@extends(auth()->user()->level === 'vendor' ? 'layouts.vendor' : 'layouts.admin')

@section('title', 'Ubah Password — Shipment Otomotif')
@section('page-title', 'Ubah Password')
@section('breadcrumb')
    <li class="breadcrumb-item active">Ubah Password</li>
@endsection

@section('content')
<div class="row">
    <div class="col-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key"></i> Ubah Password</h3>
            </div>
            <div class="card-body">

                <form method="POST" action="{{ route('password.change.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Password Lama</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password"
                                   name="current_password"
                                   placeholder="Masukkan password lama"
                                   required>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="Masukkan password baru"
                                   required>
                            <button class="btn btn-default" type="button" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Minimal {{ auth()->user()->level === 'vendor' ? '12' : '16' }} karakter, huruf besar & kecil, angka, dan simbol.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Ulangi password baru"
                                   required>
                            <button class="btn btn-default" type="button" onclick="togglePassword('password_confirmation', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Simpan
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endpush
