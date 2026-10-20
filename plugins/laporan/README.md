# Plugin Laporan

Plugin untuk mengelola berbagai laporan rekam medis pada mLITE, mencakup laporan TB, SEP BPJS, antrian online, serta 10 besar penyakit rawat jalan dan rawat inap.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Laporan**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Laporan TB
  - Laporan SEP BPJS
  - Laporan Antrian Online
  - 10 Besar Penyakit Ralan
  - 10 Besar Penyakit Ranap
  - Audit dan Review Rekam Medis
  - Penilaian Efektifitas ERM

## Panduan Pengguna (Petugas)

1. **Laporan TB (Tuberkulosis)**
   - Buka **Laporan → Laporan TB**.
   - Tentukan rentang tanggal registrasi (default: 1 Januari s.d. 31 Desember tahun berjalan).
   - Klik **Tampilkan** untuk memuat data pasien TB beserta tipe diagnosis, klasifikasi lokasi anatomi, hasil akhir pengobatan, dan tanggal mulai pengobatan.
   - Klik **Export Excel** untuk mengunduh data ke file spreadsheet.
   - Klik **Cetak PDF** untuk membuka halaman cetak laporan TB (semua pasien atau per individu).

2. **Laporan SEP BPJS**
   - Buka **Laporan → Laporan SEP BPJS**.
   - Atur rentang tanggal SEP (default: 1 Januari s.d. 31 Desember tahun berjalan).
   - Klik **Tampilkan** untuk memuat data: no SEP, no rawat, no rekam medis, nama pasien, no kartu BPJS, tanggal SEP, poli tujuan, diagnosa awal, dan jenis pelayanan.
   - Klik **Export Excel** untuk ekspor data, atau **Cetak PDF** untuk mencetak laporan.

3. **Laporan Antrian Online**
   - Buka **Laporan → Laporan Antrian Online**.
   - Atur rentang tanggal registrasi.
   - Laporan menampilkan data antrian rawat jalan: no rawat, no RM, nama pasien, tanggal dan jam registrasi, poli, dokter, status bayar, dan status periksa.
   - Gunakan tombol **Cetak PDF** untuk mencetak rekap antrian.

4. **10 Besar Penyakit Rawat Jalan**
   - Buka **Laporan → 10 Besar Penyakit Ralan**.
   - Atur rentang tanggal (default: awal bulan s.d. hari ini).
   - Sistem menampilkan 10 kode penyakit (ICD-10) dengan jumlah kasus terbanyak dari diagnosa pasien rawat jalan.
   - Gunakan **Export Excel** atau **Cetak PDF** untuk dokumentasi.

5. **10 Besar Penyakit Rawat Inap**
   - Buka **Laporan → 10 Besar Penyakit Ranap**.
   - Atur rentang tanggal, lalu lihat 10 diagnosis terbanyak dari pasien rawat inap.
   - Tersedia ekspor Excel dan cetak PDF.

6. **Audit dan Review Rekam Medis**
   - Buka **Laporan → Audit dan Review Rekam Medis**.
   - Atur rentang tanggal registrasi dan filter status layanan (Semua/Rawat Jalan/Rawat Inap).
   - Klik **Tampilkan Laporan** untuk memuat daftar kunjungan beserta status kelengkapan tiap komponen ERM: anamnesis & pemeriksaan, diagnosis, peresepan, berkas digital, dan resume medis (khusus rawat inap).
   - Setiap baris menampilkan skor kelengkapan (0–100), status audit (Lengkap/Kurang Lengkap), dan komponen yang masih kurang.
   - Ringkasan di atas tabel menampilkan total kunjungan, jumlah rekam medis lengkap/kurang lengkap, dan rata-rata skor (dihitung untuk seluruh rentang, bukan hanya halaman yang tampil).
   - Daftar kunjungan memakai paginasi server (50 baris per halaman) dengan kontrol halaman di bawah tabel; klik **Tampilkan Laporan** untuk kembali ke halaman pertama, dan gunakan pautan halaman untuk berpindah. Pencarian/pengurutan pada tabel hanya berlaku untuk halaman yang sedang tampil.
   - Gunakan **Export Excel** atau **Cetak PDF** untuk dokumentasi hasil audit — keduanya tetap memuat seluruh baris pada rentang tersebut.

7. **Penilaian Efektifitas ERM**
   - Buka **Laporan → Penilaian Efektifitas ERM**.
   - Atur rentang tanggal dan filter status layanan.
   - Laporan menampilkan indikator kelengkapan ERM per komponen (basis, terpenuhi, persentase, penilaian Baik/Cukup/Kurang) serta rekapitulasi per status layanan.
   - Skor efektivitas keseluruhan (rata-rata seluruh indikator) dipakai untuk menentukan predikat: **Sangat Efektif** (≥90%), **Efektif** (≥75%), atau **Kurang Efektif** (<75%).
   - Resume medis hanya dinilai pada kunjungan rawat inap (ditandai N/A untuk rawat jalan).
   - Gunakan **Export Excel** atau **Cetak PDF** untuk dokumentasi.

## Panduan Admin

1. **Pastikan Data Sumber Tersedia**
   - Laporan TB bergantung pada tabel `data_tb` yang diisi oleh modul terkait TB.
   - Laporan SEP BPJS bergantung pada tabel `bridging_sep` dari integrasi vclaim/BPJS.
   - Laporan antrian dan 10 besar penyakit bergantung pada tabel `reg_periksa` dan `diagnosa_pasien`.

2. **Tidak Ada Konfigurasi Tambahan**
   - Plugin Laporan tidak memiliki halaman pengaturan tersendiri; semua parameter diambil saat menampilkan laporan.
   - Pastikan modul vclaim aktif agar data SEP BPJS tersedia.

## Catatan

- Audit dan Review Rekam Medis serta Penilaian Efektifitas ERM disusun dari tabel yang sudah ada (`reg_periksa`, `pemeriksaan_ralan`/`pemeriksaan_ranap`, `diagnosa_pasien`, `resep_obat`, `berkas_digital_perawatan`, `resume_pasien`/`resume_pasien_ranap`); tidak ada tabel atau proses tambahan yang dibuat.
- Semua laporan mendukung filter rentang tanggal; jika tidak diisi, rentang default digunakan (awal tahun s.d. akhir tahun, atau awal bulan s.d. hari ini tergantung jenis laporan).
- Ekspor Excel mengunduh file langsung ke browser; pastikan pop-up tidak diblokir.
- Cetak PDF membuka halaman baru yang dapat dicetak atau disimpan sebagai PDF dari browser.
- Data laporan bersumber langsung dari database; tidak ada proses agregasi tambahan di luar waktu eksekusi laporan.
- Audit dan Review Rekam Medis memakai paginasi server-side dan query agregat untuk ringkasan, sehingga halaman tetap ringan meski rentang tanggal memuat puluhan ribu kunjungan (jumlah baris per halaman diatur lewat konstanta `AUDIT_REVIEW_PER_HALAMAN` di `Admin.php`).
