# Plugin Dokter Ralan

Modul antarmuka dokter untuk pengelolaan rekam medis pasien rawat jalan di semua poliklinik (di luar IGD).

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Dokter Ralan**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Dokter Ralan
  - Pengaturan

## Panduan Pengguna (Petugas)

Dokter/perawat rawat jalan menggunakan modul ini untuk mencatat seluruh proses pemeriksaan:

1. **Dokter Ralan (Daftar Pasien Rawat Jalan)**
   - Filter daftar pasien berdasarkan rentang tanggal kunjungan dan status periksa (Belum/Sudah).
   - Tampilan pasien dapat dibatasi per dokter atau per poliklinik sesuai konfigurasi.
   - Mendukung fitur panggil antrian dengan suara (ResponsiveVoice) jika diaktifkan.

2. **Rincian Tindakan & Layanan**
   - Tambahkan tindakan dan layanan yang diberikan kepada pasien rawat jalan.
   - Hapus item rincian bila ada input yang salah.

3. **SOAP (Subyektif–Obyektif–Asesmen–Plan)**
   - Isi catatan SOAP: anamnesis, pemeriksaan fisik, diagnosis kerja, dan rencana terapi.
   - Simpan atau hapus catatan SOAP.

4. **Obat & Resep**
   - Tambahkan resep obat jadi maupun obat racikan.
   - Atur aturan pakai setiap item obat.
   - Gunakan fitur **Copy Resep** untuk menduplikasi resep dari kunjungan sebelumnya.
   - Lihat dan kelola e-Resep melalui tampilan eResep.

5. **Laboratorium & Radiologi**
   - Buat permintaan laboratorium atau radiologi untuk pasien.
   - Hapus nomor/item permintaan bila diperlukan.

6. **Kontrol**
   - Jadwalkan atau catat kunjungan kontrol pasien rawat jalan.
   - Dukung penjadwalan kontrol BPJS (surat kontrol BPJS/SEP) melalui `postSaveKontrolBPJS`.
   - Simpan atau hapus jadwal kontrol.

7. **Resume Medis Ralan**
   - Isi resume medis pasien rawat jalan setelah pemeriksaan selesai.
   - Simpan atau hapus resume.

8. **Catatan Medis Ralan**
   - Isi form catatan medis ralan (anamnesis, pemeriksaan, tindakan medis).
   - Simpan atau hapus catatan medis.

9. **Odontogram & OHIS**
   - Untuk pasien poli gigi: isi kondisi gigi pada diagram odontogram.
   - Catat skor OHIS (Oral Hygiene Index Simplified).
   - Simpan atau hapus data odontogram/OHIS.

10. **Surat Keterangan**
    - Buat dan cetak **Surat Rujukan** ke fasilitas kesehatan lain.
    - Buat dan cetak **Surat Keterangan Sehat**.
    - Buat dan cetak **Surat Keterangan Sakit** (dengan tanggal sakit).
    - Simpan masing-masing surat sesuai kebutuhan.

11. **Rujukan Internal**
    - Buat rujukan internal antar poliklinik dalam satu faskes.
    - Edit atau hapus data rujukan internal.

12. **Diagnosis ICD-10 & ICD-9**
    - Cari dan tambahkan kode diagnosis ICD-10 (penyakit) dan ICD-9 (prosedur).
    - Gunakan fitur **AI SNOMED mapping** untuk membantu pencarian kode ICD dari istilah klinis.
    - Hapus kode diagnosis yang tidak sesuai.

## Panduan Admin

1. **Pengaturan Modul Dokter Ralan**
   - Buka submenu **Pengaturan**.
   - Simpan konfigurasi khusus modul dokter rawat jalan (mis. mode tampilan per dokter).

2. **Pembatasan Tampilan Per Dokter**
   - Aktifkan opsi `dokter_ralan_per_dokter` di pengaturan sistem agar setiap dokter hanya melihat pasien miliknya.
   - Jika tidak diaktifkan, dokter melihat pasien sesuai poliklinik yang di-assign pada akunnya.

3. **Data Master Pendukung**
   - Pastikan data **poliklinik**, **dokter**, dan **penjab** aktif (status = 1) di master data.
   - Pastikan mapping dokter ke kode BPJS (`maping_dokter_dpjpvclaim`) sudah diisi untuk fitur kontrol BPJS.

4. **Fitur Panggil Antrian (ResponsiveVoice)**
   - Aktifkan `settings.responsivevoice = true` agar sistem dapat memanggil nama pasien dengan suara.

5. **Integrasi vClaim BPJS**
   - Jika plugin vClaim terpasang dan aktif, fitur SEP/BPJS otomatis muncul di halaman Dokter Ralan.

## Catatan

- Modul ini menampilkan semua pasien rawat jalan di luar poliklinik IGD (difilter menggunakan kode `settings.igd`).
- Data SOAP, rincian, obat, laboratorium, dan radiologi terintegrasi langsung dengan modul billing.
- Fitur AI SNOMED mapping menggunakan layanan eksternal; pastikan koneksi internet tersedia.
- Surat rujukan, surat sehat, dan surat sakit dapat dicetak langsung dari halaman detail pasien.
