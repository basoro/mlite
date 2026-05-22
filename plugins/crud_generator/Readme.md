# Plugin CRUD Generator

Dokumentasi singkat penggunaan modul **CRUD Generator** di mLITE untuk membuat modul/plugin baru berbasis tabel database (CRUD: Create, Read, Update, Delete).

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **CRUD Generator**.
- Pilih submenu **Kelola**.

## Panduan Pengguna (Admin)

1. **Muat Template**
   - Klik tombol **Load Files** untuk memuat template generator.
   - Template akan muncul di tab: **Info**, **Admin**, **Manage**, **Detail**, **Chart**, **Javascript**, **Styles**, dan **Snippets**.

2. **Pilih Tabel dan Field**
   - Klik ikon pencarian (tombol **Search**) di bagian **Tables** untuk memuat daftar tabel database.
   - Pilih tabel pada dropdown **Tables**.
   - Setelah tabel dipilih, dropdown **Fields** akan terisi daftar kolom dari tabel tersebut.
   - Atur field yang dipakai sesuai kebutuhan.

3. **Atur Informasi Modul**
   - Isi **Nama Modul** (disarankan huruf kecil dan underscore, contoh: `inventori_barang`).
   - Pilih **Kategori Modul**.
   - Isi **Icon Modul** (Font Awesome 4). Anda bisa mencari icon lewat modal pencarian icon.

4. **Generate Modul**
   - Klik tombol **Generate** untuk menulis file modul ke folder `MODULES/{nama_modul}` (folder plugin mLITE).
   - Setelah berhasil, refresh halaman admin dan modul baru akan muncul di menu sesuai kategori.

## Struktur Output

Generator akan membuat struktur modul standar mLITE, termasuk:

- `Info.php`
- `Admin.php`
- `view/admin/manage.html`
- `view/admin/detail.html`
- `view/admin/chart.html`
- `js/admin/scripts.js`
- `css/admin/styles.css`

## Catatan

- Pastikan folder plugin (direktori `MODULES/`) memiliki izin tulis oleh web server, karena generator membuat folder dan file secara otomatis.
- Kolom field pertama yang dipilih pada dropdown **Fields** digunakan sebagai acuan utama untuk aksi detail/edit/hapus. Pastikan field tersebut cocok (mis. primary key atau unique key).
- Fitur **Tambah Table Database** akan menjalankan query `CREATE TABLE` langsung ke database. Disarankan backup database terlebih dahulu.
