# Plugin Dokter IGD

Modul antarmuka dokter untuk pengelolaan rekam medis pasien di unit IGD (Instalasi Gawat Darurat).

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Dokter IGD**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Dokter/perawat IGD menggunakan modul ini untuk mencatat seluruh proses pemeriksaan dan tindakan:

1. **Kelola (Daftar Pasien IGD)**
   - Filter daftar pasien berdasarkan rentang tanggal kunjungan dan status periksa (Belum/Sudah/Lunas).
   - Klik nama pasien untuk membuka detail kunjungan.

2. **Rincian Tindakan & Layanan**
   - Tambahkan tindakan (rawat_jl_dr) dan layanan yang diberikan kepada pasien.
   - Hapus item rincian bila ada input yang salah.

3. **SOAP (Subyektif–Obyektif–Asesmen–Plan)**
   - Isi catatan SOAP pasien: keluhan, pemeriksaan fisik, diagnosis kerja, dan rencana terapi.
   - Simpan atau hapus catatan SOAP.

4. **Obat & Resep**
   - Tambahkan resep obat jadi maupun obat racikan.
   - Atur aturan pakai setiap item obat.
   - Gunakan fitur **Copy Resep** untuk menduplikasi resep dari kunjungan sebelumnya.
   - Lihat dan kelola e-Resep (resep elektronik) melalui tampilan khusus eResep.

5. **Laboratorium & Radiologi**
   - Buat permintaan laboratorium atau radiologi untuk pasien.
   - Hapus nomor/item permintaan bila diperlukan.

6. **Triase IGD**
   - Isi formulir triase pasien IGD: kondisi klinis, kategori triase, dan waktu triase.
   - Simpan, hapus, atau tampilkan ulang data triase.

7. **Resume Medis IGD**
   - Isi resume medis pasien IGD setelah penanganan selesai.
   - Simpan atau hapus resume.

8. **Catatan Medis IGD**
   - Isi form catatan medis IGD (riwayat penyakit, pemeriksaan, tindakan).
   - Simpan atau hapus catatan medis.

9. **Lokalis (Gambaran Tubuh)**
   - Tandai lokasi keluhan/luka pada skema tubuh (lokalis).
   - Simpan data lokalis pasien.

10. **Kontrol**
    - Jadwalkan atau catat kunjungan kontrol pasien setelah keluar dari IGD.
    - Simpan atau hapus jadwal kontrol.

11. **Diagnosis ICD-10 & ICD-9**
    - Cari dan tambahkan kode diagnosis ICD-10 (penyakit) dan ICD-9 (prosedur).
    - Gunakan fitur **AI SNOMED mapping** untuk membantu pencarian kode ICD dari istilah klinis.
    - Hapus kode diagnosis yang tidak sesuai.

## Panduan Admin

Admin memastikan konfigurasi dasar modul Dokter IGD berjalan benar:

1. **Kode Poli IGD**
   - Pastikan kode poliklinik IGD (`igd`) sudah dikonfigurasi di pengaturan sistem (`settings.igd`).
   - Data pasien yang muncul di modul ini difilter berdasarkan kode poli IGD.

2. **Data Master Pendukung**
   - Pastikan data **poliklinik**, **dokter**, dan **penjab** sudah aktif (status = 1) di master data agar muncul di form.

3. **Integrasi vClaim BPJS**
   - Jika plugin vClaim terpasang dan aktif, fitur bridging BPJS akan muncul otomatis di halaman Kelola.

4. **Hak Akses Pengguna**
   - Berikan akses modul Dokter IGD kepada akun dokter/perawat IGD yang berwenang.

## Catatan

- Modul ini khusus menampilkan pasien yang terdaftar di poliklinik IGD sesuai kode yang dikonfigurasi di pengaturan sistem.
- Data SOAP, rincian tindakan, obat, laboratorium, dan radiologi terintegrasi langsung dengan modul billing/keuangan.
- Fitur AI SNOMED mapping menggunakan layanan eksternal; pastikan koneksi internet tersedia saat menggunakannya.
- Copy Resep memungkinkan pengambilan resep dari kunjungan lain milik pasien yang sama.
