# Plugin Jasa Medis

Dokumentasi singkat penggunaan modul **Jasa Medis** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Jasa Medis**.
- Pilih submenu sesuai kebutuhan:
  - Kelola (halaman utama)
  - Jasa Medis Dokter
  - Jasa Medis Perawat

## Panduan Pengguna (Petugas)

1. **Jasa Medis Dokter**
   - Buka submenu **Jasa Medis Dokter**.
   - Atur rentang tanggal pada filter **Tanggal Awal** dan **Tanggal Akhir**.
   - Gunakan kolom pencarian untuk memfilter berdasarkan kode dokter jika diperlukan.
   - Klik **Tampilkan** untuk memuat data remunerasi.
   - Laporan menampilkan rincian tindakan per dokter per tanggal perawatan beserta tarif tindakan dokter.
   - Gunakan tombol **Print** atau **Excel** untuk mengekspor laporan.

2. **Jasa Medis Perawat**
   - Buka submenu **Jasa Medis Perawat**.
   - Atur rentang tanggal dan gunakan pencarian berdasarkan NIP petugas bila diperlukan.
   - Klik **Tampilkan** untuk memuat data remunerasi paramedis/perawat.
   - Laporan menampilkan rincian tindakan per perawat per tanggal perawatan beserta tarif tindakan paramedis.
   - Gunakan tombol **Print** atau **Excel** untuk mengekspor laporan.

## Panduan Admin

1. **Tarif Tindakan Dokter**
   - Data tarif jasa medis dokter (`tarif_tindakandr`) bersumber dari tabel `rawat_jl_dr` dan dikaitkan dengan tabel `jns_perawatan`.
   - Pastikan tarif tindakan sudah diatur dengan benar di master data jenis perawatan agar perhitungan remunerasi akurat.

2. **Tarif Tindakan Paramedis**
   - Data tarif jasa paramedis (`tarif_tindakanpr`) bersumber dari tabel `rawat_jl_pr`.
   - Verifikasi tarif di master data jenis perawatan secara berkala.

3. **Data Billing**
   - Plugin ini mengambil data billing dari tabel `mlite_billing`.
   - Pastikan proses billing rawat jalan sudah berjalan dengan benar agar laporan jasa medis menampilkan data yang lengkap.

## Catatan

- Laporan hanya memuat data rawat jalan (rawat_jl_dr dan rawat_jl_pr); rawat inap menggunakan modul terpisah.
- Grand total remunerasi dihitung otomatis dari akumulasi semua tindakan dalam rentang tanggal yang dipilih.
- Pastikan data dokter dan petugas berstatus aktif agar muncul dalam laporan.
