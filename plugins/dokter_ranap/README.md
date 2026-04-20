# Plugin Dokter Ranap

Modul antarmuka dokter untuk pengelolaan rekam medis pasien rawat inap (opname).

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Dokter Ranap**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Dokter/perawat rawat inap menggunakan modul ini untuk mencatat seluruh proses perawatan selama opname:

1. **Kelola (Daftar Pasien Rawat Inap)**
   - Filter daftar pasien berdasarkan rentang tanggal masuk, status pulang, dan status periksa.
   - Klik nama pasien untuk membuka detail perawatan inap.

2. **Rincian Tindakan & Layanan**
   - Tambahkan tindakan harian dan layanan yang diberikan selama pasien dirawat inap.
   - Hapus item rincian yang salah.

3. **SOAP (Subyektif–Obyektif–Asesmen–Plan)**
   - Isi catatan SOAP harian: anamnesis, pemeriksaan fisik, diagnosis kerja, dan rencana terapi.
   - Simpan atau hapus catatan SOAP.

4. **Obat & Resep**
   - Tambahkan resep obat jadi maupun obat racikan untuk pasien rawat inap.
   - Atur aturan pakai setiap item obat.
   - Gunakan fitur **Copy Resep** untuk menduplikasi resep dari kunjungan sebelumnya.

5. **Laboratorium & Radiologi**
   - Buat permintaan laboratorium atau radiologi selama masa rawat inap.
   - Hapus nomor/item permintaan bila diperlukan.

6. **Kontrol**
   - Catat atau jadwalkan kunjungan kontrol pasien setelah keluar dari rawat inap.
   - Simpan atau hapus jadwal kontrol.

7. **Resume Medis**
   - Isi resume medis pasien rawat inap: ringkasan riwayat penyakit, terapi, dan kondisi pulang.
   - Simpan atau hapus resume.

8. **Catatan Medis Ranap**
   - Isi form catatan medis harian rawat inap (anamnesis lanjut, CPPT, tindakan keperawatan).
   - Simpan atau hapus catatan medis.

9. **Assessment Nyeri**
   - Isi formulir penilaian ulang nyeri pasien rawat inap (skala nyeri, lokasi, karakteristik).
   - Simpan, hapus, atau lihat riwayat penilaian nyeri.

10. **Vital Signs Chart**
    - Pantau tren tanda-tanda vital pasien rawat inap dalam bentuk grafik (tekanan darah, nadi, suhu, dll).

11. **Diagnosis ICD-10 & ICD-9**
    - Cari dan tambahkan kode diagnosis ICD-10 (penyakit) dan ICD-9 (prosedur).
    - Hapus kode diagnosis yang tidak sesuai.

12. **Pemeriksaan Ralan Sebelumnya**
    - Lihat data pemeriksaan rawat jalan pasien sebelum masuk rawat inap sebagai referensi.

## Panduan Admin

1. **Data Master Pendukung**
   - Pastikan data **bangsal**, **dokter**, **penjab**, dan **kamar** sudah aktif di master data.
   - Tanpa data bangsal yang benar, pasien rawat inap tidak akan dapat di-assign ke kamar.

2. **Hak Akses Pengguna**
   - Berikan akses modul Dokter Ranap kepada akun dokter dan perawat yang bertugas di bangsal rawat inap.

3. **Integrasi vClaim BPJS**
   - Jika plugin vClaim terpasang, data SEP BPJS pasien rawat inap dapat diakses dari modul ini.

## Catatan

- Modul Dokter Ranap mengelola data pasien yang memiliki nomor registrasi rawat inap (berbeda dengan rawat jalan).
- Assessment nyeri (penilaian_ulang_nyeri) terintegrasi dengan data perawat yang bertugas.
- Vital signs chart menampilkan grafik berbasis data pemeriksaan yang sudah diinput sebelumnya.
- Data rincian, obat, laboratorium, dan radiologi terintegrasi langsung dengan modul billing rawat inap.
