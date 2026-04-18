@extends('layouts.admin')

@section('title', 'Panduan Admin — System User Guide')
@section('page-title', 'Panduan Pengguna — Admin & Superadmin')
@section('breadcrumb')
    <li class="breadcrumb-item">Dokumentasi</li>
    <li class="breadcrumb-item active">Panduan Admin</li>
@endsection

@push('styles')
<style>
    .guide-section { margin-bottom: 2rem; }
    .guide-section h2 { font-size: 1.15rem; font-weight: 700; color: #343a40; border-bottom: 2px solid #007bff; padding-bottom: .4rem; margin-bottom: 1rem; }
    .guide-section h3 { font-size: .95rem; font-weight: 700; color: #495057; margin: 1rem 0 .5rem; }
    .guide-table th { background-color: #343a40; color: #fff; white-space: nowrap; font-size: 13px; }
    .guide-table td, .guide-table th { vertical-align: top; font-size: 13px; }
    .step-number { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #007bff; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; flex-shrink: 0; }
    .step-item { display: flex; gap: .6rem; align-items: flex-start; margin-bottom: .5rem; }
    .access-badge { font-size: 12px; padding: 3px 8px; border-radius: 12px; font-weight: 600; }
    .access-superadmin { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .access-admin { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }
    .tip-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .warning-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .info-box-doc { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
</style>
@endpush

@section('content')

{{-- Header Info --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-book-open me-2"></i> Informasi Dokumen</span>
        <div>
            <span class="access-badge access-superadmin">Superadmin</span>
            <span class="access-badge access-admin ms-1">Admin</span>
        </div>
    </div>
    <div class="card-body">
        <p class="mb-0 text-muted">Panduan ini ditujukan untuk pengguna level <strong>Admin</strong> dan <strong>Superadmin</strong>. Mencakup cara mengelola user, data shipment, data vendor, laporan, serta pengaturan keamanan akun.</p>
    </div>
</div>

{{-- 1. Pengenalan --}}
<div class="card mb-4 guide-section" id="guide-1">
    <div class="card-header"><i class="fas fa-info-circle me-2"></i> 1. Pengenalan Sistem</div>
    <div class="card-body">
        <p>Aplikasi Update Shipment Otomotif adalah sistem berbasis web untuk mencatat, memantau, dan memperbarui status pengiriman kendaraan dari PDC hingga tujuan akhir. Vendor di setiap titik pengiriman melakukan scan VIN, dan sistem mencatat tanggal secara otomatis.</p>

        <h3>Fitur Utama untuk Admin</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Fitur</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Manajemen User</td><td>Tambah, edit, hapus, toggle aktif/nonaktif akun pengguna</td></tr>
                <tr><td>Data Keberangkatan Unit</td><td>Input dan kelola data shipment kendaraan (13 field)</td></tr>
                <tr><td>Data Vendor</td><td>Kelola vendor dan penetapan posisi dalam rantai pengiriman</td></tr>
                <tr><td>Laporan</td><td>Lihat, filter rentang tanggal, dan export ke Excel</td></tr>
            </tbody>
        </table>

        <h3>Hak Akses Berdasarkan Level</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Fitur</th><th>Superadmin</th><th>Admin</th></tr></thead>
            <tbody>
                <tr><td>Kelola user semua level</td><td class="text-center text-success">✅</td><td class="text-center text-danger">❌</td></tr>
                <tr><td>Kelola user level Vendor</td><td class="text-center text-success">✅</td><td class="text-center text-success">✅</td></tr>
                <tr><td>Input Data Keberangkatan Unit</td><td class="text-center text-success">✅</td><td class="text-center text-success">✅</td></tr>
                <tr><td>Kelola Data Vendor</td><td class="text-center text-success">✅</td><td class="text-center text-success">✅</td></tr>
                <tr><td>Lihat Laporan & Export Excel</td><td class="text-center text-success">✅</td><td class="text-center text-success">✅</td></tr>
                <tr><td>Akses halaman dokumentasi TSD</td><td class="text-center text-success">✅</td><td class="text-center text-success">✅</td></tr>
            </tbody>
        </table>

        <div class="info-box-doc"><strong>Catatan Superadmin:</strong> Minimal harus ada 1 akun Superadmin aktif di sistem. Sistem <strong>tidak mengizinkan</strong> penghapusan Superadmin terakhir.</div>
    </div>
</div>

{{-- 2. Login & Logout --}}
<div class="card mb-4 guide-section" id="guide-2">
    <div class="card-header"><i class="fas fa-sign-in-alt me-2"></i> 2. Login & Logout</div>
    <div class="card-body">
        <h3>Cara Login</h3>
        <div class="step-item"><span class="step-number">1</span><span>Buka browser dan akses URL aplikasi.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Masukkan <strong>Email</strong> dan <strong>Password</strong> akun Anda.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Klik tombol <strong>Masuk</strong>. Sistem akan mengarahkan ke dashboard Admin.</span></div>
        <div class="warning-box"><strong>Perhatian:</strong> Percobaan login yang gagal maksimal <strong>5 kali</strong>. Setelah itu akun akan terkunci sementara. Hubungi Superadmin jika terkena lockout.</div>

        <h3>Cara Logout</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik nama akun Anda di pojok kanan atas.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Pilih opsi <strong>Logout</strong> dari menu dropdown.</span></div>

        <h3>Lupa Password</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik tautan <strong>Lupa Password</strong> di halaman login.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Masukkan alamat email yang terdaftar dan klik <strong>Kirim</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Buka email Anda dan klik tautan reset password (berlaku 60 menit).</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Masukkan password baru sesuai ketentuan. Klik <strong>Simpan</strong>.</span></div>
    </div>
</div>

{{-- 3. Manajemen User --}}
<div class="card mb-4 guide-section" id="guide-3">
    <div class="card-header"><i class="fas fa-users me-2"></i> 3. Manajemen Pengguna</div>
    <div class="card-body">

        <h3>Melihat Daftar User</h3>
        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, pilih <strong>Kelola Users</strong> (Superadmin) atau <strong>Kelola Vendor</strong> (Admin).</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Gunakan fitur pencarian DataTables untuk mencari berdasarkan nama, username, atau email.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Atur jumlah data per halaman menggunakan dropdown di atas tabel.</span></div>

        <h3>Menambah User Baru</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik tombol <strong>+ Tambah User</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Isi formulir:</span></div>
        <table class="table table-bordered guide-table ms-4">
            <thead><tr><th>Field</th><th>Ketentuan</th></tr></thead>
            <tbody>
                <tr><td>Username</td><td>Unik, alfanumerik + titik/underscore/strip</td></tr>
                <tr><td>Email</td><td>Format email valid, unik</td></tr>
                <tr><td>No HP</td><td>Format nomor telepon valid</td></tr>
                <tr><td>Nama</td><td>Nama lengkap pengguna</td></tr>
                <tr><td>Level</td><td>Superadmin / Admin / Vendor (sesuai hak akses)</td></tr>
                <tr><td>Password</td><td>Min 16 karakter (Admin/Superadmin) atau 12 karakter (Vendor)</td></tr>
            </tbody>
        </table>
        <div class="step-item"><span class="step-number">3</span><span>Klik <strong>Simpan</strong>.</span></div>
        <div class="tip-box"><strong>Catatan Admin:</strong> Admin hanya dapat menambah user dengan level <strong>Vendor</strong>. Untuk menambah Admin atau Superadmin, hubungi Superadmin.</div>

        <h3>Mengedit User</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik tombol <strong>Edit</strong> pada baris user yang ingin diubah.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Ubah data yang diperlukan. Password bersifat opsional saat edit (kosongkan jika tidak ingin diubah).</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Klik <strong>Simpan Perubahan</strong>.</span></div>

        <h3>Menonaktifkan / Mengaktifkan User</h3>
        <div class="step-item"><span class="step-number">1</span><span>Pada daftar user, klik tombol <strong>Edit</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Ubah field <strong>Status</strong> menjadi <code>Tidak Aktif</code> atau <code>Aktif</code>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Klik <strong>Simpan Perubahan</strong>.</span></div>
        <div class="info-box-doc">User yang dinonaktifkan akan otomatis dikeluarkan dari sesi aktif mereka.</div>

        <h3>Menghapus User</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik tombol <strong>Hapus</strong> pada baris user.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Konfirmasi pada dialog yang muncul. Klik <strong>Ya, Hapus</strong>.</span></div>
        <div class="warning-box"><strong>Penting:</strong> Sistem <strong>tidak mengizinkan</strong> penghapusan Superadmin terakhir. Pastikan ada minimal 1 Superadmin lain sebelum menghapus.</div>
    </div>
</div>

{{-- 4. Data Shipment --}}
<div class="card mb-4 guide-section" id="guide-4">
    <div class="card-header"><i class="fas fa-truck me-2"></i> 4. Kelola Data Keberangkatan Unit</div>
    <div class="card-body">

        <h3>Menambah Data Keberangkatan Baru</h3>
        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, pilih <strong>Shipments</strong>, lalu klik <strong>+ Tambah Data</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Isi seluruh field formulir:</span></div>
        <table class="table table-bordered guide-table ms-4">
            <thead><tr><th>Field</th><th>Format / Ketentuan</th></tr></thead>
            <tbody>
                <tr><td>Lokasi</td><td>Kode lokasi (contoh: <code>D730</code>)</td></tr>
                <tr><td>No DO</td><td>Nomor Delivery Order</td></tr>
                <tr><td>Type Kendaraan</td><td>Jenis/model kendaraan (contoh: <code>AYLA</code>, <code>ROCKY</code>)</td></tr>
                <tr><td>No Rangka (VIN)</td><td><strong>Tepat 17 karakter</strong>, regex <code>[A-HJ-NPR-Z0-9]{17}</code></td></tr>
                <tr><td>No. Engine</td><td>Nomor mesin kendaraan</td></tr>
                <tr><td>Warna</td><td>Warna unit (contoh: <code>WHITE DSO</code>)</td></tr>
                <tr><td>Asal PDC</td><td>Nama kota asal PDC (contoh: <code>SUNTER</code>)</td></tr>
                <tr><td>Kota</td><td>Kota asal pengiriman</td></tr>
                <tr><td>Tujuan Pengiriman</td><td>Kota tujuan</td></tr>
                <tr><td>Terima DO</td><td>Tanggal (format: <code>DD-Mon-YY</code>)</td></tr>
                <tr><td>Keluar dari PDC</td><td>Tanggal (format: <code>DD-Mon-YY</code>)</td></tr>
                <tr><td>Nama Kapal</td><td>Nama kapal pengangkut</td></tr>
                <tr><td>Keberangkatan Kapal</td><td>Tanggal (format: <code>DD-Mon-YY</code>)</td></tr>
            </tbody>
        </table>
        <div class="step-item"><span class="step-number">3</span><span>Klik <strong>Simpan</strong>.</span></div>
        <div class="info-box-doc">Format tanggal: <code>DD-Mon-YY</code> — contoh: <code>02-Apr-26</code> berarti 2 April 2026.</div>

        <h3>Mengedit Data</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik tombol <strong>Edit</strong> pada baris data yang ingin diubah.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Lakukan perubahan yang diperlukan, lalu klik <strong>Simpan Perubahan</strong>.</span></div>

        <h3>Menghapus Data</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik tombol <strong>Hapus</strong> pada baris data.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Konfirmasi penghapusan pada dialog. Klik <strong>Ya, Hapus</strong>.</span></div>
    </div>
</div>

{{-- 5. Data Vendor --}}
<div class="card mb-4 guide-section" id="guide-5">
    <div class="card-header"><i class="fas fa-warehouse me-2"></i> 5. Kelola Data Vendor</div>
    <div class="card-body">

        <h3>Menambah Data Vendor</h3>
        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, pilih <strong>Vendor</strong>, lalu klik <strong>+ Tambah Vendor</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Isi formulir:</span></div>
        <table class="table table-bordered guide-table ms-4">
            <thead><tr><th>Field</th><th>Ketentuan</th></tr></thead>
            <tbody>
                <tr><td>User</td><td>Pilih user dengan level Vendor yang sudah terdaftar dan belum terhubung ke vendor lain</td></tr>
                <tr><td>Nama Vendor</td><td>Otomatis terisi dari data user yang dipilih</td></tr>
                <tr><td>Posisi</td><td>Pilih salah satu dari 5 posisi pengiriman</td></tr>
            </tbody>
        </table>

        <h3>Pilihan Posisi Vendor</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Posisi</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>AT Storage Port</td><td>Kendaraan tiba di Storage Port keberangkatan</td></tr>
                <tr><td>ATD Kapal (Loading)</td><td>Kendaraan berangkat naik kapal (proses loading)</td></tr>
                <tr><td>ATA Kapal</td><td>Kendaraan tiba di kapal</td></tr>
                <tr><td>ATA Storage Port (Destination)</td><td>Kendaraan tiba di Storage Port tujuan</td></tr>
                <tr><td>AT PtD (Dooring)</td><td>Kendaraan tiba di titik dooring (pengiriman akhir)</td></tr>
            </tbody>
        </table>

        <div class="step-item"><span class="step-number">3</span><span>Klik <strong>Simpan</strong>.</span></div>
        <div class="tip-box">Menghapus data vendor <strong>tidak</strong> menghapus akun user terkait.</div>
    </div>
</div>

{{-- 6. Laporan --}}
<div class="card mb-4 guide-section" id="guide-6">
    <div class="card-header"><i class="fas fa-chart-bar me-2"></i> 6. Laporan & Export</div>
    <div class="card-body">

        <h3>Melihat & Memfilter Laporan</h3>
        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, pilih <strong>Laporan</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Sistem menampilkan seluruh data shipment + update posisi semua vendor dalam satu tabel.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Untuk memfilter, isi <strong>Dari Tanggal</strong> dan <strong>Sampai Tanggal</strong>, lalu klik <strong>Terapkan Filter</strong>.</span></div>

        <h3>Export ke Excel</h3>
        <div class="step-item"><span class="step-number">1</span><span>Terapkan filter sesuai kebutuhan (opsional).</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Klik tombol <strong>Export Excel</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>File <code>.xlsx</code> akan otomatis terunduh.</span></div>

        <h3>Kolom File Export</h3>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered guide-table">
                    <thead><tr><th>No.</th><th>Kolom (Shipment)</th></tr></thead>
                    <tbody>
                        @foreach(['Lokasi','No DO','Type Kendaraan','No Rangka','No. Engine','Warna','Asal PDC','Kota','Tujuan Pengiriman','Terima DO','Keluar dari PDC','Nama Kapal','Keberangkatan Kapal'] as $i => $col)
                        <tr><td>{{ $i+1 }}</td><td>{{ $col }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered guide-table">
                    <thead><tr><th>No.</th><th>Kolom (Vendor Update)</th></tr></thead>
                    <tbody>
                        @foreach(['AT Storage Port','ATD Kapal (Loading)','ATA Kapal','ATA Storage Port (Destination)','AT PtD (Dooring)','Link Dokumen'] as $i => $col)
                        <tr><td>{{ $i+14 }}</td><td>{{ $col }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 7. Pengaturan Akun --}}
<div class="card mb-4 guide-section" id="guide-7">
    <div class="card-header"><i class="fas fa-user-cog me-2"></i> 7. Pengaturan Akun</div>
    <div class="card-body">
        <h3>Ubah Password</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik nama akun di pojok kanan atas.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Pilih <strong>Ubah Password</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Isi Password Lama, Password Baru, dan Konfirmasi Password Baru.</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Klik <strong>Simpan</strong>.</span></div>

        <h3>Ketentuan Password Admin / Superadmin</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Syarat</th><th>Ketentuan</th></tr></thead>
            <tbody>
                <tr><td>Panjang minimal</td><td><strong>16 karakter</strong></td></tr>
                <tr><td>Huruf kecil</td><td>✅ Wajib (a–z)</td></tr>
                <tr><td>Huruf besar</td><td>✅ Wajib (A–Z)</td></tr>
                <tr><td>Angka</td><td>✅ Wajib (0–9)</td></tr>
                <tr><td>Simbol</td><td>✅ Wajib (!, @, #, $, dll)</td></tr>
                <tr><td>Pola mudah ditebak</td><td>❌ Dilarang</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 8. Keamanan --}}
<div class="card mb-4 guide-section" id="guide-8">
    <div class="card-header"><i class="fas fa-shield-alt me-2"></i> 8. Panduan Keamanan</div>
    <div class="card-body">
        <table class="table table-bordered guide-table">
            <thead><tr><th>Praktik Keamanan</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Jangan bagikan password</td><td>Kepada siapapun, termasuk sesama admin</td></tr>
                <tr><td>Ganti password berkala</td><td>Minimal setiap 90 hari</td></tr>
                <tr><td>Selalu logout</td><td>Terutama pada perangkat bersama atau publik</td></tr>
                <tr><td>Laporkan akses mencurigakan</td><td>Segera hubungi Superadmin jika akun diduga dikompromikan</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 9. Troubleshooting --}}
<div class="card mb-4 guide-section" id="guide-9">
    <div class="card-header"><i class="fas fa-tools me-2"></i> 9. Troubleshooting</div>
    <div class="card-body">
        <h3>Tidak Bisa Login</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Gejala</th><th>Solusi</th></tr></thead>
            <tbody>
                <tr><td>Password salah</td><td>Pastikan Caps Lock tidak aktif. Coba kembali.</td></tr>
                <tr><td>Akun terkunci (lockout)</td><td>Tunggu beberapa menit, atau hubungi Superadmin untuk unlock.</td></tr>
                <tr><td>Akun tidak aktif</td><td>Hubungi Superadmin untuk mengaktifkan kembali akun.</td></tr>
                <tr><td>Lupa password</td><td>Gunakan fitur <strong>Lupa Password</strong> di halaman login.</td></tr>
            </tbody>
        </table>

        <h3>Data Tidak Tersimpan</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Gejala</th><th>Solusi</th></tr></thead>
            <tbody>
                <tr><td>Muncul pesan validasi merah</td><td>Periksa dan perbaiki field yang bermasalah sesuai petunjuk.</td></tr>
                <tr><td>Halaman tidak merespons</td><td>Refresh browser. Jika berlanjut, hubungi tim teknis.</td></tr>
            </tbody>
        </table>

        <h3>Export Excel Gagal / Kosong</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Gejala</th><th>Solusi</th></tr></thead>
            <tbody>
                <tr><td>File tidak terunduh</td><td>Pastikan popup blocker browser tidak memblokir unduhan.</td></tr>
                <tr><td>File kosong</td><td>Pastikan data tersedia di rentang tanggal yang dipilih.</td></tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
