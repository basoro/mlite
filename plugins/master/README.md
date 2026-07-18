# Plugin Master Data

Data master awal mLITE yang menyediakan pengelolaan seluruh referensi data pokok sistem, mulai dari SDM (dokter, petugas, spesialis), infrastruktur (poliklinik, bangsal, kamar, ruang OK), farmasi dan barang, hingga data wilayah, penyakit, dan kepegawaian.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Master Data**.
- Pilih submenu sesuai kebutuhan:
  - Manage
  - Dokter
  - Petugas
  - Poliklinik
  - Bangsal
  - Kamar
  - Data Barang
  - Perawatan Ralan
  - Perawatan Ranap
  - Perawatan Laboratorium
  - Perawatan Radiologi
  - Bahasa
  - Propinsi
  - Kabupaten
  - Kecamatan
  - Kelurahan
  - Cacat Fisik
  - Suku Bangsa
  - Perusahaan Pasien
  - Penanggung Jawab
  - Golongan Barang
  - Industri Farmasi
  - Jenis Barang
  - Kategori Barang
  - Kategori Penyakit
  - ICD 10
  - ICD 9
  - Kategori Perawatan
  - Kode Satuan
  - Master Aturan Pakai
  - Master Berkas Digital
  - Spesialis
  - Bank
  - Bidang
  - Departemen
  - Emergency Index
  - Jabatan
  - Jenjang Jabatan
  - Kelompok Jabatan
  - Pendidikan
  - Resiko Kerja
  - Status Kerja
  - Status WP
  - Metode Racik
  - Ruang OK

## Panduan Pengguna (Petugas)

Pengguna biasa umumnya hanya membaca data master sebagai referensi. Operasi tambah/ubah/hapus dilakukan oleh admin.

1. **Pencarian Data**
   - Gunakan kolom pencarian di setiap halaman master untuk mencari data berdasarkan kode atau nama.
   - Data ditampilkan dalam tabel yang dapat diurutkan per kolom.

2. **Melihat Detail**
   - Klik baris data untuk melihat detail lengkap entri master.

## Panduan Admin

1. **SDM dan Layanan**
   - **Dokter**: tambah/ubah/hapus data dokter (kode, nama, spesialis, status aktif).
   - **Petugas**: kelola data petugas/tenaga medis non-dokter.
   - **Spesialis**: kelola daftar spesialisasi dokter yang digunakan sebagai referensi.
   - **Poliklinik**: kelola poli/unit layanan (kode poli, nama, biaya registrasi, status aktif). Kode poli digunakan di seluruh modul pendaftaran.
   - **Bangsal**: kelola data bangsal rawat inap.
   - **Kamar**: kelola kamar per bangsal beserta kapasitas dan kelas.
   - **Ruang OK**: kelola data ruang operasi.

2. **Tarif dan Tindakan**
   - **Perawatan Ralan**: kelola jenis tindakan/perawatan rawat jalan beserta tarif.
   - **Perawatan Ranap**: kelola jenis perawatan rawat inap beserta tarif.
   - **Perawatan Laboratorium**: kelola panel pemeriksaan laboratorium beserta tarif dan template parameter hasil.
   - **Perawatan Radiologi**: kelola jenis pemeriksaan radiologi beserta tarif.
   - **Kategori Perawatan**: kelola kategori pengelompokan tindakan.
   - **Metode Racik**: kelola metode peracikan obat (digunakan di modul farmasi).
   - **Master Aturan Pakai**: kelola aturan pemakaian obat/tindakan.

3. **Farmasi dan Logistik**
   - **Data Barang**: kelola data obat dan barang medis (kode, nama, satuan, harga beli, dll.).
   - **Golongan Barang**: kelola pengelompokan barang (misal: obat generik, alat kesehatan).
   - **Industri Farmasi**: kelola daftar produsen/distributor farmasi.
   - **Jenis Barang**: kelola jenis/klasifikasi barang medis.
   - **Kategori Barang**: kelola kategori barang lebih spesifik.
   - **Kode Satuan**: kelola satuan ukuran barang (tablet, botol, ampul, dll.).
   - **Master Berkas Digital**: kelola template berkas digital yang dapat dilampirkan ke rekam medis.

4. **Data Wilayah**
   - **Propinsi**, **Kabupaten**, **Kecamatan**, **Kelurahan**: kelola hierarki data wilayah Indonesia, digunakan untuk mengisi alamat pasien dan pegawai.

5. **Data Pasien dan Kepesertaan**
   - **Penanggung Jawab (Penjab)**: kelola jenis penanggung jawab pembayaran (Umum, BPJS, Asuransi, dll.). Kode penjab digunakan di seluruh modul pendaftaran dan keuangan.
   - **Perusahaan Pasien**: kelola data perusahaan yang menanggung biaya pasien.
   - **Cacat Fisik**: kelola daftar jenis cacat fisik sebagai referensi pendataan pasien.
   - **Suku Bangsa**: kelola daftar suku bangsa untuk data sosial pasien.
   - **Bahasa**: kelola daftar bahasa yang digunakan pasien.

6. **Klinis dan Diagnosa**
   - **ICD 10 (Penyakit)**: kelola kode dan nama penyakit berdasarkan standar ICD-10. Digunakan untuk diagnosa pasien di seluruh modul klinis.
   - **ICD 9**: kelola kode prosedur/tindakan medis berdasarkan ICD-9-CM.
   - **Kategori Penyakit**: kelola pengelompokan penyakit (misal: penyakit menular, penyakit kronis).
   - **Emergency Index**: kelola indeks tingkat kegawatdaruratan pasien.

7. **Kepegawaian**
   - **Bidang**, **Departemen**: kelola struktur organisasi unit kerja.
   - **Jabatan**, **Jenjang Jabatan**, **Kelompok Jabatan**: kelola hirarki dan klasifikasi jabatan pegawai.
   - **Pendidikan**: kelola daftar jenjang pendidikan formal.
   - **Resiko Kerja**: kelola kategori risiko pekerjaan pegawai.
   - **Status Kerja**: kelola status kepegawaian (PNS, kontrak, honorer, dll.).
   - **Status WP**: kelola status wajib pajak pegawai.
   - **Bank**: kelola daftar bank untuk data rekening pegawai.

## Catatan

- Perubahan pada data master (terutama Dokter, Poliklinik, Penjab, Data Barang, ICD 10) berdampak langsung pada modul lain yang mereferensikan data tersebut; lakukan perubahan dengan hati-hati.
- Plugin ini juga menyediakan endpoint API (`apiList`) yang memungkinkan akses data master melalui REST API menggunakan autentikasi API key.
- Data wilayah (propinsi, kabupaten, kecamatan, kelurahan) biasanya diisi satu kali saat instalasi awal menggunakan skrip SQL impor.
