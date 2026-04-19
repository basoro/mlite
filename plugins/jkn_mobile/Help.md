# Plugin JKN Mobile

Dokumentasi singkat penggunaan modul **JKN Mobile** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **JKN Mobile**.
- Pilih submenu sesuai kebutuhan:
  - Kelola (halaman utama)
  - WS BPJS
  - WS RS
  - Katalog
  - Mapping Poliklinik
  - Mapping Dokter
  - Jadwal Dokter HFIS
  - Data Booking Antrol
  - Task ID
  - Quality Rate
  - Dashboard Antrol BPJS
  - Pengaturan

## Panduan Pengguna (Petugas)

1. **Dashboard Antrol BPJS**
   - Buka submenu **Dashboard Antrol BPJS** untuk melihat daftar antrian online pasien BPJS yang masuk dari aplikasi Mobile JKN.
   - Filter antrian berdasarkan tanggal, poliklinik, atau status antrian.
   - Gunakan tombol **Kirim Antrian** pada baris pasien untuk mendaftarkan pasien ke sistem RS.
   - Gunakan tombol **Batal Antrol** untuk membatalkan antrian bila diperlukan.

2. **Data Booking Antrol**
   - Pantau data booking antrian online dari pasien.
   - Lihat detail booking termasuk nomor referensi, kode booking, dan jadwal kunjungan.

3. **Task ID**
   - Buka submenu **Task ID** untuk mengelola task pengiriman antrian ke BPJS.
   - Input task ID baru melalui tombol yang tersedia pada baris data pasien.
   - Pantau log task ID melalui submenu **Log Task ID** untuk memverifikasi status pengiriman.

4. **Quality Rate**
   - Lihat laporan quality rate antrian untuk memantau kualitas layanan antrian BPJS.

5. **Jadwal Dokter HFIS**
   - Buka submenu **Jadwal Dokter HFIS** untuk sinkronisasi jadwal dokter dari sistem HFIS BPJS.

## Panduan Admin

1. **Pengaturan**
   - Buka submenu **Pengaturan**.
   - Isi kredensial API BPJS: **BpjsConsID**, **BpjsSecretKey**, dan **BpjsUserKey**.
   - Atur URL endpoint antrian BPJS (`BpjsAntrianUrl`); gunakan URL dev untuk testing dan URL produksi untuk live.
   - Isi **kd_pj_bpjs** (kode penanggung jawab BPJS), dan atur opsi `kirimantrian` (aktif/tidak).
   - Simpan konfigurasi sebelum menggunakan fitur antrian.

2. **Mapping Poliklinik**
   - Buka submenu **Mapping Poliklinik**.
   - Tambahkan mapping antara kode poli di sistem RS dengan kode poli di BPJS.
   - Tanpa mapping yang benar, antrian tidak dapat dikirimkan ke poli yang tepat.

3. **Mapping Dokter**
   - Buka submenu **Mapping Dokter**.
   - Tambahkan mapping antara kode dokter di sistem RS dengan kode dokter di BPJS (HFIS).

4. **WS BPJS & WS RS**
   - Gunakan submenu **WS BPJS** untuk menguji koneksi web service ke server BPJS.
   - Gunakan submenu **WS RS** untuk memverifikasi respons web service dari sisi RS.

## Catatan

- Konfigurasi **BpjsConsID**, **BpjsSecretKey**, dan **BpjsUserKey** wajib diisi sebelum plugin dapat berkomunikasi dengan API BPJS.
- Gunakan URL dev (`apijkn-dev.bpjs-kesehatan.go.id`) saat testing; ganti ke URL produksi sebelum go-live.
- Mapping poliklinik dan dokter harus lengkap agar pengiriman antrian ke BPJS tidak gagal.
- Log task ID tersimpan untuk memudahkan penelusuran jika terjadi kegagalan pengiriman antrian.
