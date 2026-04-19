# Plugin Kasir Rawat Jalan

Dokumentasi singkat penggunaan modul **Kasir Rawat Jalan** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Kasir Rawat Jalan**.
- Pilih submenu sesuai kebutuhan:
  - Kelola (daftar pasien rawat jalan)
  - Kasir (buka/tutup shift)
  - Laporan

## Panduan Pengguna (Petugas)

1. **Buka Shift Kasir**
   - Sebelum memulai transaksi, buka submenu **Kasir**.
   - Klik **Buka Kasir**, isi modal awal, lalu konfirmasi.
   - Shift hanya bisa dibuka sekali per sesi; pastikan modal awal sesuai dengan uang kas fisik.

2. **Proses Pembayaran Pasien Rawat Jalan**
   - Dari submenu **Kelola**, cari pasien berdasarkan tanggal kunjungan atau status periksa.
   - Klik nama pasien untuk membuka halaman **Rincian Billing**.
   - Rincian menampilkan komponen biaya: **Layanan**, **Obat**, **Laboratorium**, dan **Radiologi**.
   - Tambah atau hapus item pada masing-masing komponen bila diperlukan.
   - Setelah rincian lengkap, klik **Proses Bayar** untuk membuat faktur.
   - Pilih metode pembayaran, isi jumlah yang diterima, lalu simpan transaksi.
   - Cetak **Billing Kecil** (struk) atau **Billing Besar** (rincian lengkap) untuk diberikan ke pasien.

3. **Tambah Item Billing Manual**
   - Pada halaman rincian, tambahkan item layanan, obat, laboratorium, atau radiologi yang belum tercatat.
   - Gunakan form yang tersedia, pilih jenis item, isi kuantitas dan harga, lalu simpan.

4. **Tutup Shift Kasir**
   - Di akhir sesi, buka submenu **Kasir** lalu klik **Tutup Kasir**.
   - Sistem akan merekap total transaksi selama shift berjalan.
   - Konfirmasi penutupan shift setelah kas fisik dicocokkan.

5. **Laporan**
   - Buka submenu **Laporan** untuk melihat rekap transaksi kasir rawat jalan.
   - Filter berdasarkan rentang tanggal.
   - Gunakan tombol **Export** untuk mengunduh laporan dalam format Excel atau cetak langsung.

## Panduan Admin

1. **Kelola Daftar Pasien**
   - Submenu **Kelola** menampilkan semua kunjungan pasien rawat jalan beserta status periksa.
   - Filter berdasarkan tanggal kunjungan awal, tanggal kunjungan akhir, dan status periksa.

2. **Verifikasi Komponen Billing**
   - Pastikan data layanan, obat, laboratorium, dan radiologi sudah terisi lengkap dari modul terkait sebelum proses pembayaran.
   - Item billing dapat ditambah/dihapus manual dari halaman rincian jika terdapat koreksi.

3. **Invoice Digital**
   - Saat instalasi, plugin membuat folder `uploads/invoices` untuk menyimpan file invoice digital.
   - Pastikan folder tersebut dapat ditulis oleh web server (permission 0777).

4. **Pengiriman Email Faktur**
   - Sistem mendukung pengiriman faktur via email setelah pembayaran selesai.
   - Pastikan konfigurasi email (SMTP) sudah diatur di pengaturan sistem mLITE.

## Catatan

- Shift kasir harus dibuka terlebih dahulu sebelum transaksi pembayaran dapat diproses.
- Setiap transaksi tercatat dengan nomor rawat dan waktu billing; verifikasi rincian sebelum menyimpan untuk menghindari duplikasi.
- Billing besar dan billing kecil dapat dicetak ulang dari halaman rincian pasien selama data masih tersimpan.
- Berbeda dengan rawat inap, komponen rawat jalan tidak mencakup biaya tambahan akomodasi/kamar.
