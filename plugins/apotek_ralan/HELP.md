# Plugin Apotek Ralan

Dokumentasi singkat penggunaan modul **Apotek Rawat Jalan** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Apotek Ralan**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Petugas apotek rawat jalan menggunakan menu **Kelola** untuk seluruh proses pelayanan resep:

1. **Melihat Daftar Resep**
   - Buka **Apotek Ralan → Kelola**.
   - Atur filter **Tanggal Kunjungan** (dari–sampai) dan **Status Periksa** (semua / belum / selesai / lunas).
   - Klik **Tampilkan** untuk memuat daftar pasien rawat jalan.

2. **Memproses Resep Pasien**
   - Klik baris pasien untuk membuka detail resep.
   - Periksa daftar obat yang diresepkan dokter.
   - Klik **Validasi Resep** untuk memvalidasi resep sebelum penyiapan obat.
   - Tambah item obat dengan mengisi kode barang, jumlah, aturan pakai, dan keterangan, lalu klik **Simpan**.
   - Untuk obat racikan, gunakan form **Racikan** — isi nama racikan, jumlah, dan komposisi obat.
   - Hapus item obat atau resep yang tidak sesuai menggunakan tombol hapus.

3. **Mencetak Etiket dan E-Resep**
   - Setelah resep divalidasi, klik **Cetak Etiket** untuk mencetak label etiket obat per item.
   - Klik **Cetak E-Resep** untuk mencetak keseluruhan resep elektronik pasien.

4. **Rincian Resep**
   - Buka **Rincian** untuk melihat detail biaya dan item resep yang sudah diproses.

## Panduan Admin

1. **Konfigurasi Depo Apotek Ralan**
   - Pastikan pengaturan `farmasi.deporalan` sudah mengarah ke kode bangsal/gudang apotek rawat jalan yang benar (diatur di modul Farmasi/Settings).
   - Kode bangsal ini digunakan saat validasi stok obat saat memproses resep.

2. **Integrasi VClaim**
   - Jika modul **VClaim** aktif, kolom SEP akan ditampilkan pada daftar pasien di Kelola.
   - Pastikan modul VClaim sudah dikonfigurasi sebelum menggunakan fitur ini.

## Catatan

- Resep hanya dapat diproses untuk pasien yang sudah terdaftar di rawat jalan (tabel `reg_periksa`).
- Validasi resep harus dilakukan sebelum obat dapat diserahkan kepada pasien.
- Cetak etiket mendukung format per item obat (termasuk aturan pakai dan nama pasien).
