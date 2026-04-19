# Plugin Bridging HFIS

Dokumentasi singkat penggunaan modul **Bridging HFIS** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Bridging HFIS**.
- Pilih submenu sesuai kebutuhan:
  - Manage
    - Bridging Update HFIS
    - Bridging Lihat HFIS
    - Jadwal Dokter

## Panduan Pengguna (Petugas)

Petugas yang mengelola jadwal dokter menggunakan sub-modul berikut:

1. **Bridging Update HFIS**
   - Buka **Bridging HFIS → Manage → Bridging Update HFIS**.
   - Pilih **Dokter** dan **Poliklinik** yang jadwalnya akan diperbarui ke BPJS HFIS.
   - Atur hari, jam buka, jam tutup, dan kuota untuk setiap sesi praktek.
   - Jika jam buka dan tutup dikosongkan pada hari tertentu, jadwal hari tersebut akan dihapus dari BPJS HFIS.
   - Klik **Update** untuk mengirimkan data jadwal ke BPJS HFIS.
   - Konfirmasi keberhasilan ditunjukkan dengan pesan *"Berhasil Simpan"*.

2. **Bridging Lihat HFIS**
   - Buka **Bridging HFIS → Manage → Bridging Lihat HFIS**.
   - Pilih **Poliklinik (kode BPJS)** dan **Tanggal** yang ingin dilihat.
   - Klik **Tampilkan** untuk mengambil data jadwal dokter langsung dari server BPJS HFIS.
   - Data jadwal yang ditampilkan merupakan data aktual di sisi BPJS, bukan data lokal SIMRS.

3. **Jadwal Dokter**
   - Buka **Bridging HFIS → Manage → Jadwal Dokter**.
   - Pilih **Poliklinik** dan **Dokter**, lalu pilih **Bulan/Tahun**.
   - Klik **Tampilkan** untuk melihat jadwal dokter lokal SIMRS per bulan.
   - Data ini dapat dijadikan referensi sebelum melakukan update ke HFIS.

## Panduan Admin

1. **Konfigurasi Koneksi BPJS Antrian**
   - Plugin ini menggunakan kredensial dari modul **JKN Mobile** (pengaturan `jkn_mobile`).
   - Pastikan **BpjsConsID**, **BpjsSecretKey**, **BpjsUserKey**, dan **BpjsAntrianUrl** sudah dikonfigurasi di pengaturan modul JKN Mobile.

2. **Pemetaan Dokter dan Poliklinik**
   - Data dokter yang ditampilkan di **Update HFIS** berasal dari tabel `maping_dokter_dpjpvclaim`.
   - Data poliklinik berasal dari tabel `maping_poli_bpjs`.
   - Pastikan pemetaan dokter dan poliklinik RS ke kode BPJS sudah lengkap sebelum menggunakan fitur bridging.

## Catatan

- Perubahan jadwal dokter di mLITE tidak otomatis terkirim ke BPJS HFIS; petugas harus secara manual melakukan **Update HFIS** setiap ada perubahan jadwal.
- Kode poliklinik yang digunakan di HFIS adalah kode BPJS, bukan kode poli internal RS.
- Koneksi ke BPJS HFIS membutuhkan jaringan yang dapat mengakses URL antrian BPJS Kesehatan.
