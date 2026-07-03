@extends('layouts.guest')

@section('title', 'Kebijakan Privasi - Shipment Otomotif')

@section('content')
<div class="login-card">
    <div class="login-card-header">
        <i class="fas fa-shield-alt"></i> Kebijakan Privasi
    </div>
    <div class="login-card-body">
        <p class="form-text" style="font-size:14px;line-height:1.6;">
            Aplikasi ini hanya digunakan untuk kebutuhan internal operasional shipment otomotif.
        </p>

        <div class="alert alert-info">
            <div class="alert-content">
                <strong>Data yang diproses</strong>
                <ul class="info-list">
                    <li>Nama pengguna.</li>
                    <li>Email pengguna.</li>
                    <li>Data login aplikasi.</li>
                    <li>Data operasional shipment yang dimasukkan oleh pengguna berwenang.</li>
                </ul>
            </div>
        </div>

        <p class="form-text" style="font-size:14px;line-height:1.6;">
            Aplikasi ini tidak meminta password layanan eksternal, data kartu pembayaran, PIN bank,
            recovery phrase, atau data pribadi yang tidak relevan dengan operasional aplikasi.
        </p>

        <div class="login-links" style="text-align:left;line-height:1.6;">
            @if (config('app.admin_contact_email'))
                Pertanyaan terkait data: <a href="mailto:{{ config('app.admin_contact_email') }}">{{ config('app.admin_contact_email') }}</a>
            @else
                Untuk pertanyaan terkait data, hubungi administrator sistem.
            @endif
            <br>
            <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
        </div>
    </div>
</div>
@endsection
