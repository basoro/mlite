# Plugin iCare

Modul bridging iCare BPJS untuk mengakses riwayat pelayanan peserta BPJS Kesehatan secara langsung dari API BPJS.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **iCare**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Petugas menggunakan modul ini untuk melihat riwayat pelayanan kesehatan peserta BPJS dari sistem iCare:

1. **Riwayat iCare Pasien**
   - Fitur riwayat dipanggil dari halaman detail kunjungan pasien (Dokter Ralan/IGD/Ranap) melalui tautan iCare.
   - Sistem mengambil data riwayat pelayanan peserta berdasarkan **nomor peserta BPJS** dan **kode dokter BPJS** yang bertugas.
   - Mendukung dua mode:
     - **Mode DPJP (Dokter Spesialis)**: menggunakan endpoint iCare RS (`BpjsService::postAplicare`).
     - **Mode FKTP (PCare)**: menggunakan endpoint PCare (`PcareService::postIcare`).
   - Data riwayat mencakup daftar kunjungan sebelumnya peserta di berbagai fasilitas kesehatan.

## Panduan Admin

1. **Konfigurasi Koneksi iCare**
   - Buka submenu **Kelola**.
   - Isi seluruh parameter koneksi iCare:
     - **URL iCare RS**: URL endpoint API iCare untuk rawat inap/jalan (default: `https://apijkn.bpjs-kesehatan.go.id/wsihs/api/rs/validate`).
     - **URL PCare**: URL endpoint API PCare (default: `https://apijkn.bpjs-kesehatan.go.id/wsihs/api/pcare/validate`).
     - **Cons ID**: Consumer ID yang diberikan BPJS untuk aplikasi.
     - **Secret Key**: Secret key untuk otentikasi HMAC-SHA256.
     - **User Key**: User key tambahan untuk otorisasi API.
     - **Username iCare**: Username akun iCare BPJS rumah sakit.
     - **Password iCare**: Password akun iCare BPJS rumah sakit.
   - Klik **Simpan** untuk menyimpan pengaturan.

2. **Mapping Dokter BPJS**
   - Pastikan tabel `maping_dokter_dpjpvclaim` sudah diisi dengan mapping antara kode dokter sistem lokal dan kode dokter BPJS.
   - Untuk mode PCare/FKTP, pastikan tabel `maping_dokter_pcare` juga sudah diisi.
   - Tanpa mapping ini, request iCare akan gagal karena kode dokter tidak ditemukan.

3. **Konfigurasi PCare (Mode FKTP)**
   - Untuk mode PCare, sistem juga menggunakan `pcare.consumerID`, `pcare.consumerSecret`, dan `pcare.consumerUserKey`.
   - Pastikan plugin PCare sudah dikonfigurasi jika ingin menggunakan fitur riwayat FKTP.

## Catatan

- Otentikasi API BPJS menggunakan HMAC-SHA256 dengan timestamp berbasis UTC.
- Respons API dienkripsi dengan algoritma LZ-String; modul akan mendekripsi otomatis.
- Pastikan server memiliki akses internet ke endpoint BPJS agar fitur riwayat dapat berfungsi.
- Kode aplikasi (`kdAplikasi`) yang digunakan adalah `095` (nilai tetap, tidak perlu diubah).
- Jika data riwayat tidak muncul, periksa kembali Cons ID, Secret Key, dan mapping dokter BPJS.
