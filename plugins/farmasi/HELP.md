# Plugin Farmasi

Pengelolaan data gudang farmasi: stok obat, BMHP, pengajuan, pemesanan, penerimaan, dan laporan pergerakan barang.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Farmasi**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Mutasi Obat & BHP
  - Pengajuan Obat & BMHP
  - Pemesanan Obat & BMHP
  - Penerimaan Obat & BMHP
  - Stok Opname
  - Darurat Stok
  - Detail Pemberian Obat
  - Riwayat Barang Medis
  - Pengaturan

## Panduan Pengguna (Petugas)

Petugas farmasi menggunakan modul ini untuk operasional harian gudang:

1. **Mutasi Obat & BHP**
   - Lihat daftar seluruh obat dan barang habis pakai (BHP) aktif maupun non-aktif berdasarkan tab status.
   - Kelola stok obat per depo/bangsal: tambah, ubah, atur ulang stok (`postSetStok`), atau re-stok (`postReStok`).
   - Nonaktifkan/hapus item obat dari daftar aktif bila sudah tidak digunakan.
   - Pulihkan item yang sudah dinonaktifkan (restore).

2. **Pengajuan Obat & BMHP**
   - Buat pengajuan kebutuhan obat/BMHP baru dengan memilih kode barang dan jumlah yang dibutuhkan.
   - Simpan pengajuan; setiap pengajuan memiliki nomor pengajuan unik.
   - Admin dapat menyetujui pengajuan melalui tombol **Approve**.

3. **Pemesanan Obat & BMHP**
   - Buat surat pemesanan obat/BMHP berdasarkan pengajuan yang sudah disetujui.
   - Isi data supplier, jumlah pesan, dan tanggal pemesanan.
   - Cetak **Surat Pemesanan Obat/BMHP** (PDF) untuk dikirim ke supplier.
   - Pantau status pemesanan (Draft → Terkirim → Selesai).

4. **Penerimaan Obat & BMHP**
   - Catat penerimaan obat/BMHP berdasarkan nomor pemesanan.
   - Isi jumlah yang diterima, nomor faktur, tanggal penerimaan, jenis pembayaran, dan tanggal jatuh tempo.
   - Stok gudang otomatis bertambah setelah penerimaan disimpan.

5. **Stok Opname**
   - Lakukan penghitungan stok fisik (opname) seluruh item sekaligus (`postOpnameAll`) atau per item (`postOpnameData`).
   - Perbarui hasil opname ke sistem (`postOpnameUpdate`).

6. **Darurat Stok**
   - Pantau daftar obat/BMHP yang stoknya di bawah batas minimum (stok darurat).
   - Gunakan laporan ini sebagai acuan untuk membuat pengajuan kebutuhan baru.

7. **Detail Pemberian Obat**
   - Lihat rincian pemberian obat kepada pasien berdasarkan filter yang tersedia.
   - Data mencakup nama pasien, nama obat, dosis, dan waktu pemberian.

8. **Riwayat Barang Medis**
   - Lacak seluruh pergerakan (masuk/keluar) barang medis per kode barang.
   - Filter berdasarkan periode dan jenis transaksi.

## Panduan Admin

1. **Pengaturan Depo Farmasi**
   - Buka submenu **Pengaturan**.
   - Atur kode bangsal untuk setiap depo: **Depo Ralan**, **Depo IGD**, **Depo Ranap**, dan **Gudang Utama**.
   - Isi **Keterangan Etiket**, **Embalase**, dan **Tuslah** sesuai kebijakan farmasi rumah sakit.
   - Simpan pengaturan.

2. **Manajemen Data Barang**
   - Tambahkan obat/BMHP baru melalui menu Mutasi Obat & BHP.
   - Pastikan setiap barang memiliki kode barang (`kode_brng`) yang unik.
   - Assign barang ke depo/bangsal yang sesuai melalui tabel `gudangbarang`.

3. **Persetujuan Pengajuan**
   - Admin farmasi menyetujui pengajuan yang dibuat petugas melalui tombol **Approve** di halaman Pengajuan Obat & BMHP.
   - Pengajuan yang disetujui dapat dilanjutkan ke proses pemesanan.

4. **Hak Akses**
   - Modul Farmasi memiliki sistem izin CRUD (`_crudPermissionsFarmasi`).
   - Pastikan akun petugas farmasi memiliki hak baca minimal agar dapat mengakses semua submenu.

## Catatan

- Alur kerja farmasi: **Pengajuan → Pemesanan → Penerimaan → Mutasi Stok**.
- Stok opname sebaiknya dilakukan secara berkala (bulanan/triwulan) untuk rekonsiliasi data sistem dengan fisik.
- Darurat Stok membantu memantau ketersediaan obat secara real-time; perhatikan daftar ini setiap hari.
- Surat Pemesanan Obat/BMHP dapat dicetak dalam format PDF langsung dari halaman Pemesanan.
- Data pemberian obat pasien bersumber dari resep yang diinput di modul Dokter (Ralan/IGD/Ranap).
