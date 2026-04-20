# Plugin Oral Diagnostic

Dokumentasi singkat penggunaan modul **Oral Diagnostic** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Oral Diagnostic**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

1. **Melihat Daftar Kunjungan**
   - Halaman utama menampilkan daftar kunjungan poli Oral Diagnostic hari ini.
   - Gunakan filter tanggal awal dan akhir untuk melihat kunjungan periode tertentu.
   - Filter tambahan tersedia untuk status periksa (belum/selesai) dan status bayar (lunas).

2. **Mendaftarkan Pasien Baru**
   - Klik tombol tambah kunjungan.
   - Cari pasien berdasarkan nomor rekam medis atau nama.
   - Isi form pendaftaran: tanggal registrasi, jam, dokter, penjamin.
   - Sistem otomatis mengisi kode poli Oral Diagnostic (OD), biaya registrasi, umur, dan status daftar (Baru/Lama).
   - Simpan untuk membuat nomor registrasi dan nomor rawat baru.

3. **Mengisi Rekam Medis (SOAP & Odontogram)**
   - Buka form rincian pasien dari daftar kunjungan.
   - Isi **SOAP** (Subjektif, Objektif, Assesment, Plan) melalui form soap.
   - Isi **Odontogram** untuk menandai kondisi gigi per elemen.
   - Tambahkan **Asesmen** diagnosis ICD-10 dan **Lokalis** kondisi lokal.
   - Catat tindakan atau obat yang diberikan melalui form layanan/rincian.

4. **Cetak dan Surat**
   - Cetak kartu antrian pasien melalui menu antrian.
   - Buat dan cetak **Surat Rujukan** ke fasilitas kesehatan lanjutan.
   - Buat dan cetak **Surat Sakit** atau **Surat Sehat** sesuai kebutuhan pasien.
   - Cetak rekam medis oral diagnostic (cetak.oral_diagnostic).

5. **Berkas Digital**
   - Unggah berkas digital terkait kunjungan melalui form berkas digital.
   - Berkas yang diunggah tersimpan di direktori upload berkasrawat.

6. **Status Selesai**
   - Setelah pelayanan selesai, ubah status poli melalui tombol selesai.
   - Data kunjungan akan ditandai sebagai "Sudah" pada kolom stts.

## Panduan Admin

1. **Integrasi VClaim (SEP)**
   - Jika modul VClaim aktif, nomor SEP BPJS dapat ditambahkan/dilihat dari halaman daftar kunjungan.
   - Pastikan modul VClaim sudah dikonfigurasi dan terhubung sebelum menggunakan fitur ini.

2. **Pengaturan Dokter dan Poliklinik**
   - Pastikan dokter gigi telah terdaftar di master dokter dengan status aktif.
   - Poliklinik dengan kode `OD` harus tersedia dan aktif di master poliklinik.
   - Biaya registrasi poliklinik OD dikonfigurasi di tabel master poliklinik.

3. **Penjamin**
   - Daftar penjamin diambil dari master penjab. Pastikan penjamin yang digunakan pasien tersedia dan aktif.

## Catatan

- Kode poliklinik Oral Diagnostic yang digunakan adalah `OD`. Pastikan kode ini konsisten di seluruh master data.
- Status daftar pasien (Baru/Lama) ditentukan otomatis berdasarkan riwayat kunjungan sebelumnya.
- Jika pengaturan `cekstatusbayar` aktif, sistem akan memblokir pendaftaran pasien yang masih memiliki tagihan belum bayar.
