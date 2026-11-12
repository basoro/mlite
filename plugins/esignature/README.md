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

2. **Passphrase Pribadi**
   - Form pembuatan/pengubahan passphrase pribadi berada di plugin **Profil** sebagai submodul **Passphrase TTE** (hanya tampil bila plugin eSignature aktif). Halaman **Passphrase** di plugin ini khusus admin.
   - Passphrase disimpan dalam bentuk hash (`password_hash`) di tabel `mlite_esignature_passphrase` dan tidak dapat dibaca petugas lain.
   - Passphrase wajib dibuat sebelum dapat menandatangani dokumen.
   - Setelah 5 kali percobaan passphrase salah saat menandatangani, penandatanganan dikunci 15 menit (dicatat di tabel `mlite_esignature_sign_attempt`).
   - Pembatasan yang sama berlaku saat mengganti passphrase lama: 5 kali salah → penggantian dikunci 15 menit (tabel `mlite_esignature_passphrase_attempt`).

   **Penetapan & reset passphrase oleh admin:**
   - Administrator membuka submenu **Passphrase** pada plugin eSignature; pengguna non-admin yang membukanya diarahkan ke Profil > Passphrase TTE. Tabel *Penetapan Passphrase Pengguna* berisi seluruh pengguna dari tabel `mlite_users` beserta status passphrase-nya.
   - Daftar pengguna dapat dicari berdasarkan nama lengkap atau username (kolom **Cari**) dan ditampilkan 10 baris per halaman dengan navigasi halaman. Kata kunci dan halaman aktif tetap dipertahankan setelah aksi Simpan/Reset.
   - Kolom **Tetapkan Passphrase (Admin)** dipakai untuk memberikan/mengganti passphrase pengguna langsung dari daftar tersebut (minimal 6 karakter).
   - Tombol **Reset** menghapus passphrase pengguna (dan catatan percobaan salah), sehingga pengguna wajib membuat passphrase baru sebelum menandatangani lagi. Riwayat tanda tangan tidak berubah.
   - Setiap penetapan dan reset dicatat di tabel `mlite_esignature_passphrase_log` (pengguna, aksi, admin pelaku, waktu, IP, keterangan) dan ditampilkan pada panel **Riwayat Aktivitas Passphrase (Audit)** di halaman yang sama.
   - Panel audit juga dapat dicari (pengguna, pelaku, atau keterangan) melalui kolom **Cari** dan ditampilkan 10 aktivitas per halaman dengan navigasi halaman.

3. **Proses Tanda Tangan (Sign)**
   - Tanda tangan dipanggil dari modul lain (mis. Dokter Ralan, IGD) melalui tautan berkas digital.
   - Halaman tanda tangan menampilkan identitas penandatangan dan kolom **passphrase** (tanpa kanvas gambar tanda tangan).
   - Masukkan passphrase lalu klik **Tandatangani Dokumen**. Passphrase diverifikasi terhadap hash milik pengguna yang sedang login.
   - Sistem menyimpan hash SHA-256 tanda tangan beserta audit trail (IP, waktu, user agent).

4. **Generate PDF**
   - Setelah tanda tangan tersimpan, sistem dapat menghasilkan dokumen PDF berkas digital yang sudah ditandatangani.
   - PDF memuat blok tanda tangan elektronik (nama, jabatan, waktu, keterangan "Ditandatangani dengan passphrase") beserta QR verifikasi.

## Panduan Admin

1. **Pengaturan Kode Berkas Digital**
   - Buka submenu **Pengaturan**.
   - Pilih **Kode Berkas Digital** yang akan diproses melalui modul E-Signature dari daftar `master_berkas_digital`.
   - Simpan pengaturan.

2. **Master Berkas Digital**
   - Pastikan jenis-jenis berkas digital (formulir persetujuan, resume medis, dll.) sudah terdaftar di tabel `master_berkas_digital`.
   - Setiap berkas digital yang akan ditandatangani secara elektronik harus terdaftar di master tersebut.

3. **Penyimpanan**
   - Passphrase per pengguna disimpan di tabel `mlite_esignature_passphrase`.
   - Tanda tangan berbasis passphrase tidak menyimpan file gambar; hanya hash dan audit trail di tabel `mlite_esignatures`.

4. **Hak Akses**
   - Berikan akses modul E-Signature kepada dokter dan petugas yang berwenang menandatangani dokumen.

## Catatan

- Tanda tangan tidak lagi memakai signature pad/gambar; keabsahan dibuktikan dengan passphrase pribadi penandatangan.
- Modul ini terintegrasi dengan berkas digital pasien dari modul IGD dan Rawat Jalan/Ranap melalui parameter `ref_type` dan `ref_id`.
- Tanda tangan lama yang masih memiliki file gambar tetap ditampilkan gambarnya pada PDF dan halaman verifikasi.
- Riwayat tanda tangan hanya menampilkan 50 entri terakhir; untuk audit lengkap gunakan query langsung ke tabel `mlite_esignatures`.


# Panduan integrasi Modul E-Signature dengan modul lain

Modul ini menyediakan fitur Tanda Tangan Elektronik (TTE) yang mematuhi UU ITE, terintegrasi dengan Resume Medis dan modul lainnya serta mendukung audit trail.

## Fitur Utama
1.  **Tanda Tangan Digital**: Menggunakan passphrase pribadi per pengguna (tanpa signature pad).
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