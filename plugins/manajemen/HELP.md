# Plugin Manajemen

Modul dashboard manajemen mLITE yang menampilkan statistik kunjungan, grafik poli, tren rawat inap dan rujukan, data cara bayar pasien, serta informasi presensi pegawai harian.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Manajemen**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Pengaturan

## Panduan Pengguna (Petugas)

1. **Dashboard Statistik Kunjungan**
   - Buka **Manajemen → Kelola** untuk melihat ringkasan statistik hari ini secara default.
   - Kartu statistik menampilkan:
     - Total pasien terdaftar (kumulatif).
     - Total kunjungan (visite) keseluruhan, tahun ini, bulan ini, dan hari ini.
     - Persentase perbandingan kunjungan tahun ini vs tahun lalu, bulan ini vs bulan lalu, hari ini vs kemarin.
   - 5 pasien terbaru yang mendaftar ditampilkan di bagian bawah dashboard.
   - Dokter yang bertugas hari ini (sesuai jadwal hari berjalan) ditampilkan secara acak.

2. **Filter Rentang Tanggal**
   - Gunakan form **Filter Tanggal** (parameter `start_date` dan `end_date` di URL) untuk melihat statistik pada rentang waktu tertentu.
   - Saat filter aktif, semua grafik dan kartu statistik menyesuaikan dengan rentang tanggal yang dipilih.

3. **Grafik Kunjungan**
   - **Grafik Per Poli**: distribusi kunjungan rawat jalan per poliklinik hari ini (atau rentang filter).
   - **Kunjungan Tahun**: tren kunjungan bulanan sepanjang tahun berjalan.
   - **Rawat Inap Tahun**: tren pasien rawat inap bulanan (status "Dirawat").
   - **Rujukan Tahun**: tren pasien dirujuk bulanan (status "Dirujuk").

4. **Cara Bayar**
   - Kartu **Tunai**, **BPJS**, dan **Lainnya** menampilkan jumlah kunjungan per jenis penanggung jawab pembayaran tahun ini (atau rentang filter).
   - Kode penjab untuk Tunai dan BPJS dikonfigurasi melalui menu Pengaturan.

5. **Presensi Pegawai**
   - Dashboard menampilkan jumlah pegawai yang sudah absen hari ini (dari `temporary_presensi` dan `rekap_presensi`).
   - Jumlah pegawai yang belum absen dihitung dari jadwal jaga dikurangi yang sudah absen.

## Panduan Admin

1. **Pengaturan Kode Penanggung Jawab**
   - Buka **Manajemen → Pengaturan**.
   - Isi **Kode Penjab Umum** (default: `UMU`) sesuai kode penjab pasien umum/tunai di tabel `penjab`.
   - Isi **Kode Penjab BPJS** (default: `BPJ`) sesuai kode penjab pasien BPJS.
   - Klik **Simpan**; pengaturan disimpan ke tabel `mlite_settings` dengan modul `manajemen`.
   - Pengaturan ini memengaruhi penghitungan kartu statistik cara bayar di dashboard.

2. **Verifikasi Data Jadwal Dokter**
   - Dashboard menampilkan dokter bertugas berdasarkan `jadwal.hari_kerja` yang sesuai dengan nama hari saat ini (format: SENIN, SELASA, dll.).
   - Pastikan data jadwal dokter diisi lengkap di master data agar tampilan akurat.

## Catatan

- Dashboard menggunakan data real-time dari database; tidak ada cache; refresh halaman untuk memperbarui data.
- Persentase perbandingan kunjungan hanya tersedia pada mode default (tanpa filter tanggal kustom).
- Fitur presensi bergantung pada tabel `temporary_presensi`, `rekap_presensi`, dan `jadwal_pegawai`; pastikan modul presensi aktif dan data terisi.
- Grafik menggunakan library Chart.js; pastikan koneksi internet tersedia jika aset dimuat dari CDN, atau gunakan aset lokal.
