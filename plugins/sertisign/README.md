# Plugin Sertisign

Modul integrasi **Tanda Tangan Elektronik (TTE)** menggunakan layanan Sertisign di mLITE, mendukung TTE dengan QR visual maupun tanda tangan tak terlihat (invisible) pada dokumen PDF.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Sertisign**.
- Pilih submenu sesuai kebutuhan:
  - Manage
  - TTE QR Visual
  - TTE Invisible
  - Webhook Data
  - Tampil Webhook
  - Pengaturan

## Panduan Pengguna (Petugas)

1. **TTE QR Visual**
   - Buka **Sertisign → TTE QR Visual**.
   - Upload file PDF yang akan ditandatangani.
   - Isi data **Signer** dalam format JSON (email, nama, NIK penandatangan).
   - Atur posisi QR: halaman (`first`/`last`/`specific`), lebar (`w`), dan tinggi (`h`) dalam piksel.
   - Optionally isi **Flagging** (JSON) untuk metadata tambahan.
   - Klik **Proses TTE** — sistem mengirim dokumen ke API Sertisign endpoint `v2/poa/multiple-signing-qr-multiple`.
   - Jika berhasil, Transaction ID akan ditampilkan pada notifikasi.

2. **TTE Invisible**
   - Buka **Sertisign → TTE Invisible**.
   - Upload file PDF yang akan ditandatangani.
   - Isi data **Signer** dalam format JSON.
   - Pilih halaman tanda tangan dan optionally isi Flagging.
   - Klik **Proses TTE** — sistem mengirim ke endpoint `v2/poa/multiple-signing-invisible`.
   - TTE invisible tidak menempatkan gambar QR pada dokumen; tanda tangan tertanam dalam metadata PDF.

3. **TTE dari ERM (Electronic Medical Record)**
   - TTE dapat dipicu langsung dari halaman rekam medis pasien (fitur `postSigningQrERM`).
   - File PDF yang ditandatangani harus sudah ada di `uploads/sertisign/` dengan nama format `Riwayat_Perawatan_{no_rkm_medis}_{no_rawat}.pdf`.
   - Sertakan parameter `no_rkm_medis` dan `no_rawat` beserta data Signer.

4. **Tampil Webhook**
   - Buka **Sertisign → Tampil Webhook** untuk melihat data callback dari Sertisign.
   - Menampilkan daftar webhook yang diterima beserta transaction ID, payload, dan waktu terima.
   - Gunakan filter `transaction_id` (via parameter GET) untuk mencari webhook tertentu.

## Panduan Admin

1. **Pengaturan (Wajib dikonfigurasi pertama kali)**
   - Buka **Sertisign → Pengaturan** dan isi:
     - **API Host**: URL base API Sertisign (default: `https://api-stag.sertisign.id/` untuk staging).
     - **API Key**: API Key yang diperoleh dari dashboard Sertisign.
   - Klik **Simpan** — konfigurasi disimpan ke tabel `mlite_settings` dengan modul `sertisign`.
   - Ganti URL ke endpoint produksi saat siap go-live.

2. **Manajemen Penandatangan (Signer)**
   - Sebelum dapat menandatangani, penandatangan harus didaftarkan ke Sertisign:
     - **Register Base**: daftarkan penandatangan dengan NIK, nama, tanggal lahir, email, nomor HP, dan foto selfie.
     - **Register Subscribe**: hubungkan email penandatangan dengan ID PoA.
     - **Activate PIN**: aktifkan PIN penandatangan via email.
     - **Reset PIN**: reset PIN jika diperlukan.
   - Fungsi-fungsi ini tersedia secara programatik melalui metode API di Admin.php.

3. **Webhook**
   - Daftarkan URL webhook Sertisign ke `{domain}/admin/sertisign/datawebhook`.
   - Data webhook yang masuk disimpan otomatis ke tabel `mlite_sertisign_webhook`.
   - Monitor data webhook melalui **Tampil Webhook** untuk memastikan callback diterima dengan benar.

## Catatan

- Format **Signer** harus berupa JSON string sesuai spesifikasi API Sertisign (bukan array PHP).
- Ukuran QR default adalah 40×40 piksel; sesuaikan `w` dan `h` sesuai kebutuhan dokumen.
- File PDF untuk TTE dari ERM harus sudah digenerate terlebih dahulu dan ditempatkan di `uploads/sertisign/`.
- Ganti `api_host` dari staging (`api-stag.sertisign.id`) ke produksi sebelum digunakan di lingkungan nyata.
