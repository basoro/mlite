# Plugin Penjualan

Dokumentasi singkat penggunaan modul **Penjualan** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Penjualan**.
- Pilih submenu sesuai kebutuhan:
  - Penjualan
  - Order Baru
  - Barang Jualan

## Panduan Pengguna (Petugas)

1. **Penjualan**
   - Halaman ini menampilkan daftar semua transaksi penjualan yang pernah dibuat.
   - Kolom **Total** menampilkan jumlah yang telah dibayar, atau "Belum Bayar" jika pembayaran belum diproses.
   - Gunakan tombol aksi pada setiap baris untuk membuka, melanjutkan order, atau menghapus transaksi.

2. **Order Baru**
   - Klik **Order Baru** untuk membuat transaksi penjualan baru.
   - Isi data pembeli: nama, alamat, nomor telepon, email, tanggal, jam, dan keterangan.
   - Cari barang dari daftar **Barang Jualan** atau dari stok farmasi depo ralan.
   - Masukkan nama barang, jumlah, dan harga. Harga diambil otomatis dari master barang.
   - Klik tambah untuk memasukkan item ke rincian order; stok barang akan langsung dikurangi.
   - Untuk order yang sudah ada (edit), masukkan item tambahan tanpa mengisi ulang data pembeli.

3. **Proses Pembayaran (Billing)**
   - Dari halaman order, buka form rincian penjualan untuk melihat total tagihan.
   - Isi jumlah total, potongan/diskon, jumlah harus bayar, dan jumlah yang dibayarkan.
   - Simpan billing. Status penjualan akan berubah dari "Belum Bayar" menjadi jumlah yang dibayar.

4. **Cetak Faktur**
   - Setelah billing tersimpan, cetak **Faktur Besar** (A4, dengan QR Code, disimpan otomatis sebagai PDF) atau **Faktur Kecil** (struk ringkas).
   - PDF faktur tersimpan di direktori `uploads/invoices/`.

5. **Hapus Transaksi**
   - Hapus transaksi penjualan beserta seluruh rincian dan data billing-nya melalui tombol hapus.

## Panduan Admin

1. **Barang Jualan**
   - Kelola daftar produk non-farmasi yang dijual bebas melalui submenu **Barang Jualan**.
   - Tambah barang baru: isi nama barang, stok awal, dan harga satuan.
   - Ubah atau hapus barang yang sudah ada.
   - Barang farmasi dari depo ralan (konfigurasi `farmasi.deporalan`) otomatis tersedia di halaman order tanpa perlu ditambahkan secara manual.

2. **Integrasi Stok Farmasi**
   - Jika barang yang dijual berasal dari depo farmasi, penjualan akan mengurangi stok di tabel `gudangbarang` dan mencatat riwayat keluar di `riwayat_barang_medis` dengan posisi "Penjualan".
   - Pastikan kode depo ralan sudah dikonfigurasi di pengaturan modul farmasi (`farmasi.deporalan`).

3. **Pengaturan Identitas Faktur**
   - Identitas faskes yang tercetak di faktur diambil dari pengaturan umum (`settings`).
   - Pastikan nama fasilitas kesehatan, alamat, dan logo sudah dikonfigurasi di pengaturan utama mLITE.

## Catatan

- Stok barang berkurang secara langsung saat item ditambahkan ke order, bukan saat billing.
- Faktur PDF digenerate otomatis menggunakan mPDF dan disimpan di server saat faktur besar dicetak.
- QR Code pada faktur besar berisi nama petugas yang memproses transaksi.
- Modul ini mendukung penjualan obat bebas dari depo ralan maupun produk non-farmasi yang didaftarkan di master Barang Jualan.
