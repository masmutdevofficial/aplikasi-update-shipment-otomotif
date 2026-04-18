@extends('layouts.admin')

@section('title', 'Panduan Vendor — User Guide Vendor')
@section('page-title', 'Panduan Pengguna — Vendor')
@section('breadcrumb')
    <li class="breadcrumb-item">Dokumentasi</li>
    <li class="breadcrumb-item active">Panduan Vendor</li>
@endsection

@push('styles')
<style>
    .guide-section { margin-bottom: 2rem; }
    .guide-section h2 { font-size: 1.15rem; font-weight: 700; color: #343a40; border-bottom: 2px solid #007bff; padding-bottom: .4rem; margin-bottom: 1rem; }
    .guide-section h3 { font-size: .95rem; font-weight: 700; color: #495057; margin: 1rem 0 .5rem; }
    .guide-table th { background-color: #343a40; color: #fff; white-space: nowrap; font-size: 13px; }
    .guide-table td, .guide-table th { vertical-align: top; font-size: 13px; }
    .step-number { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #17a2b8; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; flex-shrink: 0; }
    .step-item { display: flex; gap: .6rem; align-items: flex-start; margin-bottom: .5rem; }
    .access-badge { font-size: 12px; padding: 3px 8px; border-radius: 12px; font-weight: 600; }
    .access-superadmin { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .access-admin { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }
    .access-vendor { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    .tip-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .warning-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .info-box-doc { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .position-badge { background: #e8f4fd; color: #004085; border: 1px solid #b8daff; border-radius: 4px; padding: 2px 8px; font-size: 12px; display: inline-block; }
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
            <span class="access-badge access-vendor ms-1">Vendor</span>
        </div>
    </div>
    <div class="card-body">
        <p class="mb-0 text-muted">Halaman ini menampilkan panduan penggunaan aplikasi dari perspektif <strong>Vendor</strong>. Admin dapat menggunakan halaman ini sebagai referensi untuk membantu Vendor yang mengalami kendala.</p>
    </div>
</div>

{{-- 1. Pengenalan --}}
<div class="card mb-4 guide-section" id="guide-1">
    <div class="card-header"><i class="fas fa-info-circle me-2"></i> 1. Pengenalan</div>
    <div class="card-body">
        <p>Aplikasi ini memungkinkan vendor dalam rantai pengiriman otomotif untuk melakukan <strong>scan VIN (Vehicle Identification Number)</strong> sebagai konfirmasi keberadaan kendaraan di titik posisi masing-masing.</p>

        <h3>Fitur yang Tersedia untuk Vendor</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Fitur</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Dashboard</td><td>Informasi status akun dan posisi vendor</td></tr>
                <tr><td>Scan VIN</td><td>Scan barcode/QR menggunakan kamera atau input manual (tersedia jika posisi sudah ditetapkan)</td></tr>
                <tr><td>Riwayat Scan</td><td>Daftar semua kendaraan yang sudah pernah discan oleh akun ini</td></tr>
                <tr><td>Ubah Password</td><td>Manajemen keamanan akun mandiri</td></tr>
                <tr><td>Panduan Penggunaan</td><td>Halaman ini — panduan operasional</td></tr>
            </tbody>
        </table>

        <h3>Posisi dalam Rantai Pengiriman</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Posisi</th><th>Titik Pengiriman</th></tr></thead>
            <tbody>
                <tr><td><span class="position-badge">AT Storage Port</span></td><td>Kendaraan tiba di Storage Port keberangkatan</td></tr>
                <tr><td><span class="position-badge">ATD Kapal (Loading)</span></td><td>Kendaraan berangkat — proses loading ke kapal</td></tr>
                <tr><td><span class="position-badge">ATA Kapal</span></td><td>Kendaraan tiba di kapal</td></tr>
                <tr><td><span class="position-badge">ATA Storage Port (Destination)</span></td><td>Kendaraan tiba di Storage Port tujuan</td></tr>
                <tr><td><span class="position-badge">AT PtD (Dooring)</span></td><td>Kendaraan tiba di titik dooring (pengiriman akhir)</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 2. Persyaratan --}}
<div class="card mb-4 guide-section" id="guide-2">
    <div class="card-header"><i class="fas fa-mobile-alt me-2"></i> 2. Persyaratan Penggunaan</div>
    <div class="card-body">
        <table class="table table-bordered guide-table">
            <thead><tr><th>Kategori</th><th>Persyaratan</th></tr></thead>
            <tbody>
                <tr>
                    <td>Perangkat</td>
                    <td>Smartphone, tablet, atau komputer/laptop dengan kamera yang berfungsi</td>
                </tr>
                <tr>
                    <td>Browser</td>
                    <td>
                        Google Chrome (direkomendasikan)<br>
                        Mozilla Firefox, Microsoft Edge (didukung)<br>
                        <em class="text-muted">Versi terbaru sangat dianjurkan untuk dukungan kamera optimal</em>
                    </td>
                </tr>
                <tr>
                    <td>Kamera</td>
                    <td>Diperlukan untuk fitur Scan VIN. Izinkan akses kamera saat browser meminta.</td>
                </tr>
                <tr>
                    <td>Koneksi Internet</td>
                    <td>Koneksi stabil diperlukan. Minimal 2G untuk penggunaan dasar.</td>
                </tr>
            </tbody>
        </table>
        <div class="tip-box"><strong>Tips:</strong> Jika fitur Scan VIN tidak muncul di menu, hubungi Admin untuk memastikan posisi vendor sudah ditetapkan pada akun Anda.</div>
    </div>
</div>

{{-- 3. Login & Logout --}}
<div class="card mb-4 guide-section" id="guide-3">
    <div class="card-header"><i class="fas fa-sign-in-alt me-2"></i> 3. Login & Logout</div>
    <div class="card-body">
        <h3>Cara Login</h3>
        <div class="step-item"><span class="step-number">1</span><span>Buka browser dan akses URL aplikasi yang diberikan oleh Admin.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Masukkan <strong>Email</strong> dan <strong>Password</strong> akun Anda.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Klik tombol <strong>Masuk</strong>. Sistem akan mengarahkan ke dashboard Vendor.</span></div>
        <div class="warning-box"><strong>Perhatian:</strong> Percobaan login yang gagal maksimal <strong>5 kali</strong>. Setelah itu akun akan terkunci sementara. Hubungi Admin jika terkena lockout.</div>

        <h3>Cara Logout</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik nama akun Anda di pojok kanan atas.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Pilih opsi <strong>Logout</strong> dari menu dropdown.</span></div>
        <div class="info-box-doc">Selalu logout setelah selesai bekerja, terutama jika menggunakan perangkat bersama.</div>

        <h3>Lupa Password</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik tautan <strong>Lupa Password</strong> di halaman login.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Masukkan alamat email yang terdaftar dan klik <strong>Kirim</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Buka email Anda — cari tautan reset password (berlaku 60 menit).</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Klik tautan reset, masukkan password baru, lalu klik <strong>Simpan</strong>.</span></div>
    </div>
</div>

{{-- 4. Dashboard --}}
<div class="card mb-4 guide-section" id="guide-4">
    <div class="card-header"><i class="fas fa-tachometer-alt me-2"></i> 4. Dashboard Vendor</div>
    <div class="card-body">
        <p>Dashboard menampilkan ringkasan informasi akun Anda setelah login.</p>

        <h3>Kondisi Posisi Belum Ditetapkan</h3>
        <p>Jika posisi belum ditetapkan oleh Admin, dashboard akan menampilkan notifikasi: <em>"Posisi vendor Anda belum ditetapkan. Hubungi Admin."</em></p>
        <div class="warning-box">Menu <strong>Scan VIN</strong> dan <strong>Riwayat Scan</strong> <strong>tidak akan muncul</strong> di navigasi sampai Admin menetapkan posisi pada akun Anda.</div>

        <h3>Kondisi Posisi Sudah Ditetapkan</h3>
        <p>Dashboard menampilkan:</p>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Informasi</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Posisi Anda</td><td>Posisi dalam rantai pengiriman (contoh: AT Storage Port)</td></tr>
                <tr><td>Total Scan</td><td>Jumlah kendaraan yang sudah Anda scan</td></tr>
                <tr><td>Scan Hari Ini</td><td>Jumlah scan yang dilakukan pada hari ini</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 5. Scan VIN --}}
<div class="card mb-4 guide-section" id="guide-5">
    <div class="card-header"><i class="fas fa-camera me-2"></i> 5. Scan Kendaraan (Scan VIN)</div>
    <div class="card-body">
        <div class="info-box-doc">Fitur ini hanya tersedia jika posisi vendor sudah ditetapkan oleh Admin.</div>

        <h3>Cara Scan Menggunakan Kamera</h3>
        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, klik <strong>Scan VIN</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Izinkan akses kamera saat browser meminta (klik <strong>Allow/Izinkan</strong>).</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Arahkan kamera ke barcode/label VIN pada kendaraan.</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Klik tombol <strong>Capture</strong> untuk mengambil gambar.</span></div>
        <div class="step-item"><span class="step-number">5</span><span>Sistem akan memproses gambar dan mengekstrak nomor VIN.</span></div>
        <div class="step-item"><span class="step-number">6</span><span>Periksa nomor VIN yang ditampilkan. Jika benar, klik <strong>Konfirmasi</strong>.</span></div>
        <div class="step-item"><span class="step-number">7</span><span>Sistem menyimpan data dan menampilkan konfirmasi scan berhasil.</span></div>

        <h3>Cara Input VIN Manual</h3>
        <div class="step-item"><span class="step-number">1</span><span>Pada halaman Scan VIN, pilih tab atau tombol <strong>Input Manual</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Ketik nomor VIN kendaraan (tepat 17 karakter alfanumerik).</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Klik <strong>Cari</strong> untuk memverifikasi nomor VIN ada di sistem.</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Jika ditemukan, klik <strong>Konfirmasi</strong> untuk menyimpan scan.</span></div>

        <h3>Format VIN</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Aturan</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Panjang</td><td>Tepat <strong>17 karakter</strong></td></tr>
                <tr><td>Karakter yang valid</td><td>Huruf <code>A-Z</code> (kecuali I, O, Q) dan angka <code>0-9</code></td></tr>
                <tr><td>Huruf kecil</td><td>Otomatis dikonversi ke huruf besar</td></tr>
            </tbody>
        </table>

        <h3>Tips Scan Kamera yang Baik</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Kondisi</th><th>Rekomendasi</th></tr></thead>
            <tbody>
                <tr><td>Pencahayaan kurang</td><td>Aktifkan flash/senter atau cari area yang lebih terang</td></tr>
                <tr><td>Gambar buram</td><td>Stabilkan perangkat, dekatkan ke label VIN (jarak 15–30 cm)</td></tr>
                <tr><td>OCR gagal / VIN salah</td><td>Coba ambil gambar ulang atau gunakan input manual</td></tr>
                <tr><td>Label VIN kotor/rusak</td><td>Bersihkan label, gunakan input manual jika perlu</td></tr>
            </tbody>
        </table>

        <h3>Aturan Scan</h3>
        <div class="warning-box"><strong>1 kendaraan hanya bisa discan 1 kali per posisi.</strong> Sistem akan menolak scan duplikat untuk kombinasi No. Rangka + Posisi yang sudah pernah discan sebelumnya.</div>
    </div>
</div>

{{-- 6. Riwayat Scan --}}
<div class="card mb-4 guide-section" id="guide-6">
    <div class="card-header"><i class="fas fa-history me-2"></i> 6. Riwayat Scan</div>
    <div class="card-body">
        <p>Menu <strong>Riwayat Scan</strong> menampilkan daftar seluruh kendaraan yang pernah Anda scan, lengkap dengan tanggal dan No. Rangka.</p>

        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, klik <strong>Riwayat Scan</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Tabel akan menampilkan daftar scan dengan kolom: No. Rangka, Posisi, Tanggal Scan.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Gunakan fitur pencarian DataTables untuk menemukan kendaraan tertentu.</span></div>

        <div class="info-box-doc">Riwayat Scan bersifat <strong>hanya baca</strong>. Data scan tidak dapat diedit atau dihapus dari sini. Hubungi Admin jika ada data yang perlu dikoreksi.</div>
    </div>
</div>

{{-- 7. Ubah Password --}}
<div class="card mb-4 guide-section" id="guide-7">
    <div class="card-header"><i class="fas fa-key me-2"></i> 7. Pengaturan Akun</div>
    <div class="card-body">
        <h3>Ubah Password</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik nama akun di pojok kanan atas.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Pilih <strong>Ubah Password</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Isi Password Lama, Password Baru, dan Konfirmasi Password Baru.</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Klik <strong>Simpan</strong>.</span></div>

        <h3>Ketentuan Password Vendor</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Syarat</th><th>Ketentuan</th></tr></thead>
            <tbody>
                <tr><td>Panjang minimal</td><td><strong>12 karakter</strong></td></tr>
                <tr><td>Huruf kecil</td><td>✅ Wajib</td></tr>
                <tr><td>Huruf besar</td><td>✅ Wajib</td></tr>
                <tr><td>Angka</td><td>✅ Wajib</td></tr>
                <tr><td>Simbol</td><td>✅ Wajib</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 8. FAQ --}}
<div class="card mb-4 guide-section" id="guide-8">
    <div class="card-header"><i class="fas fa-question-circle me-2"></i> 8. FAQ — Pertanyaan yang Sering Diajukan</div>
    <div class="card-body">
        <table class="table table-bordered guide-table">
            <thead><tr><th>Pertanyaan</th><th>Jawaban</th></tr></thead>
            <tbody>
                <tr>
                    <td>Menu Scan VIN tidak muncul di navigasi. Kenapa?</td>
                    <td>Posisi Anda belum ditetapkan. Hubungi Admin untuk penetapan posisi vendor.</td>
                </tr>
                <tr>
                    <td>VIN kendaraan tidak terbaca / salah saat scan.</td>
                    <td>Coba ambil foto ulang dengan cahaya lebih baik, atau gunakan input manual.</td>
                </tr>
                <tr>
                    <td>Muncul pesan "VIN tidak ditemukan".</td>
                    <td>No. Rangka belum ada di sistem. Hubungi Admin untuk mendaftarkannya terlebih dahulu.</td>
                </tr>
                <tr>
                    <td>Muncul pesan "Kendaraan sudah pernah discan di posisi ini".</td>
                    <td>Scan duplikat terdeteksi. Satu kendaraan hanya dapat discan 1 kali per posisi.</td>
                </tr>
                <tr>
                    <td>Akun saya tidak bisa login / terkunci.</td>
                    <td>Hubungi Admin untuk membuka kunci akun Anda.</td>
                </tr>
                <tr>
                    <td>Kamera tidak bisa diakses.</td>
                    <td>Pastikan izin kamera sudah diberikan di pengaturan browser. Coba refresh halaman.</td>
                </tr>
                <tr>
                    <td>Saya tidak menerima email reset password.</td>
                    <td>Periksa folder Spam/Junk. Jika tidak ada, hubungi Admin.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 9. Bantuan & Kontak --}}
<div class="card mb-4 guide-section" id="guide-9">
    <div class="card-header"><i class="fas fa-headset me-2"></i> 9. Bantuan & Kontak</div>
    <div class="card-body">
        <p>Jika mengalami kendala yang tidak tercakup dalam panduan ini, hubungi Admin atau Superadmin melalui saluran resmi yang disediakan perusahaan.</p>
        <div class="info-box-doc"><strong>Catatan untuk Admin:</strong> Pastikan vendor memiliki nomor kontak Admin yang dapat dihubungi di luar jam kerja untuk kendala darurat saat proses pengiriman aktif.</div>
    </div>
</div>

@endsection
