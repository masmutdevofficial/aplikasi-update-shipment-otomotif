# Laporan Tindak Lanjut Evaluasi Pengerjaan Q1

Tanggal: 6 Agustus 2026
Proyek: Aplikasi Update Shipment Otomotif

## Ringkasan

Perbaikan dilakukan berdasarkan dua masukan klien: pemisahan foto dokumen PTD Dooring dari proses pembacaan VIN, serta pencocokan VIN Pending ketika data shipment tersedia.

## Hasil Implementasi

| Area | Perubahan | Status |
| --- | --- | --- |
| PTD Dooring - scan VIN | Scan/OCR dan input manual VIN 17 karakter tetap dipertahankan. | Selesai |
| PTD Dooring - foto dokumen | Upload dokumen dipindahkan ke Riwayat Scan. Pada setiap VIN milik vendor PTD Dooring tersedia aksi Upload/Ganti Dokumen untuk berkas PNG/JPEG hingga 2 MB. | Selesai |
| VIN Pending - alur impor | Pending VIN dengan nomor rangka yang sama tetap dipindahkan ke timeline shipment ketika shipment diimpor. | Selesai |
| VIN Pending - tambah manual | Pencocokan yang sama kini dijalankan pula saat admin menambahkan shipment secara manual. Data pending dipindahkan ke shipment update dan dihapus dari daftar pending. | Selesai |

## Alur Pengguna PTD Dooring

1. Vendor memindai VIN melalui kamera/OCR atau memasukkan VIN secara manual.
2. Vendor membuka **Riwayat Scan** dan mencari nomor rangka yang diinginkan.
3. Vendor mengambil atau memilih foto dokumen pada baris VIN tersebut.
4. Sistem menyimpan foto dokumen pada update shipment atau VIN Pending terkait, terpisah dari gambar pembacaan VIN.

## Alur VIN Pending

1. Jika VIN belum ada pada shipment, vendor dapat memilih untuk menyimpannya sebagai pending.
2. Admin dapat melihat data tersebut pada menu VIN Pending.
3. Saat shipment dengan VIN yang sama diimpor atau ditambahkan manual, sistem membuat update shipment dari data pending dan menghapus entri pending yang telah dipindahkan.

## Verifikasi

Test cakupan untuk pencocokan VIN Pending pada penambahan shipment manual telah ditambahkan. Pengujian otomatis perlu dijalankan di lingkungan yang memiliki dependensi PHP Composer dan database pengujian aktif.

## Catatan Rilis

Sebelum penerapan ke produksi, jalankan migrasi yang belum diterapkan dan jalankan suite test aplikasi. Pastikan konfigurasi penyimpanan dokumen (Cloudflare R2) tersedia agar foto dokumen dapat disimpan dan dibuka.
