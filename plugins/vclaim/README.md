# Plugin VClaim Request

Dokumentasi singkat penggunaan modul **VClaim Request** di mLITE untuk bridging API VClaim BPJS Kesehatan, pengelolaan SEP, rujukan, PRB, dan monitoring klaim.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **VClaim Request**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Referensi
  - Peserta
  - Rencana Kontrol
  - SEP
  - Rujukan
  - PRB
  - Lembar Pengajuan Klaim
  - Monitoring
  - Mapping Poli
  - Mapping Dokter

## Panduan Pengguna (Petugas)

1. **Peserta**
   - Cari data peserta BPJS berdasarkan nomor kartu atau NIK.
   - Hasil query menampilkan data peserta, status keaktifan, dan kelas rawat dari API VClaim.

2. **SEP (Surat Eligibilitas Peserta)**
   - Buka submenu **SEP** untuk membuat, mengubah, atau menghapus SEP.
   - Isi data SEP: nomor kartu peserta, tanggal pelayanan, jenis pelayanan, poliklinik, dokter DPJP, nomor rujukan, dan kelas rawat.
   - Klik **Simpan** untuk mengirim SEP ke API VClaim.
   - Gunakan **Cetak SEP** untuk mencetak SEP standar atau **Cetak SEP Internal** untuk format internal RS.
   - Gunakan **Cetak SRB** (Surat Rujukan Balik) untuk mencetak SRB pasien.

3. **Rujukan**
   - Cari data rujukan pasien dari Faskes Tingkat Pertama berdasarkan nomor rujukan.
   - Tampilkan detail rujukan termasuk diagnosa dan tanggal berlaku.

4. **Rencana Kontrol**
   - Buat dan kelola rencana kontrol ulang pasien setelah rawat inap.
   - Isi data: nomor SEP, tanggal rencana kontrol, poliklinik tujuan, dan dokter.

5. **PRB (Program Rujuk Balik)**
   - Kelola data PRB pasien penyakit kronis.
   - Isi diagnosa PRB, obat PRB, dan keterangan program.

6. **Lembar Pengajuan Klaim (LPK)**
   - Tampilkan LPK untuk keperluan verifikasi klaim rawat inap.
   - Filter berdasarkan tanggal dan nomor SEP.

7. **Monitoring**
   - Pantau status klaim yang sudah diajukan melalui dashboard monitoring.
   - Filter berdasarkan periode dan jenis pelayanan.

8. **Referensi**
   - Akses data referensi API VClaim: daftar diagnosa ICD-10, prosedur, faskes, dokter DPJP, spesialistik, ruang rawat, cara keluar, dan pasca pulang.

## Panduan Admin

1. **Konfigurasi API BPJS VClaim**
   - Pastikan kredensial API VClaim sudah diisi pada **Pengaturan** sistem mLITE:
     - `BpjsConsID`: Consumer ID dari BPJS
     - `BpjsSecretKey`: Secret Key dari BPJS
     - `BpjsUserKey`: User Key dari BPJS
     - `BpjsApiUrl`: URL API VClaim (production/staging)
   - Tanpa kredensial yang valid, seluruh fungsi bridging tidak akan bekerja.

2. **Mapping Poli**
   - Buka submenu **Mapping Poli** untuk memetakan kode poliklinik internal RS ke kode spesialistik BPJS.
   - Mapping ini digunakan saat pembuatan SEP agar kode poli sesuai dengan referensi VClaim.

3. **Mapping Dokter**
   - Buka submenu **Mapping Dokter** untuk memetakan kode dokter internal ke kode dokter DPJP BPJS.
   - Pastikan semua dokter yang melayani pasien BPJS sudah di-mapping.

## Catatan

- Seluruh fungsi di modul ini memerlukan koneksi internet aktif ke server API BPJS VClaim.
- SEP yang sudah dikirim ke BPJS tidak dapat dihapus sembarangan; koordinasikan dengan petugas BPJS sebelum membatalkan SEP.
- QR Code SEP disimpan otomatis di folder `uploads/qrcode/sep/` saat SEP berhasil dibuat.
- Gunakan submenu **Referensi** untuk memverifikasi kode diagnosa, prosedur, dan faskes sebelum mengisi form SEP.
- Halaman Kelola menampilkan dokumentasi ini (README.md) sebagai panduan cepat bagi pengguna.
