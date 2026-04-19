# Plugin Modul-Modul

Modul pengelolaan plugin/modul mLITE. Memungkinkan admin mengaktifkan, menonaktifkan, mengunggah, dan menghapus modul yang terpasang di sistem mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Modul-Modul**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Unggah

## Panduan Pengguna (Petugas)

Plugin ini hanya digunakan oleh administrator sistem. Petugas biasa tidak memerlukan akses ke menu ini.

## Panduan Admin

1. **Melihat Daftar Modul Aktif**
   - Buka **Modul-Modul → Kelola**, tab **Aktif**.
   - Semua modul yang sedang aktif ditampilkan dalam kartu dua kolom beserta nama, ikon, versi, kategori, dan deskripsi singkat.
   - Klik **Detail** pada kartu modul untuk melihat informasi lengkap termasuk isi file `ReadMe.md` (jika ada).

2. **Melihat Daftar Modul Nonaktif**
   - Klik tab **Nonaktif** pada halaman Kelola.
   - Modul yang sudah diunggah tetapi belum diaktifkan ditampilkan di sini.

3. **Mengaktifkan Modul**
   - Pada tab **Nonaktif**, klik tombol **Aktifkan** pada kartu modul yang diinginkan.
   - Sistem memeriksa kompatibilitas versi modul dengan versi mLITE saat ini.
   - Jika kompatibel, modul didaftarkan ke tabel `mlite_modules` dan fungsi `install()` dari `Info.php` modul dijalankan.
   - Notifikasi sukses atau gagal ditampilkan.

4. **Menonaktifkan Modul**
   - Pada tab **Aktif**, klik tombol **Nonaktifkan** pada kartu modul.
   - Modul dihapus dari tabel `mlite_modules` dan fungsi `uninstall()` dari `Info.php` dijalankan.
   - **Modul dasar sistem** (basic modules) tidak dapat dinonaktifkan.

5. **Menghapus Berkas Modul**
   - Pada tab **Nonaktif**, klik tombol **Hapus Berkas** untuk menghapus seluruh folder modul dari server.
   - **Modul dasar sistem tidak dapat dihapus.**
   - Tindakan ini permanen dan tidak dapat dibatalkan; pastikan modul sudah dinonaktifkan terlebih dahulu.

6. **Mengunggah Modul Baru**
   - Buka **Modul-Modul → Unggah**.
   - Pilih file `.zip` modul dari komputer Anda.
   - Klik **Unggah**; sistem memvalidasi struktur ZIP:
     - File tidak boleh mengandung path traversal (`..`).
     - Ekstensi yang berbahaya (`.php`, `.phtml`, `.exe`, `.sh`, dll.) hanya diizinkan untuk file `Info.php`, `Admin.php`, `Site.php`, dan `index.html`.
     - ZIP harus memiliki struktur direktori (tidak boleh file langsung di root ZIP).
   - Jika modul sudah ada, versi baru harus lebih tinggi dari versi yang terpasang.
   - Setelah unggah berhasil, buka tab **Nonaktif** dan aktifkan modul.

## Catatan

- Kompatibilitas modul diperiksa berdasarkan field `compatibility` di `Info.php` modul (format: `6.*.*`); modul yang tidak kompatibel tidak dapat diaktifkan.
- Urutan modul di tabel `mlite_modules` menggunakan field `sequence` yang diisi otomatis saat aktivasi.
- Saat `FILE_LOCK` aktif di konfigurasi sistem, fitur unggah modul dinonaktifkan untuk keamanan.
- Selalu backup database dan folder `plugins/` sebelum melakukan penghapusan atau perubahan modul pada sistem produksi.
