# Plugin JKN Mobile FKTP

Dokumentasi singkat penggunaan modul **JKN Mobile FKTP** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **JKN Mobile FKTP**.
- Pilih submenu sesuai kebutuhan:
  - Kelola (halaman utama)
  - Katalog
  - Mapping Poli
  - Mapping Dokter
  - Pengaturan

## Panduan Pengguna (Petugas)

1. **Katalog Antrian PCare**
   - Buka submenu **Katalog** untuk melihat daftar antrian dari aplikasi Mobile JKN khusus FKTP (Puskesmas/Klinik Pratama).
   - Pantau status antrian pasien yang masuk melalui PCare BPJS.
   - Gunakan fitur **Tambah Antrian** untuk mendaftarkan antrian baru jika diperlukan.
   - Gunakan fitur **Panggil Antrian** untuk memanggil nomor antrian berikutnya.
   - Gunakan fitur **Batal Antrian** untuk membatalkan antrian pasien yang tidak hadir.

2. **Tambah Antrian Manual**
   - Dari halaman Katalog, klik tombol **Tambah Antrian** untuk mendaftarkan pasien secara manual menggunakan nomor rekam medis.
   - Sistem akan mengirimkan permintaan antrian ke API PCare BPJS.

## Panduan Admin

1. **Pengaturan**
   - Buka submenu **Pengaturan**.
   - Isi **username** dan **password** akun PCare BPJS yang terdaftar untuk FKTP ini.
   - Isi kode wilayah: **kdprop** (kode provinsi), **kdkab** (kode kabupaten/kota), **kdkec** (kode kecamatan), **kdkel** (kode kelurahan).
   - Isi **kd_pj** (kode penanggung jawab) sesuai data FKTP di BPJS.
   - Atur **hari** (jumlah hari ke depan yang dapat dipesan; default 3 hari).
   - Simpan pengaturan sebelum menggunakan fitur antrian.

2. **Mapping Poli**
   - Buka submenu **Mapping Poli**.
   - Tambahkan mapping antara kode poli di sistem FKTP dengan kode poli di PCare BPJS.
   - Tanpa mapping yang benar, antrian tidak dapat dikirim ke poli yang sesuai.

3. **Mapping Dokter**
   - Buka submenu **Mapping Dokter**.
   - Tambahkan mapping antara kode dokter di sistem FKTP dengan kode dokter di PCare BPJS.

## Catatan

- Plugin ini khusus untuk **FKTP** (Fasilitas Kesehatan Tingkat Pertama) seperti Puskesmas dan Klinik Pratama; untuk RS rujukan gunakan plugin **JKN Mobile**.
- Kredensial PCare (**username** dan **password**) harus sesuai dengan akun yang terdaftar resmi di BPJS Kesehatan.
- Mapping poli dan dokter wajib dilengkapi sebelum fitur antrian dapat digunakan.
- Kode wilayah (provinsi, kabupaten, kecamatan, kelurahan) harus sesuai dengan data registrasi FKTP di BPJS.
