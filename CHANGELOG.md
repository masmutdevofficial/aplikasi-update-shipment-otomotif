# Changelog
## Aplikasi Update Shipment Otomotif — PT. Serasi Logistics Indonesia

Semua perubahan penting pada proyek ini akan didokumentasikan di file ini.

Format mengacu pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
dan proyek ini mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

> Bagian ini mencatat pekerjaan yang sedang berjalan dan belum dirilis.

---

## [6.5.0] - 2026-04-18

### Added
- `Dockerfile` — single-container image dengan PHP 8.3-fpm-alpine, Nginx, dan Supervisord
- `docker/nginx/default.conf` — konfigurasi virtual host Nginx → PHP-FPM di `127.0.0.1:9000`
- `docker/supervisord.conf` — menjalankan `php-fpm` dan `nginx` dalam satu container dengan log ke stdout/stderr
- `docker/start.sh` — entrypoint script: clear cache lalu jalankan supervisord

### Changed
- `.env.example` — diperbarui ke nilai production default (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, placeholder DB Coolify)
- `readme.md` — ditambah section "Deploy dengan Docker" (build, run, migrate, Coolify guide) dan section Production dipisah (dengan/tanpa Docker)
- `docs/TSD_Technical_Specification_Document.md` — section 2.3 Deployment Environment diperbarui dengan detail Docker, tabel file Docker, baris Containerization di stack teknologi, Dockerfile ditambahkan ke daftar Deliverable

### Removed
- `docker-compose.yaml` dan `docker-compose.yml` — dihapus, digantikan oleh Dockerfile

### Fixed
- `admin/vendors/edit.blade.php` — `@section` shorthand dengan `{{ }}` di dalam string literal menyebabkan raw PHP (`<?php echo e(...); ?>`) tampil sebagai teks di halaman; diperbaiki ke form multi-line `@section` / `@endsection`
- `admin/users/edit.blade.php` — masalah yang sama seperti di atas

---

## [6.4.0] - 2026-04-17

### Added
- Halaman dokumentasi in-app TSD (`admin/docs/tsd.blade.php`) — mencakup 10 seksi teknis lengkap dengan kolom Status, Notes, dan Evidence; hanya dapat diakses oleh Superadmin dan Admin
- Halaman dokumentasi in-app Panduan Admin (`admin/docs/user-guide-admin.blade.php`) — berdasarkan `docs/System_User_Guide.md`; mencakup login, manajemen user, shipment, vendor, laporan, keamanan, dan troubleshooting
- Halaman dokumentasi in-app Panduan Vendor versi Admin (`admin/docs/user-guide-vendor.blade.php`) — referensi admin untuk memahami pengalaman vendor
- Halaman dokumentasi in-app Panduan Penggunaan untuk Vendor (`vendor/docs/user-guide.blade.php`) — panduan lengkap untuk Vendor, termasuk TOC, scan VIN, riwayat, ubah password, dan FAQ
- Routes dokumentasi di `routes/web.php`:
  - `admin.docs.tsd`, `admin.docs.user-guide-admin`, `admin.docs.user-guide-vendor`
  - `vendor.docs.user-guide`
- Navigasi sidebar "Dokumentasi" di layout Admin (link TSD, Panduan Admin, Panduan Vendor)
- Navigasi sidebar "Bantuan" di layout Vendor (link Panduan Penggunaan)

### Changed
- `readme.md` — bagian "Akun Default (Development)" diperbarui dengan tabel lengkap email, password, redirect per level, dan perintah seeder

---

## [6.3.0] - 2026-04-17

### Added
- `AdminSeeder` — 2 akun admin contoh (admin.jakarta, admin.surabaya)
- `VendorSeeder` — 5 akun vendor + 5 record vendor, masing-masing 1 posisi berbeda (AT Storage Port, ATD Kapal, ATA Kapal, ATA Storage Port Destination, AT PtD Dooring)
- `DatabaseSeeder` sekarang memanggil `SuperadminSeeder`, `AdminSeeder`, `VendorSeeder` secara berurutan
- Install `resend/resend-laravel` v1.3.2 + `resend/resend-php` v1.3.0 sebagai mail provider
- `DataTables 2.3.7` diinstal lokal via npm (`datatables.net` + `datatables.net-dt`), file statis disalin ke `public/vendor/datatables/`

### Changed
- Mail driver diganti dari `smtp` ke `resend` di `.env.example`
- `.env.example`: hapus `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` — diganti dengan `RESEND_API_KEY`
- CDN DataTables (`cdn.datatables.net/2.0.8`) diganti ke asset lokal (`/vendor/datatables/`) di kedua layout
- `readme.md` ditulis ulang — sekarang berisi panduan instalasi praktis, konfigurasi environment, akun default, perintah berguna

---

## [6.2.0] - 2026-04-17

### Added
- PHPUnit test suite: 63 tests covering authentication, CRUD, access protection, and scan VIN
- `Tests\Feature\Auth\LoginTest` — login success, failure, throttle lockout, logout (10 tests)
- `Tests\Feature\Admin\UserCrudTest` — CRUD user, toggle status, protect last superadmin, level-based access (13 tests)
- `Tests\Feature\Admin\ShipmentCrudTest` — CRUD shipment, VIN validation (17-char + regex), date validation (9 tests)
- `Tests\Feature\Admin\VendorCrudTest` — CRUD vendor, position enum validation, access control (6 tests)
- `Tests\Feature\Vendor\ScanVinTest` — scan confirm, invalid VIN, VIN not found, duplicate scan, access control (9 tests)
- `Tests\Feature\Admin\ReportExportTest` — report view, filter, date validation, access control (5 tests)
- `Tests\Feature\AccessProtectionTest` — guest redirect, cross-level access denial, inactive vendor logout (9 tests)
- `ShipmentFactory` for test data generation with valid VIN
- Updated `UserFactory` with level states (superadmin, admin, vendor, inactive)
- `.env.testing` for isolated test database configuration
- `DatabaseTransactions` in base `TestCase` for fast MySQL-based testing

### Changed
- `phpunit.xml` configured for MySQL test database (`shipment_otomotif_test`)

---

## [6.1.0] - 2026-04-17

### Security
- Added `max:3000000` size limit to base64 image validation in `ScannerController::scan()` (DoS prevention)
- Added VIN regex validation (`/^[A-HJ-NPR-Z0-9]{17}$/`) to `StoreShipmentRequest`, `UpdateShipmentRequest`, and `ScannerController::confirm()`
- Added MIME type validation in `OcrService::extractVin()` — rejects non-image binary payloads
- Added input validation to `ReportController::index()` and `export()` (search, date_from, date_to)
- Dynamic password minimum length in `ResetPasswordRequest` — 16 chars for admin/superadmin, 12 for vendor
- Fixed XSS vulnerability in scanner JavaScript — all dynamic server data now escaped with `escapeHtml()`
- Removed hardcoded `DB_PASSWORD=secret` from `.env.example`
- Added `SESSION_SECURE_COOKIE=true` to `.env.example`
- `composer audit`: 0 vulnerabilities found
- `npm audit`: 0 vulnerabilities found

### Changed
- All Blade views redesigned to AdminLTE 3 style with custom CSS (no external AdminLTE package)
- Admin layout: fixed sidebar (dark #343a40 + blue #007bff), fixed topbar, hamburger toggle, user dropdown
- Vendor layout: fixed sidebar (navy #1a3a5c + teal #17a2b8), conditional nav items based on vendor position
- Guest layout: centered login card with AdminLTE styling, Source Sans 3 font
- All Bootstrap Icons (`bi bi-*`) replaced with Font Awesome 6.4.0 (`fas fa-*`) across all views
- Info-boxes, cards, tables, badges, and forms styled consistently with AdminLTE conventions
- Responsive design maintained for mobile vendor usage

---

## [5.2.0] - 2026-04-17

### Added
- `ShipmentExport` class (FromArray, WithHeadings, WithStyles) menggunakan `maatwebsite/excel`
- Export kolom sesuai format: 13 kolom admin + 5 kolom posisi vendor + 1 link dokumen
- File `.xlsx` ter-download dengan nama berisi timestamp (`laporan_shipment_YYYYMMDD_HHmmss.xlsx`)
- Tombol Export di halaman laporan mengikuti filter aktif
- Bold heading row di file Excel

---

## [5.1.0] - 2026-04-17

### Added
- `ReportService` dengan query gabungan: `shipments` + eager load `shipment_updates` (per posisi)
- `ReportController` dengan method `index` (view dengan filter) dan `export` (download Excel)
- Halaman laporan (`admin/reports`) dengan tabel lengkap: 13 kolom shipment + 5 kolom posisi vendor + 1 link dokumen
- Filter rentang tanggal (date_from, date_to) dan pencarian berdasarkan No. Rangka
- Posisi vendor ditampilkan dengan badge hijau jika sudah di-scan
- Link dokumen dengan tombol akses langsung
- Route: `admin.reports.index` (GET) dan `admin.reports.export` (GET)
- Nav link "Laporan" aktif di semua halaman admin (dashboard, shipments, vendors, users)

---

## [4.4.0] - 2026-04-17

### Added
- Logic simpan hasil scan di `ScannerController::confirm`: database transaction untuk `ShipmentUpdate` + `ScanHistory`
- Validasi: VIN harus 17 karakter, cek duplikasi (shipment + posisi yang sama)
- `HistoryController` (__invoke) dengan pagination dan pencarian No. Rangka
- Halaman riwayat scan vendor (`vendor/history`) dengan tabel + search
- Route: `vendor.scanner.confirm` (POST) dan `vendor.history` (GET)

---

## [4.3.0] - 2026-04-17

### Added
- `OcrService` lengkap: preprocessing gambar (grayscale, contrast, sharpen) dengan Intervention Image + OCR via Tesseract
- Validasi hasil OCR: 17 karakter VIN, regex `[A-HJ-NPR-Z0-9]{17}`
- `ScannerController`: index (halaman scanner), scan (proses OCR), confirm (simpan hasil)
- Halaman scanner vendor: akses kamera via `navigator.mediaDevices.getUserMedia` (facingMode: environment)
- Capture gambar dari video stream → canvas → base64
- Input VIN manual sebagai alternatif OCR
- Input link dokumen (opsional, validasi URL)
- Upload gambar ke backend via fetch API (AJAX)
- Route: `vendor.scanner` (GET) dan `vendor.scanner.scan` (POST)

---

## [4.2.0] - 2026-04-17

### Added
- `DashboardController` (Vendor) — __invoke method
- Halaman dashboard vendor yang sudah terintegrasi:
  - Kondisi 1: posisi belum diisi → card informasi "Posisi Belum Ditetapkan"
  - Kondisi 2: posisi sudah diisi → 3 stat cards (posisi, scan hari ini, total scan) + tombol scan + daftar scan terakhir
- Nav links vendor bersifat conditional (Scan VIN & Riwayat hanya muncul jika vendor punya posisi)
- Route: `vendor.dashboard` menggunakan invokable controller

---

## [4.1.0] - 2026-04-17

### Added
- Migration tabel `shipment_updates` (UUID PK, FK ke shipments & vendors, posisi enum 5 nilai, scan_date, document_link, audit trail)
- Unique constraint `['shipment_id', 'position']` — satu update per shipment per posisi
- Migration tabel `scan_histories` (UUID PK, FK ke users, no_rangka VARCHAR 17, scan_date, created_at only)
- Model `ShipmentUpdate` dengan relasi: shipment(), vendor(), creator(), updater()
- Model `ScanHistory` dengan relasi: user() dan UPDATED_AT = null
- Relasi `shipmentUpdates()` HasMany di model `Shipment` dan `Vendor`
- Docker Compose setup: MySQL 8.4 (shipment-mysql) + phpMyAdmin (shipment-phpmyadmin port 8080)
- Volume `mysql_data` dan network `shipment-net` untuk Docker

---

## [3.2.0] - 2026-04-17

### Added
- CRUD data keberangkatan unit / shipment lengkap (`ShipmentController`, `ShipmentService`)
- `StoreShipmentRequest` dan `UpdateShipmentRequest` Form Request dengan validasi:
  - No. Rangka (VIN) harus tepat 17 karakter (`size:17`)
  - Unique constraint pada `no_rangka` (ignore saat update)
  - Semua 13 field wajib diisi
- Halaman daftar shipment dengan pencarian (No. Rangka, No. DO, Lokasi, Type, Kapal)
- Halaman tambah data shipment (13 field input termasuk 3 field tanggal)
- Halaman edit data shipment dengan pre-filled values
- Format tanggal tampil konsisten `d-M-y` (DD-Mon-YY) di tabel index
- Pagination dengan query string preservation
- Routes: resource `admin/shipments` (except show)
- Navigasi Shipments aktif di semua view admin terkait

---

## [3.1.0] - 2026-04-17

### Added
- Migration tabel `shipments` dengan UUID primary key
  - 10 field string: lokasi, no_do, type_kendaraan, no_rangka (unique), no_engine, warna, asal_pdc, kota, tujuan_pengiriman, nama_kapal
  - 3 field date: terima_do, keluar_dari_pdc, keberangkatan_kapal
  - Audit trail: created_by, updated_by (FK ke users), timestamps
- Model `Shipment` dengan HasUuids, date casts, relasi creator/updater

---

## [2.2.0] - 2026-04-17

### Added
- CRUD vendor lengkap (`VendorController`, `VendorService`)
- `StoreVendorRequest` dan `UpdateVendorRequest` Form Request dengan validasi:
  - User ID harus level vendor, aktif, dan belum terhubung ke vendor lain
  - Nama vendor max 150 karakter
  - Posisi harus salah satu dari 5 enum yang valid
- Halaman daftar vendor dengan pencarian (nama, posisi, email)
- Halaman tambah vendor (auto-fill nama dari user yang dipilih via JS)
- Halaman edit vendor (user account read-only, hanya nama & posisi bisa diubah)
- Warning alert jika tidak ada user vendor yang tersedia
- Routes: resource `admin/vendors` (except show)
- Navigasi Vendor dan Shipments aktif di dashboard, users, dan vendor views

---

## [2.1.0] - 2026-04-17

### Added
- Migration tabel `vendors` dengan UUID primary key
  - Relasi `user_id` (FK unique ke users, cascade on delete)
  - Field: vendor_name, position (enum 5 posisi)
  - Audit trail: created_by, updated_by, timestamps
- Model `Vendor` dengan HasUuids, relasi user/creator/updater, static `positions()`
- Relasi `vendor(): HasOne` ditambahkan ke model `User`

---

## [1.4.0] - 2026-04-17

### Added
- CRUD manajemen user lengkap (`UserController`, `UserService`)
- `StoreUserRequest` dan `UpdateUserRequest` Form Request dengan validasi:
  - Password min 16 karakter untuk admin/superadmin, 12 untuk vendor
  - Aturan mixedCase, numbers, symbols, uncompromised
  - Username hanya alfanumerik + titik/underscore/strip
  - Unique constraint pada username dan email
- Halaman daftar user dengan pencarian (nama, username, email)
- Halaman tambah user baru dengan pemilihan level
- Halaman edit user (password opsional saat update)
- Fitur toggle status aktif/nonaktif user (invalidasi session saat nonaktif)
- Fitur hapus user dengan proteksi superadmin terakhir
- Business rule: superadmin dapat kelola semua level, admin hanya level vendor
- Otorisasi di Form Request (`authorize()`) berdasarkan level user
- Dynamic password hint berdasarkan level yang dipilih
- Navigasi Users di dashboard admin dan superadmin
- Routes: resource `admin/users` + `PATCH toggle-status`

### Security
- Superadmin terakhir tidak dapat dihapus (validasi di `UserService`)
- Admin tidak dapat mengedit/menghapus user non-vendor (validasi di controller + Form Request)
- User tidak dapat menghapus/menonaktifkan akun sendiri
- Session dihapus saat user dinonaktifkan
- No. telepon dienkripsi AES-256 saat disimpan

---

## [1.3.0] - 2026-04-17

### Added
- Halaman ubah password (`auth/change-password.blade.php`) dengan:
  - Validasi password lama
  - Password baru min 12/16 karakter sesuai level
  - Toggle show/hide password
- Halaman lupa password (`auth/forgot-password.blade.php`) untuk request reset link via email
- Halaman reset password (`auth/reset-password.blade.php`) via token
- `PasswordController` dengan 6 method: showChangeForm, changePassword, showForgotForm, sendResetLink, showResetForm, resetPassword
- `ChangePasswordRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest` Form Requests
- Routes: `/password/forgot` (GET, POST), `/password/reset/{token}` (GET), `/password/reset` (POST), `/password/change` (GET, POST)
- Forgot/reset password routes ditempatkan di middleware `guest` sesuai Laravel convention

### Changed
- Route placeholder `abort(404)` diganti dengan `PasswordController` methods
- Route forgot password dipindah dari middleware `auth` ke `guest`

### Security
- Password reset token kadaluarsa dalam 60 menit (config/auth.php)
- CSRF protection pada semua form password

---

## [1.2.0] - 2026-04-17

### Added
- Halaman login (Bootstrap 5.3.8) dengan toggle show/hide password
- `AuthController` dengan method: `showLoginForm`, `login`, `logout`
- `LoginRequest` Form Request untuk validasi input login
- `AuthService` — business logic login dengan:
  - Throttling: maks 5x gagal → lockout 15 menit via `RateLimiter`
  - Pencatatan failed login ke tabel `failed_logins`
  - Validasi status aktif user
  - Redirect sesuai level user setelah login
- Session-based authentication dengan session regeneration
- Halaman dashboard admin (placeholder dengan summary cards)
- Halaman dashboard vendor (placeholder dengan info card)
- Routes: login, logout, admin dashboard, vendor dashboard
- Middleware alias di `bootstrap/app.php`: `level`, `vendor.status`
- Guest redirect ke `/login`, authenticated redirect ke `/admin/dashboard`

### Security
- CSRF protection pada semua form
- Login throttling (5 attempts, 15-minute lockout)
- Session regeneration setelah login berhasil
- Session invalidation + token regeneration saat logout

---

## [1.1.0] - 2026-04-17

### Added
- Migration tabel `users` dengan UUID primary key, field:
  - `username` (VARCHAR 100, unique)
  - `name` (VARCHAR 150)
  - `email` (VARCHAR 150, unique)
  - `phone` (VARCHAR 500, encrypted AES-256)
  - `password` (VARCHAR 255, Argon2id hash)
  - `level` (ENUM: superadmin, admin, vendor)
  - `is_active` (BOOLEAN, default true)
  - `created_by` / `updated_by` (UUID, FK → users.id)
- Migration tabel `failed_logins` untuk tracking login gagal:
  - `email`, `ip_address`, `attempts`, `last_attempt_at`, `locked_until`
- Model `User` dengan `HasUuids` trait, phone encryption/decryption (AES-256), level helper methods, audit trail relations
- Model `FailedLogin` dengan fillable dan datetime casts
- `SuperadminSeeder` — membuat 1 akun superadmin default (email: superadmin@gmail.com)
- `DatabaseSeeder` memanggil `SuperadminSeeder`

### Changed
- Migration users: `id` dari `bigIncrements` → `uuid` primary key
- Migration users: `sessions.user_id` dari `foreignId` → `uuid`
- `.env.example`: tambah `DEFAULT_SUPERADMIN_PASSWORD` config key

### Security
- Phone number dienkripsi dengan `Crypt::encryptString()` (AES-256) saat simpan
- Password di-hash dengan Argon2id
- Superadmin default password dikonfigurasi via `.env`, bukan hardcoded

---

## [0.2.0] - 2026-04-17

### Added
- Install dependency proyek:
  - `thiagoalessio/tesseract_ocr` v2.13.0 (OCR)
  - `intervention/image` v4.0.1 + `intervention/image-laravel` v4.0.0 (Image Processing)
  - `maatwebsite/excel` v3.1.68 (Export Excel)
- Struktur direktori dasar sesuai spesifikasi:
  - Controllers: `Auth/`, `Admin/` (User, Shipment, Vendor, Report), `Vendor/` (Scanner, History)
  - Middleware: `CheckLevel`, `CheckVendorStatus`
  - Requests: `Auth/`, `Admin/`, `Vendor/`
  - Services: `AuthService`, `OcrService`, `ShipmentService`, `VendorService`, `ReportService`
  - Exports: `ShipmentExport`
  - Seeders: `SuperadminSeeder`
- Layout Blade utama dengan Bootstrap 5.3.8:
  - `layouts/app.blade.php` — layout untuk halaman authenticated (navbar, flash messages, footer)
  - `layouts/guest.blade.php` — layout untuk halaman login/auth
- Placeholder views: `auth/`, `admin/` (users, shipments, vendors, reports), `vendor/` (scanner, history)
- File `resources/js/scanner.js` (placeholder untuk scanner VIN)

### Changed
- `.env.example` dikonfigurasi untuk proyek:
  - Database: MySQL (`shipment_otomotif`)
  - Locale: `id` / `id_ID`
  - Session encrypt: `true`
  - Mail: SMTP (bukan log)
  - Hapus konfigurasi Redis, Memcached, AWS yang tidak dipakai
- Konfigurasi hashing: Argon2id (`config/hashing.php`)
- `.gitignore`: tambah `composer-setup.php`

---

## [0.1.1] - 2026-04-17

### Changed
- `.copilot-instructions.md` — ditambahkan section **"Cara Mendapatkan Dokumentasi Terbaru"** berisi:
  - Strategi 1: Context7 MCP (`use context7`) dengan library ID Laravel & Bootstrap
  - Strategi 2: Pin versi eksplisit di setiap prompt
  - Strategi 3: Referensi docs resmi langsung di prompt
  - Strategi 4: Prompt template standar proyek
  - Tabel peringatan potensi masalah versi lama vs solusinya

### Notes
- Context7 API key gratis tersedia di [context7.com/dashboard](https://context7.com/dashboard)
- Selalu gunakan `use context7` di awal prompt Copilot untuk Laravel 13 / Bootstrap 5.3.8

---

## [0.1.0] - 2026-04-17

### Added
- Inisialisasi dokumen proyek:
  - `TSD_Technical_Specification_Document.md` — arsitektur sistem, struktur database, alur proses, spesifikasi modul, ketentuan keamanan
  - `System_User_Guide.md` — panduan penggunaan untuk administrator dan operator internal
  - `User_Guide_Vendor.md` — panduan penggunaan untuk end-user level vendor
  - `Dokumen_Spesifikasi_Teknis_Rapi.md` — dokumen spesifikasi teknis resmi yang telah dirapikan
  - `.copilot-instructions.md` — panduan pengembangan lengkap (fase, milestone, aturan, konvensi git)
  - `CHANGELOG.md` — file ini

### Notes
- Fase 0 (inisialisasi proyek) belum dimulai, menunggu setup Laravel 13
- Repository target: `git@github.com:masmutdevofficial/aplikasi-update-shipment-otomotif.git`

---

<!--
Template untuk update berikutnya — salin dan isi setiap kali milestone selesai:

## [X.Y.Z] - YYYY-MM-DD

### Added
- 

### Changed
- 

### Fixed
- 

### Security
- 

### Notes
- 
-->

---

## Roadmap Versi

| Versi | Fase | Isi Utama | Status |
|---|---|---|---|
| `0.1.0` | Dokumentasi | Inisialisasi dokumen & panduan | ✅ Selesai |
| `0.1.1` | Dokumentasi | Update `.copilot-instructions.md` — strategi docs terbaru | ✅ Selesai |
| `0.2.0` | Fase 0 | Setup Laravel, dependency, struktur direktori, layout | ⏳ Belum dimulai |
| `1.0.0` | Fase 1 | Autentikasi, manajemen user, login/logout, throttling | ⏳ Belum dimulai |
| `1.1.0` | Fase 1.1 | Migration users, seeder superadmin | ⏳ Belum dimulai |
| `1.2.0` | Fase 1.2 | Login, logout, throttle | ⏳ Belum dimulai |
| `1.3.0` | Fase 1.3 | Change password, forgot password | ⏳ Belum dimulai |
| `1.4.0` | Fase 1.4 | CRUD user (admin & superadmin) | ⏳ Belum dimulai |
| `2.0.0` | Fase 2 | Kelola data vendor | ⏳ Belum dimulai |
| `2.1.0` | Fase 2.1 | Migration & model vendor | ⏳ Belum dimulai |
| `2.2.0` | Fase 2.2 | CRUD vendor | ⏳ Belum dimulai |
| `3.0.0` | Fase 3 | Kelola data keberangkatan unit | ⏳ Belum dimulai |
| `3.1.0` | Fase 3.1 | Migration & model shipment | ⏳ Belum dimulai |
| `3.2.0` | Fase 3.2 | CRUD shipment | ⏳ Belum dimulai |
| `4.0.0` | Fase 4 | Fitur scan VIN (vendor) | ⏳ Belum dimulai |
| `4.1.0` | Fase 4.1 | Migration shipment_updates & scan_histories | ⏳ Belum dimulai |
| `4.2.0` | Fase 4.2 | Dashboard vendor | ⏳ Belum dimulai |
| `4.3.0` | Fase 4.3 | Scanner kamera & OCR | ⏳ Belum dimulai |
| `4.4.0` | Fase 4.4 | Simpan hasil scan & riwayat | ⏳ Belum dimulai |
| `5.0.0` | Fase 5 | Laporan & export Excel | ⏳ Belum dimulai |
| `5.1.0` | Fase 5.1 | Halaman laporan & filter | ⏳ Belum dimulai |
| `5.2.0` | Fase 5.2 | Export Excel | ⏳ Belum dimulai |
| `6.0.0` | Fase 6 | Hardening, testing & deployment | ⏳ Belum dimulai |
| `6.1.0` | Fase 6.1 | Security hardening | ⏳ Belum dimulai |
| `6.2.0` | Fase 6.2 | Pengujian fungsi utama | ⏳ Belum dimulai |
| **`v1.0.0`** | **Release** | **Production release** | ⏳ Belum dimulai |