# Plugin E-Signature

Modul Tanda Tangan Elektronik (TTE) Tersertifikasi untuk penandatanganan berkas digital di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **E-Signature**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Pengaturan

## Panduan Pengguna (Petugas)

Petugas/dokter menggunakan modul ini untuk menandatangani berkas digital pasien secara elektronik:

1. **Kelola (Riwayat Tanda Tangan)**
   - Lihat daftar 50 tanda tangan elektronik terbaru yang tersimpan di sistem.
   - Setiap entri mencatat jenis dokumen (`ref_type`), referensi dokumen (`ref_id`), penandatangan, dan waktu tanda tangan.

2. **Proses Tanda Tangan (Sign)**
   - Tanda tangan dipanggil dari modul lain (mis. Dokter Ralan, IGD) melalui tautan berkas digital.
   - Halaman tanda tangan menampilkan formulir dengan kanvas untuk menggambar tanda tangan.
   - Isi nama dan jabatan penandatangan, lalu gambar tanda tangan pada area yang tersedia.
   - Klik **Simpan** untuk menyimpan tanda tangan; file PNG tanda tangan disimpan di direktori server.

3. **Generate PDF**
   - Setelah tanda tangan tersimpan, sistem dapat menghasilkan dokumen PDF berkas digital yang sudah ditandatangani.
   - PDF berisi konten berkas digital beserta gambar tanda tangan elektronik.

## Panduan Admin

1. **Pengaturan Kode Berkas Digital**
   - Buka submenu **Pengaturan**.
   - Pilih **Kode Berkas Digital** yang akan diproses melalui modul E-Signature dari daftar `master_berkas_digital`.
   - Simpan pengaturan.

2. **Master Berkas Digital**
   - Pastikan jenis-jenis berkas digital (formulir persetujuan, resume medis, dll.) sudah terdaftar di tabel `master_berkas_digital`.
   - Setiap berkas digital yang akan ditandatangani secara elektronik harus terdaftar di master tersebut.

3. **Direktori Penyimpanan**
   - File tanda tangan (PNG) disimpan di `webapps/berkas/esignature/`.
   - Pastikan direktori ini dapat ditulis oleh web server (writable).

4. **Hak Akses**
   - Berikan akses modul E-Signature kepada dokter dan petugas yang berwenang menandatangani dokumen.

## Catatan

- Tanda tangan disimpan sebagai file gambar PNG dengan nama unik berbasis timestamp.
- Modul ini terintegrasi dengan berkas digital pasien dari modul IGD dan Rawat Jalan/Ranap melalui parameter `ref_type` dan `ref_id`.
- Pastikan direktori `webapps/berkas/esignature/` memiliki izin tulis sebelum menggunakan fitur tanda tangan.
- Riwayat tanda tangan hanya menampilkan 50 entri terakhir; untuk audit lengkap gunakan query langsung ke tabel `mlite_esignatures`.
