# Plugin Dashboard

Dokumentasi singkat penggunaan modul **Dashboard** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Dashboard tampil otomatis sebagai halaman pertama setelah login.
- Pilih submenu sesuai kebutuhan:
  - Main (halaman utama/beranda)

## Panduan Pengguna (Petugas)

Dashboard adalah halaman pertama yang dilihat setiap pengguna setelah login:

1. **Halaman Utama (Main)**
   - Menampilkan informasi hari dan tanggal saat ini dalam bahasa Indonesia.
   - Jika modul **Presensi** aktif, halaman ini menampilkan:
     - Nama pegawai yang sedang login.
     - Status presensi hari ini (sudah/belum absen masuk).
     - Rekap kehadiran harian.
     - Jadwal jaga sesuai departemen pegawai.
     - Teks sambutan/motivasi harian yang dapat dikustomisasi.
   - Notifikasi presensi ditampilkan jika pengaturan notifikasi aktif.

2. **Menu Modul (Dashboard)**
   - Klik ikon/tombol **Menu** atau **Dashboard** untuk membuka tampilan daftar modul.
   - Modul yang tampil disesuaikan dengan hak akses akun yang sedang login.
   - Klik modul yang diinginkan untuk berpindah ke modul tersebut.
   - Admin dengan akses **"all"** dapat melihat seluruh modul yang terpasang.

## Panduan Admin

1. **Pengaturan Akses Modul per Pengguna**
   - Hak akses modul diatur di manajemen pengguna mLITE.
   - Pengguna dengan akses selain `all` hanya melihat modul yang tercantum di daftar aksesnya.
   - Urutan modul di dashboard mengikuti kolom `sequence` di tabel `mlite_modules`.

2. **Integrasi Modul Presensi**
   - Jika modul **Presensi** terpasang dan aktif, widget presensi muncul otomatis di halaman Main.
   - Konfigurasi teks sambutan diatur di **Pengaturan Presensi** (field `helloworld`), dipisahkan dengan tanda titik koma (`;`) untuk rotasi acak.

3. **Upload dan Geolokasi**
   - Dashboard mendukung fitur upload file dan pencatatan geolokasi (digunakan oleh modul pendukung seperti Presensi).

## Catatan

- Dashboard tidak memiliki data master sendiri; semua informasi yang tampil diambil dari modul lain yang aktif.
- Jika modul Presensi tidak terpasang, widget presensi tidak akan muncul dan halaman Main hanya menampilkan info tanggal dan menu modul.
