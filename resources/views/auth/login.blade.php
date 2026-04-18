@extends('layouts.guest')

@section('title', 'Login — Shipment Otomotif')

@section('content')
<div class="login-card">
    <div class="login-card-header">
        <i class="fas fa-sign-in-alt"></i> Login
    </div>
    <div class="login-card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="alert-content">
                    <ul style="margin:0;padding-left:16px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Masukkan email"
                           required
                           autofocus>
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Masukkan password"
                           required>
                    <span class="input-group-text" id="togglePassword" style="cursor:pointer;"><i class="fas fa-eye"></i></span>
                </div>
            </div>

            <div class="form-group" style="margin-top:24px;">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;font-size:15px;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>

            <div class="login-links">
                <a href="{{ route('password.request') }}">Lupa password?</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
</script>
@endpush
