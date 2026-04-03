# Mini PACS (mLITE Plugin)

Plugin terintegrasi untuk menangkap, mengelola, dan menampilkan berkas citra medis format standar DICOM dari mesin Radiologi (CT, MRI, X-Ray, CR, USG) ke dalam modul SIMRS mLITE.

## Fitur Utama

- **DICOM Viewer Terintegrasi**: Memanfaatkan library Cornerstone untuk menghadirkan pengalaman pantauan radiologi interaktif (Zoom, Pan, Window/Level, Invert, Length Measurement) secara *native* di dalam browser tanpa perlu aplikasi pihak ketiga (OHIF/Orthanc).
- **DICOM Server (SCP Listener)**: Bertindak sebagai peladen (server) mandiri yang selalu siaga menjemput dan mengintersep gambar *Push* dari modalitas/alat radiologi lewat koneksi jaringan.
- **Auto-Routing Database (Accession Number)**: Skrip secara pintar mengurai Metadata Header berkas. Alat yang mengirim Accession Number (No Order) bakal secara instan dirajut relasinya ke Kunjungan/No Rawat pasien di SIMRS.
- **Thumbnail Otomatis**: Secara di belakang layar membangkitkan dan mendistribusikan *thumbnail JPG* dari gambar besar DICOM langsung ke hadapan formulir Poli Pasien & Rekam Medis.
- **Unggah Manual**: Mampu mengkonversi gambar standar (JPG/PNG) menjadi DICOM File (DCM) dan memasukannya ke PACS saat Petugas salah memencet mesin atau mesin tak konek ke jaringan.

## Persyaratan Sistem

Pastikan Server atau sistem operasi Anda (*Mac / Linux Ubuntu / Windows WSL*) telah terpasang **DCMTK** (DICOM Toolkit) untuk kelancaran pemrosesan di balik layar (Background Processing). 

### Instalasi DCMTK:
- **Mac (Homebrew)**: `brew install dcmtk`
- **Ubuntu/Debian**: `sudo apt install dcmtk`
- **CentOS/RHEL**: `sudo yum install dcmtk`

Biner yang dibutuhkan oleh sistem ini:
1. `storescp` (Untuk menerima gambar)
2. `dcmj2pnm` (Untuk mengekstrak JPG Thumbnail)
3. `img2dcm` (Untuk fitur upload JPG to DICOM)

## Cara Penggunaan (Listener)

Agar server mLITE Anda bisa menangkap kiriman dari mesin X-Ray di IGD, jalankan skrip pendengar (Listener) port DICOM melalui terminal/console:

```bash
cd /path/to/mlite/plugins/mini_pacs
./start_receiver.sh
```

- Skrip akan membuka jalan *(listen)* di port standar **11112**.
- *Application Entity Title (AET)* bawaannya adalah: **MINIPACS**.
- Mesin alat di Rumah Sakit silakan disetel IP tujuannya ke IP lokal komputer Server SIMRS (cth: `192.168.1.10`), Port `11112`, dan AET `MINIPACS`.

## Struktur Modifikasi Database

Seluruh data gambar yang masuk telah didesain hierarkinya mengikuti standar medis global:
1. `mlite_mini_pacs_study` (1 Pasien per Kunjungan)
2. `mlite_mini_pacs_series` (Sub-grup modalitas atau sesar potong)
3. `mlite_mini_pacs_instance` (Lembaran-lembaran sekuens citra utuh yang dikaitkan ke Series dan memiliki Path lokasi gambar).

Penyimpanan berkas murni (Teks DICOM `.dcm` tanpa database) akan dikumpulkan semua di folder `uploads/pacs/`.
