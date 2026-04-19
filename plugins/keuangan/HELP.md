# Plugin Keuangan

Dokumentasi singkat penggunaan modul **Keuangan** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Keuangan**.
- Pilih submenu sesuai kebutuhan:
  - Akun Rekening
  - Rekening Tahun
  - Pengaturan Rekening
  - Posting Jurnal
  - Jurnal Harian
  - Buku Besar
  - Cash Flow
  - Neraca Keuangan
  - Pengaturan

## Panduan Pengguna (Petugas)

User/petugas umumnya menggunakan fitur operasional dan pelaporan:

1. **Posting Jurnal**
   - Isi header jurnal (nomor, bukti, tanggal, jenis, kegiatan).
   - Tambahkan minimal 2 entry jurnal.
   - Pastikan **Total Debet = Total Kredit**.
   - Simpan jurnal.

2. **Jurnal Harian**
   - Pilih rentang tanggal.
   - Tampilkan daftar transaksi jurnal.
   - Gunakan tombol **Print** untuk cetak laporan.

3. **Buku Besar**
   - Pilih periode tanggal.
   - Tinjau mutasi dan saldo berjalan akun.
   - Gunakan **Print** bila diperlukan.

4. **Cash Flow**
   - Lihat ringkasan arus kas per kategori kegiatan.
   - Gunakan **Print** untuk laporan cetak.

5. **Neraca Keuangan**
   - Atur periode laporan.
   - Lihat posisi **Aktiva** dan **Pasiva**.
   - Gunakan **Print** atau **Excel** untuk ekspor.

## Panduan Admin

Admin mengelola master data dan konfigurasi:

1. **Akun Rekening**
   - Tambah/ubah/hapus kode akun, nama akun, tipe, dan balance.
   - Pastikan struktur kode akun konsisten untuk pelaporan.

2. **Rekening Tahun**
   - Input saldo awal akun per tahun.
   - Update atau hapus jika ada koreksi data awal.

3. **Pengaturan Rekening**
   - Kelola daftar kegiatan keuangan.
   - Mapping kegiatan ke akun rekening yang sesuai.

4. **Pengaturan**
   - Atur akun default modul keuangan (mis. akun kredit layanan).
   - Simpan perubahan konfigurasi.

## Catatan

- Gunakan data akun dan jurnal yang valid agar laporan Cash Flow dan Neraca akurat.
- Lakukan pengecekan berkala antara jurnal, buku besar, dan neraca.
