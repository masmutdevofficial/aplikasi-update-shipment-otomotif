<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin — Shipment Otomotif')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables 2.3.7 -->
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.dataTables.min.css') }}">
    <!-- Google Fonts – Source Sans Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #343a40;
            --sidebar-color: #c2c7d0;
            --sidebar-hover-bg: rgba(255,255,255,0.1);
            --sidebar-active-bg: #007bff;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 60px;
            --navbar-bg: #fff;
            --navbar-border: #dee2e6;
            --navbar-height: 57px;
            --content-bg: #f4f6f9;
            --card-bg: #fff;
            --card-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --secondary: #6c757d;
            --dark: #343a40;
            --light: #f8f9fa;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Source Sans 3', 'Source Sans Pro', sans-serif;
            font-size: 14px;
            background-color: var(--content-bg);
            color: #212529;
            overflow-x: hidden;
        }
        a { text-decoration: none; color: inherit; }

        /* === WRAPPER === */
        .wrapper { display: flex; min-height: 100vh; position: relative; }

        /* === NAVBAR === */
        .main-header {
            position: fixed; top: 0; right: 0; left: var(--sidebar-width);
            height: var(--navbar-height);
            background: var(--navbar-bg);
            border-bottom: 1px solid var(--navbar-border);
            display: flex; align-items: center; padding: 0 16px;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            transition: left .3s ease;
        }
        .main-header .navbar-nav { display: flex; align-items: center; gap: 4px; list-style: none; margin: 0; padding: 0; }
        .main-header .nav-link {
            padding: 8px 12px; color: #495057; border-radius: 4px;
            display: flex; align-items: center; gap: 6px; font-size: 14px;
            cursor: pointer; border: none; background: none;
        }
        .main-header .nav-link:hover { background: #f8f9fa; }
        .navbar-left { flex: 1; display: flex; align-items: center; }
        .navbar-right { display: flex; align-items: center; gap: 4px; }
        .navbar-badge {
            font-size: 10px; font-weight: 700; padding: 2px 5px;
            border-radius: 10px; background: var(--danger); color: #fff;
            position: absolute; top: 2px; right: 2px;
        }
        /* Dropdown */
        .dropdown { position: relative; }
        .dropdown-menu-adminlte {
            display: none; position: absolute; right: 0; top: 100%;
            background: #fff; border: 1px solid #dee2e6; border-radius: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,.15); min-width: 200px; z-index: 1050;
            padding: 4px 0;
        }
        .dropdown.show .dropdown-menu-adminlte { display: block; }
        .dropdown-item-adminlte {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 16px; color: #495057; font-size: 14px;
            border: none; background: none; width: 100%; text-align: left; cursor: pointer;
        }
        .dropdown-item-adminlte:hover { background: #f8f9fa; }
        .dropdown-divider-adminlte { border-top: 1px solid #dee2e6; margin: 4px 0; }

        /* === SIDEBAR === */
        .main-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1035;
            overflow-x: hidden;
            transition: width .3s ease;
            display: flex; flex-direction: column;
        }
        .brand-link {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px;
            height: var(--navbar-height);
            border-bottom: 1px solid rgba(255,255,255,.1);
            color: #fff !important;
            font-weight: 700; font-size: 16px;
            white-space: nowrap;
        }
        .brand-link i { font-size: 22px; }
        .sidebar { padding: 8px 0; overflow-y: auto; flex: 1; }
        .user-panel {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            margin-bottom: 8px;
        }
        .user-panel .user-avatar {
            width: 34px; height: 34px; border-radius: 50%; border: 2px solid rgba(255,255,255,.3);
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; flex-shrink: 0;
        }
        .user-panel .info { color: #fff; font-size: 13px; line-height: 1.3; overflow: hidden; }
        .user-panel .info .user-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-panel .info small { color: var(--sidebar-color); font-size: 11px; }
        .nav-header {
            padding: 6px 16px 4px;
            font-size: 11px; font-weight: 700; letter-spacing: .8px;
            color: rgba(255,255,255,.4); text-transform: uppercase;
        }
        .nav-sidebar { list-style: none; padding: 0; margin: 0; }
        .nav-sidebar .nav-item { position: relative; }
        .nav-sidebar .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px;
            color: var(--sidebar-color);
            border-radius: 0;
            transition: background .2s, color .2s;
            white-space: nowrap;
            font-size: 14px;
        }
        .nav-sidebar .nav-link:hover { background: var(--sidebar-hover-bg); color: #fff; }
        .nav-sidebar .nav-link.active {
            background: var(--sidebar-active-bg); color: #fff;
            box-shadow: 2px 0 8px rgba(0,123,255,.4);
        }
        .nav-sidebar .nav-link .nav-icon { width: 18px; text-align: center; font-size: 14px; flex-shrink: 0; }
        .nav-sidebar .nav-link p { flex: 1; margin: 0; font-size: 14px; }

        /* === CONTENT WRAPPER === */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            min-height: calc(100vh - var(--navbar-height));
            background: var(--content-bg);
            transition: margin-left .3s ease;
            flex: 1;
            display: flex; flex-direction: column;
        }
        .content-header {
            padding: 20px 24px 10px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .content-header h1 { font-size: 20px; font-weight: 600; color: #343a40; margin: 0; }
        .breadcrumb {
            display: flex; gap: 4px; align-items: center;
            list-style: none; padding: 0; margin: 0;
            font-size: 13px;
        }
        .breadcrumb-item { color: #6c757d; }
        .breadcrumb-item a { color: var(--primary); }
        .breadcrumb-item + .breadcrumb-item::before { content: "/"; margin-right: 4px; color: #adb5bd; }
        .breadcrumb-item.active { color: #495057; }
        .content { padding: 0 24px 24px; flex: 1; }
        .container-fluid { width: 100%; }

        /* === FOOTER === */
        .main-footer {
            padding: 14px 24px;
            background: var(--navbar-bg);
            border-top: 1px solid var(--navbar-border);
            font-size: 13px; color: #6c757d;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* === CARDS === */
        .card {
            background: var(--card-bg);
            border-radius: 6px; box-shadow: var(--card-shadow);
            margin-bottom: 20px; border: 0;
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(0,0,0,.06);
            background: transparent;
        }
        .card-title { font-size: 15px; font-weight: 600; color: #343a40; margin: 0; }
        .card-body { padding: 16px; }
        .card-footer {
            padding: 10px 16px;
            border-top: 1px solid rgba(0,0,0,.06);
            background: rgba(0,0,0,.02);
            font-size: 13px; color: #6c757d;
        }
        .card-tools { display: flex; gap: 4px; }
        .btn-tool {
            background: none; border: none; cursor: pointer;
            color: #adb5bd; padding: 4px 6px; border-radius: 4px;
        }
        .btn-tool:hover { color: #495057; background: #f8f9fa; }
        .card.card-primary .card-header { border-top: 3px solid var(--primary); }
        .card.card-success .card-header { border-top: 3px solid var(--success); }
        .card.card-warning .card-header { border-top: 3px solid var(--warning); }
        .card.card-danger .card-header { border-top: 3px solid var(--danger); }
        .card.card-info .card-header { border-top: 3px solid var(--info); }

        /* === INFO BOX === */
        .info-box {
            display: flex; align-items: stretch;
            background: var(--card-bg);
            border-radius: 6px; box-shadow: var(--card-shadow);
            margin-bottom: 20px; min-height: 80px; overflow: hidden;
        }
        .info-box-icon {
            width: 70px; display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: #fff; flex-shrink: 0;
        }
        .info-box-content { padding: 10px 14px; flex: 1; }
        .info-box-text { font-size: 13px; color: #6c757d; display: block; }
        .info-box-number { font-size: 22px; font-weight: 700; color: #343a40; }
        .progress-adminlte { height: 4px; background: #e9ecef; border-radius: 2px; margin: 6px 0 4px; }
        .progress-bar-adminlte { height: 100%; border-radius: 2px; }
        .progress-description { font-size: 11px; color: #6c757d; }

        /* === TABLE === */
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table th {
            padding: 10px 14px; background: #f8f9fa;
            border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;
            text-align: left;
        }
        .table td { padding: 10px 14px; border-bottom: 1px solid #dee2e6; vertical-align: middle; }
        .table-striped tbody tr:nth-child(odd) { background: rgba(0,0,0,.02); }
        .table-hover tbody tr:hover { background: rgba(0,123,255,.04); }
        .table-sm th, .table-sm td { padding: 6px 10px; }

        /* === BUTTONS === */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 4px; border: 1px solid transparent;
            font-size: 13px; font-weight: 400; cursor: pointer;
            transition: opacity .15s, box-shadow .15s;
            white-space: nowrap; font-family: inherit;
        }
        .btn:hover { opacity: .85; }
        .btn-primary  { background: var(--primary);   color: #fff; border-color: var(--primary); }
        .btn-success  { background: var(--success);   color: #fff; border-color: var(--success); }
        .btn-warning  { background: var(--warning);   color: #212529; border-color: var(--warning); }
        .btn-danger   { background: var(--danger);    color: #fff; border-color: var(--danger); }
        .btn-info     { background: var(--info);      color: #fff; border-color: var(--info); }
        .btn-secondary{ background: var(--secondary); color: #fff; border-color: var(--secondary); }
        .btn-default  { background: #fff; color: #495057; border-color: #ced4da; }
        .btn-sm  { padding: 4px 10px; font-size: 12px; }
        .btn-lg  { padding: 10px 22px; font-size: 15px; }
        .btn-block { width: 100%; justify-content: center; }
        .btn-outline-primary { background: transparent; color: var(--primary); border-color: var(--primary); }
        .btn-outline-primary:hover { background: var(--primary); color: #fff; }
        .btn-outline-secondary { background: transparent; color: var(--secondary); border-color: var(--secondary); }
        .btn-outline-secondary:hover { background: var(--secondary); color: #fff; }

        /* === BADGE === */
        .badge {
            display: inline-block; padding: 3px 8px;
            font-size: 11px; font-weight: 600;
            border-radius: 10px; color: #fff;
        }
        .badge-primary, .bg-primary-badge  { background: var(--primary); }
        .badge-success, .bg-success-badge  { background: var(--success); }
        .badge-warning, .bg-warning-badge  { background: var(--warning); color: #212529; }
        .badge-danger,  .bg-danger-badge   { background: var(--danger); }
        .badge-info,    .bg-info-badge     { background: var(--info); }
        .badge-secondary, .bg-secondary-badge { background: var(--secondary); }

        /* === ALERT === */
        .alert {
            padding: 12px 16px; border-radius: 4px; margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 10px;
            border: 1px solid transparent; font-size: 14px;
        }
        .alert-success  { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .alert-warning  { background: #fff3cd; border-color: #ffeeba; color: #856404; }
        .alert-danger   { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .alert-info     { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .alert .alert-content { flex: 1; }
        .alert .btn-close-alert {
            background: none; border: none; cursor: pointer; color: inherit; opacity: .6;
            font-size: 18px; padding: 0; line-height: 1;
        }
        .alert .btn-close-alert:hover { opacity: 1; }

        /* === FORM === */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; }
        .form-control {
            display: block; width: 100%;
            padding: 7px 12px; font-size: 14px;
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
        select.form-control { cursor: pointer; }
        .form-text { font-size: 12px; color: #6c757d; margin-top: 4px; }

        /* === GRID === */
        .row { display: flex; flex-wrap: wrap; margin: 0 -12px; }
        [class^="col-"], [class*=" col-"] { padding: 0 12px; }
        .col-1  { width: 8.33%; }  .col-2  { width: 16.66%; }
        .col-3  { width: 25%; }    .col-4  { width: 33.33%; }
        .col-5  { width: 41.66%; } .col-6  { width: 50%; }
        .col-7  { width: 58.33%; } .col-8  { width: 66.66%; }
        .col-9  { width: 75%; }    .col-10 { width: 83.33%; }
        .col-11 { width: 91.66%; } .col-12 { width: 100%; }

        /* === PAGINATION === */
        .pagination { display: flex; gap: 4px; list-style: none; padding: 0; margin: 16px 0 0; justify-content: center; }
        .page-item a, .page-item span {
            display: flex; align-items: center; justify-content: center;
            min-width: 34px; height: 34px; padding: 0 8px;
            border: 1px solid #dee2e6; border-radius: 4px;
            font-size: 13px; color: #495057; background: #fff;
            cursor: pointer; transition: all .2s;
        }
        .page-item a:hover { background: #e9ecef; }
        .page-item.active span { background: var(--primary); border-color: var(--primary); color: #fff; }
        .page-item.disabled span { color: #adb5bd; cursor: not-allowed; }

        /* === UTILITIES === */
        .bg-primary { background-color: var(--primary) !important; }
        .bg-success { background-color: var(--success) !important; }
        .bg-warning { background-color: var(--warning) !important; }
        .bg-danger  { background-color: var(--danger)  !important; }
        .bg-info    { background-color: var(--info)    !important; }
        .bg-secondary { background-color: var(--secondary) !important; }
        .bg-light   { background-color: var(--light)   !important; }
        .text-primary { color: var(--primary) !important; }
        .text-success { color: var(--success) !important; }
        .text-danger  { color: var(--danger) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-info    { color: var(--info) !important; }
        .text-muted   { color: #6c757d !important; }
        .text-white   { color: #fff !important; }
        .text-center  { text-align: center; }
        .text-right   { text-align: right; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 24px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 16px; }
        .mt-4 { margin-top: 24px; }
        .me-2 { margin-right: 8px; }
        .ms-auto { margin-left: auto; }
        .p-0 { padding: 0 !important; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .d-flex { display: flex; }
        .d-none { display: none; }
        .d-block { display: block; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .justify-content-center { justify-content: center; }
        .gap-1 { gap: 4px; } .gap-2 { gap: 8px; } .gap-3 { gap: 16px; }
        .flex-wrap { flex-wrap: wrap; }
        .w-100 { width: 100%; }
        .fs-1 { font-size: 2rem; }
        .opacity-50 { opacity: .5; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .input-group { display: flex; }
        .input-group .form-control { border-radius: 0 4px 4px 0; }
        .input-group-text {
            padding: 7px 12px; background: #e9ecef; border: 1px solid #ced4da;
            border-radius: 4px 0 0 4px; border-right: 0; font-size: 14px; color: #495057;
            display: flex; align-items: center;
        }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 13px; color: #e83e8c; }

        /* === BOOTSTRAP COMPAT === */
        .form-select {
            display: block; width: 100%; padding: 7px 12px; font-size: 14px;
            border: 1px solid #ced4da; border-radius: 4px; background: #fff; color: #495057;
            transition: border-color .2s; font-family: inherit; cursor: pointer;
            appearance: auto;
        }
        .form-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,123,255,.15); }
        .form-select.is-invalid { border-color: var(--danger); }
        .btn-group { display: inline-flex; gap: 2px; }
        .btn-group .btn { border-radius: 4px; }
        .btn-group-sm .btn { padding: 4px 10px; font-size: 12px; }
        .table-dark th { background: #343a40; color: #fff; border-bottom-color: #454d55; }
        .me-2 { margin-right: 8px; }
        .me-3 { margin-right: 16px; }
        .g-2 > [class*="col-"] { padding-bottom: 8px; }
        .g-3 > [class*="col-"] { padding-bottom: 16px; }
        .align-items-end { align-items: flex-end; }
        .small { font-size: 12px; }
        hr { border: none; border-top: 1px solid #dee2e6; margin: 16px 0; }

        /* === RESPONSIVE === */
        @media (max-width: 992px) {
            :root { --sidebar-width: 0px; }
            .main-sidebar { transform: translateX(-250px); width: 250px; transition: transform .3s ease; }
            .main-sidebar.show { transform: translateX(0); }
            .main-header { left: 0 !important; }
            .content-wrapper { margin-left: 0 !important; }
            .col-3, .col-4 { width: 50%; }
            .col-6 { width: 100%; }
            .sidebar-overlay {
                position: fixed; inset: 0; background: rgba(0,0,0,.5);
                z-index: 1034; display: none;
            }
            .sidebar-overlay.show { display: block; }
        }
        @media (max-width: 576px) {
            .col-3, .col-4, .col-6 { width: 100%; }
            .content { padding: 0 12px 12px; }
            .content-header { padding: 16px 12px 8px; flex-wrap: wrap; gap: 8px; }
        }

        /* Collapsed sidebar (desktop) */
        .sidebar-collapsed .main-sidebar { width: var(--sidebar-collapsed-width); }
        .sidebar-collapsed .main-header { left: var(--sidebar-collapsed-width); }
        .sidebar-collapsed .content-wrapper { margin-left: var(--sidebar-collapsed-width); }
        .sidebar-collapsed .nav-sidebar .nav-link p,
        .sidebar-collapsed .user-panel .info,
        .sidebar-collapsed .brand-link span,
        .sidebar-collapsed .nav-header { display: none; }
        .sidebar-collapsed .user-panel { justify-content: center; padding: 12px 0; }
        .sidebar-collapsed .brand-link { justify-content: center; padding: 12px 0; }
        .sidebar-collapsed .nav-sidebar .nav-link { justify-content: center; padding: 10px 0; }

        /* === DATATABLES THEME === */
        div.dataTables_wrapper { font-size: 13px; }
        div.dt-layout-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; flex-wrap: wrap; gap: 8px; }
        div.dt-layout-row:last-child { border-top: 1px solid rgba(0,0,0,.06); }
        div.dt-layout-cell { padding: 0; }
        div.dt-search label, div.dt-length label { display: flex; align-items: center; gap: 6px; color: #495057; font-weight: normal; margin: 0; }
        div.dt-search input { border: 1px solid #ced4da; border-radius: 4px; padding: 5px 10px; font-size: 13px; font-family: inherit; outline: none; transition: border-color .15s; }
        div.dt-search input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,123,255,.1); }
        div.dt-length select { border: 1px solid #ced4da; border-radius: 4px; padding: 4px 8px; font-size: 13px; font-family: inherit; cursor: pointer; background: #fff; color: #495057; }
        div.dt-info { font-size: 13px; color: #6c757d; }
        div.dt-paging { display: flex; align-items: center; gap: 2px; }
        .dt-paging-button { display: inline-flex !important; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 8px !important; border: 1px solid #dee2e6 !important; border-radius: 4px !important; font-size: 13px; cursor: pointer; background: #fff !important; color: #495057 !important; margin: 0 1px; transition: all .15s; }
        .dt-paging-button:hover:not(.disabled) { background: #e9ecef !important; }
        .dt-paging-button.current { background: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important; }
        .dt-paging-button.disabled { color: #adb5bd !important; cursor: not-allowed; opacity: .6; }
        table.dataTable thead > tr > th.dt-orderable-asc, table.dataTable thead > tr > th.dt-orderable-desc { cursor: pointer; }
        table.dataTable thead > tr > th { padding: 10px 14px !important; }
        table.dataTable tbody > tr > td { padding: 10px 14px !important; }
        table.dataTable.no-footer { border-bottom: 1px solid #dee2e6; }
        table.dataTable { width: 100% !important; }

        @stack('styles')
    </style>
</head>
<body>
    <div class="wrapper" id="app">
        {{-- SIDEBAR OVERLAY (mobile) --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- NAVBAR --}}
        <nav class="main-header navbar">
            <div class="navbar-left">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" id="sidebarToggle" role="button" title="Toggle Sidebar">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="navbar-right">
                <div class="dropdown" id="userDropdown">
                    <a class="nav-link" role="button" onclick="toggleDropdown('userDropdown')">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ Auth::user()->name }}</span>
                        <i class="fas fa-caret-down" style="font-size:11px;"></i>
                    </a>
                    <div class="dropdown-menu-adminlte">
                        <a class="dropdown-item-adminlte" href="{{ route('password.change') }}">
                            <i class="fas fa-key"></i> Ubah Password
                        </a>
                        <div class="dropdown-divider-adminlte"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item-adminlte" style="color: var(--danger);">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        {{-- SIDEBAR --}}
        <aside class="main-sidebar" id="mainSidebar">
            <a href="{{ route('admin.dashboard') }}" class="brand-link">
                <i class="fas fa-truck"></i>
                <span>Shipment Otomotif</span>
            </a>
            <div class="sidebar">
                <div class="user-panel">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <small>{{ ucfirst(Auth::user()->level) }}</small>
                    </div>
                </div>

                <div class="nav-header">Navigasi</div>
                <nav>
                    <ul class="nav-sidebar">
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.shipments.index') }}" class="nav-link @if(request()->routeIs('admin.shipments.*')) active @endif">
                                <i class="nav-icon fas fa-truck"></i>
                                <p>Shipments</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.vendors.index') }}" class="nav-link @if(request()->routeIs('admin.vendors.*')) active @endif">
                                <i class="nav-icon fas fa-warehouse"></i>
                                <p>Vendor</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}" class="nav-link @if(request()->routeIs('admin.reports.*')) active @endif">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Laporan</p>
                            </a>
                        </li>
                        @if(auth()->user()->isSuperadmin())
                        <div class="nav-header">Superadmin</div>
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}" class="nav-link @if(request()->routeIs('admin.users.*')) active @endif">
                                <i class="nav-icon fas fa-users"></i>
                                <p>{{ auth()->user()->isSuperadmin() ? 'Kelola Users' : 'Kelola Vendor' }}</p>
                            </a>
                        </li>
                        <div class="nav-header">Dokumentasi</div>
                        <li class="nav-item">
                            <a href="{{ route('admin.docs.tsd') }}" class="nav-link @if(request()->routeIs('admin.docs.tsd')) active @endif">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>TSD</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.docs.user-guide-admin') }}" class="nav-link @if(request()->routeIs('admin.docs.user-guide-admin')) active @endif">
                                <i class="nav-icon fas fa-book"></i>
                                <p>Panduan Admin</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.docs.user-guide-vendor') }}" class="nav-link @if(request()->routeIs('admin.docs.user-guide-vendor')) active @endif">
                                <i class="nav-icon fas fa-book-open"></i>
                                <p>Panduan Vendor</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        {{-- CONTENT --}}
        <div class="content-wrapper">
            <div class="content-header">
                <h1>@yield('page-title', 'Dashboard')</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    @yield('breadcrumb')
                </ol>
            </div>

            <section class="content">
                <div class="container-fluid">
                    {{-- Flash Messages --}}
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
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="alert-content">
                                <ul style="margin:0; padding-left:16px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button class="btn-close-alert" onclick="this.closest('.alert').remove()">&times;</button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </section>

            {{-- FOOTER --}}
            <footer class="main-footer">
                <div>&copy; {{ date('Y') }} <strong>PT. Serasi Logistics Indonesia</strong></div>
                <div>Shipment Otomotif v5.2.0</div>
            </footer>
        </div>
    </div>

    <script>
    (function() {
        // Sidebar toggle
        const sidebar = document.getElementById('mainSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const isMobile = () => window.innerWidth <= 992;

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                if (isMobile()) {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                } else {
                    document.body.classList.toggle('sidebar-collapsed');
                }
            });
        }
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        // Dropdown toggle
        window.toggleDropdown = function(id) {
            const dd = document.getElementById(id);
            dd.classList.toggle('show');
        };
        document.addEventListener('click', function(e) {
            document.querySelectorAll('.dropdown.show').forEach(dd => {
                if (!dd.contains(e.target)) dd.classList.remove('show');
            });
        });
    })();
    </script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
