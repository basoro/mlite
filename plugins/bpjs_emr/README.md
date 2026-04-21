# Plugin BPJS E-Medical Records

Dokumentasi singkat penggunaan modul **BPJS E-Medical Records** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **BPJS E-Medical Records**.
- Pilih submenu sesuai kebutuhan:
  - Data BPJS EMR
  - Pemetaan
  - Pengaturan

## Panduan Pengguna (Petugas)

Petugas rekam medis menggunakan menu **Data BPJS EMR** untuk mengirimkan data rekam medis elektronik ke BPJS:

1. **Melihat Data dan Status Pengiriman**
   - Buka **BPJS E-Medical Records → Data BPJS EMR**.
   - Gunakan filter **Tanggal Mulai** dan **Tanggal Selesai** untuk menampilkan daftar kunjungan.
   - Gunakan kolom pencarian untuk menemukan pasien berdasarkan nama atau nomor SEP.
   - Cek status setiap data: sudah terkirim atau belum.

2. **Cek Kelengkapan Data**
   - Klik tombol **Cek Kelengkapan** pada baris kunjungan untuk memeriksa apakah seluruh komponen rekam medis (diagnosis, prosedur, kondisi masuk, tanda vital, dll.) sudah lengkap sebelum dikirim.

3. **Kirim Data ke BPJS EMR**
   - Setelah data lengkap, klik **Kirim** untuk mengirimkan bundle FHIR rekam medis ke API BPJS E-Medical Records.
   - Periksa respons pengiriman; status 200 berarti berhasil.

4. **Melihat Log Respons**
   - Buka submenu **Data BPJS EMR** dan cek kolom respons untuk melihat hasil pengiriman sebelumnya.

## Panduan Admin

1. **Pengaturan Koneksi API**
   - Buka **BPJS E-Medical Records → Pengaturan**.
   - Isi **ConsID**, **Secret Key**, dan **User Key** sesuai kredensial yang diberikan BPJS Kesehatan.
   - Isi **Kode DRS** (kode dokter/faskes) dan **Kode Kemkes** sesuai kode faskes dari Kemenkes.
   - Isi **Kecamatan** dan **Kode Pos** faskes.
   - Atur **Base URL** API BPJS EMR (gunakan URL produksi saat go-live; URL dev untuk pengujian).
   - Klik **Simpan** untuk menyimpan konfigurasi.

2. **Pemetaan LOINC/SNOMED**
   - Buka **BPJS E-Medical Records → Pemetaan**.
   - Petakan kode tindakan laboratorium RS ke kode **LOINC** yang sesuai.
   - Petakan kode tindakan radiologi RS ke kode **LOINC** radiologi.
   - Petakan kode prosedur rawat jalan dan rawat inap RS ke kode **SNOMED** prosedur, termasuk mapping **focalDevice**.
   - Petakan kode paket operasi RS ke kode **SNOMED** operasi, termasuk mapping **focalDevice**.
   - Gunakan fitur **Fetch AI SNOMED** untuk mendapatkan saran kode SNOMED secara otomatis berdasarkan nama tindakan.
   - Gunakan fitur **Fetch AI Focal** untuk mendapatkan saran **nama perangkat medis** dan **aksi** pada `focalDevice`. Setelah AI mengisi nama dan aksi, isi **ID Resource Device** secara manual dengan `id` dari resource `Device` FHIR yang sesuai (contoh: `0901R001-1180006-12-ddd99da0-3916-446e-8429-207c22738496`). ID ini digunakan sebagai referensi `Device/{id}` pada field `manipulated.reference`.
   - Simpan pemetaan agar data yang dikirim ke BPJS EMR menggunakan kode standar internasional.

## Catatan

- Data yang dikirim menggunakan format **FHIR Bundle** sesuai standar HL7 FHIR yang ditetapkan BPJS Kesehatan.
- Pastikan pemetaan LOINC/SNOMED/focalDevice sudah lengkap sebelum mengirim data agar tidak terjadi penolakan dari server BPJS.
- Field **ID Resource Device** pada mapping focalDevice harus diisi dengan nilai `id` dari resource `Device` FHIR yang sudah ada di server (bukan kode SNOMED). Nilai ini digunakan sebagai `Device/{id}` pada `Procedure.focalDevice.manipulated.reference`.
- URL API default adalah lingkungan **development** (`apijkn-dev`); ganti ke URL produksi setelah pengujian selesai.
- Tabel pemetaan prosedur rawat inap (`mlite_bpjs_emr_mapping_prosedur_ranap`) dan operasi (`mlite_bpjs_emr_mapping_operasi`) dibuat otomatis saat instalasi plugin.
