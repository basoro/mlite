# Plugin Inventaris

Dokumentasi singkat penggunaan modul **Inventaris** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Inventaris**.
- Pilih submenu sesuai kebutuhan:
  - Manage (halaman utama)
  - Data Aset
  - Data Barang
  - Jenis Barang
  - Kategori Barang
  - Merk Barang
  - Produsen
  - Ruang
  - Pemeliharaan
  - Permintaan Perbaikan
  - Peminjaman

## Panduan Pengguna (Petugas)

1. **Permintaan Perbaikan**
   - Buka submenu **Manage**, lalu klik tombol **Permintaan Perbaikan Baru**.
   - Isi nomor permintaan, aset yang dimaksud, deskripsi kerusakan, dan tanggal.
   - Simpan. Permintaan akan masuk ke daftar dan dapat ditindaklanjuti oleh teknisi.
   - Untuk melihat detail atau memperbarui status, buka **Permintaan Perbaikan** lalu klik baris data.

2. **Pemeliharaan**
   - Buka submenu **Pemeliharaan**.
   - Klik **Tambah Pemeliharaan**, isi data aset, jenis pemeliharaan, tanggal, dan keterangan.
   - Simpan untuk mencatat riwayat pemeliharaan aset.

3. **Peminjaman**
   - Buka submenu **Peminjaman**.
   - Catat peminjaman barang/aset dengan mengisi data peminjam, barang, dan tanggal pinjam.
   - Update status pengembalian saat barang dikembalikan.

4. **Perbaikan**
   - Dari daftar **Permintaan Perbaikan**, klik tombol **Perbaikan** pada baris permintaan yang sudah diproses.
   - Isi data tindakan perbaikan, biaya, dan tanggal selesai, lalu simpan.

## Panduan Admin

1. **Data Aset**
   - Kelola daftar aset milik fasilitas (tambah, ubah, hapus).
   - Setiap aset memiliki nomor inventaris, nama, kategori, merk, produsen, dan ruang penempatan.

2. **Data Barang**
   - Tambah/ubah/hapus data barang yang dapat dipinjam atau dirawat.
   - Kaitkan barang dengan kategori, jenis, merk, dan produsen yang sudah didaftarkan.

3. **Master Data (Jenis, Kategori, Merk, Produsen, Ruang)**
   - Daftarkan terlebih dahulu data master sebelum menginput aset atau barang.
   - Buka masing-masing submenu (**Jenis**, **Kategori**, **Merk**, **Produsen**, **Ruang**), klik **Tambah**, isi data, dan simpan.

## Catatan

- Daftarkan seluruh master data (jenis, kategori, merk, produsen, ruang) sebelum menginput aset atau barang agar referensi tersedia.
- Nomor inventaris dan nomor permintaan dibuat otomatis oleh sistem.
- Riwayat pemeliharaan dan perbaikan tercatat per aset untuk keperluan audit dan laporan kondisi aset.
