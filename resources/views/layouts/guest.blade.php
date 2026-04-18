<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Shipment Otomotif'))</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #007bff;
            --danger: #dc3545;
            --card-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Source Sans 3', 'Source Sans Pro', sans-serif;
            font-size: 14px;
            background: #e9ecef;
            color: #212529;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        a { text-decoration: none; color: var(--primary); }
        a:hover { text-decoration: underline; }
        .login-page {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo i { font-size: 40px; color: #343a40; }
        .login-logo h1 { font-size: 24px; font-weight: 700; color: #343a40; margin: 8px 0 4px; }
        .login-logo p { font-size: 13px; color: #6c757d; }
        .login-card {
            background: #fff;
            border-radius: 6px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        .login-card-header {
            background: #343a40;
            color: #fff;
            padding: 16px 24px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
        }
        .login-card-body { padding: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; }
        .form-control {
            display: block; width: 100%;
            padding: 9px 12px; font-size: 14px;
            border: 1px solid #ced4da; border-radius: 4px;
            background: #fff; color: #495057;
            transition: border-color .2s, box-shadow .2s;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,123,255,.15);
        }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { font-size: 12px; color: var(--danger); margin-top: 4px; }
        .input-group { display: flex; position: relative; }
        .input-group .form-control { border-radius: 4px 0 0 4px; flex: 1; }
        .input-group-text {
            padding: 9px 14px; background: #e9ecef; border: 1px solid #ced4da;
            border-radius: 0 4px 4px 0; border-left: 0; display: flex; align-items: center;
            color: #495057; cursor: pointer; font-size: 14px;
        }
        .input-group-text:hover { background: #dee2e6; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 20px; border-radius: 4px; border: 1px solid transparent;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: opacity .15s; white-space: nowrap; font-family: inherit; width: 100%;
        }
        .btn:hover { opacity: .85; }
        .btn-primary { background: var(--primary); color: #fff; border-color: var(--primary); }
        .alert {
            padding: 12px 16px; border-radius: 4px; margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 10px; border: 1px solid transparent; font-size: 14px;
        }
        .alert-success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .alert-danger  { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .alert .alert-content { flex: 1; }
        .alert .btn-close-alert { background: none; border: none; cursor: pointer; color: inherit; opacity: .6; font-size: 18px; }
        .login-links { text-align: center; margin-top: 16px; font-size: 13px; color: #6c757d; }
        .login-links a { color: var(--primary); }
        .form-text { font-size: 12px; color: #6c757d; margin-top: 4px; }

        @stack('styles')
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-logo">
            <i class="fas fa-truck"></i>
            <h1>Shipment Otomotif</h1>
            <p>PT. Serasi Logistics Indonesia</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <div class="alert-content">{{ session('success') }}</div>
                <button class="btn-close-alert" onclick="this.closest('.alert').remove()">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                <div class="alert-content">{{ session('error') }}</div>
                <button class="btn-close-alert" onclick="this.closest('.alert').remove()">&times;</button>
            </div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
