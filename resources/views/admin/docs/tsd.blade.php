@extends('layouts.admin')

@section('title', 'TSD — Technical Specification Document')
@section('page-title', 'Technical Specification Document (TSD)')
@section('breadcrumb')
    <li class="breadcrumb-item">Dokumentasi</li>
    <li class="breadcrumb-item active">TSD</li>
@endsection

@push('styles')
<style>
    .tsd-section { margin-bottom: 2rem; }
    .tsd-section h2 { font-size: 1.2rem; font-weight: 700; color: #343a40; border-bottom: 2px solid #007bff; padding-bottom: .4rem; margin-bottom: 1rem; }
    .tsd-section h3 { font-size: 1rem; font-weight: 700; color: #495057; margin: 1rem 0 .5rem; }
    .tsd-table th { background-color: #343a40; color: #fff; white-space: nowrap; }
    .tsd-table td, .tsd-table th { vertical-align: top; font-size: 13px; }
    .badge-implemented { background-color: #28a745; color: #fff; }
    .badge-partial { background-color: #ffc107; color: #343a40; }
    .badge-planned { background-color: #6c757d; color: #fff; }
    .badge-na { background-color: #dee2e6; color: #495057; }
    .evidence-tag { font-size: 11px; background: #e9f5ff; color: #0056b3; border: 1px solid #b8daff; border-radius: 3px; padding: 1px 6px; display: inline-block; margin: 1px; }
    .doc-info-table td:first-child { font-weight: 600; width: 180px; }
    .toc-list { column-count: 2; column-gap: 2rem; }
    .toc-list a { color: #007bff; }
    pre.flow-diagram { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 1rem; font-size: 12px; overflow-x: auto; }
</style>
@endpush

@section('content')

{{-- Document Header --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-alt me-2"></i> Informasi Dokumen</span>
        <span class="badge" style="background:#007bff;color:#fff;font-size:13px;">Versi 1.0</span>
    </div>
    <div class="card-body">
        <table class="table table-sm table-borderless doc-info-table mb-0">
            <tbody>
                <tr><td>Nama Proyek</td><td>Aplikasi Update Shipment Otomotif</td></tr>
                <tr><td>Klien</td><td>PT. Serasi Logistics Indonesia</td></tr>
                <tr><td>Status Dokumen</td><td><span class="badge badge-implemented">Final</span></td></tr>
                <tr><td>Tanggal</td><td>April 2026</td></tr>
                <tr><td>Akses</td><td>Superadmin & Admin</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Legend --}}
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-info-circle me-2"></i> Keterangan Status</div>
    <div class="card-body d-flex gap-3 flex-wrap">
        <span><span class="badge badge-implemented">Implemented</span> — Sudah diimplementasikan</span>
        <span><span class="badge badge-partial">Partial</span> — Diimplementasikan sebagian</span>
        <span><span class="badge badge-planned">Planned</span> — Direncanakan / fase lanjutan</span>
        <span><span class="badge badge-na">N/A</span> — Tidak berlaku</span>
    </div>
</div>

{{-- 1. Pendahuluan --}}
<div class="card mb-4 tsd-section" id="section-1">
    <div class="card-header"><i class="fas fa-bookmark me-2"></i> 1. Pendahuluan</div>
    <div class="card-body">
        <h3>1.1 Latar Belakang</h3>
        <p>Aplikasi Update Shipment Otomotif merupakan sistem berbasis web yang dikembangkan untuk PT. Serasi Logistics Indonesia guna mendukung proses pengelolaan, pemantauan, dan pembaruan data shipment pada lingkungan operasional otomotif secara terstruktur, terdokumentasi, dan aman.</p>

        <h3>1.2 Tujuan</h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Tujuan</th><th>Status</th><th>Notes</th></tr></thead>
            <tbody>
                <tr><td>Sistem terpusat pencatatan & pembaruan data shipment</td><td><span class="badge badge-implemented">Implemented</span></td><td>Modul Shipment CRUD + scan VIN vendor</td></tr>
                <tr><td>Meningkatkan akurasi & konsistensi data operasional</td><td><span class="badge badge-implemented">Implemented</span></td><td>Validasi input ketat, UUID, unique constraint no_rangka</td></tr>
                <tr><td>Jejak audit atas setiap perubahan data</td><td><span class="badge badge-implemented">Implemented</span></td><td>Field <code>created_by</code>, <code>updated_by</code> di semua tabel utama</td></tr>
                <tr><td>Standar keamanan aplikasi yang memadai</td><td><span class="badge badge-implemented">Implemented</span></td><td>OWASP Top 10, Argon2id, AES-256, throttling, CSRF</td></tr>
                <tr><td>Dokumentasi teknis & panduan penggunaan</td><td><span class="badge badge-implemented">Implemented</span></td><td>TSD, System User Guide, User Guide Vendor tersedia</td></tr>
            </tbody>
        </table>

        <h3>1.3 Ruang Lingkup</h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Item</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
                <tr><td>Aplikasi berbasis web</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">Laravel 13</span><span class="evidence-tag">PHP 8.3</span></td></tr>
                <tr><td>Modul autentikasi & manajemen pengguna</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthController</span><span class="evidence-tag">UserController</span></td></tr>
                <tr><td>Pengelolaan data shipment & scan VIN</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ShipmentController</span><span class="evidence-tag">ScannerController</span></td></tr>
                <tr><td>Validasi input & pengamanan data</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">FormRequests</span><span class="evidence-tag">Middleware</span></td></tr>
                <tr><td>Audit trail pada data utama</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">created_by</span><span class="evidence-tag">updated_by</span></td></tr>
                <tr><td>Deployment environment</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">.env.example</span><span class="evidence-tag">Docker Compose</span></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 2. Arsitektur --}}
<div class="card mb-4 tsd-section" id="section-2">
    <div class="card-header"><i class="fas fa-sitemap me-2"></i> 2. Arsitektur Sistem</div>
    <div class="card-body">
        <h3>2.1 Gambaran Umum</h3>
        <p>Sistem menggunakan arsitektur <strong>monolitik berbasis MVC</strong> dengan Laravel 13 sebagai backend framework.</p>
        <pre class="flow-diagram">
┌─────────────────────────────────────────────────┐
│                    BROWSER                      │
│     (HTML5 + Bootstrap 5.3.8 + JavaScript)      │
└─────────────────────┬───────────────────────────┘
                      │ HTTP/HTTPS
┌─────────────────────▼───────────────────────────┐
│            WEB SERVER (Nginx/Apache)             │
└─────────────────────┬───────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────┐
│         LARAVEL 13 APPLICATION (PHP 8.3+)        │
│  Routes → Middleware → Controllers → Services   │
│  ┌─────────────────────────────────────────┐    │
│  │  OCR (Tesseract + Intervention Image)   │    │
│  └─────────────────────────────────────────┘    │
└─────────────────────┬───────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────┐
│                 MySQL 8.4 LTS                    │
└─────────────────────────────────────────────────┘</pre>

        <h3>2.2 Komponen Utama</h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Komponen</th><th>Teknologi</th><th>Status</th><th>Notes</th></tr></thead>
            <tbody>
                <tr><td>Web Server</td><td>Nginx / Apache</td><td><span class="badge badge-implemented">Implemented</span></td><td>Termasuk dalam .env APP_URL</td></tr>
                <tr><td>Backend</td><td>PHP 8.3 / Laravel 13</td><td><span class="badge badge-implemented">Implemented</span></td><td>composer.json: <code>laravel/framework ^13.0</code></td></tr>
                <tr><td>Frontend</td><td>Bootstrap 5.3.8 + DataTables 2.3.7</td><td><span class="badge badge-implemented">Implemented</span></td><td>Embedded di layout blade, DataTables via npm lokal</td></tr>
                <tr><td>Database</td><td>MySQL 8.4 LTS</td><td><span class="badge badge-implemented">Implemented</span></td><td>Docker Compose ready, migrations tersedia</td></tr>
                <tr><td>OCR Engine</td><td>Tesseract OCR</td><td><span class="badge badge-implemented">Implemented</span></td><td><code>thiagoalessio/tesseract_ocr</code> v2.13</td></tr>
                <tr><td>Image Processing</td><td>Intervention Image v4</td><td><span class="badge badge-implemented">Implemented</span></td><td>Grayscale, contrast, sharpen pre-processing</td></tr>
                <tr><td>Session & Auth</td><td>Laravel Session-based</td><td><span class="badge badge-implemented">Implemented</span></td><td>Session encrypt=true, secure cookie</td></tr>
                <tr><td>Email</td><td>Resend</td><td><span class="badge badge-implemented">Implemented</span></td><td><code>resend/resend-laravel</code> v1.3.2</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 3. Stack Teknologi --}}
<div class="card mb-4 tsd-section" id="section-3">
    <div class="card-header"><i class="fas fa-layer-group me-2"></i> 3. Stack Teknologi</div>
    <div class="card-body">
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Layer</th><th>Item</th><th>Versi</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
                <tr><td>Backend</td><td>PHP</td><td>8.3+</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">composer.json</span></td></tr>
                <tr><td>Backend</td><td>Laravel Framework</td><td>13.x</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">laravel/framework ^13.0</span></td></tr>
                <tr><td>Backend</td><td>Tesseract OCR wrapper</td><td>2.13</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">OcrService.php</span></td></tr>
                <tr><td>Backend</td><td>Intervention Image</td><td>4.x</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">intervention/image ^4.0</span></td></tr>
                <tr><td>Backend</td><td>Maatwebsite Excel</td><td>3.1+</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ShipmentExport.php</span></td></tr>
                <tr><td>Backend</td><td>Resend Mail</td><td>1.3.2</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">resend/resend-laravel</span></td></tr>
                <tr><td>Frontend</td><td>Bootstrap</td><td>5.3.8</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">layouts/*.blade.php</span></td></tr>
                <tr><td>Frontend</td><td>Font Awesome</td><td>6.4.0</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">CDN di semua layout</span></td></tr>
                <tr><td>Frontend</td><td>DataTables</td><td>2.3.7</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">public/vendor/datatables/</span></td></tr>
                <tr><td>Frontend</td><td>Browser MediaDevices API</td><td>Native</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">scanner.js</span></td></tr>
                <tr><td>Build</td><td>Vite</td><td>8.x</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">vite.config.js</span></td></tr>
                <tr><td>Database</td><td>MySQL</td><td>8.4 LTS</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">Docker Compose</span></td></tr>
                <tr><td>Database</td><td>UUID sebagai PK</td><td>—</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">HasUuids trait</span></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 4. Struktur Database --}}
<div class="card mb-4 tsd-section" id="section-4">
    <div class="card-header"><i class="fas fa-database me-2"></i> 4. Struktur Database</div>
    <div class="card-body">
        <p class="text-muted small mb-3">Semua tabel menggunakan <code>uuid</code> sebagai primary identifier. Format tanggal tampil: <code>DD-Mon-YY</code> (contoh: <code>02-Apr-26</code>).</p>

        <h3>4.1 Tabel <code>users</code></h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Kolom</th><th>Tipe</th><th>Keterangan</th><th>Status</th></tr></thead>
            <tbody>
                <tr><td><code>id</code></td><td>CHAR(36) UUID</td><td>Primary Key</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>username</code></td><td>VARCHAR(100)</td><td>Unik</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>name</code></td><td>VARCHAR(150)</td><td>Nama lengkap</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>email</code></td><td>VARCHAR(150)</td><td>Unik</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>phone</code></td><td>VARCHAR(500)</td><td>Terenkripsi AES-256</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>password</code></td><td>VARCHAR(255)</td><td>Hash Argon2id</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>level</code></td><td>ENUM</td><td>superadmin / admin / vendor</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>is_active</code></td><td>BOOLEAN</td><td>Default true</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>created_by</code> / <code>updated_by</code></td><td>UUID FK</td><td>Audit trail → users.id</td><td><span class="badge badge-implemented">✓</span></td></tr>
            </tbody>
        </table>

        <h3>4.2 Tabel <code>vendors</code></h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Kolom</th><th>Tipe</th><th>Keterangan</th><th>Status</th></tr></thead>
            <tbody>
                <tr><td><code>id</code></td><td>CHAR(36) UUID</td><td>Primary Key</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>user_id</code></td><td>UUID FK</td><td>Unique → users.id (cascade delete)</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>vendor_name</code></td><td>VARCHAR(150)</td><td>Nama vendor</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>position</code></td><td>ENUM (5 nilai)</td><td>Posisi dalam rantai pengiriman</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>created_by</code> / <code>updated_by</code></td><td>UUID FK</td><td>Audit trail</td><td><span class="badge badge-implemented">✓</span></td></tr>
            </tbody>
        </table>

        <h3>4.3 Tabel <code>shipments</code></h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Kolom</th><th>Tipe</th><th>Keterangan</th><th>Status</th></tr></thead>
            <tbody>
                <tr><td><code>id</code></td><td>UUID PK</td><td>Primary Key</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>no_rangka</code></td><td>VARCHAR(17)</td><td>VIN 17 digit, unique</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td>lokasi, no_do, type_kendaraan, no_engine, warna, asal_pdc, kota, tujuan_pengiriman, nama_kapal</td><td>VARCHAR</td><td>Data shipment</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td>terima_do, keluar_dari_pdc, keberangkatan_kapal</td><td>DATE</td><td>Tanggal-tanggal kunci</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>created_by</code> / <code>updated_by</code></td><td>UUID FK</td><td>Audit trail</td><td><span class="badge badge-implemented">✓</span></td></tr>
            </tbody>
        </table>

        <h3>4.4 Tabel <code>shipment_updates</code></h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Kolom</th><th>Tipe</th><th>Keterangan</th><th>Status</th></tr></thead>
            <tbody>
                <tr><td><code>shipment_id</code></td><td>UUID FK</td><td>→ shipments.id</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>vendor_id</code></td><td>UUID FK</td><td>→ vendors.id</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>position</code></td><td>ENUM</td><td>Posisi saat scan</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>scan_date</code></td><td>DATE</td><td>Tanggal scan</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>document_link</code></td><td>TEXT</td><td>Link dokumen (opsional)</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td>Unique constraint</td><td>—</td><td>[shipment_id, position] — 1 scan per posisi</td><td><span class="badge badge-implemented">✓</span></td></tr>
            </tbody>
        </table>

        <h3>4.5 Tabel <code>scan_histories</code> & <code>failed_logins</code></h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Tabel</th><th>Kegunaan</th><th>Status</th></tr></thead>
            <tbody>
                <tr><td><code>scan_histories</code></td><td>Riwayat per-user: user_id, no_rangka, scan_date</td><td><span class="badge badge-implemented">✓</span></td></tr>
                <tr><td><code>failed_logins</code></td><td>Throttling login: email, ip, attempts, locked_until</td><td><span class="badge badge-implemented">✓</span></td></tr>
            </tbody>
        </table>

        <h3>4.6 ERD Summary</h3>
        <pre class="flow-diagram">
users (1) ─────────── (N) vendors          [user_id]
users (1) ─────────── (N) shipments        [created_by / updated_by]
shipments (1) ──────── (N) shipment_updates [shipment_id]
vendors (1) ─────────── (N) shipment_updates [vendor_id]
users (1) ─────────── (N) scan_histories   [user_id]</pre>
    </div>
</div>

{{-- 5. Alur Proses --}}
<div class="card mb-4 tsd-section" id="section-5">
    <div class="card-header"><i class="fas fa-project-diagram me-2"></i> 5. Desain Teknis & Alur Proses</div>
    <div class="card-body">
        <h3>5.1 Alur Autentikasi</h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Langkah</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
                <tr><td>Login email + password</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthController@login</span></td></tr>
                <tr><td>Throttling maks 5x gagal → lockout</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthService</span><span class="evidence-tag">failed_logins table</span></td></tr>
                <tr><td>Session regeneration setelah login</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">session()->regenerate()</span></td></tr>
                <tr><td>Redirect berdasarkan level</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthService::redirectAfterLogin</span></td></tr>
                <tr><td>Forgot/reset password via email</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">PasswordController</span><span class="evidence-tag">Resend</span></td></tr>
                <tr><td>Change password</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">PasswordController@changePassword</span></td></tr>
            </tbody>
        </table>

        <h3>5.2 Alur Scan VIN (Vendor)</h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Langkah</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
                <tr><td>Kamera browser via MediaDevices API</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">scanner.js</span></td></tr>
                <tr><td>Capture gambar → base64 → kirim ke backend</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ScannerController@scan</span></td></tr>
                <tr><td>Preprocessing gambar (Intervention Image)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">OcrService::preprocessImage</span></td></tr>
                <tr><td>Ekstraksi VIN 17 digit via Tesseract</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">OcrService::extractVin</span></td></tr>
                <tr><td>Validasi regex VIN <code>/^[A-HJ-NPR-Z0-9]{17}$/</code></td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ScannerController@confirm</span></td></tr>
                <tr><td>Cek duplikasi scan (shipment_id + position)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">unique constraint DB</span></td></tr>
                <tr><td>Simpan ke shipment_updates + scan_histories</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">DB transaction</span></td></tr>
                <tr><td>Input VIN manual sebagai alternatif OCR</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">Scanner form</span></td></tr>
            </tbody>
        </table>

        <h3>5.3 Alur Export Laporan</h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Langkah</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
                <tr><td>Filter tanggal & cari no_rangka</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ReportController@index</span></td></tr>
                <tr><td>Query gabungan shipments + shipment_updates</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ReportService</span></td></tr>
                <tr><td>Generate .xlsx via maatwebsite/excel</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ShipmentExport</span></td></tr>
                <tr><td>Kolom: 14 admin + 5 posisi vendor + 1 link dokumen</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ShipmentExport::headings</span></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 6. Spesifikasi Modul --}}
<div class="card mb-4 tsd-section" id="section-6">
    <div class="card-header"><i class="fas fa-cubes me-2"></i> 6. Spesifikasi Modul</div>
    <div class="card-body">
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Modul</th><th>Fitur</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
                <tr><td rowspan="6">Autentikasi</td><td>Login email + password</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthController</span></td></tr>
                <tr><td>Logout</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthController@logout</span></td></tr>
                <tr><td>Throttling & lockout</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthService</span></td></tr>
                <tr><td>Change password</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">PasswordController</span></td></tr>
                <tr><td>Forgot / reset password</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">PasswordController + Resend</span></td></tr>
                <tr><td>MFA</td><td><span class="badge badge-planned">Planned</span></td><td>Change request fase lanjutan</td></tr>

                <tr><td rowspan="5">Manajemen User</td><td>CRUD user (semua level) — Superadmin</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">UserController</span></td></tr>
                <tr><td>CRUD user level vendor — Admin</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">UserController</span></td></tr>
                <tr><td>Toggle aktif/nonaktif + invalidasi session</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">toggleStatus()</span></td></tr>
                <tr><td>Proteksi superadmin terakhir</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">UserService::canDelete</span></td></tr>
                <tr><td>Enkripsi nomor HP (AES-256)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">User::setPhoneAttribute</span></td></tr>

                <tr><td rowspan="2">Kelola Shipment</td><td>CRUD 13 field + 3 tanggal</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ShipmentController</span></td></tr>
                <tr><td>Validasi VIN regex + unique</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">StoreShipmentRequest</span></td></tr>

                <tr><td rowspan="2">Kelola Vendor</td><td>CRUD vendor + posisi</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">VendorController</span></td></tr>
                <tr><td>Relasi user_id (1-to-1 unique)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">vendors.user_id unique</span></td></tr>

                <tr><td rowspan="2">Scanner VIN</td><td>OCR kamera + input manual</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ScannerController</span></td></tr>
                <tr><td>Simpan ke shipment_updates + scan_histories</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">DB transaction</span></td></tr>

                <tr><td rowspan="3">Laporan & Export</td><td>Tabel gabungan shipment + vendor updates</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ReportService</span></td></tr>
                <tr><td>Filter tanggal</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ReportController</span></td></tr>
                <tr><td>Export .xlsx 20 kolom</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ShipmentExport</span></td></tr>

                <tr><td>Riwayat Scan</td><td>Daftar scan per vendor</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">HistoryController</span></td></tr>
                <tr><td>Dokumentasi</td><td>Halaman TSD, Panduan Admin, Panduan Vendor</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">admin.docs.*, vendor.docs.*</span></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 7. Keamanan --}}
<div class="card mb-4 tsd-section" id="section-7">
    <div class="card-header"><i class="fas fa-shield-alt me-2"></i> 7. Ketentuan Keamanan</div>
    <div class="card-body">
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Ketentuan</th><th>Status</th><th>Evidence / Notes</th></tr></thead>
            <tbody>
                <tr><td>Konfigurasi sensitif di <code>.env</code></td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">.env.example</span> tanpa credential nyata</td></tr>
                <tr><td>Password hash Argon2id</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">config/hashing.php</span> driver: bcrypt override → Argon2id</td></tr>
                <tr><td>Phone enkripsi AES-256</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">User model Crypt::encryptString</span></td></tr>
                <tr><td>CSRF protection semua form</td><td><span class="badge badge-implemented">Implemented</span></td><td>Laravel default + @csrf di semua form</td></tr>
                <tr><td>Login throttling (5x → lockout)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">AuthService + failed_logins</span></td></tr>
                <tr><td>Session encrypt + secure cookie</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">.env SESSION_ENCRYPT=true</span></td></tr>
                <tr><td>Route protection per level (Middleware)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">CheckLevel</span><span class="evidence-tag">CheckVendorStatus</span></td></tr>
                <tr><td>XSS prevention di scanner JS</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">escapeHtml()</span> function di scanner.js</td></tr>
                <tr><td>Max size base64 image (DoS prevention)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">max:3000000</span> validation rule</td></tr>
                <tr><td>VIN regex validation</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">/^[A-HJ-NPR-Z0-9]{17}$/</span> di Request + Controller</td></tr>
                <tr><td>MIME type validation upload gambar</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">OcrService::extractVin</span></td></tr>
                <tr><td>Input validasi di Report (search, date)</td><td><span class="badge badge-implemented">Implemented</span></td><td><span class="evidence-tag">ReportController</span></td></tr>
                <tr><td>composer audit: 0 kerentanan</td><td><span class="badge badge-implemented">Implemented</span></td><td>Dijalankan saat development</td></tr>
                <tr><td>npm audit: 0 kerentanan</td><td><span class="badge badge-implemented">Implemented</span></td><td>Dijalankan saat development</td></tr>
                <tr><td>MFA</td><td><span class="badge badge-planned">Planned</span></td><td>Change request fase lanjutan</td></tr>
            </tbody>
        </table>

        <h3>7.1 Kebijakan Password</h3>
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Level</th><th>Panjang Min</th><th>Syarat</th><th>Status</th></tr></thead>
            <tbody>
                <tr><td>Superadmin / Admin</td><td>16 karakter</td><td>Huruf kecil + besar + angka + simbol</td><td><span class="badge badge-implemented">Implemented</span></td></tr>
                <tr><td>Vendor</td><td>12 karakter</td><td>Huruf kecil + besar + angka + simbol</td><td><span class="badge badge-implemented">Implemented</span></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 8. Audit Trail --}}
<div class="card mb-4 tsd-section" id="section-8">
    <div class="card-header"><i class="fas fa-history me-2"></i> 8. Audit Trail</div>
    <div class="card-body">
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Field</th><th>Keterangan</th><th>Status</th><th>Tabel yang Memiliki</th></tr></thead>
            <tbody>
                <tr><td><code>created_by</code></td><td>UUID user yang membuat data</td><td><span class="badge badge-implemented">Implemented</span></td><td>users, vendors, shipments, shipment_updates</td></tr>
                <tr><td><code>created_at</code></td><td>Timestamp pembuatan</td><td><span class="badge badge-implemented">Implemented</span></td><td>Semua tabel</td></tr>
                <tr><td><code>updated_by</code></td><td>UUID user yang terakhir mengubah</td><td><span class="badge badge-implemented">Implemented</span></td><td>users, vendors, shipments, shipment_updates</td></tr>
                <tr><td><code>updated_at</code></td><td>Timestamp perubahan terakhir</td><td><span class="badge badge-implemented">Implemented</span></td><td>Semua tabel (kecuali scan_histories)</td></tr>
                <tr><td><code>deleted_by</code> / <code>deleted_at</code></td><td>Soft delete (opsional)</td><td><span class="badge badge-planned">Planned</span></td><td>Fase lanjutan</td></tr>
                <tr><td>Log histori perubahan field</td><td>Sebelum vs sesudah</td><td><span class="badge badge-planned">Planned</span></td><td>Fase lanjutan</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 9. Dokumentasi & Deliverable --}}
<div class="card mb-4 tsd-section" id="section-9">
    <div class="card-header"><i class="fas fa-box me-2"></i> 9. Dokumentasi & Deliverable</div>
    <div class="card-body">
        <table class="table table-bordered tsd-table">
            <thead><tr><th>Deliverable</th><th>Status</th><th>Lokasi / Notes</th></tr></thead>
            <tbody>
                <tr><td>Source code aplikasi</td><td><span class="badge badge-implemented">Implemented</span></td><td>Repository GitHub</td></tr>
                <tr><td>Database schema / migration files</td><td><span class="badge badge-implemented">Implemented</span></td><td><code>database/migrations/</code></td></tr>
                <tr><td>File konfigurasi contoh</td><td><span class="badge badge-implemented">Implemented</span></td><td><code>.env.example</code></td></tr>
                <tr><td>TSD (Technical Specification Document)</td><td><span class="badge badge-implemented">Implemented</span></td><td><code>docs/TSD_Technical_Specification_Document.md</code> + halaman ini</td></tr>
                <tr><td>System User Guide (Admin)</td><td><span class="badge badge-implemented">Implemented</span></td><td><code>docs/System_User_Guide.md</code> + halaman in-app</td></tr>
                <tr><td>User Guide (Vendor)</td><td><span class="badge badge-implemented">Implemented</span></td><td><code>docs/User_Guide_Vendor.md</code> + halaman in-app</td></tr>
                <tr><td>Hasil pengujian (PHPUnit)</td><td><span class="badge badge-implemented">Implemented</span></td><td>63 tests, 0 failure</td></tr>
                <tr><td>Aplikasi yang dapat dijalankan</td><td><span class="badge badge-implemented">Implemented</span></td><td>Docker Compose + <code>composer run setup</code></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 10. Kriteria Penerimaan --}}
<div class="card mb-4 tsd-section" id="section-10">
    <div class="card-header"><i class="fas fa-check-double me-2"></i> 10. Kriteria Penerimaan</div>
    <div class="card-body">
        <table class="table table-bordered tsd-table">
            <thead><tr><th>No.</th><th>Kriteria</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
                <tr><td>1</td><td>Berjalan sesuai fungsi utama yang disepakati</td><td><span class="badge badge-implemented">✓ Met</span></td><td>Semua modul implemented</td></tr>
                <tr><td>2</td><td>Seluruh input utama tervalidasi dengan baik</td><td><span class="badge badge-implemented">✓ Met</span></td><td>FormRequests + Controller validation</td></tr>
                <tr><td>3</td><td>Autentikasi, reset password, change password berjalan normal</td><td><span class="badge badge-implemented">✓ Met</span></td><td>AuthController, PasswordController + 10 AuthTest</td></tr>
                <tr><td>4</td><td>Audit trail minimal tersedia</td><td><span class="badge badge-implemented">✓ Met</span></td><td>created_by, updated_by di semua tabel utama</td></tr>
                <tr><td>5</td><td>Konfigurasi sensitif dipisahkan dari source code</td><td><span class="badge badge-implemented">✓ Met</span></td><td>.env + .gitignore</td></tr>
                <tr><td>6</td><td>Password tersimpan menggunakan hashing Argon2id</td><td><span class="badge badge-implemented">✓ Met</span></td><td>config/hashing.php</td></tr>
                <tr><td>7</td><td>Tidak ada issue kritikal yang menghambat operasional dasar</td><td><span class="badge badge-implemented">✓ Met</span></td><td>63 tests pass, 0 vulnerabilities</td></tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
