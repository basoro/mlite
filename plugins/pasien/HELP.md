# Plugin Pendaftaran Pasien

Dokumentasi singkat penggunaan modul **Pendaftaran Pasien** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Pendaftaran Pasien**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

1. **Mencari Data Pasien**
   - Di halaman Kelola, gunakan kolom pencarian untuk mencari pasien berdasarkan nama atau nomor rekam medis.
   - Navigasi halaman menggunakan tombol pagination di bagian bawah daftar.

2. **Mendaftarkan Pasien Baru**
   - Klik tombol tambah pasien.
   - Sistem menyediakan nomor rekam medis otomatis. Jika perlu nomor manual, aktifkan opsi input manual.
   - Isi data lengkap pasien:
     - Data pribadi: nama, NIK/KTP, jenis kelamin, tempat dan tanggal lahir, nama ibu, agama, pendidikan, pekerjaan, status nikah, golongan darah, nomor telepon, email, suku bangsa.
     - Alamat: provinsi, kabupaten/kota, kecamatan, kelurahan. Data wilayah baru otomatis ditambahkan jika belum ada.
     - Data penjamin: jenis penjamin dan nomor peserta (untuk pasien JKN/asuransi).
     - Data penanggung jawab: nama, hubungan, alamat, pekerjaan.
   - Simpan. Sistem akan mengisi otomatis umur pasien berdasarkan tanggal lahir.

3. **Mengubah Data Pasien**
   - Klik baris pasien yang ingin diubah untuk membuka form.
   - Perbarui data yang diperlukan lalu simpan.

4. **Foto Pasien**
   - Klik ikon upload foto pada baris pasien.
   - Unggah foto dari file atau gunakan kamera webcam langsung.
   - Foto disimpan di direktori `webapps/photopasien/` dengan resolusi maksimum 512×512 piksel.

5. **Cetak Kartu Pasien**
   - Klik tombol cetak kartu pada baris pasien untuk mencetak kartu identitas berobat.

6. **Riwayat Perawatan**
   - Lihat seluruh riwayat kunjungan pasien di semua unit layanan.

7. **Integrasi VClaim / PCare**
   - Jika modul VClaim aktif, cari data kepesertaan BPJS pasien berdasarkan NIK atau nomor kartu melalui tombol VClaim.
   - Jika modul PCare aktif, cari data peserta PCare berdasarkan NIK atau nomor kartu.

8. **Menghapus Data Pasien**
   - Hapus pasien melalui tombol hapus. Aksi ini memerlukan hak akses `can_delete`.

## Panduan Admin

1. **Hak Akses CRUD**
   - Kelola hak akses tambah, ubah, dan hapus pasien melalui konfigurasi `mlite_crud_permissions` untuk modul `pasien`.
   - Petugas tanpa hak `can_create` tidak dapat mendaftarkan pasien baru.
   - Petugas tanpa hak `can_update` tidak dapat mengubah data pasien.
   - Petugas tanpa hak `can_delete` tidak dapat menghapus data pasien.

2. **Nomor Rekam Medis**
   - Format nomor rekam medis diatur oleh fungsi `setNoRM()` di sistem inti mLITE.
   - Sistem menggunakan mekanisme retry otomatis (hingga 5 kali) untuk menghindari duplikasi nomor rekam medis saat pendaftaran bersamaan.

3. **Pengaturan Integrasi**
   - Pastikan modul VClaim atau PCare sudah terpasang dan dikonfigurasi jika ingin menggunakan fitur cek kepesertaan dari halaman pasien.
   - WhatsApp Gateway dapat dikonfigurasi untuk mengirim notifikasi ke nomor pasien.

## Catatan

- Data wilayah (provinsi, kabupaten, kecamatan, kelurahan) yang belum ada di master akan otomatis ditambahkan saat pendaftaran pasien.
- Penghapusan pasien tidak menghapus data kunjungan terkait. Pastikan tidak ada transaksi aktif sebelum menghapus.
- Modul ini mendukung ekspor data pasien ke PDF.
