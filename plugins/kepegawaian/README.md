# Plugin Kepegawaian

Dokumentasi singkat penggunaan modul **Kepegawaian** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Kepegawaian**.
- Pilih submenu sesuai kebutuhan:
  - Kelola (halaman utama)
  - Data Pegawai
  - Tambah Baru

## Panduan Pengguna (Petugas)

1. **Melihat Data Pegawai**
   - Buka submenu **Data Pegawai** untuk menampilkan daftar seluruh pegawai.
   - Gunakan fitur pencarian pada tabel untuk menemukan pegawai berdasarkan nama atau NIP.
   - Klik ikon **Detail** pada baris pegawai untuk melihat profil lengkap termasuk jabatan, departemen, status kerja, dan data kepegawaian lainnya.

2. **Cetak Daftar Pegawai**
   - Dari halaman **Data Pegawai**, klik tombol **Cetak** untuk mencetak atau mengekspor daftar seluruh pegawai dalam format PDF.

## Panduan Admin

1. **Tambah Pegawai Baru**
   - Buka submenu **Tambah Baru**.
   - Isi seluruh data pegawai yang wajib: **NIP** dan **Nama**.
   - Lengkapi data tambahan: jenis kelamin, jabatan, jenjang jabatan, kelompok jabatan, risiko kerja, departemen, bidang, status WP, status kerja, pendidikan, tanggal lahir, alamat, tanggal mulai kerja, gaji pokok, rekening bank, dan status aktif.
   - Upload foto pegawai (format gambar; akan di-crop otomatis menjadi 1:1 dan di-resize ke 512×512 px).
   - Klik **Simpan**. Sistem akan memvalidasi keunikan NIP sebelum menyimpan.

2. **Edit Data Pegawai**
   - Dari halaman **Data Pegawai**, klik ikon **Edit** pada baris pegawai yang akan diubah.
   - Perbarui data yang diperlukan lalu klik **Simpan**.
   - NIP tidak dapat diubah menjadi NIP yang sudah dimiliki pegawai lain.

3. **Lihat Detail Pegawai**
   - Klik ikon **Detail/View** untuk melihat profil pegawai secara lengkap, termasuk foto dan status petugas terkait.

4. **Master Data Referensi**
   - Sebelum menambah pegawai, pastikan tabel referensi sudah terisi: **jnj_jabatan** (jenjang jabatan), **kelompok_jabatan**, **resiko_kerja**, **departemen**, **bidang**, **stts_wp**, **stts_kerja**, **pendidikan**, **bank**, dan **emergency_index**.
   - Data referensi ini dikelola melalui modul atau tabel database terkait.

## Catatan

- NIP pegawai bersifat unik; sistem menolak penyimpanan jika NIP sudah terdaftar.
- Foto pegawai disimpan di direktori `webapps/penggajian/pages/pegawai/photo/` dengan nama file menggunakan NIP pegawai.
- Data pegawai terintegrasi dengan modul **Penggajian** dan tabel `petugas`; pastikan NIP pegawai konsisten di seluruh modul.
- Status aktif pegawai terdiri dari: **AKTIF**, **CUTI**, **KELUAR**, dan **TENAGA LUAR**.
