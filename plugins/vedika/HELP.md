# Plugin Vedika

Dokumentasi singkat penggunaan modul **Vedika** di mLITE untuk pengelolaan klaim online BPJS melalui sistem e-Klaim (bridging INA-CBG).

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Vedika**.
- Pilih submenu sesuai kebutuhan:
  - Manage
  - Index
  - Lengkap
  - Pengajuan
  - Perbaikan
  - IDR Codes
  - INACBG Codes
  - Mapping Inacbgs
  - Bridging e-Klaim
  - Logs e-Klaim
  - User Vedika
  - Pengaturan

## Panduan Pengguna (Petugas)

1. **Index (Daftar Pasien Siap Klaim)**
   - Buka submenu **Index** untuk melihat daftar pasien rawat jalan atau rawat inap yang siap diklaim.
   - Filter berdasarkan tanggal dan jenis pelayanan (Ralan/Ranap).
   - Klik baris pasien untuk melihat detail: no SEP, diagnosa (ICD-10), prosedur (ICD-9), berkas digital, dan status pengajuan.
   - Ubah status klaim dan tambahkan catatan melalui form set status.
   - Unggah berkas digital perawatan (foto/dokumen pendukung klaim) dari halaman ini.

2. **Lengkap**
   - Menampilkan data pasien yang berkas klaimnya sudah lengkap, siap untuk diajukan ke e-Klaim.
   - Tersedia ekspor ke **Excel** untuk keperluan rekap dan verifikasi.

3. **Pengajuan**
   - Menampilkan daftar klaim yang sudah diajukan ke BPJS beserta status pengajuan.
   - Ekspor ke **Excel** untuk laporan pengajuan klaim.

4. **Perbaikan**
   - Tampilkan klaim yang perlu diperbaiki (dikembalikan oleh BPJS).
   - Lakukan koreksi diagnosa atau prosedur, lalu ajukan ulang.
   - Ekspor ke **Excel** tersedia.

5. **Bridging e-Klaim**
   - Kirim data resume medis dan klaim ke sistem e-Klaim BPJS secara langsung dari mLITE.
   - Isi data sesuai form resume (diagnosa, prosedur, biaya INA-CBG) sebelum mengirim.
   - Pantau log pengiriman melalui submenu **Logs e-Klaim**.

6. **Resume Medis**
   - Akses form resume rawat jalan/inap dari halaman Index atau Lengkap.
   - Isi diagnosa utama dan sekunder (ICD-10), prosedur (ICD-9), dan data klinis lainnya.
   - Ubah diagnosa dan prosedur melalui form khusus (Ubah Diagnosa / Ubah Prosedur).

## Panduan Admin

1. **Pengaturan**
   - Buka submenu **Pengaturan** untuk mengisi konfigurasi e-Klaim:
     - **Cara Bayar**: kode penjamin yang diproses modul ini (dipisah koma).
     - **e-Klaim URL**: URL server e-Klaim rumah sakit.
     - **e-Klaim Key**: API key e-Klaim.
     - **Kelas RS**: kode kelas RS (misal: CP untuk pratama).
     - **Payor ID** dan **Payor CD**: identifikasi pembayar (default: 3 / JKN).
     - **COB CD**: kode COB (default: #).
     - **Billing**: sumber data billing (default: mlite).
     - **Periode** dan **Periode Verifikasi**: bulan klaim aktif (format YYYY-MM).

2. **Mapping INA-CBG**
   - Buka submenu **Mapping Inacbgs** untuk memetakan komponen biaya RS ke kelompok INA-CBG:
     - Prosedur Bedah, Prosedur Non Bedah, Konsultasi, Tenaga Ahli, Keperawatan, Penunjang, Pelayanan Darah, Rehabilitasi, Rawat Intensif.
   - Mapping ini menentukan bagaimana biaya layanan RS dikirim ke e-Klaim.

3. **IDR Codes dan INACBG Codes**
   - Lihat dan kelola daftar kode IDR dan kode INA-CBG yang digunakan sebagai referensi mapping.

4. **User Vedika**
   - Kelola daftar pengguna yang memiliki akses ke fitur e-Vedika Dashboard.
   - Tambah, edit, atau hapus pengguna Vedika dari submenu ini.

5. **Logs e-Klaim**
   - Pantau riwayat pengiriman data ke e-Klaim beserta respons dari server.
   - Gunakan log ini untuk troubleshooting klaim yang gagal terkirim.

## Catatan

- Plugin ini memerlukan koneksi ke server e-Klaim RS dan API BPJS VClaim yang sudah dikonfigurasi.
- Kredensial BPJS (ConsID, SecretKey, UserKey, ApiUrl) dikonfigurasi di pengaturan global mLITE, bukan di pengaturan Vedika.
- Data diagnosa dan prosedur diambil otomatis dari rekam medis (tabel `diagnosa_pasien` dan `prosedur_pasien`); pastikan dokter sudah mengisi dengan benar sebelum proses klaim.
- Berkas digital perawatan diperlukan untuk kelengkapan klaim; unggah dari halaman Index atau Lengkap.
- Dashboard e-Vedika dapat diakses langsung melalui halaman publik (`/vedika`) oleh pengguna Vedika yang terdaftar.
