# Plugin WA Gateway

Dokumentasi singkat penggunaan modul **WA Gateway** di mLITE untuk integrasi pengiriman pesan, gambar, dan file melalui WhatsApp Gateway.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **WA Gateway**.
- Pilih submenu sesuai kebutuhan:
  - Manage
  - Send Message
  - Send Image
  - Send File
  - Settings

## Panduan Pengguna (Petugas)

1. **Send Message (Kirim Pesan Teks)**
   - Buka submenu **Send Message**.
   - Isi **nomor tujuan** (format internasional tanpa tanda `+`, misal: `628123456789`).
   - Isi **pesan** yang akan dikirimkan.
   - Klik **Kirim**; sistem akan mengirim pesan via API WA Gateway yang terkonfigurasi.
   - Notifikasi sukses atau gagal akan muncul setelah pengiriman.

2. **Send Image (Kirim Gambar)**
   - Buka submenu **Send Image**.
   - Isi nomor tujuan, URL gambar yang akan dikirim, dan caption (pesan teks pendamping gambar).
   - Klik **Kirim**; gambar dikirim ke nomor tujuan melalui WA Gateway.

3. **Send File (Kirim Dokumen)**
   - Buka submenu **Send File**.
   - Isi nomor tujuan, URL file dokumen (PDF, Word, dll.), dan pesan pendamping.
   - Klik **Kirim**; file dikirim sebagai dokumen WhatsApp.

## Panduan Admin

1. **Settings (Pengaturan WA Gateway)**
   - Buka submenu **Settings**.
   - Isi konfigurasi berikut:
     - **Server**: URL server WA Gateway (wajib HTTPS, misal: `https://mlite.id`).
     - **Token**: API token untuk autentikasi ke WA Gateway.
     - **Phone Number**: nomor WhatsApp pengirim yang terdaftar di WA Gateway (format internasional tanpa `+`).
   - Klik **Simpan**; sistem akan memverifikasi aktivasi WA Gateway secara otomatis ke server `mlite.id`.

2. **Verifikasi Koneksi**
   - Setelah menyimpan pengaturan, halaman **Manage** menampilkan status koneksi server WA Gateway dan nomor pengirim yang aktif.
   - Lakukan uji coba pengiriman melalui **Send Message** untuk memastikan konfigurasi berjalan dengan benar.

## Catatan

- URL server WA Gateway wajib menggunakan protokol **HTTPS**; koneksi HTTP atau ke IP lokal/private tidak diizinkan demi keamanan (SSRF protection).
- Fitur Send Message, Send Image, dan Send File pada modul ini berfungsi sebagai **alat uji coba** koneksi WA Gateway; pengiriman notifikasi otomatis ke pasien dilakukan oleh modul lain (mis. UTD, pendaftaran) yang terintegrasi dengan plugin ini.
- Token dan nomor pengirim bersifat sensitif; jangan bagikan ke pengguna yang tidak berwenang.
- Plugin lain seperti **UTD** menggunakan konfigurasi WA Gateway ini untuk mengirim notifikasi donor darah.
