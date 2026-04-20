# Plugin Operasi

Dokumentasi singkat penggunaan modul **Operasi** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Operasi**.
- Pilih submenu sesuai kebutuhan:
  - Pasien Operasi
  - Booking Operasi
  - Paket Operasi
  - Obat Operasi
  - Laporan Operasi

## Panduan Pengguna (Petugas)

1. **Pasien Operasi**
   - Pilih rentang tanggal operasi lalu klik filter.
   - Cari pasien berdasarkan nomor rawat di kolom pencarian.
   - Klik tombol form untuk mengisi atau memperbarui data operasi.
   - Isi detail: tanggal operasi, jenis anestesi, kategori, operator 1–3, asisten operator, dokter anestesi, asisten anestesi, bidan, perawat luar, dan paket operasi.
   - Pilih status operasi lalu simpan.

2. **Obat Operasi (input per pasien)**
   - Dari halaman Pasien Operasi, buka rincian obat untuk nomor rawat tertentu.
   - Cari obat/BHP dari daftar obatbhp_ok menggunakan kolom pencarian.
   - Masukkan jumlah pemakaian lalu simpan.
   - Hapus item jika terjadi kesalahan input.

3. **Booking Operasi**
   - Tambah jadwal operasi baru: isi nomor rawat pasien, pilih dokter, ruang OK, paket operasi, tanggal, dan status booking.
   - Ubah atau hapus booking yang sudah ada melalui tombol aksi pada tabel.

4. **Laporan Operasi**
   - Tambah laporan pasca operasi per nomor rawat.
   - Isi data laporan termasuk permintaan PA (Patologi Anatomi) jika diperlukan.
   - Ubah atau hapus laporan yang sudah tersimpan.

## Panduan Admin

1. **Paket Operasi**
   - Tambah paket operasi baru: isi kode paket, nama perawatan, kategori, kelas, penjamin, dan rincian biaya (operator 1–3, asisten, anestesi, bidan, alat, sewa OK, sarpras, dll.).
   - Ubah atau hapus paket yang sudah ada.
   - Pastikan paket aktif (status = 1) agar bisa dipilih saat input pasien operasi.

2. **Obat Operasi (master)**
   - Tambah item obat/BHP kamar operasi ke daftar `obatbhp_ok`.
   - Isi kode obat, nama obat, satuan, dan harga satuan.
   - Ubah atau hapus item obat yang tidak relevan.

3. **Hak Akses**
   - Pengguna dengan role `medis` hanya dapat melihat data operasi yang menjadi operator1-nya sendiri.
   - Pengguna dengan role lain dapat melihat semua data operasi.

## Catatan

- Paket operasi harus sudah tersedia sebelum input pasien operasi agar biaya terhitung otomatis.
- Jika modul VClaim aktif, data pasien operasi dapat diintegrasikan dengan bridging SEP.
- Berkas digital pasien operasi dapat dilampirkan melalui menu form operasi jika master berkas digital sudah dikonfigurasi.
