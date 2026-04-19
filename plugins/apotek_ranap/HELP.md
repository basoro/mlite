# Plugin Apotek Ranap

Dokumentasi singkat penggunaan modul **Apotek Rawat Inap** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Apotek Ranap**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Petugas apotek rawat inap menggunakan menu **Kelola** untuk seluruh proses pelayanan resep pasien yang sedang dirawat:

1. **Melihat Daftar Pasien Rawat Inap**
   - Buka **Apotek Ranap → Kelola**.
   - Atur filter **Tanggal Masuk** (dari–sampai) dan **Status Pulang** (semua / belum pulang / sudah pulang).
   - Klik **Tampilkan** untuk memuat daftar pasien rawat inap.

2. **Memproses Resep Pasien**
   - Klik baris pasien untuk membuka detail resep rawat inap.
   - Periksa daftar obat yang diresepkan dokter untuk setiap episode rawat.
   - Klik **Validasi Resep** untuk memvalidasi resep sebelum penyiapan obat.
   - Tambah item obat dengan mengisi kode barang, jumlah, aturan pakai, dan keterangan, lalu klik **Simpan**.
   - Untuk obat racikan, gunakan form **Racikan** — isi nama racikan, jumlah, dan komposisi obat.
   - Hapus item obat atau resep yang tidak sesuai menggunakan tombol hapus.

3. **Mencetak Etiket dan E-Resep**
   - Setelah resep divalidasi, klik **Cetak Etiket** untuk mencetak label etiket obat per item.
   - Klik **Cetak E-Resep** untuk mencetak keseluruhan resep elektronik pasien rawat inap.

4. **Rincian Resep**
   - Buka **Rincian** untuk melihat detail biaya dan item resep yang sudah diproses per episode rawat.

## Panduan Admin

1. **Konfigurasi Depo Apotek Ranap**
   - Pastikan pengaturan `farmasi.deposranap` sudah mengarah ke kode bangsal/gudang apotek rawat inap yang benar (diatur di modul Farmasi/Settings).
   - Kode bangsal ini digunakan saat validasi stok obat ketika memproses resep rawat inap.

2. **Integrasi VClaim**
   - Jika modul **VClaim** aktif, kolom SEP akan ditampilkan pada daftar pasien.
   - Pastikan modul VClaim sudah dikonfigurasi agar data klaim JKN pasien rawat inap dapat diakses.

## Catatan

- Resep rawat inap hanya dapat diproses untuk pasien yang tercatat aktif di rawat inap (tabel `kamar_inap`).
- Filter berdasarkan tanggal masuk memudahkan pemantauan pasien lama maupun pasien baru yang masuk hari ini.
- Validasi resep harus dilakukan sebelum obat dapat disiapkan dan diserahkan ke ruang perawatan.
