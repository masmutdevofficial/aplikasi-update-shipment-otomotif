@extends('layouts.vendor')

@section('title', 'Panduan Penggunaan')
@section('page-title', 'Panduan Penggunaan')
@section('breadcrumb')
    <li class="breadcrumb-item">Bantuan</li>
    <li class="breadcrumb-item active">Panduan Penggunaan</li>
@endsection

@push('styles')
<style>
    .guide-section { margin-bottom: 2rem; }
    .guide-section h2 { font-size: 1.15rem; font-weight: 700; color: #1a3a5c; border-bottom: 2px solid #17a2b8; padding-bottom: .4rem; margin-bottom: 1rem; }
    .guide-section h3 { font-size: .95rem; font-weight: 700; color: #495057; margin: 1rem 0 .5rem; }
    .guide-table th { background-color: #1a3a5c; color: #fff; white-space: nowrap; font-size: 13px; }
    .guide-table td, .guide-table th { vertical-align: top; font-size: 13px; }
    .step-number { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #17a2b8; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; flex-shrink: 0; }
    .step-item { display: flex; gap: .6rem; align-items: flex-start; margin-bottom: .5rem; }
    .tip-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .warning-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .info-box-doc { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: .75rem 1rem; border-radius: 0 4px 4px 0; margin: .75rem 0; font-size: 13px; }
    .position-badge { background: #e8f4fd; color: #004085; border: 1px solid #b8daff; border-radius: 4px; padding: 2px 8px; font-size: 12px; display: inline-block; }
    .toc-nav { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
    .toc-nav h6 { font-weight: 700; color: #1a3a5c; margin-bottom: .5rem; }
    .toc-nav ul { margin: 0; padding-left: 1.25rem; }
    .toc-nav ul li { font-size: 13px; margin-bottom: .2rem; }
    .toc-nav ul li a { color: #17a2b8; text-decoration: none; }
    .toc-nav ul li a:hover { text-decoration: underline; }
</style>
@endpush

@section('content')

{{-- TOC --}}
<div class="toc-nav">
    <h6><i class="fas fa-list me-2"></i>Daftar Isi</h6>
    <ul>
        <li><a href="#guide-1">1. Pengenalan</a></li>
        <li><a href="#guide-2">2. Persyaratan Penggunaan</a></li>
        <li><a href="#guide-3">3. Login & Logout</a></li>
        <li><a href="#guide-4">4. Dashboard</a></li>
        <li><a href="#guide-5">5. Scan Kendaraan (Scan VIN)</a></li>
        <li><a href="#guide-6">6. Riwayat Scan</a></li>
        <li><a href="#guide-7">7. Pengaturan Akun</a></li>
        <li><a href="#guide-8">8. FAQ</a></li>
        <li><a href="#guide-9">9. Bantuan & Kontak</a></li>
    </ul>
</div>

{{-- 1. Pengenalan --}}
<div class="card mb-4 guide-section" id="guide-1">
    <div class="card-header"><i class="fas fa-info-circle me-2"></i> 1. Pengenalan</div>
    <div class="card-body">
        <p>Selamat datang di <strong>Aplikasi Update Shipment Otomotif</strong>. Aplikasi ini memungkinkan Anda sebagai vendor dalam rantai pengiriman kendaraan untuk melakukan <strong>scan VIN</strong> sebagai bukti keberadaan kendaraan di posisi Anda.</p>

        <h3>Apa yang Bisa Anda Lakukan</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Fitur</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Dashboard</td><td>Informasi status akun dan posisi Anda dalam rantai pengiriman</td></tr>
                <tr><td>Scan VIN</td><td>Scan nomor rangka kendaraan (VIN) menggunakan kamera atau input manual</td></tr>
                <tr><td>Riwayat Scan</td><td>Daftar semua kendaraan yang pernah Anda scan</td></tr>
                <tr><td>Ubah Password</td><td>Ganti password akun Anda secara mandiri</td></tr>
                <tr><td>Panduan Penggunaan</td><td>Halaman ini — panduan lengkap penggunaan aplikasi</td></tr>
            </tbody>
        </table>

        <h3>Posisi Anda dalam Rantai Pengiriman</h3>
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
                    <td>Browser yang Direkomendasikan</td>
                    <td>
                        Google Chrome (direkomendasikan)<br>
                        Mozilla Firefox, Microsoft Edge (didukung)
                    </td>
                </tr>
                <tr>
                    <td>Kamera</td>
                    <td>Diperlukan untuk fitur Scan VIN. Izinkan akses kamera saat browser meminta.</td>
                </tr>
                <tr>
                    <td>Koneksi Internet</td>
                    <td>Koneksi stabil. Minimal 2G untuk penggunaan dasar.</td>
                </tr>
            </tbody>
        </table>
        <div class="tip-box"><strong>Tips:</strong> Untuk pengalaman terbaik, gunakan smartphone dengan browser Chrome versi terbaru dan pastikan kamera dalam kondisi bersih.</div>
    </div>
</div>

{{-- 3. Login & Logout --}}
<div class="card mb-4 guide-section" id="guide-3">
    <div class="card-header"><i class="fas fa-sign-in-alt me-2"></i> 3. Login & Logout</div>
    <div class="card-body">
        <h3>Cara Login</h3>
        <div class="step-item"><span class="step-number">1</span><span>Buka browser dan akses URL aplikasi yang diberikan oleh Admin.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Masukkan <strong>Email</strong> dan <strong>Password</strong> akun Anda.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Klik tombol <strong>Masuk</strong>. Sistem akan mengarahkan ke dashboard.</span></div>
        <div class="warning-box"><strong>Perhatian:</strong> Login yang gagal maksimal <strong>5 kali</strong>. Setelah itu akun Anda akan terkunci sementara. Hubungi Admin untuk membuka kunci.</div>

        <h3>Cara Logout</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik nama akun Anda di pojok kanan atas.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Pilih <strong>Logout</strong>.</span></div>
        <div class="info-box-doc">Selalu logout setelah selesai bekerja, terutama pada perangkat yang digunakan bersama.</div>

        <h3>Lupa Password</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik <strong>Lupa Password</strong> di halaman login.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Masukkan alamat email terdaftar Anda, lalu klik <strong>Kirim</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Buka email Anda dan klik tautan reset password (berlaku <strong>60 menit</strong>).</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Masukkan password baru, lalu klik <strong>Simpan</strong>.</span></div>
    </div>
</div>

{{-- 4. Dashboard --}}
<div class="card mb-4 guide-section" id="guide-4">
    <div class="card-header"><i class="fas fa-tachometer-alt me-2"></i> 4. Dashboard</div>
    <div class="card-body">
        <p>Dashboard menampilkan ringkasan informasi akun dan aktivitas Anda.</p>

        <h3>Jika Posisi Belum Ditetapkan</h3>
        <p>Dashboard akan menampilkan pesan:</p>
        <div class="warning-box"><em>"Posisi vendor Anda belum ditetapkan. Hubungi Admin untuk menetapkan posisi."</em><br><br>
        Menu <strong>Scan VIN</strong> dan <strong>Riwayat Scan</strong> tidak akan tampil sampai Admin menetapkan posisi Anda.</div>

        <h3>Jika Posisi Sudah Ditetapkan</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Informasi di Dashboard</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Posisi Anda</td><td>Posisi Anda dalam rantai pengiriman</td></tr>
                <tr><td>Total Scan</td><td>Jumlah kendaraan yang sudah Anda scan</td></tr>
                <tr><td>Scan Hari Ini</td><td>Jumlah scan pada hari ini</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 5. Scan VIN --}}
<div class="card mb-4 guide-section" id="guide-5">
    <div class="card-header"><i class="fas fa-camera me-2"></i> 5. Scan Kendaraan (Scan VIN)</div>
    <div class="card-body">
        <div class="info-box-doc">Menu ini hanya tersedia jika posisi Anda sudah ditetapkan oleh Admin.</div>

        <h3>Scan Menggunakan Kamera</h3>
        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, klik <strong>Scan VIN</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Saat browser meminta izin kamera, klik <strong>Izinkan / Allow</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Arahkan kamera ke label/barcode VIN pada kendaraan.</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Klik tombol <strong>Capture</strong> untuk mengambil gambar.</span></div>
        <div class="step-item"><span class="step-number">5</span><span>Sistem memproses gambar dan menampilkan nomor VIN yang terdeteksi.</span></div>
        <div class="step-item"><span class="step-number">6</span><span>Periksa nomor VIN. Jika sudah benar, klik <strong>Konfirmasi</strong>.</span></div>
        <div class="step-item"><span class="step-number">7</span><span>Scan berhasil — data tersimpan.</span></div>

        <h3>Input VIN Manual</h3>
        <div class="step-item"><span class="step-number">1</span><span>Di halaman Scan VIN, pilih <strong>Input Manual</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Ketik nomor VIN kendaraan (tepat 17 karakter).</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Klik <strong>Cari</strong>.</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Jika kendaraan ditemukan, klik <strong>Konfirmasi</strong>.</span></div>

        <h3>Format VIN yang Valid</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Aturan</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Panjang</td><td>Tepat <strong>17 karakter</strong></td></tr>
                <tr><td>Karakter valid</td><td>Huruf <code>A–Z</code> (kecuali I, O, Q) dan angka <code>0–9</code></td></tr>
                <tr><td>Contoh VIN valid</td><td><code>MHF5AB3G5P5001234</code></td></tr>
            </tbody>
        </table>

        <h3>Tips agar Scan Berhasil</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Masalah</th><th>Solusi</th></tr></thead>
            <tbody>
                <tr><td>Kurang cahaya</td><td>Gunakan senter atau cari area lebih terang</td></tr>
                <tr><td>Gambar buram</td><td>Stabilkan tangan, dekatkan ke label (15–30 cm)</td></tr>
                <tr><td>OCR gagal / VIN salah</td><td>Coba ambil foto ulang atau pakai input manual</td></tr>
                <tr><td>Label kotor / rusak</td><td>Bersihkan label, gunakan input manual jika perlu</td></tr>
            </tbody>
        </table>

        <div class="warning-box"><strong>Penting:</strong> Setiap kendaraan hanya dapat discan <strong>1 kali</strong> pada posisi Anda. Scan duplikat akan ditolak oleh sistem.</div>
    </div>
</div>

{{-- 6. Riwayat Scan --}}
<div class="card mb-4 guide-section" id="guide-6">
    <div class="card-header"><i class="fas fa-history me-2"></i> 6. Riwayat Scan</div>
    <div class="card-body">
        <p>Lihat daftar seluruh kendaraan yang pernah Anda scan.</p>

        <div class="step-item"><span class="step-number">1</span><span>Dari menu navigasi, klik <strong>Riwayat Scan</strong>.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Tabel menampilkan: No. Rangka, Posisi, dan Tanggal Scan.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Gunakan fitur pencarian untuk menemukan kendaraan tertentu.</span></div>

        <div class="info-box-doc">Data riwayat scan hanya bisa dilihat, tidak bisa diedit. Jika ada koreksi, hubungi Admin.</div>
    </div>
</div>

{{-- 7. Pengaturan Akun --}}
<div class="card mb-4 guide-section" id="guide-7">
    <div class="card-header"><i class="fas fa-key me-2"></i> 7. Pengaturan Akun</div>
    <div class="card-body">
        <h3>Ubah Password</h3>
        <div class="step-item"><span class="step-number">1</span><span>Klik nama akun di pojok kanan atas.</span></div>
        <div class="step-item"><span class="step-number">2</span><span>Pilih <strong>Ubah Password</strong>.</span></div>
        <div class="step-item"><span class="step-number">3</span><span>Isi Password Lama, Password Baru, dan Konfirmasi Password Baru.</span></div>
        <div class="step-item"><span class="step-number">4</span><span>Klik <strong>Simpan</strong>.</span></div>

        <h3>Syarat Password Vendor</h3>
        <table class="table table-bordered guide-table">
            <thead><tr><th>Syarat</th><th>Ketentuan</th></tr></thead>
            <tbody>
                <tr><td>Panjang minimal</td><td><strong>12 karakter</strong></td></tr>
                <tr><td>Huruf kecil (a–z)</td><td>✅ Wajib</td></tr>
                <tr><td>Huruf besar (A–Z)</td><td>✅ Wajib</td></tr>
                <tr><td>Angka (0–9)</td><td>✅ Wajib</td></tr>
                <tr><td>Simbol (!, @, #, dll)</td><td>✅ Wajib</td></tr>
            </tbody>
        </table>
        <div class="tip-box">Contoh password kuat: <code>Vendor@Serasi2026!!</code></div>
    </div>
</div>

{{-- 8. FAQ --}}
<div class="card mb-4 guide-section" id="guide-8">
    <div class="card-header"><i class="fas fa-question-circle me-2"></i> 8. Pertanyaan yang Sering Diajukan (FAQ)</div>
    <div class="card-body">
        <table class="table table-bordered guide-table">
            <thead><tr><th>Pertanyaan</th><th>Jawaban</th></tr></thead>
            <tbody>
                <tr>
                    <td>Menu Scan VIN tidak muncul. Kenapa?</td>
                    <td>Posisi Anda belum ditetapkan. Hubungi Admin untuk penetapan posisi.</td>
                </tr>
                <tr>
                    <td>VIN kendaraan tidak terbaca saat scan kamera.</td>
                    <td>Coba foto ulang dengan pencahayaan lebih baik, atau gunakan input manual.</td>
                </tr>
                <tr>
                    <td>Muncul "VIN tidak ditemukan".</td>
                    <td>No. Rangka belum ada di sistem. Hubungi Admin untuk mendaftarkannya.</td>
                </tr>
                <tr>
                    <td>Muncul "Kendaraan sudah pernah discan".</td>
                    <td>Scan duplikat terdeteksi. Satu kendaraan hanya bisa discan 1 kali per posisi.</td>
                </tr>
                <tr>
                    <td>Akun terkunci / tidak bisa login.</td>
                    <td>Hubungi Admin untuk membuka kunci akun.</td>
                </tr>
                <tr>
                    <td>Kamera tidak bisa diakses.</td>
                    <td>Pastikan izin kamera sudah diberikan di pengaturan browser, lalu refresh halaman.</td>
                </tr>
                <tr>
                    <td>Tidak menerima email reset password.</td>
                    <td>Periksa folder Spam/Junk. Jika tidak ada, hubungi Admin.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 9. Bantuan --}}
<div class="card mb-4 guide-section" id="guide-9">
    <div class="card-header"><i class="fas fa-headset me-2"></i> 9. Bantuan & Kontak</div>
    <div class="card-body">
        <p>Jika mengalami kendala yang tidak tercakup dalam panduan ini, segera hubungi Admin melalui saluran resmi yang disediakan perusahaan.</p>
        <div class="info-box-doc"><strong>Catatan:</strong> Simpan nomor kontak Admin Anda agar mudah dihubungi saat mengalami kendala, terutama saat proses pengiriman aktif di lapangan.</div>
    </div>
</div>

@endsection
