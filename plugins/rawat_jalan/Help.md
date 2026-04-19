# Plugin Rawat Jalan

Modul pendaftaran dan pengelolaan layanan rawat jalan di mLITE, mencakup registrasi pasien, booking, jadwal dokter, input tindakan dan obat, hingga integrasi BPJS.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Rawat Jalan**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Rawat Jalan
  - Booking Registrasi
  - Booking Periksa
  - Jadwal Dokter

## Panduan Pengguna (Petugas)

1. **Kelola (Dashboard)**
   - Halaman utama Rawat Jalan sebagai titik masuk modul.

2. **Rawat Jalan**
   - Tampil daftar kunjungan rawat jalan hari ini (default), dapat difilter berdasarkan rentang tanggal.
   - Filter berdasarkan status periksa: **Belum**, **Selesai**, atau **Lunas**.
   - Tampilan dibatasi sesuai poliklinik yang menjadi wewenang akun pengguna (kecuali admin atau centang "Semua Poli").
   - **Mendaftarkan Pasien Baru**:
     - Pilih pasien (no rekam medis), poliklinik, dokter, penjamin, dan tanggal.
     - Sistem otomatis mengisi no rawat, no urut antrian, biaya registrasi, dan umur pasien.
     - Jika tanggal lebih dari hari ini, registrasi dicatat sebagai **booking** (`booking_registrasi`).
   - **Input Tindakan**:
     - Pilih jenis perawatan, provider (dokter/petugas/dokter+petugas), tanggal, jam, dan jumlah.
     - Sistem menyimpan ke tabel `rawat_jl_dr`, `rawat_jl_pr`, atau `rawat_jl_drpr` sesuai provider.
   - **Input Obat**:
     - Pilih obat, isi jumlah dan aturan pakai.
     - Sistem membuat atau menggabungkan resep harian ke tabel `resep_obat` dan `resep_dokter`.
   - **Input Racikan**:
     - Isi nama racikan, komposisi obat, kandungan, jumlah, dan aturan pakai.
     - Data tersimpan di `resep_dokter_racikan` dan `resep_dokter_racikan_detail`.
   - **Rujukan Internal**: Aktifkan filter rujukan internal untuk menampilkan pasien yang dirujuk antar poli.
   - **Dokumen Pendukung**: Buat surat rujukan, surat sakit, surat sehat, dan persetujuan umum dari halaman detail pasien.

3. **Booking Registrasi**
   - Lihat daftar booking pendaftaran yang belum diproses.
   - Ubah status booking menjadi **Terdaftar** untuk mengkonversinya menjadi `reg_periksa` resmi pada hari pemeriksaan.

4. **Booking Periksa**
   - Kelola jadwal booking pemeriksaan pasien yang telah dikonfirmasi.

5. **Jadwal Dokter**
   - Lihat dan kelola jadwal praktik dokter per poliklinik.
   - Jadwal bersumber dari tabel `jadwal` yang dikelola di modul Master.

## Panduan Admin

1. **Pengaturan Poliklinik dan Dokter**
   - Kelola data poliklinik (termasuk biaya registrasi) dan dokter di modul **Master**.
   - Pastikan status poliklinik dan dokter aktif (`status = 1`) agar tampil di form pendaftaran.

2. **Pengaturan IGD**
   - Di menu **Settings → Umum**, atur kode poliklinik IGD (`igd`) agar pasien IGD tidak tercampur di daftar rawat jalan biasa.

3. **Pengaturan Antrian per Dokter**
   - Aktifkan opsi `dokter_ralan_per_dokter` di Settings agar nomor urut antrian dihitung per dokter (bukan per poliklinik).

4. **Hak Akses Poliklinik**
   - Atur `cap` (capability) akun petugas dengan kode poliklinik yang menjadi wewenangnya (dipisah koma).
   - Admin dapat melihat semua poliklinik; petugas hanya melihat poliklinik sesuai `cap`-nya.

5. **Integrasi vClaim BPJS**
   - Jika modul `vclaim` aktif, tombol pengajuan SEP muncul di daftar pasien.
   - Nomor SEP dari bridging ditampilkan di kolom SEP.

6. **Berkas Digital**
   - Berkas digital pasien (persetujuan, form, dll.) diunggah ke `webapps/berkasrawat/pages/upload/`.

## Catatan

- Registrasi untuk tanggal yang akan datang otomatis masuk ke `booking_registrasi`; registrasi hari ini langsung ke `reg_periksa`.
- No rawat dihasilkan otomatis dengan format `YYYY/MM/DD/XXXXX`.
- Sistem mencoba kembali hingga 5 kali jika terjadi konflik no rawat (duplicate key) saat pendaftaran bersamaan.
