# Plugin WA Gateway

Dokumentasi singkat penggunaan modul **WA Gateway** di mLITE untuk integrasi pengiriman pesan, gambar, dan file melalui WhatsApp Gateway.

Spesifikasi endpoint mengikuti [API Docs](https://wa.mlite.id/api-docs.json) (OpenAPI 3) — mencakup endpoint `kirimpesan`, `kirimgambar`, `kirimfile`, `kirimbutton`, `kirimtemplate`, `uploadimage`, dan `uploadfile`.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **WA Gateway**.
- Pilih submenu sesuai kebutuhan:
  - Manage
  - Send Message
  - Send Image
  - Send File
  - Send Button
  - Send Template
  - Upload Image
  - Upload File
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

4. **Send Button (Kirim Button Message)**
   - Buka submenu **Send Button**.
   - Isi nomor tujuan, isi pesan, label tombol 1, label tombol 2, dan footer (semuanya wajib).
   - Klik **Kirim**; pesan dengan 2 tombol quick reply terkirim ke nomor tujuan (endpoint `kirimbutton`).

5. **Send Template (Kirim Template Message)**
   - Buka submenu **Send Template**.
   - Isi nomor tujuan, pesan, dua template, dan footer.
   - Format tiap template: `{type}|{label}|{value}`, di mana `type` = `url` (tombol link) atau `call` (tombol telepon).
     Contoh: `url|Website|https://example.com` atau `call|Customer Service|081234567890`.
   - Klik **Kirim**; pesan dengan 2 tombol URL/telepon terkirim (endpoint `kirimtemplate`).

6. **Upload Image (Upload & Kirim Gambar)**
   - Buka submenu **Upload Image**.
   - Isi nomor tujuan, pilih file gambar (JPG/JPEG/PNG/GIF/WEBP, maksimal 50MB), dan caption (opsional).
   - Klik **Kirim**; file diunggah langsung ke gateway tanpa perlu hosting URL publik (endpoint `uploadimage`).

7. **Upload File (Upload & Kirim Dokumen)**
   - Buka submenu **Upload File**.
   - Isi nomor tujuan dan pilih file (PDF atau gambar, maksimal 50MB).
   - Klik **Kirim**; file gambar otomatis terkirim sebagai media gambar, selain itu sebagai dokumen (endpoint `uploadfile`).

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
- Endpoint kirim bersifat blocking sampai pesan terkirim (bisa beberapa detik); endpoint upload memberi batas waktu lebih longgar karena file dibaca dari memori (maksimal 50MB, response timeout 30 detik di sisi server).
- Respons API seragam `{status: bool, msg: string}`; pesan error dari gateway (mis. `The sender is not registered or not scan yet!`) ditampilkan apa adanya sebagai notifikasi gagal.
- Fitur Send Message, Send Image, dan Send File pada modul ini berfungsi sebagai **alat uji coba** koneksi WA Gateway; pengiriman notifikasi otomatis ke pasien dilakukan oleh modul lain (mis. UTD, pendaftaran) yang terintegrasi dengan plugin ini.
- Token dan nomor pengirim bersifat sensitif; jangan bagikan ke pengguna yang tidak berwenang.
- Plugin lain seperti **UTD** menggunakan konfigurasi WA Gateway ini untuk mengirim notifikasi donor darah.
