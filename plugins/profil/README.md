# Plugin Profil

Modul pengelolaan profil dan kehadiran pegawai di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Profil**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Biodata
  - Presensi Masuk
  - Rekap Presensi
  - Jadwal Pegawai
  - Ganti Password

## Panduan Pengguna (Petugas)

1. **Kelola**
   - Halaman beranda profil menampilkan nama, NIK, tanggal hari ini, serta ringkasan kehadiran bulan berjalan (hadir dan absen).
   - Klik sub-menu yang tersedia untuk berpindah ke fitur lain.

2. **Biodata**
   - Lihat dan edit data diri pegawai: nama, jenis kelamin, departemen, bidang, jabatan, pendidikan, status wajib pajak, dll.
   - Upload foto profil (akan otomatis dipotong persegi dan dikompresi maks 512×512 px).
   - Klik **Simpan** setelah mengisi form.

3. **Presensi Masuk**
   - Menampilkan daftar presensi masuk (data dari `temporary_presensi`) milik pegawai yang sedang login.
   - Admin dapat melihat presensi semua pegawai.
   - Klik ikon peta untuk melihat lokasi GPS presensi di Google Maps.

4. **Rekap Presensi**
   - Menampilkan rekap kehadiran per bulan beserta shift, jam datang, jam pulang, durasi, dan status.
   - Pilih bulan untuk melihat rekap bulan berbeda.
   - Admin dapat melihat rekap seluruh pegawai; petugas biasa hanya melihat rekap milik sendiri.

5. **Jadwal Pegawai**
   - Menampilkan jadwal kerja pegawai untuk bulan dan tahun berjalan.
   - Admin melihat jadwal semua pegawai; petugas melihat jadwal milik sendiri.

6. **Ganti Password**
   - Masukkan **Password Lama** dan **Password Baru**.
   - Password baru tidak boleh sama dengan password lama.
   - Klik **Simpan** untuk memperbarui password.

## Panduan Admin

1. **Kelola Biodata Pegawai**
   - Pastikan data pegawai di tabel `pegawai` sudah terisi (NIK, nama, departemen, bidang, jabatan, dll.) agar menu Biodata berfungsi.
   - Data departemen, bidang, jenjang jabatan, pendidikan, dan status wajib pajak dikelola di modul **Master**.

2. **Verifikasi Presensi**
   - Data presensi bersumber dari tabel `temporary_presensi` (presensi masuk) dan `rekap_presensi` (rekap bulanan).
   - Admin dapat memantau seluruh pegawai sekaligus; petugas biasa hanya melihat data sendiri.
   - Foto presensi dan data geolokasi tersimpan di tabel `mlite_geolocation_presensi`.

3. **Jadwal Kerja**
   - Jadwal dikelola dari modul **Presensi** (tabel `jadwal_pegawai`).
   - Plugin Profil hanya menampilkan jadwal yang sudah diinput di modul tersebut.

## Catatan

- NIK pegawai harus sama dengan username akun mLITE agar data profil terhubung dengan benar.
- Foto profil disimpan di direktori `webapps/penggajian/pages/pegawai/photo/`.
- Jika pegawai belum terdaftar di tabel `pegawai`, halaman Kelola menampilkan nama "Admin Utama".
