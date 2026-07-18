# Plugin Veronisa

Dokumentasi singkat penggunaan modul **Veronisa** di mLITE untuk verifikasi dan pengiriman data obat kronis pasien JKN ke sistem Apotek Online BPJS.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Veronisa**.
- Pilih submenu sesuai kebutuhan:
  - Manage
  - Index
  - Apotek Online
  - Log Apotek Online
  - Mapping Obat
  - Monitoring Data Klaim
  - Pengaturan

## Panduan Pengguna (Petugas)

1. **Index (Daftar Pasien Obat Kronis)**
   - Buka submenu **Index** untuk melihat daftar pasien rawat jalan yang memiliki data Veronisa (nosep tercatat).
   - Filter berdasarkan tanggal registrasi dan kata kunci (no RM, no rawat, nama pasien).
   - Setiap baris menampilkan status pengajuan, nomor SEP, berkas digital perawatan, dan tautan PDF.
   - Klik **Kirim ke Apotek Online** untuk mengirim data resep pasien ke sistem Apotek Online BPJS.
   - Unggah berkas digital perawatan (foto resep/dokumen) melalui form berkas di baris pasien.
   - Hapus data Veronisa dengan tombol **Batal**.

2. **Apotek Online**
   - Buka submenu **Apotek Online** untuk melihat dan mengelola data resep yang siap dikirim ke Apotek Online BPJS.
   - Tampilkan detail obat per pasien, pilih obat yang sesuai, dan kirimkan ke sistem BPJS.

3. **Log Apotek Online**
   - Pantau riwayat pengiriman data ke Apotek Online beserta status respons dari BPJS.
   - Gunakan filter halaman untuk menelusuri log per periode.
   - Hapus log yang tidak diperlukan melalui tombol hapus.

4. **Monitoring Data Klaim**
   - Tampilkan rekapitulasi data klaim obat kronis yang sudah dikirim dan statusnya.
   - Filter berdasarkan rentang tanggal untuk monitoring bulanan.

5. **PDF Resep**
   - Dari halaman Index, klik ikon PDF untuk mencetak resume/resep pasien dalam format PDF.

## Panduan Admin

1. **Pengaturan**
   - Buka submenu **Pengaturan** untuk mengisi konfigurasi koneksi API BPJS Veronisa:
     - **Username** dan **Password**: kredensial login Apotek Online BPJS.
     - **Cons ID**: Consumer ID BPJS API.
     - **User Key** dan **Secret Key**: kunci autentikasi API BPJS.
     - **BPJS API URL**: endpoint API BPJS untuk Veronisa.
     - **Kode PPK**: kode Pemberi Pelayanan Kesehatan fasilitas ini.
     - **Obat Kronis**: kode item/kelompok obat yang termasuk obat kronis.

2. **Mapping Obat**
   - Buka submenu **Mapping Obat** untuk memetakan kode obat internal RS ke kode DPHO (Daftar dan Plafon Harga Obat) BPJS.
   - Tambah mapping baru dengan mengisi kode obat RS dan kode obat BPJS yang sesuai.
   - Mapping ini wajib dilengkapi agar pengiriman obat ke Apotek Online berhasil.
   - Hapus mapping yang tidak diperlukan melalui tombol hapus.

3. **Referensi DPHO**
   - Gunakan fitur referensi untuk mencari kode obat DPHO BPJS saat melakukan mapping.
   - Data referensi diambil dari API BPJS secara langsung.

## Catatan

- Plugin ini memerlukan koneksi internet aktif ke server API BPJS.
- Pastikan semua obat kronis yang diresepkan sudah di-mapping ke kode DPHO sebelum pengiriman ke Apotek Online.
- Data nosep (nomor SEP) harus sudah terdaftar di tabel `mlite_veronisa` sebelum pasien muncul di Index; daftarkan melalui form di halaman Index.
- Satu no rawat hanya dapat memiliki satu data Veronisa; pengiriman ulang akan memperbarui status yang ada.
- Log Apotek Online mencatat setiap percobaan pengiriman, termasuk yang gagal, sebagai referensi troubleshooting.
