# Plugin Kasir Rawat Inap

Dokumentasi singkat penggunaan modul **Kasir Rawat Inap** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Kasir Rawat Inap**.
- Pilih submenu sesuai kebutuhan:
  - Kelola (daftar pasien rawat inap)
  - Kasir (buka/tutup shift)
  - Laporan

## Panduan Pengguna (Petugas)

1. **Buka Shift Kasir**
   - Sebelum memulai transaksi, buka submenu **Kasir**.
   - Klik **Buka Kasir**, isi modal awal, lalu konfirmasi.
   - Shift hanya bisa dibuka sekali per sesi; pastikan modal awal sesuai dengan uang kas fisik.

2. **Proses Pembayaran Pasien Rawat Inap**
   - Dari submenu **Kelola**, cari pasien berdasarkan tanggal masuk atau status pulang.
   - Klik nama pasien untuk membuka halaman **Rincian Billing**.
   - Rincian menampilkan komponen biaya: **Layanan**, **Obat**, **Laboratorium**, **Radiologi**, dan **Tambahan Biaya**.
   - Tambah atau hapus item pada masing-masing komponen bila diperlukan.
   - Setelah rincian lengkap, klik **Proses Bayar** untuk membuat faktur.
   - Pilih metode pembayaran, isi jumlah yang diterima, lalu simpan transaksi.
   - Cetak **Billing Kecil** (struk) atau **Billing Besar** (rincian lengkap) untuk diberikan ke pasien.

3. **Tambahan Biaya**
   - Pada halaman rincian, gunakan panel **Tambahan Biaya** untuk menambahkan biaya di luar paket standar (misal: biaya administrasi, akomodasi khusus).
   - Isi nama biaya, jumlah, lalu simpan.

4. **Tutup Shift Kasir**
   - Di akhir sesi, buka submenu **Kasir** lalu klik **Tutup Kasir**.
   - Sistem akan merekap total transaksi selama shift berjalan.
   - Konfirmasi penutupan shift setelah kas fisik dicocokkan.

5. **Laporan**
   - Buka submenu **Laporan** untuk melihat rekap transaksi kasir rawat inap.
   - Filter berdasarkan rentang tanggal.
   - Gunakan tombol **Export** untuk mengunduh laporan dalam format Excel atau cetak langsung.

## Panduan Admin

1. **Kelola Daftar Pasien**
   - Submenu **Kelola** menampilkan semua pasien rawat inap beserta status (masih dirawat/sudah pulang).
   - Filter berdasarkan tanggal masuk, tanggal keluar, status pulang, atau status periksa.

2. **Verifikasi Komponen Billing**
   - Pastikan data layanan, obat, laboratorium, dan radiologi sudah terisi lengkap dari modul terkait sebelum proses pembayaran.
   - Item billing dapat ditambah/dihapus manual dari halaman rincian jika terdapat koreksi.

3. **Pengiriman Email Faktur**
   - Sistem mendukung pengiriman faktur via email setelah pembayaran selesai.
   - Pastikan konfigurasi email (SMTP) sudah diatur di pengaturan sistem mLITE.

## Fitur Lock Billing

- Modul kasir rawat inap sekarang memiliki mekanisme **closing billing** untuk mencegah perubahan transaksi setelah kondisi tertentu terpenuhi.
- Lock ini berlaku pada proses yang berkaitan dengan uang, seperti:
  - tambah rincian billing manual
  - hapus rincian billing
  - simpan billing/faktur
  - pembuatan nota
  - pencetakan bukti pembayaran/invoice baru

### Aturan Lock

- Jika `reg_periksa.status_bayar = Sudah Bayar`, maka seluruh transaksi yang berkaitan dengan uang akan dikunci.
- Jika `reg_periksa.stts != Sudah`, maka kasir tidak dapat membuat billing baru, nota, atau bukti pembayaran.
- Jika billing sudah pernah tersimpan sebelumnya, cetak ulang billing tetap dapat dilakukan selama tidak terkena pembatasan proses baru.

### Bypass Admin

- Jika user yang login memiliki role `admin`, maka sistem **mengabaikan lock billing**.
- Admin tetap dapat:
  - menambah rincian
  - menghapus rincian
  - menyimpan billing
  - membuat nota
  - mencetak invoice
- Bypass ini dibuat agar admin tetap bisa melakukan koreksi atau intervensi operasional bila diperlukan.

## Catatan

- Shift kasir harus dibuka terlebih dahulu sebelum transaksi pembayaran dapat diproses.
- Setiap transaksi tercatat dengan nomor rawat dan waktu billing; duplikasi billing dapat dihindari dengan memverifikasi rincian sebelum menyimpan.
- Billing besar dan billing kecil dapat dicetak ulang dari halaman rincian pasien selama data masih tersimpan.
- Jika tombol simpan atau cetak nonaktif, periksa lebih dulu `status_bayar`, `stts` pemeriksaan, dan role user yang sedang login.
