@extends('layouts.guest')

@section('title', 'Reset Password — Shipment Otomotif')

@section('content')
<div class="login-card">
    <div class="login-card-header">
        <i class="fas fa-key"></i> Reset Password
    </div>
    <div class="login-card-body">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email', request('email')) }}"
                           placeholder="Masukkan email"
                           required>
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                @error('email')
                    <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password Baru</label>
                <div class="input-group">
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           placeholder="Masukkan password baru"
                           required>
                    <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword('password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                @error('password')
                    <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                @enderror
                <div class="form-text">Minimal 12 karakter, huruf besar & kecil, angka, dan simbol.</div>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <input type="password"
                           class="form-control"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="Ulangi password baru"
                           required>
                    <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword('password_confirmation', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group" style="margin-top:24px;">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;font-size:15px;">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </div>

            <div class="login-links">
                <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
            </div>
        </form>
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
