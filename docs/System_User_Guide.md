# System User Guide
## Aplikasi Update Shipment Otomotif
**PT. Serasi Logistics Indonesia**

---

| Informasi Dokumen | |
|---|---|
| Nama Sistem | Aplikasi Update Shipment Otomotif |
| Versi | 1.0 |
| Target Pembaca | Administrator, Superadmin, Operator Internal |
| Tanggal | April 2026 |

---

## Daftar Isi

1. [Pengenalan Sistem](#1-pengenalan-sistem)
2. [Level Akses Pengguna](#2-level-akses-pengguna)
3. [Login & Logout](#3-login--logout)
4. [Manajemen Pengguna](#4-manajemen-pengguna)
5. [Kelola Data Keberangkatan Unit](#5-kelola-data-keberangkatan-unit)
6. [Kelola Data Vendor](#6-kelola-data-vendor)
7. [Laporan & Export](#7-laporan--export)
8. [Pengaturan Akun](#8-pengaturan-akun)
9. [Panduan Keamanan](#9-panduan-keamanan)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Pengenalan Sistem

### 1.1 Tentang Aplikasi

Aplikasi Update Shipment Otomotif adalah sistem berbasis web yang digunakan untuk mencatat, memantau, dan memperbarui status pengiriman kendaraan (unit otomotif) dari asal PDC hingga tujuan akhir. Sistem ini terintegrasi dengan proses scan kendaraan oleh pihak vendor di berbagai titik pengiriman.

### 1.2 Fitur Utama untuk Admin

| Fitur | Keterangan |
|---|---|
| Manajemen User | Tambah, edit, hapus akun pengguna |
| Data Keberangkatan Unit | Input dan kelola data shipment kendaraan |
| Data Vendor | Kelola vendor dan penetapan posisi |
| Laporan | Lihat, filter, cari, dan export data shipment |

### 1.3 Akses Sistem

Sistem dapat diakses melalui browser di URL yang telah disediakan. Tidak diperlukan instalasi perangkat lunak khusus. Browser yang direkomendasikan:

- Google Chrome (versi terbaru)
- Mozilla Firefox (versi terbaru)
- Microsoft Edge (versi terbaru)

---

## 2. Level Akses Pengguna

Sistem memiliki tiga level pengguna dengan hak akses yang berbeda:

| Fitur | Superadmin | Admin | Vendor |
|---|---|---|---|
| Kelola user semua level | ✅ | ❌ | ❌ |
| Kelola user level Vendor | ✅ | ✅ | ❌ |
| Input Data Keberangkatan Unit | ✅ | ✅ | ❌ |
| Kelola Data Vendor | ✅ | ✅ | ❌ |
| Lihat Laporan & Export | ✅ | ✅ | ❌ |
| Scan VIN & Update Posisi | ❌ | ❌ | ✅ |
| Lihat Riwayat Scan | ❌ | ❌ | ✅ |

> **Catatan Superadmin:** Superadmin memiliki tampilan dan akses yang sama dengan Admin, ditambah kemampuan mengelola akun di semua level termasuk Admin dan Superadmin lainnya. Minimal harus ada 1 akun Superadmin aktif di sistem.

---

## 3. Login & Logout

### 3.1 Cara Login

1. Buka browser dan akses URL aplikasi.
2. Pada halaman login, masukkan:
   - **Email:** Alamat email yang telah terdaftar.
   - **Password:** Password akun Anda.
3. Klik tombol **Masuk**.
4. Sistem akan mengarahkan ke dashboard sesuai level akun.

> **Perhatian:** Percobaan login yang gagal maksimal **5 kali**. Setelah itu akun akan mengalami penundaan atau dikunci sementara. Hubungi Superadmin jika terkena lockout.

### 3.2 Cara Logout

1. Klik nama akun Anda di pojok kanan atas.
2. Pilih opsi **Logout** dari menu dropdown.
3. Anda akan diarahkan kembali ke halaman login.

### 3.3 Lupa Password

1. Pada halaman login, klik tautan **Lupa Password**.
2. Masukkan alamat email yang terdaftar.
3. Klik **Kirim**.
4. Buka email Anda dan klik tautan reset password yang diterima.
5. Masukkan password baru sesuai ketentuan.
6. Klik **Simpan**. Login menggunakan password baru.

---

## 4. Manajemen Pengguna

### 4.1 Melihat Daftar User

1. Dari menu navigasi, pilih **Kelola User**.
2. Daftar seluruh user akan ditampilkan dalam bentuk tabel.
3. Gunakan fitur pencarian untuk mencari user berdasarkan nama, username, atau email.

### 4.2 Menambah User Baru

1. Klik tombol **+ Tambah User**.
2. Isi formulir dengan data berikut:

| Field | Ketentuan |
|---|---|
| Username | Unik, tidak boleh sama dengan user lain |
| Email | Format email valid, unik |
| No HP | Format nomor telepon valid |
| Nama | Nama lengkap pengguna |
| Level | Pilih: Superadmin / Admin / Vendor |
| Status | Pilih: Aktif / Tidak Aktif |
| Password | Sesuai ketentuan kebijakan password (lihat §9.1) |

3. Klik **Simpan** untuk menyimpan data.

> **Catatan Admin:** Admin hanya dapat menambah user dengan level **Vendor**. Untuk menambah Admin atau Superadmin, hubungi Superadmin.

### 4.3 Mengedit User

1. Pada daftar user, klik tombol **Edit** pada baris user yang ingin diubah.
2. Ubah data yang diperlukan.
3. Klik **Simpan Perubahan**.

### 4.4 Menonaktifkan / Mengaktifkan User

1. Pada daftar user, klik tombol **Edit**.
2. Ubah field **Status** menjadi `Tidak Aktif` (untuk menonaktifkan) atau `Aktif`.
3. Klik **Simpan Perubahan**.

> User dengan status **Tidak Aktif** tidak dapat login ke sistem.

### 4.5 Menghapus User

1. Pada daftar user, klik tombol **Hapus** pada baris user yang ingin dihapus.
2. Konfirmasi penghapusan pada dialog yang muncul.
3. Klik **Ya, Hapus**.

> **Penting:** Sistem **tidak mengizinkan** penghapusan Superadmin terakhir. Pastikan terdapat minimal 1 Superadmin lain sebelum menghapus.

---

## 5. Kelola Data Keberangkatan Unit

### 5.1 Melihat Data Keberangkatan

1. Dari menu navigasi, pilih **Data Keberangkatan Unit**.
2. Daftar seluruh shipment akan ditampilkan dalam bentuk tabel.
3. Gunakan fitur pencarian atau filter untuk menyaring data.

### 5.2 Menambah Data Keberangkatan Baru

1. Klik tombol **+ Tambah Data**.
2. Isi seluruh field formulir berikut:

| Field | Ketentuan |
|---|---|
| Lokasi | Kode lokasi (contoh: `D730`) |
| No DO | Nomor Delivery Order |
| Type Kendaraan | Jenis/model kendaraan (contoh: `AYLA`, `ROCKY`) |
| No Rangka | VIN 17 digit (contoh: `MHKAA1AC2TJ036972`) |
| No. Engine | Nomor mesin kendaraan |
| Warna | Warna unit (contoh: `WHITE DSO`) |
| Asal PDC | Nama kota asal PDC (contoh: `SUNTER`, `KARAWANG`) |
| Kota | Kota asal pengiriman |
| Tujuan Pengiriman | Kota tujuan |
| Terima DO | Tanggal unit menerima DO (format: `DD-Mon-YY`) |
| Keluar dari PDC | Tanggal unit keluar PDC (format: `DD-Mon-YY`) |
| Nama Kapal | Nama kapal pengangkut |
| Keberangkatan Kapal | Tanggal kapal berangkat (format: `DD-Mon-YY`) |

3. Klik **Simpan** untuk menyimpan data.

> **Format tanggal:** Semua tanggal menggunakan format `DD-Mon-YY`, contoh: `02-Apr-26`.

### 5.3 Mengedit Data Keberangkatan

1. Pada daftar data, klik tombol **Edit** pada baris yang ingin diubah.
2. Lakukan perubahan yang diperlukan.
3. Klik **Simpan Perubahan**.

### 5.4 Menghapus Data Keberangkatan

1. Pada daftar data, klik tombol **Hapus**.
2. Konfirmasi penghapusan pada dialog yang muncul.
3. Klik **Ya, Hapus**.

---

## 6. Kelola Data Vendor

### 6.1 Melihat Daftar Vendor

1. Dari menu navigasi, pilih **Kelola Data Vendor**.
2. Daftar vendor beserta posisi masing-masing akan ditampilkan.

### 6.2 Menambah Data Vendor

1. Klik tombol **+ Tambah Vendor**.
2. Isi formulir:

| Field | Ketentuan |
|---|---|
| User | Pilih user dengan level Vendor yang sudah terdaftar |
| Nama Vendor | Otomatis terisi dari data user yang dipilih |
| Posisi | Pilih salah satu posisi pengiriman |

**Pilihan Posisi yang tersedia:**

| Kode Posisi | Keterangan |
|---|---|
| AT Storage Port | Tiba di Storage Port asal |
| ATD Kapal (Loading) | Berangkat naik kapal (loading) |
| ATA Kapal | Tiba di kapal |
| ATA Storage Port (Destination) | Tiba di Storage Port tujuan |
| AT PtD (Dooring) | Tiba di titik dooring |

3. Klik **Simpan**.

### 6.3 Mengedit Data Vendor

1. Pada daftar vendor, klik tombol **Edit**.
2. Ubah posisi atau data yang diperlukan.
3. Klik **Simpan Perubahan**.

### 6.4 Menghapus Data Vendor

1. Pada daftar vendor, klik tombol **Hapus**.
2. Konfirmasi pada dialog yang muncul.
3. Klik **Ya, Hapus**.

> **Catatan:** Menghapus data vendor tidak menghapus akun user terkait.

---

## 7. Laporan & Export

### 7.1 Melihat Laporan Keseluruhan

1. Dari menu navigasi, pilih **Laporan**.
2. Sistem akan menampilkan seluruh data shipment beserta update posisi dari semua vendor dalam satu tabel terintegrasi.

### 7.2 Filter Data Laporan

1. Pada halaman Laporan, temukan panel **Filter**.
2. Isi kolom **Dari Tanggal** dengan tanggal awal.
3. Isi kolom **Sampai Tanggal** dengan tanggal akhir.
4. Klik tombol **Terapkan Filter**.
5. Data akan diperbarui sesuai rentang tanggal yang dipilih.

### 7.3 Mencari Berdasarkan No. Rangka

1. Pada halaman Laporan, temukan kolom pencarian **No. Rangka**.
2. Ketik VIN (minimal beberapa karakter) kendaraan yang dicari.
3. Hasil pencarian akan tampil secara otomatis atau tekan **Enter**.

### 7.4 Export ke Excel

1. Terapkan filter dan/atau pencarian sesuai kebutuhan (opsional).
2. Klik tombol **Export Excel**.
3. File `.xlsx` akan otomatis terunduh ke perangkat Anda.

**Kolom dalam file export:**

| No. | Nama Kolom |
|---|---|
| 1 | No |
| 2 | Lokasi |
| 3 | No DO |
| 4 | Type Kendaraan |
| 5 | No Rangka |
| 6 | No. Engine |
| 7 | Warna |
| 8 | Asal PDC |
| 9 | Kota |
| 10 | Tujuan Pengiriman |
| 11 | Terima DO |
| 12 | Keluar dari PDC |
| 13 | Nama Kapal |
| 14 | Keberangkatan Kapal |
| 15 | AT Storage Port |
| 16 | ATD Kapal (Loading) |
| 17 | ATA Kapal |
| 18 | ATA Storage Port (Destination) |
| 19 | AT PtD (Dooring) |
| 20 | AT PtD (Dooring) |
| 21 | Link Dokumen |

---

## 8. Pengaturan Akun

### 8.1 Ubah Password

1. Klik nama akun di pojok kanan atas.
2. Pilih **Pengaturan Akun** atau **Ubah Password**.
3. Isi formulir:
   - **Password Lama:** Password saat ini.
   - **Password Baru:** Password baru sesuai ketentuan.
   - **Konfirmasi Password Baru:** Ulangi password baru.
4. Klik **Simpan**.

---

## 9. Panduan Keamanan

### 9.1 Ketentuan Password

Pastikan password yang digunakan memenuhi syarat berikut:

| Tipe Akun | Panjang Minimal | Syarat |
|---|---|---|
| Admin | 16 karakter | Huruf kecil + huruf besar + angka + simbol |
| Vendor | 12 karakter | Huruf kecil + huruf besar + angka + simbol |

**Contoh password yang TIDAK diperbolehkan:**
- `Admin123456!`
- `Password123!`
- Pola berulang atau mudah ditebak

**Contoh password yang direkomendasikan:**
- `Lgs@Adm2026#Shipm`
- `Vnd$SLS!2026Rnk#`

### 9.2 Praktik Keamanan yang Disarankan

- **Jangan bagikan password** kepada siapapun, termasuk sesama admin.
- **Ganti password secara berkala**, minimal setiap 90 hari.
- **Segera logout** setelah selesai menggunakan sistem, terutama pada perangkat bersama.
- **Laporkan segera** kepada Superadmin jika akun Anda dicurigai diakses oleh pihak tidak berwenang.

---

## 10. Troubleshooting

### 10.1 Tidak Bisa Login

| Gejala | Solusi |
|---|---|
| Password salah | Pastikan Caps Lock tidak aktif. Coba kembali. |
| Akun terkunci (lockout) | Tunggu beberapa menit, atau hubungi Superadmin untuk unlock. |
| Akun tidak aktif | Hubungi Superadmin untuk mengaktifkan kembali akun. |
| Lupa password | Gunakan fitur **Lupa Password** di halaman login. |

### 10.2 Data Tidak Tersimpan

| Gejala | Solusi |
|---|---|
| Muncul pesan validasi merah | Periksa dan perbaiki field yang bermasalah sesuai petunjuk. |
| Halaman tidak merespons | Refresh browser. Jika berlanjut, hubungi tim teknis. |

### 10.3 Export Excel Gagal / Kosong

| Gejala | Solusi |
|---|---|
| File tidak terunduh | Pastikan popup blocker browser tidak memblokir unduhan. |
| File kosong | Pastikan data tersedia di rentang tanggal yang dipilih. |

### 10.4 Fitur Tidak Muncul

- Pastikan Anda login dengan akun yang memiliki level akses yang sesuai.
- Jika fitur masih tidak muncul setelah login ulang, hubungi Superadmin atau tim teknis.

---

### Kontak Dukungan Teknis

Untuk permasalahan teknis yang tidak dapat diselesaikan melalui panduan ini, hubungi Masmut Dev selaku Developer.

---

*Dokumen ini ditujukan untuk administrator dan operator internal sistem. Harap tidak didistribusikan kepada pihak di luar yang berkepentingan.*
