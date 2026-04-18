# Aplikasi Update Shipment Otomotif
**PT. Serasi Logistics Indonesia**

Aplikasi berbasis web untuk mencatat, memantau, dan memperbarui status pengiriman kendaraan (unit otomotif) dari asal PDC hingga tujuan akhir. Vendor di setiap titik pengiriman melakukan scan VIN kendaraan via kamera browser, dan sistem mencatat tanggal secara otomatis.

---

## Daftar Isi

- [Stack Teknologi](#stack-teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi Lokal](#instalasi-lokal)
- [Deploy dengan Docker](#deploy-dengan-docker)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Level Pengguna](#level-pengguna)
- [Akun Default (Development)](#akun-default-development)
- [Struktur Proyek](#struktur-proyek)
- [Perintah Berguna](#perintah-berguna)
- [Dokumentasi Tambahan](#dokumentasi-tambahan)

---

## Stack Teknologi

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.3+ · Laravel 13 |
| Frontend | Bootstrap 5.3.8 · Font Awesome 6.4.0 · DataTables 2.3.7 |
| Database | MySQL 8.4 LTS |
| OCR | Tesseract OCR + `thiagoalessio/tesseract_ocr` |
| Image Processing | `intervention/image` v4 |
| Export Excel | `maatwebsite/excel` |
| Email | Resend (`resend/resend-laravel`) |
| Build Tool | Vite 8 + `laravel-vite-plugin` |

---

## Persyaratan Sistem

Pastikan software berikut sudah terinstal sebelum memulai:

- **PHP** >= 8.3 dengan ekstensi: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `exif`, `zip`
- **Composer** >= 2.x
- **Node.js** >= 20.x + npm
- **MySQL** 8.4 (atau jalankan via Docker — lihat di bawah)
- **Tesseract OCR** terinstal di sistem dan dapat diakses dari PATH

> **Cek Tesseract:** `tesseract --version`

---

## Instalasi Lokal

### Opsi A — Setup Otomatis (Rekomendasi)

```bash
git clone git@github.com:masmutdevofficial/aplikasi-update-shipment-otomotif.git
cd aplikasi-update-shipment-otomotif
cp .env.example .env
# Edit .env sesuai kebutuhan (lihat bagian Konfigurasi)
composer run setup
```

Perintah `composer run setup` akan otomatis:
1. Install semua dependency PHP (`composer install`)
2. Generate `APP_KEY` (`php artisan key:generate`)
3. Jalankan migrasi database (`php artisan migrate`)
4. Install dependency Node.js (`npm install`)
5. Build asset frontend (`npm run build`)

### Opsi B — Manual Step by Step

```bash
git clone git@github.com:masmutdevofficial/aplikasi-update-shipment-otomotif.git
cd aplikasi-update-shipment-otomotif

# 1. Install dependency PHP
composer install

# 2. Salin dan konfigurasi environment
cp .env.example .env
php artisan key:generate

# 3. Jalankan migrasi dan seeder
php artisan migrate --seed

# 4. Install dan build asset frontend
npm install
npm run build
```

---

## Deploy dengan Docker

Aplikasi sudah dilengkapi `Dockerfile` yang menjalankan **Nginx + PHP-FPM** dalam satu container (via supervisord). Cocok untuk deployment ke Coolify, Portainer, atau server apapun yang mendukung Docker.

### Struktur file Docker

```
Dockerfile
docker/
├── nginx/
│   └── default.conf      # Konfigurasi Nginx
└── supervisord.conf       # Menjalankan php-fpm + nginx
```

### Build image

```bash
docker build -t shipment-otomotif:latest .
```

### Jalankan container

```bash
docker run -d \
  --name shipment-otomotif \
  -p 80:80 \
  -e APP_KEY=base64:... \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_URL=https://shipment.masmut.dev \
  -e DB_HOST=<HOST_MYSQL> \
  -e DB_PORT=3306 \
  -e DB_DATABASE=shipment_otomotif \
  -e DB_USERNAME=shipment \
  -e DB_PASSWORD=<PASSWORD_MYSQL> \
  -e RESEND_API_KEY=re_xxxx \
  -e MAIL_FROM_ADDRESS=noreply@yourdomain.com \
  shipment-otomotif:latest
```

### Jalankan migrasi (pertama kali)

```bash
docker exec shipment-otomotif php artisan migrate --force
docker exec shipment-otomotif php artisan db:seed --class=SuperadminSeeder
```

### Deploy via Coolify

1. Buat **New Resource** → **Docker Image** atau **Git Repository** (Dockerfile)
2. Set environment variables sesuai `.env.example`
3. Pastikan `APP_KEY` sudah di-generate: `php artisan key:generate --show`
4. Set **Port** ke `80`
5. Deploy

> Database MySQL perlu disediakan terpisah (bisa via Coolify Resource MySQL/MariaDB, atau managed DB eksternal).

---

## Konfigurasi Environment

Salin `.env.example` ke `.env` lalu isi nilai-nilai berikut:

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shipment_otomotif
DB_USERNAME=shipment
DB_PASSWORD=password_anda
```

### Email (Resend)

Aplikasi ini menggunakan [Resend](https://resend.com) sebagai mail provider.

1. Daftar/login di [resend.com](https://resend.com)
2. Buat API Key di **API Keys** → **Create API Key**
3. Tambahkan dan verifikasi domain Anda di **Domains**
4. Isi `.env`:

```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxx
```

> **Catatan:** `MAIL_FROM_ADDRESS` harus menggunakan domain yang sudah diverifikasi di Resend.

### Password Default Superadmin

```env
DEFAULT_SUPERADMIN_PASSWORD=SuperAdmin@2026!!
```

> **Wajib diganti** sebelum deploy ke production!

### Keamanan Session

```env
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true   # Aktifkan jika menggunakan HTTPS
```

---

## Menjalankan Aplikasi

### Development

```bash
composer run dev
```

Perintah ini menjalankan secara bersamaan:
- PHP built-in server -> `http://localhost:8000`
- Queue worker
- Log viewer (Pail)
- Vite dev server (hot reload)

### Production (server langsung / tanpa Docker)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
npm run build
```

Pastikan di `.env` production:
```env
APP_ENV=production
APP_DEBUG=false
```

### Production (Docker)

Lihat bagian [Deploy dengan Docker](#deploy-dengan-docker) di atas. Container sudah menangani Nginx + PHP-FPM secara otomatis; tidak perlu menjalankan perintah server secara manual.

---

## Level Pengguna

| Level | Akses |
|---|---|
| **Superadmin** | Kelola semua user (semua level), data shipment, vendor, laporan, export |
| **Admin** | Kelola user level vendor, data shipment, vendor, laporan, export |
| **Vendor** | Scan VIN kendaraan sesuai posisi, lihat riwayat scan sendiri |

---

## Akun Default (Development)

### Cara Menjalankan Seeder

```bash
# Migrasi fresh + seed semua data (reset semua data!)
php artisan migrate:fresh --seed

# Seed saja tanpa reset database
php artisan db:seed

# Seed spesifik per level
php artisan db:seed --class=SuperadminSeeder
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=VendorSeeder
```

Setelah seeder selesai, buka `http://localhost:8000/login` dan gunakan salah satu akun di bawah.

---

### Login sebagai Superadmin

| Field | Nilai |
|---|---|
| URL Login | `http://localhost:8000/login` |
| Email | `superadmin@gmail.com` |
| Password | `SuperAdmin@2026!!` |
| Redirect setelah login | `/admin/dashboard` |

### Login sebagai Admin

| Email | Password | Keterangan |
|---|---|---|
| `admin1@gmail.com` | `Admin@Jakarta2026!!` | Admin Jakarta |
| `admin2@gmail.com` | `Admin@Surabaya2026!!` | Admin Surabaya |

Redirect setelah login: `/admin/dashboard`

### Login sebagai Vendor

| Email | Password | Posisi |
|---|---|---|
| `vendor.storageport1@gmail.com` | `Vendor@Port2026!` | AT Storage Port |
| `vendor.loading@gmail.com` | `Vendor@Load2026!` | ATD Kapal (Loading) |
| `vendor.atakapal@gmail.com` | `Vendor@Kapal2026!` | ATA Kapal |
| `vendor.storageport2@gmail.com` | `Vendor@Dest2026!!` | ATA Storage Port (Destination) |
| `vendor.dooring@gmail.com` | `Vendor@Door2026!!` | AT PtD (Dooring) |

Redirect setelah login: `/vendor/dashboard`

> **Jangan gunakan password ini di production.** Ganti semua password sebelum go-live.

---

## Struktur Proyek

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/          — Login, logout, reset password
│   │   ├── Admin/         — User, Shipment, Vendor, Report
│   │   └── Vendor/        — Scanner, History
│   ├── Middleware/
│   │   ├── CheckLevel.php          — Proteksi route berdasarkan level
│   │   └── CheckVendorStatus.php   — Cek status aktif vendor
│   └── Requests/          — Form Request validasi (Auth, Admin, Vendor)
├── Models/                — User, Vendor, Shipment, ShipmentUpdate, ScanHistory, FailedLogin
├── Services/              — AuthService, OcrService, ShipmentService, VendorService, ReportService
└── Exports/               — ShipmentExport (Excel)

database/
├── migrations/            — Semua migrasi tabel
└── seeders/
    ├── SuperadminSeeder.php
    ├── AdminSeeder.php
    └── VendorSeeder.php

resources/views/
├── auth/                  — Login, forgot/reset/change password
├── layouts/               — Admin layout, Vendor layout, Guest layout
├── admin/                 — Users, Shipments, Vendors, Reports
└── vendor/                — Scanner, History

public/
└── vendor/datatables/     — DataTables 2.3.7 (CSS + JS, local)
```

---

## Perintah Berguna

```bash
# Migrasi ulang + jalankan semua seeder
php artisan migrate:fresh --seed

# Hanya jalankan seeder tanpa reset database
php artisan db:seed

# Jalankan test suite (63 tests)
php artisan test

# Cache config, route, view (production)
php artisan optimize

# Bersihkan semua cache
php artisan optimize:clear

# Cek keamanan dependency
composer audit
npm audit
```

---

## Dokumentasi Tambahan

| Dokumen | Lokasi |
|---|---|
| Technical Specification Document | [docs/TSD_Technical_Specification_Document.md](docs/TSD_Technical_Specification_Document.md) |
| Panduan Pengguna (Admin) | [docs/System_User_Guide.md](docs/System_User_Guide.md) |
| Panduan Pengguna (Vendor) | [docs/User_Guide_Vendor.md](docs/User_Guide_Vendor.md) |
| Changelog | [CHANGELOG.md](CHANGELOG.md) |

---

