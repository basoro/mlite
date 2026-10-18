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
  - Daftar Batch Obat
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

   **Mutasi dengan No. Batch & FEFO:**

   - Kolom **Lokasi & Stok** menampilkan info lengkap per lokasi: nama & kode bangsal, stok total, **No. Batch**, dan **tanggal Kedaluwarsa** batch utama di lokasi itu (dari ledger batch).
   - Tombol **Mutasi** membuka form dengan kolom:
     - **Pilih bangsal** - depo/bangsal tujuan mutasi.
     - **Stok** - jumlah yang dipindahkan.
     - **No. Batch (opsional)** - isi kode batch tertentu (mis. `12321`) untuk memaksakan batch itu yang keluar dari gudang; **kosongkan** untuk membiarkan sistem memilih otomatis dengan **FEFO** (batch dengan kedaluwarsa terdekat keluar lebih dulu).
     - **Harga Beli (opsional)** - isi bila menambah stok baru ke gudang dan ingin harga batch yang terekam; bila kosong, harga mengikuti batch sumber atau harga dasar barang.
   - Tombol **Re-Stok** (ikon putar di tiap lokasi depo) mengembalikan barang ke gudang; kolom **No. Batch (opsional)** berlaku dengan aturan FEFO yang sama.
   - Setiap potongan batch yang keluar/masuk tercatat di *Riwayat Barang Medis* dan *mutasibarang* dengan no. batch masing-masing, sehingga alokasi FEFO dapat dilacak.
   - Bila satu mutasi menyeberang dua batch (stok batch pertama kurang), sistem memecahnya menjadi beberapa potongan secara otomatis dan masing-masing tercatat dengan no. batch dan harga belinya sendiri.

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
   - Filter tambahan: **No. Batch** dan **Kedaluwarsa** (`all`/`expired`/`soon`/`safe`) melalui ledger batch `mlite_farmasi_obat_batch`; kolom kedaluwarsa ditandai warna.

8. **Riwayat Barang Medis**
   - Lacak seluruh pergerakan (masuk/keluar) barang medis per kode barang.
   - Filter berdasarkan periode dan jenis transaksi.

## Panduan Admin

1. **Pengaturan Depo Farmasi**
   - Buka submenu **Pengaturan**.
   - Atur kode bangsal untuk setiap depo: **Depo Ralan**, **Depo IGD**, **Depo Ranap**, dan **Gudang Utama**.
   - Isi **Keterangan Etiket**, **Embalase**, **Tuslah**, dan **Pajak Obat (%)** sesuai kebijakan farmasi rumah sakit.
   - Atur **Terapkan Nomor Batch: Iya / Tidak**:
     - **Iya** (default): stok per batch diperhitungkan FEFO, harga pasien mengikuti harga beli batch, billing dapat terdiri dari beberapa baris bila satu resep menyeberang dua batch.
     - **Tidak**: tagihan memakai harga master (`databarang.dasar`) dan billing satu baris per obat seperti alur sebelum fitur; stok per batch di ledger tetap tersusutkan secara senyap agar *Daftar Batch Obat* tetap akurat.
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

## API Farmasi

Endpoint API farmasi diakses di **`/admin/api/farmasi/{action}`** dan mengikuti dispatcher API mLITE (`admin/index.php`). Autentikasi sama dengan modul lain:

- Kirim **`X-Api-Key: <nomor-api-key>`** (API Key yang dibuat melalui Pengaturan API mLITE, tercatat di tabel `mlite_api_key`), atau
- Kirim **`Authorization: Bearer <token-jwt>`** hasil login `POST /admin/api/login`, atau
- Kirim kredensial pengguna: **`X-Username-Permission`** & **`X-Password-Permission`** (form-encoded/JSON).

Akses modul: akun pengguna/API client harus memiliki modul `farmasi`, dan izin CRUD `farmasi` menentukan aksi (`can_read` untuk endpoint baca, `can_create` untuk simpan, `can_update` untuk approve).

Semua endpoint list mengembalikan format seragam:

```json
{
  "status": "success",
  "data": [ ... ],
  "total": 123,
  "page": 1,
  "per_page": 10
}
```

| No | Method | Endpoint | Fungsi | Parameter utama |
|---|---|---|---|---|
| 1 | GET | `api/farmasi/stokList` | Stok per item per depot (gudangbarang + master barang + bangsal) | `page`, `per_page`, `s` (cari), `kd_bangsal`, `status` (1/0) |
| 2 | GET | `api/farmasi/batchList` | Stok per batch (ledger batch) + status kedaluwarsa | `page`, `per_page`, `s`, `kode_brng`, `kd_bangsal`, `expire` (`all`/`expired`/`soon`/`safe`) |
| 3 | GET | `api/farmasi/penerimaanList` | Daftar penerimaan obat & BMHP | `page`, `per_page`, `s`, `tgl_awal`, `tgl_akhir`, `no_batch` |
| 4 | GET | `api/farmasi/pemberianList` | Detail pemberian obat pasien (dengan expire batch & pasien) | `page`, `per_page`, `s`, `kode_brng`, `no_batch`, `tgl_awal`, `tgl_akhir`, `expire` (`all`/`expired`/`soon`/`safe`) |
| 5 | GET | `api/farmasi/pengajuanList` | Daftar pengajuan obat & BMHP | `page`, `per_page`, `s`, `status` (`Menunggu`/`Disetujui`/`Dipesan`/`all`) |
| 6 | POST | `api/farmasi/pengajuanSimpan` | Buat pengajuan baru; nomor otomatis `PGJ` bila kosong | body JSON/form: `kode_brng`, `jumlah`, `tanggal_pengajuan`, `catatan` |
| 7 | POST | `api/farmasi/pengajuanApprove` | Setujui pengajuan (status → `Disetujui`) | body JSON/form: `id` |

Contoh panggilan (curl):

```bash
# Stok obat per depot
curl -H "X-Api-Key: APIKEY" \
  "https://mlite.local/admin/api/farmasi/stokList?page=1&per_page=10&s=paracetamol"

# Batch kedaluwarsa ≤ 90 hari di gudang utama
curl -H "X-Api-Key: APIKEY" \
  "https://mlite.local/admin/api/farmasi/batchList?expire=soon"

# Buat pengajuan baru
curl -X POST -H "X-Api-Key: APIKEY" \
  -H "Content-Type: application/json" \
  -d '{"kode_brng":"BXXXX","jumlah":20,"catatan":"uji"}' \
  "https://mlite.local/admin/api/farmasi/pengajuanSimpan"
```

Contoh respons `pengajuanSimpan`:

```json
{
  "status": "success",
  "id": 12,
  "no_pengajuan": "PGJ202610180001",
  "kode_brng": "B0123",
  "nama_brng": "Paracetamol 500mg",
  "jumlah": 20
}
```

Kesalahan izin/aplikasi mengembalikan `{"status": "error", "message": "..."}` (dispatcher bisa juga memberi HTTP 401/403/404 dari token/akses).

## Catatan

- Penerimaan dapat mencatat **Nomor Batch** dan **Kedaluwarsa**; bila **Pengaturan › Terapkan Nomor Batch** aktif (`Iya`), stok dan billing mengikuti alokasi FEFO per batch.
- Alur kerja farmasi: **Pengajuan → Pemesanan → Penerimaan → Mutasi Stok**.
- Stok opname sebaiknya dilakukan secara berkala (bulanan/triwulan) untuk rekonsiliasi data sistem dengan fisik.
- Darurat Stok membantu memantau ketersediaan obat secara real-time; perhatikan daftar ini setiap hari.
- Surat Pemesanan Obat/BMHP dapat dicetak dalam format PDF langsung dari halaman Pemesanan.
- Data pemberian obat pasien bersumber dari resep yang diinput di modul Dokter (Ralan/IGD/Ranap).
- Pajak obat dihitung otomatis berdasarkan persentase pada Pengaturan Modul Farmasi dan diterapkan pada transaksi obat yang bersumber dari `databarang`.
