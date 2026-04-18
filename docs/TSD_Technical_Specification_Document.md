# Technical Specification Document (TSD)
## Aplikasi Update Shipment Otomotif
**PT. Serasi Logistics Indonesia**

---

| Informasi Dokumen | |
|---|---|
| Nama Proyek | Aplikasi Update Shipment Otomotif |
| Versi Dokumen | 1.0 |
| Status | Draft |
| Tanggal | April 2026 |
| Penyusun | Tim Pengembang |

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Stack Teknologi](#3-stack-teknologi)
4. [Struktur Database](#4-struktur-database)
5. [Desain Teknis & Alur Proses](#5-desain-teknis--alur-proses)
6. [Spesifikasi Modul](#6-spesifikasi-modul)
7. [Ketentuan Keamanan](#7-ketentuan-keamanan)
8. [Audit Trail](#8-audit-trail)
9. [Dokumentasi & Deliverable](#9-dokumentasi--deliverable)
10. [Kriteria Penerimaan](#10-kriteria-penerimaan)

---

## 1. Pendahuluan

### 1.1 Latar Belakang

Aplikasi Update Shipment Otomotif merupakan sistem berbasis web yang dikembangkan untuk PT. Serasi Logistics Indonesia guna mendukung proses pengelolaan, pemantauan, dan pembaruan data shipment pada lingkungan operasional otomotif secara terstruktur, terdokumentasi, dan aman.

### 1.2 Tujuan

- Menyediakan sistem terpusat untuk pencatatan dan pembaruan data shipment.
- Meningkatkan akurasi dan konsistensi data operasional.
- Menyediakan jejak audit atas setiap perubahan data.
- Menerapkan standar keamanan aplikasi yang memadai.
- Menyediakan dokumentasi teknis dan panduan penggunaan.

### 1.3 Ruang Lingkup

- Perancangan dan pengembangan aplikasi berbasis web.
- Implementasi modul autentikasi dan manajemen pengguna.
- Pengelolaan data shipment dan proses pembaruannya via scanner VIN.
- Penerapan validasi input dan pengamanan data.
- Penerapan audit trail pada data utama.
- Deployment sesuai lingkungan yang disepakati.

---

## 2. Arsitektur Sistem

### 2.1 Gambaran Umum Arsitektur

Sistem menggunakan arsitektur **monolitik berbasis MVC (Model-View-Controller)** dengan Laravel 13 sebagai backend framework. Semua komponen (frontend, backend, database) berjalan dalam satu deployment unit, namun dengan pemisahan logika yang jelas secara kode.

```
┌─────────────────────────────────────────────────────────┐
│                        BROWSER                          │
│          (HTML5 + Bootstrap 5.3.8 + JavaScript)         │
└────────────────────────┬────────────────────────────────┘
                         │ HTTP/HTTPS
┌────────────────────────▼────────────────────────────────┐
│                   WEB SERVER (Nginx/Apache)              │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│              LARAVEL 13 APPLICATION (PHP 8+)             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐  │
│  │  Routes  │  │Controllers│  │  Models  │  │  Views │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────┘  │
│  ┌──────────────────────────────────────────────────┐   │
│  │         Services / Business Logic Layer          │   │
│  └──────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────┐   │
│  │    OCR Service (Tesseract + Intervention Image)  │   │
│  └──────────────────────────────────────────────────┘   │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│                   MySQL 8.4 LTS                          │
└─────────────────────────────────────────────────────────┘
```

### 2.2 Komponen Utama

| Komponen | Teknologi | Keterangan |
|---|---|---|
| Web Server | Nginx / Apache | Reverse proxy & static asset |
| Backend | PHP 8+ / Laravel 13 | Business logic & API |
| Frontend | Bootstrap 5.3.8 + JS | UI Responsif |
| Database | MySQL 8.4 LTS | Penyimpanan data utama |
| OCR Engine | Tesseract OCR | Ekstraksi VIN dari gambar |
| Image Processing | Intervention Image / Imagick | Preprocessing gambar OCR |
| Session & Auth | Laravel Sanctum / Session | Manajemen autentikasi |

### 2.3 Deployment Environment

- Aplikasi di-deploy pada environment yang telah disepakati bersama klien.
- Konfigurasi environment dipisahkan melalui file `.env`.
- Tidak ada credential sensitif di dalam source code.

---

## 3. Stack Teknologi

### 3.1 Backend

| Item | Nilai |
|---|---|
| Bahasa Pemrograman | PHP 8+ |
| Framework | Laravel 13 |
| OCR Wrapper | `thiagoalessio/tesseract_ocr-for-php` |
| Image Processing | Intervention Image (`intervention/image`) |

### 3.2 Frontend

| Item | Nilai |
|---|---|
| Markup | HTML5 |
| Styling | CSS3 |
| Scripting | JavaScript (Vanilla / minimal library) |
| UI Framework | Bootstrap 5.3.8 |
| Kamera / Scanner | Browser MediaDevices API (getUserMedia) |

### 3.3 Database

| Item | Nilai |
|---|---|
| DBMS | MySQL 8.4 LTS |
| Identifier | UUID (semua entitas utama) |
| Collation | `utf8mb4_unicode_ci` |

### 3.4 Library & Dependency Utama

| Library | Kegunaan |
|---|---|
| `laravel/framework` v13 | Core framework |
| `thiagoalessio/tesseract_ocr-for-php` | OCR VIN |
| `intervention/image` | Preprocessing gambar |
| `maatwebsite/excel` | Export Excel |
| Bootstrap 5.3.8 | UI Component |

---

## 4. Struktur Database

> Semua tabel menggunakan `uuid` sebagai primary identifier. Format tanggal yang ditampilkan: `DD-Mon-YY` (contoh: `02-Apr-26`).

### 4.1 Tabel `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `CHAR(36)` UUID | Primary Key |
| `username` | `VARCHAR(100)` | Username unik |
| `name` | `VARCHAR(150)` | Nama lengkap |
| `email` | `VARCHAR(150)` | Alamat email unik |
| `phone` | `VARCHAR(20)` | Nomor HP (terenkripsi) |
| `password` | `VARCHAR(255)` | Hash Argon2id |
| `level` | `ENUM('superadmin','admin','vendor')` | Level akses |
| `status` | `ENUM('active','inactive')` | Status akun |
| `created_by` | `CHAR(36)` UUID | FK → `users.id` |
| `created_at` | `TIMESTAMP` | Waktu dibuat |
| `updated_by` | `CHAR(36)` UUID | FK → `users.id` |
| `updated_at` | `TIMESTAMP` | Waktu diubah |

### 4.2 Tabel `vendors`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `CHAR(36)` UUID | Primary Key |
| `user_id` | `CHAR(36)` UUID | FK → `users.id` |
| `vendor_name` | `VARCHAR(150)` | Nama vendor |
| `position` | `ENUM(...)` | Posisi vendor (lihat enum di bawah) |
| `created_by` | `CHAR(36)` UUID | FK → `users.id` |
| `created_at` | `TIMESTAMP` | |
| `updated_by` | `CHAR(36)` UUID | FK → `users.id` |
| `updated_at` | `TIMESTAMP` | |

**Enum `position`:**
```
'AT Storage Port'
'ATD Kapal (Loading)'
'ATA Kapal'
'ATA Storage Port (Destination)'
'AT PtD (Dooring)'
```

### 4.3 Tabel `shipments`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `CHAR(36)` UUID | Primary Key |
| `lokasi` | `VARCHAR(50)` | Kode lokasi |
| `no_do` | `VARCHAR(50)` | Nomor Delivery Order |
| `type_kendaraan` | `VARCHAR(50)` | Jenis kendaraan |
| `no_rangka` | `VARCHAR(17)` | VIN / Nomor Rangka (17 digit) |
| `no_engine` | `VARCHAR(50)` | Nomor mesin |
| `warna` | `VARCHAR(50)` | Warna kendaraan |
| `asal_pdc` | `VARCHAR(100)` | Asal PDC |
| `kota` | `VARCHAR(100)` | Kota asal |
| `tujuan_pengiriman` | `VARCHAR(100)` | Tujuan pengiriman |
| `terima_do` | `DATE` | Tanggal terima DO |
| `keluar_dari_pdc` | `DATE` | Tanggal keluar dari PDC |
| `nama_kapal` | `VARCHAR(100)` | Nama kapal |
| `keberangkatan_kapal` | `DATE` | Tanggal keberangkatan kapal |
| `created_by` | `CHAR(36)` UUID | FK → `users.id` |
| `created_at` | `TIMESTAMP` | |
| `updated_by` | `CHAR(36)` UUID | FK → `users.id` |
| `updated_at` | `TIMESTAMP` | |

### 4.4 Tabel `shipment_updates`

Tabel ini mencatat setiap update posisi yang dilakukan oleh vendor terhadap suatu shipment.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `CHAR(36)` UUID | Primary Key |
| `shipment_id` | `CHAR(36)` UUID | FK → `shipments.id` |
| `vendor_id` | `CHAR(36)` UUID | FK → `vendors.id` |
| `position` | `ENUM(...)` | Posisi yang diisi (sesuai posisi vendor) |
| `scan_date` | `DATE` | Tanggal hasil scan (format `DD-Mon-YY`) |
| `document_link` | `TEXT` | Link Google Drive / Sharepoint (opsional) |
| `created_by` | `CHAR(36)` UUID | FK → `users.id` |
| `created_at` | `TIMESTAMP` | |
| `updated_by` | `CHAR(36)` UUID | FK → `users.id` |
| `updated_at` | `TIMESTAMP` | |

### 4.5 Tabel `scan_histories`

Riwayat scan VIN oleh masing-masing user vendor.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `CHAR(36)` UUID | Primary Key |
| `user_id` | `CHAR(36)` UUID | FK → `users.id` |
| `no_rangka` | `VARCHAR(17)` | VIN hasil scan |
| `scan_date` | `DATE` | Tanggal scan |
| `created_at` | `TIMESTAMP` | |

### 4.6 Tabel `failed_logins`

Untuk keperluan throttling dan lockout percobaan login gagal.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `BIGINT` AUTO_INCREMENT | Primary Key |
| `email` | `VARCHAR(150)` | Email yang dicoba |
| `ip_address` | `VARCHAR(45)` | IP Address |
| `attempts` | `TINYINT` | Jumlah percobaan |
| `last_attempt_at` | `TIMESTAMP` | Waktu percobaan terakhir |
| `locked_until` | `TIMESTAMP` | Waktu unlock |

### 4.7 Relasi Antar Tabel (ERD Summary)

```
users (1) ────────── (N) vendors
users (1) ────────── (N) shipments [created_by / updated_by]
shipments (1) ─────── (N) shipment_updates
vendors (1) ─────────(N) shipment_updates
users (1) ────────── (N) scan_histories
```

---

## 5. Desain Teknis & Alur Proses

### 5.1 Alur Autentikasi

```
[User membuka aplikasi]
        │
        ▼
[Halaman Login]
        │
        ├── Input email & password
        │
        ▼
[Cek failed_logins: apakah terkunci?]
        │
        ├── YA → Tampilkan pesan lockout + sisa waktu
        │
        └── TIDAK
                │
                ▼
        [Validasi kredensial]
                │
                ├── GAGAL → Tambah counter, cek apakah >= 5x → Lockout
                │
                └── BERHASIL
                        │
                        ▼
                [Buat session / token]
                        │
                        ▼
                [Redirect ke Dashboard sesuai Level]
```

### 5.2 Alur Pengelolaan Data Shipment (Admin)

```
[Admin login]
        │
        ▼
[Dashboard Admin]
        │
        ▼
[Menu: Kelola Data Keberangkatan Unit]
        │
        ├── Tambah Data Baru
        │       └── Form input → Validasi → Simpan ke tabel shipments
        │
        ├── Edit Data
        │       └── Form edit → Validasi → Update + catat updated_by, updated_at
        │
        └── Hapus Data
                └── Konfirmasi → Soft/hard delete → Catat di audit
```

### 5.3 Alur Scan VIN (Vendor)

```
[Vendor login]
        │
        ▼
[Cek status vendor: aktif?]
        │
        ├── TIDAK AKTIF → Tampilkan pesan, tolak akses
        │
        └── AKTIF
                │
                ▼
        [Cek posisi vendor sudah diisi?]
                │
                ├── BELUM → Tampilkan card informasi saja
                │
                └── SUDAH
                        │
                        ▼
                [Halaman Scanner Kamera]
                        │
                        ▼
                [Ambil gambar via kamera browser]
                        │
                        ▼
                [Preprocessing gambar (Intervention Image)]
                        │
                        ▼
                [OCR: Tesseract → Ekstrak 17 digit VIN]
                        │
                        ▼
                [Validasi: apakah VIN 17 digit valid?]
                        │
                        ├── TIDAK VALID → Tampilkan error, ulangi scan
                        │
                        └── VALID
                                │
                                ▼
                        [Cari VIN di tabel shipments]
                                │
                                ├── TIDAK DITEMUKAN → Tampilkan notifikasi
                                │
                                └── DITEMUKAN
                                        │
                                        ▼
                                [Simpan tanggal scan ke shipment_updates]
                                [Simpan ke scan_histories]
                                        │
                                        ▼
                                [Tampilkan konfirmasi sukses]
```

### 5.4 Alur Export Laporan (Admin)

```
[Admin → Menu Laporan]
        │
        ▼
[Pilih filter: rentang tanggal]
        │
        ▼
[Opsional: cari berdasarkan No. Rangka]
        │
        ▼
[Query ke database: gabung shipments + shipment_updates]
        │
        ▼
[Tampilkan tabel laporan di layar]
        │
        ▼
[Klik "Export ke Excel"]
        │
        ▼
[Generate file .xlsx via maatwebsite/excel]
        │
        ▼
[Download file ke browser]
```

---

## 6. Spesifikasi Modul

### 6.1 Modul Autentikasi

| Fitur | Keterangan |
|---|---|
| Login | Email + password dengan throttling maks 5x percobaan |
| Logout | Invalidasi session |
| Change Password | Validasi password lama + aturan password baru |
| Forgot Password | Reset via email (token satu kali pakai, kadaluarsa) |
| Lockout | Setelah 5x gagal: delay/throttle/lockout otomatis |

### 6.2 Modul Manajemen User

| Fitur | Superadmin | Admin |
|---|---|---|
| Tambah user semua level | ✅ | ❌ |
| Edit user semua level | ✅ | ❌ |
| Hapus user semua level | ✅ | ❌ |
| Tambah/Edit/Hapus user level Vendor | ✅ | ✅ |
| Proteksi minimal 1 Superadmin | ✅ | — |

**Field user:** Username, Email, No HP, Nama, Level, Status.

### 6.3 Modul Kelola Data Keberangkatan Unit

Tersedia untuk Admin dan Superadmin. Field yang dikelola:

| Field | Keterangan |
|---|---|
| Lokasi | Kode lokasi pengiriman |
| No DO | Nomor Delivery Order |
| Type Kendaraan | Jenis kendaraan |
| No Rangka | VIN 17 digit |
| No. Engine | Nomor mesin |
| Warna | Warna unit |
| Asal PDC | Asal Pre-Delivery Center |
| Kota | Kota asal |
| Tujuan Pengiriman | Kota tujuan |
| Terima DO | Tanggal unit menerima DO |
| Keluar dari PDC | Tanggal unit keluar dari PDC |
| Nama Kapal | Nama kapal pengangkut |
| Keberangkatan Kapal | Tanggal keberangkatan kapal |

### 6.4 Modul Kelola Data Vendor

| Field | Keterangan |
|---|---|
| Nama Vendor | Diambil dari relasi ke tabel `users` via UUID |
| Posisi | Salah satu dari 5 pilihan posisi pengiriman |

Setiap vendor hanya dapat mengisi data pada posisi yang ditetapkan untuknya.

### 6.5 Modul Scanner VIN (Vendor)

- Menggunakan kamera browser (MediaDevices API).
- Preprocessing gambar dengan Intervention Image.
- Ekstraksi 17 digit VIN via Tesseract OCR.
- Tanggal hasil scan otomatis terisi (format: `DD-Mon-YY`, contoh: `02-Apr-26`).
- Hasil disimpan ke `shipment_updates` dan `scan_histories`.

### 6.6 Modul Laporan

| Fitur | Keterangan |
|---|---|
| Tampil data keseluruhan | Menggabungkan data shipment + semua update vendor |
| Filter tanggal | Dari tanggal X sampai tanggal Y |
| Pencarian No. Rangka | Input VIN untuk mencari data spesifik |
| Export Excel | Generate file .xlsx sesuai format yang disepakati |

Format kolom export mengikuti contoh data lengkap (14 kolom input admin + 6 kolom posisi vendor + 1 kolom link dokumen).

### 6.7 Modul Riwayat Scan (Vendor)

Setiap vendor dapat melihat riwayat No. Rangka yang telah di-scan beserta tanggal scan oleh akun tersebut.

---

## 7. Ketentuan Keamanan

### 7.1 Pengelolaan Kredensial & Konfigurasi

- Seluruh konfigurasi sensitif (DB credentials, app key, mail, dll.) wajib disimpan di file `.env`.
- File `.env` **tidak boleh** di-commit ke repository.
- Kredensial tidak boleh ditulis hardcoded di source code.

### 7.2 Pengamanan Password & Data Sensitif

| Item | Ketentuan |
|---|---|
| Password / PIN | Hash Argon2id via `Hash::make()` Laravel |
| No. Telepon / Data Sensitif | Enkripsi `AES-256` via `Crypt::encrypt()` Laravel |
| Plain text password | **Dilarang keras** |

### 7.3 Kebijakan Password

| Tipe Akun | Panjang Minimal | Ketentuan Tambahan |
|---|---|---|
| Pengguna biasa | 12 karakter | Kombinasi: huruf kecil, huruf besar, angka, simbol |
| Administrator | 16 karakter | Kombinasi: huruf kecil, huruf besar, angka, simbol |
| Service Account | 25 karakter | Kombinasi lengkap |

- Pola mudah ditebak dilarang (contoh: `Admin123456!`, `Password123!`).

### 7.4 Autentikasi & Pengendalian Akses

- Login menggunakan email + password.
- Maks. 5 percobaan gagal → mekanisme delay, throttling, atau lockout aktif.
- Hak akses diatur berdasarkan level (Superadmin, Admin, Vendor).
- Middleware Laravel digunakan untuk proteksi route berdasarkan level.

### 7.5 Multi-Factor Authentication (MFA)

MFA **tidak termasuk** dalam ruang lingkup pengembangan fase awal. Implementasi MFA (OTP email/SMS/WhatsApp, PIN, fingerprint) dikategorikan sebagai **change request** untuk fase lanjutan.

### 7.6 Validasi & Sanitasi Input

Validasi wajib diterapkan pada seluruh input, minimal mencakup:

| Validasi | Keterangan |
|---|---|
| Required field | Field wajib tidak boleh kosong |
| Tipe & format data | Sesuai tipe kolom database |
| Format email | RFC-compliant |
| Nomor telepon | Format numerik + panjang valid |
| Kompleksitas password | Sesuai kebijakan password |
| Anti-XSS | Sanitasi input dan output |
| Upload file | Batasan ekstensi, MIME type, ukuran, cegah file berbahaya |

### 7.7 Keamanan Aplikasi (OWASP)

- Dikembangkan mengacu OWASP Top 10.
- Dependency diperiksa kerentanannya saat development, testing, dan sebelum release.
- Pada saat delivery, aplikasi **tidak boleh** menggunakan komponen dengan kerentanan **Critical** atau **High** yang belum ditangani.
- Patching, update dependency, atau mitigasi wajib dilakukan jika ditemukan kerentanan.

---

## 8. Audit Trail

Sistem wajib mencatat perubahan data pada semua entitas utama.

### 8.1 Field Audit Minimal (Fase Awal)

| Field | Keterangan |
|---|---|
| `created_by` | UUID user yang membuat data |
| `created_at` | Timestamp pembuatan |
| `updated_by` | UUID user yang terakhir mengubah |
| `updated_at` | Timestamp perubahan terakhir |

### 8.2 Perluasan Audit (Fase Lanjutan, Opsional)

| Field | Keterangan |
|---|---|
| `deleted_by` | UUID user yang menghapus |
| `deleted_at` | Timestamp penghapusan (soft delete) |
| Histori perubahan field | Log nilai sebelum dan sesudah perubahan |
| Log aktivitas user | Seluruh aktivitas login, logout, aksi CRUD |

---

## 9. Dokumentasi & Deliverable

### 9.1 Dokumentasi Wajib

| Dokumen | Isi |
|---|---|
| TSD (Technical Specification Document) | Arsitektur, desain teknis, struktur database, alur proses, spesifikasi modul, ketentuan keamanan |
| System User Guide | Panduan penggunaan dari sisi administrator/operator internal |
| User Guide | Panduan penggunaan untuk end-user sesuai fitur |

### 9.2 Deliverable Proyek

1. Source code aplikasi
2. Database schema / migration files
3. File konfigurasi contoh untuk deployment (`.env.example`)
4. Dokumentasi teknis (TSD)
5. Panduan penggunaan (System User Guide + User Guide)
6. Hasil pengujian fungsi utama
7. Aplikasi yang dapat dijalankan pada environment yang disepakati

---

## 10. Kriteria Penerimaan

Aplikasi dinyatakan memenuhi kriteria penerimaan apabila seluruh kondisi berikut terpenuhi:

| No. | Kriteria |
|---|---|
| 1 | Berjalan sesuai fungsi utama yang telah disepakati |
| 2 | Seluruh input utama tervalidasi dengan baik |
| 3 | Mekanisme autentikasi, reset password, dan change password berjalan normal |
| 4 | Audit trail minimal tersedia sesuai spesifikasi |
| 5 | Konfigurasi sensitif dipisahkan dari source code |
| 6 | Password tersimpan menggunakan hashing Argon2id |
| 7 | Aplikasi telah melalui pengujian dan tidak ditemukan issue kritikal yang menghambat operasional dasar |

---

## 11. Batasan & Asumsi

- Framework yang digunakan adalah **Laravel 13**.
- UI Framework yang digunakan adalah **Bootstrap 5.3.8**.
- Database yang digunakan adalah **MySQL 8.4 LTS**.
- Hashing password menggunakan **Argon2id**.
- Implementasi MFA **tidak termasuk** dalam fase pengembangan awal.
- Format tanggal yang ditampilkan di seluruh sistem: `DD-Mon-YY` (contoh: `02-Apr-26`).
- Perubahan ruang lingkup di luar spesifikasi ini diperlakukan sebagai **change request**.

---

*Dokumen ini merupakan acuan teknis pengembangan Aplikasi Update Shipment Otomotif PT. Serasi Logistics Indonesia. Setiap perubahan pada dokumen ini harus melalui persetujuan bersama antara tim pengembang dan pihak klien.*
