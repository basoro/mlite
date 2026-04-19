# Plugin IGD

Modul pendaftaran dan pengelolaan kunjungan pasien di unit Instalasi Gawat Darurat (IGD).

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **IGD**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Petugas pendaftaran dan perawat IGD menggunakan modul ini untuk seluruh alur pelayanan pasien IGD:

1. **Kelola (Daftar Pasien IGD)**
   - Filter daftar pasien IGD berdasarkan rentang tanggal kunjungan, status periksa (Belum/Sudah/Lunas), dan status bayar.
   - Tampilkan daftar berkas digital yang tersedia untuk pasien terpilih.

2. **Pendaftaran Pasien Baru (Form)**
   - Daftarkan pasien baru ke IGD: isi nomor rekam medis, dokter, cara bayar (penjamin), dan keluhan utama.
   - Sistem otomatis membuat nomor rawat dan nomor antrian IGD.

3. **Status Daftar**
   - Ubah status pendaftaran pasien (mis. dari antre ke dalam pemeriksaan).
   - Kelola status lanjut perawatan pasien IGD.

4. **Antrian IGD**
   - Tampilkan nomor antrian IGD aktif untuk keperluan display antrean.

5. **Rincian Tindakan & Layanan**
   - Tambahkan tindakan dan layanan yang diberikan kepada pasien IGD.
   - Hapus item rincian yang salah.

6. **SOAP (Subyektif–Obyektif–Asesmen–Plan)**
   - Isi catatan SOAP pasien: keluhan, pemeriksaan fisik, diagnosis, dan rencana terapi.
   - Simpan atau hapus catatan SOAP.

7. **Obat & Resep**
   - Tambahkan resep obat untuk pasien IGD.
   - Atur aturan pakai setiap item obat.
   - Hapus resep yang tidak dibutuhkan.

8. **Berkas Digital**
   - Unggah atau kelola berkas digital pasien (formulir persetujuan, laporan, dll.) yang terhubung ke `master_berkas_digital`.
   - Simpan metadata berkas digital.

9. **Triase IGD**
   - Isi formulir triase pasien: kondisi klinis, kategori triase (P1–P4), dan waktu triase.
   - Simpan, hapus, atau tampilkan ulang data triase.

10. **Diagnosis ICD-10 & ICD-9**
    - Cari dan tambahkan kode diagnosis ICD-10 (penyakit) dan ICD-9 (prosedur).
    - Hapus kode diagnosis yang tidak sesuai.

11. **Odontogram & OHIS**
    - Untuk pasien gigi/mulut di IGD: isi kondisi gigi pada diagram odontogram.
    - Catat skor OHIS.
    - Simpan atau hapus data odontogram/OHIS.

12. **Lokalis (Gambaran Tubuh)**
    - Tandai lokasi keluhan/luka pada skema tubuh.
    - Simpan data lokalis pasien.

13. **Assessment Awal**
    - Isi formulir assessment awal pasien IGD (kondisi umum, kesadaran, dll.).
    - Simpan, lihat, atau hapus data assessment.

14. **Surat Keterangan**
    - Buat dan simpan **Surat Rujukan** ke fasilitas kesehatan lain.
    - Buat dan simpan **Surat Keterangan Sehat**.
    - Buat dan simpan **Surat Keterangan Sakit**.

15. **Persetujuan Umum**
    - Tampilkan formulir persetujuan umum pasien berdasarkan nomor rekam medis.

16. **Detail SEP BPJS**
    - Lihat detail Surat Eligibilitas Peserta (SEP) BPJS untuk pasien IGD yang menggunakan BPJS.

17. **Cetak & PDF**
    - Cetak ringkasan kunjungan IGD dalam format cetak (`postCetak`).
    - Generate dokumen PDF kunjungan IGD (`getCetakPdf`).

## Panduan Admin

1. **Kode Poli IGD**
   - Pastikan kode poliklinik IGD sudah dikonfigurasi di pengaturan sistem (`settings.igd`).
   - Seluruh pasien yang terdaftar di kode poli ini akan muncul di modul IGD.

2. **Data Master Pendukung**
   - Pastikan data **poliklinik**, **dokter**, **penjab**, dan **master_berkas_digital** sudah aktif dan lengkap.
   - Nomor antrian IGD di-generate berdasarkan konfigurasi poli IGD.

3. **Integrasi vClaim BPJS**
   - Jika plugin vClaim terpasang dan aktif, fitur SEP BPJS akan muncul otomatis di halaman Kelola.

4. **Integrasi Fingerprint/Biometrik**
   - Modul IGD mendukung integrasi fingerprint (`username_fp`, `password_fp`) dan perangkat FRISTA (`username_frista`, `password_frista`) yang dikonfigurasi di pengaturan sistem.

5. **REST API IGD**
   - Plugin IGD menyediakan endpoint REST API untuk integrasi sistem eksternal:
     - `apiList`: ambil daftar kunjungan IGD.
     - `apiShow($no_rawat)`: ambil detail kunjungan berdasarkan nomor rawat.
     - `apiCreate`: buat kunjungan IGD baru dari sistem eksternal.
   - Semua endpoint memerlukan kredensial yang valid.

## Catatan

- Modul IGD adalah modul **pendaftaran** sekaligus **klinis**; berbeda dengan Dokter IGD yang fokus pada pencatatan medis oleh dokter.
- Nomor rawat dan nomor antrian IGD di-generate otomatis oleh sistem saat pendaftaran.
- Berkas digital pasien IGD dapat ditandatangani secara elektronik menggunakan plugin E-Signature.
- Data rincian tindakan, obat, dan layanan dari modul ini terintegrasi langsung dengan modul billing/kasir.
- REST API IGD dapat digunakan untuk integrasi dengan kiosk pendaftaran mandiri atau sistem antrian eksternal.
