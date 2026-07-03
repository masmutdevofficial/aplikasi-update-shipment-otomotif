@extends('layouts.guest')

@section('title', 'Kontak Administrator - Shipment Otomotif')

@section('content')
<div class="login-card">
    <div class="login-card-header">
        <i class="fas fa-address-card"></i> Kontak Administrator
    </div>
    <div class="login-card-body">
        <div class="alert alert-info">
            <div class="alert-content">
                <strong>Website Internal Shipment Otomotif</strong><br>
                Aplikasi ini digunakan untuk kebutuhan internal operasional shipment otomotif.
                Akses hanya diberikan kepada pengguna yang telah didaftarkan oleh administrator.
            </div>
        </div>

        <p class="form-text" style="font-size:14px;line-height:1.6;">
            Untuk permintaan akses, bantuan login, atau pertanyaan terkait penggunaan aplikasi,
            hubungi administrator sistem internal.
        </p>

        <div class="login-links" style="text-align:left;line-height:1.6;">
            @if (config('app.admin_contact_email'))
                Email administrator: <a href="mailto:{{ config('app.admin_contact_email') }}">{{ config('app.admin_contact_email') }}</a>
            @else
                Untuk bantuan akses, hubungi administrator sistem internal.
            @endif
            <br>
            <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
        </div>
    </div>
</div>
@endsection
