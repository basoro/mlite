# Modul E-Signature untuk mLITE

Modul ini menyediakan fitur Tanda Tangan Elektronik (TTE) yang mematuhi UU ITE, terintegrasi dengan Resume Medis, dan mendukung audit trail.

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
4.  Tabel `esignatures` akan otomatis dibuat di database.

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
