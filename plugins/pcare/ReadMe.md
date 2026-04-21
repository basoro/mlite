# Plugin Bridging PCare

Dokumentasi singkat penggunaan modul **Bridging PCare** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Bridging PCare**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Diagnosa
  - Dokter
  - Kesadaran
  - Kunjungan
  - MCU
  - Obat
  - Pendaftaran
  - Peserta
  - Poli
  - Alergi
  - Prognosa
  - Provider
  - Tindakan
  - Status Pulang
  - Kelompok
  - Spesialis
  - Settings

## Panduan Pengguna (Petugas)

1. **Kelola**
   - Halaman utama menampilkan panduan penggunaan modul PCare.
   - Gunakan halaman ini sebagai titik awal sebelum menggunakan fitur bridging.

2. **Referensi Data dari PCare API**
   - Submenu **Diagnosa**, **Dokter**, **Kesadaran**, **Kunjungan**, **MCU**, **Obat**, **Pendaftaran**, **Peserta**, **Poli**, **Alergi**, **Prognosa**, **Provider**, **Tindakan**, **Status Pulang**, **Kelompok**, dan **Spesialis** menampilkan data referensi langsung dari server PCare BPJS.
   - Data diambil secara real-time menggunakan API PCare dengan autentikasi konsumen.
   - Gunakan fitur ini untuk memverifikasi kode-kode referensi yang digunakan saat pengisian data kunjungan.

3. **Kunjungan**
   - Lihat daftar kunjungan PCare berdasarkan kata kunci dan parameter yang tersedia.
   - Tambah kunjungan baru ke PCare (kirim data dari SIMRS ke server PCare).
   - Edit kunjungan yang sudah ada menggunakan nomor kunjungan PCare.
   - Delete kunjungan menggunakan nomor kunjungan PCare.

4. **Pendaftaran**
   - Cek data pendaftaran pasien di server PCare.

5. **Peserta**
   - Cari data peserta BPJS berdasarkan nomor kartu atau NIK melalui API PCare.

## Panduan Admin

1. **Settings**
   - Buka submenu **Settings** dan isi seluruh parameter koneksi PCare:
     - **Username PCare** dan **Password PCare**: kredensial login ke aplikasi PCare.
     - **Consumer ID** dan **Consumer Secret**: kunci autentikasi API PCare dari BPJS.
     - **Consumer User Key**: kunci pengguna untuk API PCare.
     - **Consumer User Key Antrol**: kunci untuk API antrian online (Antrol).
     - **PCare API URL**: URL endpoint API PCare. Gunakan URL `-dev` untuk lingkungan pengujian.
     - **Kode FKTP** dan **Nama FKTP**: kode dan nama fasilitas kesehatan tingkat pertama.
     - **Kode Kabupaten/Kota** dan **Kabupaten/Kota**: wilayah operasional faskes.
     - **Wilayah** dan **Cabang**: regional dan cabang BPJS yang menaungi faskes.
   - Simpan pengaturan.

2. **Verifikasi Koneksi**
   - Setelah menyimpan pengaturan, buka salah satu submenu referensi (misal: **Diagnosa**) untuk memastikan data berhasil diambil dari server PCare.
   - Jika muncul pesan "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS", periksa kembali kredensial dan URL API.

3. **Mode Pengujian vs Produksi**
   - PCare API URL yang mengandung kata `dev` akan otomatis menggunakan endpoint Antrol dan iCare versi development.
   - Gunakan URL produksi untuk lingkungan live.

## Catatan

- Semua respons dari API PCare dienkripsi. Plugin mendekripsi dan mengurai data menggunakan `LZString` secara otomatis.
- Plugin mendukung integrasi dengan iCare (validasi peserta IHS BPJS).
- Pastikan server mLITE memiliki akses internet ke endpoint BPJS (`apijkn.bpjs-kesehatan.go.id`).
- Saat instalasi, plugin membuat entri pengaturan kosong di tabel `mlite_settings`. Isi semua field sebelum menggunakan bridging.
