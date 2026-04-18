@extends('layouts.guest')

@section('title', 'Lupa Password — Shipment Otomotif')

@section('content')
<div class="login-card">
    <div class="login-card-header">
        <i class="fas fa-envelope"></i> Lupa Password
    </div>
    <div class="login-card-body">
        <p class="text-muted" style="text-align:center;margin-bottom:16px;font-size:13px;">
            Masukkan email Anda. Kami akan mengirimkan link untuk mereset password.
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Masukkan email terdaftar"
                           required
                           autofocus>
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                @error('email')
                    <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-top:24px;">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;font-size:15px;">
                    <i class="fas fa-paper-plane"></i> Kirim Link Reset
                </button>
            </div>

            <div class="login-links">
                <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
