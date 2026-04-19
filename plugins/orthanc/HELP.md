# Plugin Orthanc

Dokumentasi singkat penggunaan modul **Orthanc** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Orthanc**.
- Pilih submenu sesuai kebutuhan:
  - Settings
  - Studies

## Panduan Pengguna (Petugas)

1. **Studies**
   - Lihat daftar studi DICOM yang tersimpan di server Orthanc.
   - Telusuri studi per pasien berdasarkan data yang tersedia.

2. **Melihat Gambar PACS dari Modul Lain**
   - Di halaman rawat inap atau rawat jalan, gunakan tombol bridging Orthanc (tersedia jika plugin aktif).
   - Sistem akan mencari data pasien di server Orthanc berdasarkan nomor rawat.
   - Tampilan menunjukkan series dan instances gambar DICOM yang tersedia.
   - Klik gambar untuk membuka viewer.

3. **Menyimpan Gambar PACS ke SIMRS**
   - Dari tampilan viewer PACS, pilih gambar yang ingin disimpan.
   - Klik tombol simpan ke SIMRS; gambar akan tersimpan di direktori radiologi dan dicatat di tabel `gambar_radiologi`.

4. **Hasil Baca Radiologi**
   - Radiolog dapat mengisi hasil pembacaan gambar melalui form hasil baca.
   - Hasil disimpan ke tabel `hasil_radiologi` untuk diakses dari modul klinis.

## Panduan Admin

1. **Settings**
   - Buka submenu **Settings** lalu isi konfigurasi koneksi Orthanc:
     - **Server**: URL server Orthanc (contoh: `http://localhost:8042`)
     - **Username**: nama pengguna Orthanc
     - **Password**: kata sandi Orthanc
   - Simpan pengaturan. Koneksi akan digunakan saat mengambil data PACS pasien.

2. **Konfigurasi AI API (Opsional)**
   - Isi **AI API Key** dan **AI API URL** untuk mengaktifkan fitur analisis AI pada gambar radiologi.
   - Gunakan tombol **Test OpenAI** untuk memverifikasi bahwa API key dan URL valid sebelum menyimpan.
   - AI API URL harus menggunakan protokol HTTPS dan domain publik (tidak boleh IP lokal).

3. **Keamanan URL**
   - Plugin memvalidasi semua URL yang digunakan untuk mengambil gambar dari Orthanc.
   - Hanya URL yang hostnya sesuai dengan server Orthanc yang dikonfigurasi yang diizinkan.
   - AI API URL harus berupa HTTPS dengan host publik.

## Catatan

- Server Orthanc harus dapat diakses dari server mLITE. Pastikan firewall dan jaringan sudah dikonfigurasi dengan benar.
- Gambar yang disimpan ke SIMRS disimpan di direktori `webapps/radiologi/pages/upload/`.
- Plugin ini terintegrasi dengan modul rawat inap dan rawat jalan melalui nomor rawat pasien.
- Saat instalasi, plugin otomatis membuat pengaturan default di tabel `mlite_settings` dengan nilai awal `http://localhost:8042`.
