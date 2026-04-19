# Plugin Pengaturan

Modul konfigurasi sistem mLITE, mencakup pengaturan umum instansi, manajemen tema tampilan, pembaruan sistem, serta backup dan restore database.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Pengaturan**.
- Pilih submenu sesuai kebutuhan:
  - Pengaturan
  - Umum
  - Tema
  - Pembaruan
  - Backup & Restore

## Panduan Pengguna (Petugas)

Plugin ini hanya diakses oleh Administrator sistem. Tidak ada operasional harian untuk petugas biasa.

## Panduan Admin

1. **Umum**
   - Isi identitas instansi: nama, alamat, kota, propinsi, nomor telepon, email, website.
   - Upload **logo** instansi (otomatis dipotong persegi, maks 512×512 px) dan **wallpaper** halaman login.
   - Atur zona waktu sistem.
   - Konfigurasikan poliklinik dan dokter default (IGD, dokter umum, dll.) jika modul Master aktif.
   - Atur format **No Rekam Medis** (`set_no_rkm_medis`) dan format **Nomor Surat**.
   - Pilih integrasi aktif: Pasien, IGD, Laboratorium, Radiologi, WhatsApp Gateway, Bridging SEP, Rawat Jalan, Presensi.
   - Lihat informasi sistem: versi PHP, versi database, dan status lisensi.
   - Aktifkan **Lisensi** dengan memasukkan kode validasi penggunaan.
   - Klik **Simpan** — data instansi juga dikirim ke server mLITE untuk registrasi.

2. **Tema**
   - Lihat daftar tema yang tersedia di direktori `themes/`.
   - Klik **Aktifkan** untuk mengganti tema tampilan publik yang aktif.
   - Klik nama file tema untuk membuka **editor kode** (MarkItUp) dan mengedit template HTML/CSS secara langsung.
   - Simpan perubahan file tema — berlaku langsung tanpa restart.

3. **Pembaruan**
   - Klik **Cek Pembaruan** untuk memeriksa versi terbaru dari GitHub (`basoro/mlite`).
   - Jika tersedia versi baru, klik **Update** untuk mengunduh dan menginstal otomatis:
     - Sistem membuat backup otomatis ke folder `backup/{timestamp}/` sebelum update.
     - File `systems/`, `plugins/`, `assets/`, dan `themes/` diperbarui.
     - File `config.php` dan `manifest.json` dipertahankan dari backup.
     - Script `upgrade.php` dijalankan untuk migrasi database jika diperlukan.
   - **Update Nightly**: unduh dan instal versi terbaru dari branch `master` GitHub.
   - **Update Manual**: letakkan file `mlite-{versi}.zip` di root direktori, lalu klik Update Manual.

4. **Backup & Restore**
   - **Backup Database**: buat salinan database saat ini dalam format file backup.
   - **Restore Database**: pulihkan database dari file backup yang diunggah.
   - Lakukan backup rutin sebelum melakukan update sistem atau perubahan konfigurasi besar.

## Catatan

- Hanya administrator sistem yang boleh mengakses menu Pengaturan.
- Saat update sistem, backup dilakukan otomatis — namun sangat disarankan untuk melakukan backup manual database terlebih dahulu melalui **Backup & Restore**.
- Jika `FILE_LOCK` aktif di konfigurasi server, pengeditan file tema melalui editor akan dinonaktifkan.
- Update sistem memerlukan ekstensi PHP **ZipArchive** dan akses **curl** aktif di server.
- Setelah update, bersihkan cache browser untuk memastikan perubahan tampilan termuat dengan benar.
