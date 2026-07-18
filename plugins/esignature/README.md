# Plugin E-Signature

Modul Tanda Tangan Elektronik (TTE) Tersertifikasi untuk penandatanganan berkas digital di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **E-Signature**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Pengaturan

## Panduan Pengguna (Petugas)

Petugas/dokter menggunakan modul ini untuk menandatangani berkas digital pasien secara elektronik:

1. **Kelola (Riwayat Tanda Tangan)**
   - Lihat daftar 50 tanda tangan elektronik terbaru yang tersimpan di sistem.
   - Setiap entri mencatat jenis dokumen (`ref_type`), referensi dokumen (`ref_id`), penandatangan, dan waktu tanda tangan.

2. **Proses Tanda Tangan (Sign)**
   - Tanda tangan dipanggil dari modul lain (mis. Dokter Ralan, IGD) melalui tautan berkas digital.
   - Halaman tanda tangan menampilkan formulir dengan kanvas untuk menggambar tanda tangan.
   - Isi nama dan jabatan penandatangan, lalu gambar tanda tangan pada area yang tersedia.
   - Klik **Simpan** untuk menyimpan tanda tangan; file PNG tanda tangan disimpan di direktori server.

3. **Generate PDF**
   - Setelah tanda tangan tersimpan, sistem dapat menghasilkan dokumen PDF berkas digital yang sudah ditandatangani.
   - PDF berisi konten berkas digital beserta gambar tanda tangan elektronik.

## Panduan Admin

1. **Pengaturan Kode Berkas Digital**
   - Buka submenu **Pengaturan**.
   - Pilih **Kode Berkas Digital** yang akan diproses melalui modul E-Signature dari daftar `master_berkas_digital`.
   - Simpan pengaturan.

2. **Master Berkas Digital**
   - Pastikan jenis-jenis berkas digital (formulir persetujuan, resume medis, dll.) sudah terdaftar di tabel `master_berkas_digital`.
   - Setiap berkas digital yang akan ditandatangani secara elektronik harus terdaftar di master tersebut.

3. **Direktori Penyimpanan**
   - File tanda tangan (PNG) disimpan di `webapps/berkas/esignature/`.
   - Pastikan direktori ini dapat ditulis oleh web server (writable).

4. **Hak Akses**
   - Berikan akses modul E-Signature kepada dokter dan petugas yang berwenang menandatangani dokumen.

## Catatan

- Tanda tangan disimpan sebagai file gambar PNG dengan nama unik berbasis timestamp.
- Modul ini terintegrasi dengan berkas digital pasien dari modul IGD dan Rawat Jalan/Ranap melalui parameter `ref_type` dan `ref_id`.
- Pastikan direktori `webapps/berkas/esignature/` memiliki izin tulis sebelum menggunakan fitur tanda tangan.
- Riwayat tanda tangan hanya menampilkan 50 entri terakhir; untuk audit lengkap gunakan query langsung ke tabel `mlite_esignatures`.


# Panduan integrasi Modul E-Signature dengan modul lain

Modul ini menyediakan fitur Tanda Tangan Elektronik (TTE) yang mematuhi UU ITE, terintegrasi dengan Resume Medis dan modul lainnya serta mendukung audit trail.

## Fitur Utama
1.  **Tanda Tangan Digital**: Menggunakan canvas signature pad.
2.  **Audit Trail**: Mencatat IP, Waktu, User Agent, dan Hash Dokumen.
3.  **Integritas Data**: Menggunakan SHA-256 hash untuk setiap tanda tangan.
4.  **PDF Generation**: Menghasilkan PDF dengan tanda tangan tertanam (menggunakan mPDF).
5.  **Verifikasi Publik**: Halaman verifikasi untuk mengecek keaslian dokumen via Hash.

## Instalasi
1.  Copy folder `esignature` ke dalam folder `plugins/` di instalasi mLITE Anda.
2.  Login sebagai Administrator.
3.  Masuk ke menu **Modul**, cari **E-Signature**, lalu klik **Install/Aktifkan**.
4.  Tabel `mlite_esignatures` akan otomatis dibuat di database.

## Cara Penggunaan

### Integrasi dengan Resume Medis (Atau Modul Lain)
Untuk menambahkan tombol tanda tangan pada modul lain (misal: Resume Medis), tambahkan kode berikut pada file view modul tersebut (misal: `plugins/resume_medis/view/display.html`):

```html
<a href="{?=url([ADMIN, 'esignature', 'sign', 'resume_medis', $value.no_rawat])?}" 
   onclick="window.open(this.href, 'signwindow', 'width=600,height=500'); return false;" 
   class="btn btn-primary">
   <i class="fa fa-pencil"></i> Tanda Tangani
</a>
```

Ganti `'resume_medis'` dengan `ref_type` yang sesuai, dan `$value.no_rawat` dengan ID referensi unik.

### Verifikasi Dokumen
Setiap tanda tangan menghasilkan **Hash**. Hash ini dapat diverifikasi melalui URL publik:
`http://url-mlite-anda/esignature/verify/{HASH_STRING}`

Atau scan QR Code yang ada di dokumen PDF (jika diimplementasikan di template PDF).

## Legal Notice
Modul ini dirancang untuk memenuhi aspek:
1.  **Identitas**: Mencatat siapa yang menandatangani (User Login).
2.  **Integritas**: Hash SHA-256 menjamin dokumen tidak berubah.
3.  **Nir-sangkal**: Audit trail mencatat kapan dan dimana tanda tangan dilakukan.

Sesuai UU No 11 Tahun 2008 tentang ITE.

## Konfigurasi
Masuk ke menu **E-Signature > Pengaturan** untuk memilih mode (Internal, BSrE, atau PSrE). Saat ini mode default adalah **Internal** (Self-managed).