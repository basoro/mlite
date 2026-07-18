# Plugin Website

Dokumentasi singkat penggunaan modul **Website** di mLITE untuk pembuatan dan pengelolaan berita serta halaman website fasilitas kesehatan.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Website**.
- Pilih submenu sesuai kebutuhan:
  - Index
  - Kelola Berita
  - Tambah Berita
  - Pengaturan Berita
  - Pengaturan Website

## Panduan Pengguna (Petugas)

1. **Tambah Berita**
   - Buka submenu **Tambah Berita**.
   - Isi **judul** artikel dan **konten** (wajib diisi).
   - Isi **intro** (ringkasan singkat yang ditampilkan di halaman daftar berita).
   - Isi **slug** URL (opsional; dibuat otomatis dari judul jika dikosongkan).
   - Tambahkan **tags** untuk kategorisasi artikel.
   - Unggah **foto cover** (opsional; ditampilkan sebagai thumbnail berita).
   - Pilih **status**: Draft (belum terbit), Sembunyi (tersimpan tapi tidak tampil), atau **Terbit** (langsung tampil di website).
   - Atur **tanggal terbit** sesuai kebutuhan.
   - Aktifkan atau nonaktifkan **komentar** untuk artikel ini.
   - Klik **Simpan**.

2. **Kelola Berita**
   - Buka submenu **Kelola Berita** untuk melihat seluruh artikel yang ada.
   - Tabel menampilkan judul, penulis, tanggal terbit, status, dan jumlah komentar.
   - Klik **Edit** untuk mengubah artikel yang sudah ada.
   - Klik **Hapus** atau centang beberapa artikel lalu klik **Hapus Terpilih** untuk menghapus massal.
   - Klik **Lihat** untuk membuka artikel di halaman publik website.

3. **Edit Berita**
   - Akses melalui tombol **Edit** di halaman Kelola Berita.
   - Ubah konten, status, cover foto, atau data lainnya.
   - Foto cover lama dapat dihapus melalui tombol **Hapus Cover**.
   - Slug diperbarui otomatis jika diubah, dengan penambahan angka urut jika slug sudah digunakan artikel lain.

## Panduan Admin

1. **Pengaturan Berita**
   - Buka submenu **Pengaturan Berita** untuk mengatur konfigurasi fitur berita:
     - Aktifkan/nonaktifkan komentar secara global.
     - Atur jumlah artikel per halaman.
     - Konfigurasi lainnya terkait tampilan daftar berita.

2. **Pengaturan Website**
   - Buka submenu **Pengaturan Website** untuk mengatur informasi umum website:
     - Nama/judul website.
     - Deskripsi dan kata kunci (SEO).
     - Informasi kontak, alamat, dan media sosial fasilitas kesehatan.
     - Konfigurasi tampilan halaman depan (Homepage).

3. **Manajemen Editor**
   - Jenis editor konten (teks biasa atau rich text) dapat dikonfigurasi melalui pengaturan global mLITE (`settings.editor`).
   - Plugin mendukung penulisan artikel dalam format **Markdown** (aktifkan opsi Markdown saat membuat/edit artikel).

## Catatan

- Halaman publik website dapat diakses di `/homepage` dan daftar berita di `/news`.
- Slug artikel bersifat unik; jika judul sama, sistem menambahkan angka urut otomatis pada slug.
- Foto cover yang dihapus dari sistem juga dihapus dari penyimpanan server secara permanen.
- Penghapusan artikel massal dilakukan dengan mencentang daftar di **Kelola Berita** lalu klik tombol **Hapus**.
- Artikel berstatus **Draft** hanya terlihat di panel admin; gunakan status **Terbit** agar artikel tampil di website publik.
