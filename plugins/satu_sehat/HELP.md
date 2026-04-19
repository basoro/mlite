# Plugin Satu Sehat

Modul integrasi platform **Satu Sehat** Kementerian Kesehatan RI di mLITE, menggunakan standar FHIR R4 untuk pengiriman data kunjungan, diagnosa, tindakan, obat, laboratorium, dan radiologi ke platform nasional.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Satu Sehat**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Referensi Praktisi
  - Referensi Pasien
  - Mapping Departemen
  - Mapping Lokasi
  - Mapping Praktisi
  - Mapping Obat
  - Mapping Laboratorium
  - Mapping Radiologi
  - Data Response
  - Verifikasi KYC
  - Pengaturan

## Panduan Pengguna (Petugas)

1. **Referensi Praktisi**
   - Cari ID IHS dokter/tenaga kesehatan dari Satu Sehat menggunakan NIK dokter.
   - Hasil pencarian menampilkan `practitioner_id` yang diperlukan untuk mapping.

2. **Referensi Pasien**
   - Cari ID IHS pasien dari Satu Sehat menggunakan NIK pasien.
   - Digunakan untuk verifikasi identitas pasien sebelum pengiriman data encounter.

3. **Data Response**
   - Lihat riwayat respons FHIR yang diterima dari platform Satu Sehat untuk setiap encounter yang dikirim.
   - Gunakan untuk memantau status keberhasilan pengiriman data.

4. **Verifikasi KYC**
   - Lakukan verifikasi KYC (Know Your Customer) pasien melalui integrasi Satu Sehat.
   - Pastikan data NIK pasien sudah benar sebelum melakukan verifikasi.

## Panduan Admin

1. **Pengaturan (Wajib dikonfigurasi pertama kali)**
   - Buka **Satu Sehat → Pengaturan** dan isi:
     - **Organization ID**: ID organisasi fasyankes di Satu Sehat.
     - **Client ID**: Client ID aplikasi dari konsol Satu Sehat.
     - **Secret Key**: Secret key aplikasi dari konsol Satu Sehat.
     - **Auth URL**: URL autentikasi OAuth2 (default: environment dev Kemkes).
     - **FHIR URL**: URL FHIR R4 (default: environment dev Kemkes).
     - **Zona Waktu**: WIB / WITA / WIT — digunakan pada timestamp FHIR.
     - **Kode Pos, Kelurahan, Kecamatan, Kabupaten, Propinsi**: kode wilayah administratif fasyankes.
     - **Longitude & Latitude**: koordinat lokasi fasyankes.
     - **Imaging**: `mini_pacs` (default) — pengaturan sistem imaging radiologi.
   - Simpan pengaturan sebelum menggunakan fitur lain.

2. **Mapping Departemen**
   - Daftarkan setiap departemen/poliklinik fasyankes sebagai **Organization** di Satu Sehat.
   - Klik **Daftar** pada departemen yang belum memiliki ID Satu Sehat — sistem mengirim request POST ke `/Organization`.
   - ID organisasi yang diterima disimpan di tabel `mlite_satu_sehat_departemen`.
   - Gunakan **Update** untuk memperbarui data organisasi jika ada perubahan.

3. **Mapping Lokasi**
   - Daftarkan setiap poliklinik/bangsal sebagai **Location** di Satu Sehat.
   - Klik **Daftar** — sistem mengirim request POST ke `/Location` dengan data alamat dan koordinat fasyankes.
   - ID lokasi yang diterima disimpan di tabel `mlite_satu_sehat_lokasi`.
   - Mapping lokasi diperlukan sebelum pengiriman data Encounter.

4. **Mapping Praktisi**
   - Petakan setiap dokter/tenaga kesehatan lokal ke ID IHS di Satu Sehat.
   - Masukkan NIK dokter untuk mencari `practitioner_id` dari platform Satu Sehat.
   - Data mapping tersimpan di tabel `mlite_satu_sehat_mapping_praktisi`.

5. **Mapping Obat**
   - Petakan kode obat lokal ke kode KFA (Katalog Farmasi Alat Kesehatan) Satu Sehat.
   - Gunakan fitur pencarian kode KFA untuk menemukan kode yang sesuai.
   - Data mapping tersimpan di tabel mapping obat Satu Sehat.

6. **Mapping Laboratorium & Mapping Radiologi**
   - Petakan jenis pemeriksaan laboratorium dan radiologi lokal ke kode LOINC atau kode standar Satu Sehat yang sesuai.

7. **Pengiriman Data Encounter**
   - Setelah semua mapping selesai, pengiriman Encounter dapat dilakukan dari modul Rawat Jalan/Rawat Inap.
   - Encounter mendukung tipe **ambulatory** (rawat jalan) dan **inpatient encounter** (rawat inap).

## Catatan

- Konfigurasi awal (Organization ID, Client ID, Secret Key, URL) **wajib** diisi sebelum fitur lain dapat digunakan.
- Urutan setup: Pengaturan → Mapping Departemen → Mapping Lokasi → Mapping Praktisi → Mapping Obat/Lab/Rad.
- Ganti **Auth URL** dan **FHIR URL** ke endpoint produksi Kemkes saat siap go-live (hilangkan `-dev` dari URL).
- Access token OAuth2 diambil otomatis setiap kali ada request FHIR — tidak perlu refresh manual.
- Data NIK pasien harus diisi dengan benar di master pasien agar pencarian ID IHS berhasil.
