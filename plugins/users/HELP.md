# Plugin Pengguna

Dokumentasi singkat penggunaan modul **Pengguna** di mLITE untuk pengelolaan akun, hak akses, dan peran pengguna sistem.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Pengguna**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Data Pengguna
  - Tambah Baru

## Panduan Pengguna (Petugas)

Modul ini umumnya hanya diakses oleh admin. Petugas dengan hak akses terbatas dapat:

1. **Melihat Daftar Pengguna**
   - Buka submenu **Data Pengguna** untuk melihat seluruh akun yang terdaftar.
   - Tabel menampilkan username, nama lengkap, email, dan aksi (edit, hak akses, hapus).

2. **Mengubah Data Sendiri**
   - Pengguna dapat mengubah data pribadi melalui menu edit akun masing-masing.
   - Password minimal 8 karakter (wajib mengandung kombinasi huruf besar, huruf kecil, angka, dan karakter khusus).

## Panduan Admin

1. **Tambah Pengguna Baru**
   - Buka submenu **Tambah Baru**.
   - Isi **username** (unik), **email** (valid), **nama lengkap**, **deskripsi**, dan **password** (minimal 8 karakter).
   - Pilih **Role** pengguna: `pengguna`, `kasir`, `rekammedis`, `radiologi`, `laboratorium`, `paramedis`, `apoteker`, `medis`, `manajemen`, atau `admin`.
   - Centang modul-modul yang dapat diakses pengguna ini, atau biarkan semua tercentang untuk akses penuh.
   - Jika modul kepegawaian aktif, pilih **Cap** (poliklinik/bangsal) untuk membatasi akses per unit.
   - Unggah foto avatar (opsional; akan di-crop otomatis menjadi persegi 512×512 px).
   - Klik **Simpan**.

2. **Edit Pengguna**
   - Dari **Data Pengguna**, klik tombol **Edit** pada baris pengguna yang dituju.
   - Ubah data yang diperlukan; kosongkan field password jika tidak ingin menggantinya.
   - Fitur **Salin Hak Akses** tersedia untuk menyalin konfigurasi akses modul dari pengguna lain.

3. **Hak Akses CRUD per Modul**
   - Klik tombol **Hak Akses** pada baris pengguna di tabel Data Pengguna.
   - Atur izin **Buat (Create)**, **Baca (Read)**, **Ubah (Update)**, dan **Hapus (Delete)** untuk setiap modul yang terpasang.
   - Klik **Simpan** untuk menerapkan perubahan hak akses.

4. **Hapus Pengguna**
   - Klik tombol **Hapus** pada baris pengguna.
   - Akun admin (ID=1) dan akun yang sedang login tidak dapat dihapus.
   - Avatar pengguna akan ikut dihapus dari server secara otomatis.

## Catatan

- Username bersifat unik; sistem menolak pendaftaran dengan username yang sudah ada.
- Pengguna dengan akses `all` dapat mengakses seluruh modul yang terpasang.
- Hak akses CRUD diatur secara terpisah dari hak akses modul; keduanya perlu dikonfigurasi untuk kendali akses yang lengkap.
- Pengguna yang sedang login tidak dapat menghapus akunnya sendiri.
- Jika modul kepegawaian aktif, pengguna dapat dikaitkan ke data pegawai untuk pembatasan akses per poliklinik atau bangsal.
