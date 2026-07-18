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

## Simulasi Penggunaan Tanpa Data Dummy (Tahun 2026)

Bagian ini contoh alur pengisian jika Anda memulai dari nol (tanpa “Data Dummy Keuangan”) untuk tahun 2026.

### A. Pengaturan Akun Rekening

Buat minimal beberapa akun berikut:

- **1101 Kas Umum** (Aktiva; tipe: N; saldo normal/balance: D)
- **1201 Bank** (opsional) (Aktiva; tipe: N; balance: D)
- **3101 Modal Disetor** (Modal; tipe: N; balance: K)
- **4101 Pendapatan Jasa** (Pendapatan; tipe: R; balance: K)
- **5101 Biaya Operasional** (Beban; tipe: R; balance: D)
- **2101 Hutang Usaha** (opsional) (Hutang; tipe: N; balance: K)

Catatan:
- Tipe **N** = akun Neraca (Aktiva/Hutang/Modal).
- Tipe **R** = akun Laba-Rugi (Pendapatan/Beban).

### B. Pengaturan Rekening Tahun (Saldo Awal 2026)

Menu: **Keuangan → Rekening Tahun** → pilih tahun **2026**.

Contoh saldo awal yang seimbang:
- 1101 Kas Umum = 10.000.000
- 3101 Modal Disetor = 10.000.000

Jika saldo awal tidak seimbang, laporan Neraca akan menampilkan peringatan selisih.

### C. Pengaturan Kegiatan (Pengaturan Rekening / Input Kegiatan)

Menu: **Keuangan → Pengaturan Rekening**.

Tujuan kegiatan adalah pengelompokan aktivitas (umumnya untuk pelaporan arus kas/kegiatan).

Contoh set minimal:
- Penerimaan Jasa → mapping rekening **4101**
- Biaya Operasional → mapping rekening **5101**
- Pembelian Kredit (opsional) → mapping rekening **2101** atau rekening beban terkait

### D. Contoh Pengisian Jurnal (Dana Masuk & Dana Keluar)

Menu: **Keuangan → Posting Jurnal** (atau input jurnal sesuai alur di instalasi Anda).

Pastikan setiap jurnal memiliki minimal 2 baris dan **Total Debet = Total Kredit**.

#### 1) Dana Masuk (Penerimaan Jasa Tunai)

- Tanggal: 2026-01-05
- Keterangan: Penerimaan jasa tunai
- Detail:
  - Debet 1101 Kas Umum = 2.000.000
  - Kredit 4101 Pendapatan Jasa = 2.000.000

#### 2) Dana Keluar (Biaya Operasional Dibayar Tunai)

- Tanggal: 2026-01-06
- Keterangan: Bayar listrik/ATK
- Detail:
  - Debet 5101 Biaya Operasional = 500.000
  - Kredit 1101 Kas Umum = 500.000

#### 3) Dana Keluar (Pembelian Kredit / Timbul Hutang) (Opsional)

- Tanggal: 2026-01-10
- Keterangan: Pembelian perlengkapan secara kredit
- Detail:
  - Debet 5101 Biaya Operasional = 1.200.000
  - Kredit 2101 Hutang Usaha = 1.200.000

### E. Cara Validasi Output Laporan

- **Buku Besar**:
  - Pilih 1101 untuk melihat saldo kas (saldo awal + mutasi).
  - Pilih 4101 untuk melihat total pendapatan.
  - Pilih 5101 untuk melihat total biaya.
- **Neraca Keuangan** (periode 01-01-2026 s/d tanggal akhir):
  - Aktiva harus sama dengan Pasiva + Modal (laba/rugi periode muncul sebagai penyesuaian modal).
- **Cash Flow**:
  - Jurnal “Dana Masuk” muncul di arus masuk, jurnal “Dana Keluar” muncul di arus keluar.

## Catatan

- Gunakan data akun dan jurnal yang valid agar laporan Cash Flow dan Neraca akurat.
- Lakukan pengecekan berkala antara jurnal, buku besar, dan neraca.
