# User Guide
## Aplikasi Update Shipment Otomotif
**PT. Serasi Logistics Indonesia**

---

| Informasi Dokumen | |
|---|---|
| Nama Sistem | Aplikasi Update Shipment Otomotif |
| Versi | 1.0 |
| Target Pembaca | Vendor / End-User |
| Tanggal | April 2026 |

---

## Daftar Isi

1. [Pengenalan Aplikasi](#1-pengenalan-aplikasi)
2. [Persyaratan Penggunaan](#2-persyaratan-penggunaan)
3. [Login & Logout](#3-login--logout)
4. [Dashboard Vendor](#4-dashboard-vendor)
5. [Scan Kendaraan (VIN Scanner)](#5-scan-kendaraan-vin-scanner)
6. [Riwayat Scan](#6-riwayat-scan)
7. [Pengaturan Akun](#7-pengaturan-akun)
8. [Pertanyaan Umum (FAQ)](#8-pertanyaan-umum-faq)
9. [Bantuan & Kontak](#9-bantuan--kontak)

---

## 1. Pengenalan Aplikasi

Aplikasi Update Shipment Otomotif adalah sistem berbasis web yang digunakan untuk memperbarui status posisi kendaraan dalam proses pengiriman. Sebagai **Vendor**, tugas utama Anda adalah melakukan **scan kendaraan** di titik posisi yang telah ditetapkan, sehingga sistem dapat mencatat secara otomatis bahwa kendaraan tersebut telah tiba atau berangkat dari titik lokasi Anda.

### Apa yang Anda Lakukan?

1. **Login** ke aplikasi menggunakan akun yang diberikan oleh Admin.
2. **Scan** kode VIN (nomor rangka 17 digit) pada kendaraan menggunakan kamera perangkat Anda.
3. Sistem secara **otomatis** mencatat tanggal scan ke data shipment kendaraan tersebut.

---

## 2. Persyaratan Penggunaan

### Perangkat

- Smartphone, tablet, atau laptop/PC dengan kamera.
- Kamera perangkat harus berfungsi dan dapat diakses browser.

### Browser yang Direkomendasikan

| Browser | Versi |
|---|---|
| Google Chrome | Terbaru |
| Mozilla Firefox | Terbaru |
| Safari (iOS/macOS) | Terbaru |

### Koneksi Internet

- Diperlukan koneksi internet yang stabil untuk menggunakan aplikasi.

### Izin Kamera

Saat pertama kali menggunakan fitur scanner, browser akan meminta izin untuk mengakses kamera perangkat Anda. **Pilih "Izinkan" (Allow)** agar fitur scanner dapat berfungsi.

---

## 3. Login & Logout

### 3.1 Cara Login

1. Buka browser di perangkat Anda.
2. Akses URL aplikasi yang telah diberikan oleh Admin.
3. Masukkan **Email** dan **Password** akun Anda.
4. Klik tombol **Masuk**.

> **Penting:** Jika login gagal lebih dari 5 kali, akun akan terkunci sementara. Tunggu beberapa menit atau hubungi Admin untuk membuka kunci akun.

### 3.2 Cara Logout

1. Klik nama akun Anda di bagian atas halaman.
2. Pilih **Logout**.
3. Anda akan kembali ke halaman login.

> Selalu **logout** setelah selesai menggunakan aplikasi, terutama jika menggunakan perangkat bersama.

### 3.3 Lupa Password

1. Klik tautan **Lupa Password** di halaman login.
2. Masukkan alamat email yang terdaftar.
3. Klik **Kirim**.
4. Cek email Anda dan ikuti instruksi untuk membuat password baru.

---

## 4. Dashboard Vendor

Setelah login, Anda akan melihat halaman utama (dashboard) yang menampilkan informasi akun Anda.

### 4.1 Tampilan Dashboard Berdasarkan Kondisi Akun

**Kondisi 1: Posisi Belum Diisi oleh Admin**

Jika Admin belum menetapkan posisi untuk akun Anda, dashboard akan menampilkan:

```
┌──────────────────────────────────────────────┐
│   ℹ️  Informasi                               │
│                                              │
│   Posisi Anda belum dikonfigurasi.           │
│   Silakan hubungi Admin untuk               │
│   mendapatkan penetapan posisi.              │
└──────────────────────────────────────────────┘
```

> Dalam kondisi ini, fitur scanner belum dapat digunakan. Hubungi Admin Anda.

**Kondisi 2: Posisi Sudah Diisi oleh Admin**

Jika posisi Anda sudah dikonfigurasi, dashboard akan menampilkan:

- Informasi akun Anda (Nama, Posisi yang ditetapkan).
- Tombol **Mulai Scan** untuk mengakses fitur scanner.
- Ringkasan riwayat scan terbaru.

### 4.2 Informasi Posisi Vendor

Posisi menentukan di titik mana dalam rantai pengiriman Anda bertugas:

| Posisi | Keterangan |
|---|---|
| AT Storage Port | Kendaraan tiba di Storage Port keberangkatan |
| ATD Kapal (Loading) | Kendaraan berangkat naik kapal (proses loading) |
| ATA Kapal | Kendaraan tiba di kapal |
| ATA Storage Port (Destination) | Kendaraan tiba di Storage Port tujuan |
| AT PtD (Dooring) | Kendaraan tiba di titik dooring (pengiriman akhir) |

---

## 5. Scan Kendaraan (VIN Scanner)

Fitur utama Anda sebagai vendor adalah melakukan scan kendaraan untuk mencatat tanggal kedatangan/keberangkatan di posisi Anda.

### 5.1 Apa Itu VIN?

VIN (Vehicle Identification Number) atau **Nomor Rangka** adalah kode unik 17 karakter yang terdapat pada setiap kendaraan. Kode ini biasanya dapat ditemukan:

- Pada plat/stiker di dashboard kendaraan (terlihat dari kaca depan).
- Pada dokumen kendaraan (DO).
- Pada label yang ditempelkan di bodi kendaraan.

**Contoh VIN:** `MHKAA1AC2TJ036972`

### 5.2 Cara Melakukan Scan

1. Pastikan akun Anda memiliki posisi yang sudah dikonfigurasi.
2. Dari dashboard, klik tombol **Mulai Scan** atau akses menu **Scanner**.
3. Halaman scanner akan terbuka dan kamera perangkat Anda akan aktif.
4. Arahkan kamera ke **kode VIN** pada kendaraan.

   ```
   ┌─────────────────────────────┐
   │                             │
   │   [ Arahkan kamera ke VIN ] │
   │                             │
   │   ┌───────────────────┐     │
   │   │ MHKAA1AC2TJ036972 │     │
   │   └───────────────────┘     │
   │                             │
   │       [ SCAN ]              │
   └─────────────────────────────┘
   ```

5. Pastikan kode VIN terlihat jelas dalam bingkai kamera (pencahayaan cukup, tidak buram).
6. Klik tombol **Scan** atau tunggu sistem mendeteksi otomatis.

### 5.3 Proses Setelah Scan

Sistem akan memproses gambar dan mengekstrak VIN secara otomatis:

**Jika Scan Berhasil:**

```
✅ Scan Berhasil!

No. Rangka : MHKAA1AC2TJ036972
Tanggal    : 02-Apr-26
Posisi     : AT Storage Port

Data telah tersimpan.
```

Tanggal akan otomatis tercatat sesuai tanggal saat scan dilakukan.

**Jika VIN Tidak Terdeteksi:**

```
⚠️ VIN tidak terdeteksi.

Pastikan:
- Kamera terarah dengan jelas ke kode VIN
- Pencahayaan cukup terang
- Gambar tidak buram

[ Coba Lagi ]
```

→ Klik **Coba Lagi** dan ulangi proses scan.

**Jika VIN Tidak Ditemukan di Sistem:**

```
⚠️ No. Rangka tidak ditemukan.

VIN: MHKAB1AA9TJ025351
tidak terdapat dalam data shipment.

Hubungi Admin jika kendaraan seharusnya
sudah terdaftar.
```

→ Hubungi Admin jika kendaraan seharusnya sudah ada di sistem.

### 5.4 Tips untuk Hasil Scan yang Baik

| Tips | Keterangan |
|---|---|
| ☀️ Pencahayaan | Pastikan area VIN cukup terang. Gunakan senter jika gelap. |
| 📏 Jarak | Jaga jarak kamera sekitar 10–20 cm dari kode VIN. |
| 🔍 Fokus | Tunggu kamera fokus sebelum menekan tombol scan. |
| 🚫 Hindari refleksi | Hindari pantulan cahaya berlebih pada permukaan VIN. |
| 🔄 Posisi stabil | Tahan perangkat dengan stabil saat melakukan scan. |

---

## 6. Riwayat Scan

Fitur **Riwayat Scan** memungkinkan Anda melihat daftar kendaraan yang telah Anda scan sebelumnya.

### 6.1 Mengakses Riwayat Scan

1. Dari menu navigasi, pilih **Riwayat Scan**.
2. Sistem akan menampilkan daftar semua scan yang telah Anda lakukan.

### 6.2 Informasi yang Ditampilkan

| Kolom | Keterangan |
|---|---|
| No. Rangka | VIN kendaraan yang di-scan |
| Tanggal Scan | Tanggal saat scan dilakukan (format: `DD-Mon-YY`) |

**Contoh tampilan riwayat:**

| No. | No. Rangka | Tanggal Scan |
|---|---|---|
| 1 | MHKAA1AC2TJ036972 | 02-Apr-26 |
| 2 | MHKAB1AA9TJ025351 | 02-Apr-26 |
| 3 | MHKAC3BB1TJ012345 | 03-Apr-26 |

> **Catatan:** Riwayat scan hanya menampilkan data scan yang dilakukan oleh akun Anda sendiri.

---

## 7. Pengaturan Akun

### 7.1 Mengubah Password

1. Klik nama akun Anda di bagian atas halaman.
2. Pilih **Ubah Password**.
3. Isi formulir:

   | Field | Keterangan |
   |---|---|
   | Password Lama | Masukkan password saat ini |
   | Password Baru | Masukkan password baru (min. 12 karakter) |
   | Konfirmasi Password Baru | Ulangi password baru |

4. Klik **Simpan**.

### 7.2 Ketentuan Password Vendor

Password akun Vendor harus memenuhi syarat:

- ✅ Minimal **12 karakter**
- ✅ Mengandung **huruf kecil** (a–z)
- ✅ Mengandung **huruf besar** (A–Z)
- ✅ Mengandung **angka** (0–9)
- ✅ Mengandung **simbol** (contoh: `!`, `@`, `#`, `$`)
- ❌ Tidak boleh menggunakan pola mudah ditebak (contoh: `Password123!`)

---

## 8. Pertanyaan Umum (FAQ)

**Q: Saya tidak bisa login. Apa yang harus dilakukan?**

A: Pastikan email dan password yang dimasukkan sudah benar. Pastikan Caps Lock tidak aktif. Jika sudah mencoba lebih dari 5 kali, tunggu beberapa menit sebelum mencoba lagi, atau hubungi Admin untuk membuka kunci akun.

---

**Q: Tombol scanner tidak muncul di dashboard saya.**

A: Kemungkinan posisi vendor Anda belum dikonfigurasi oleh Admin. Hubungi Admin untuk menetapkan posisi akun Anda.

---

**Q: Kamera tidak bisa diakses saat membuka scanner.**

A: Pastikan browser Anda memiliki izin untuk mengakses kamera. Buka pengaturan browser > Izin Situs > Kamera, dan pastikan URL aplikasi sudah diizinkan.

---

**Q: Scan berhasil tapi data tidak muncul di riwayat.**

A: Coba refresh halaman riwayat. Jika masih tidak muncul setelah beberapa saat, hubungi Admin.

---

**Q: Apakah saya bisa scan kendaraan yang sudah pernah di-scan sebelumnya?**

A: Setiap vendor hanya dapat mengisi data sesuai posisinya masing-masing. Jika kendaraan sudah pernah di-scan oleh akun Anda pada posisi yang sama, sistem akan memberikan notifikasi.

---

**Q: Format tanggal apa yang digunakan sistem?**

A: Semua tanggal ditampilkan dalam format `DD-Mon-YY`, contoh: `02-Apr-26` (berarti 2 April 2026).

---

**Q: Apakah saya bisa melihat data vendor lain?**

A: Tidak. Setiap vendor hanya dapat melihat riwayat scan dan data akun miliknya sendiri.

---

## 9. Bantuan & Kontak

Jika Anda mengalami kendala yang tidak tercakup dalam panduan ini, silakan hubungi administrator sistem PT. Serasi Logistics Indonesia.

Informasi yang perlu disiapkan saat menghubungi Admin:

1. Nama lengkap dan username akun Anda.
2. Deskripsi masalah yang dialami.
3. Tangkapan layar (screenshot) pesan error jika ada.
4. Perangkat dan browser yang digunakan.

---

*Panduan ini disusun khusus untuk pengguna level Vendor pada Aplikasi Update Shipment Otomotif PT. Serasi Logistics Indonesia.*
