# Plugin Clinical Pathway

Modul **Clinical Pathway** untuk mLITE yang digunakan untuk membangun master CP, memetakan ICD-10, membuat template aktivitas harian, melakukan generate CP pasien otomatis, memonitor realisasi aktivitas, menghitung compliance, mendeteksi variance, serta mencetak lembar Clinical Pathway pasien.

Plugin ini dirancang mengikuti pola plugin mLITE dan memanfaatkan data historis 3 tahun terakhir sebagai dasar pembentukan template evidence.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Clinical Pathway**.
- Pilih submenu sesuai kebutuhan:
  - Dashboard
  - Master CP
  - Template Harian
  - Mapping ICD
  - Evidence Engine
  - Generator CP
  - Monitoring
  - Variance

## Fitur Utama

- Master Clinical Pathway per diagnosis dan jenis layanan.
- Mapping ICD-10 ke master CP.
- Template aktivitas harian per hari rawat.
- Evidence engine berbasis data historis 3 tahun terakhir.
- Generator template otomatis dari evidence lokal.
- Generator CP pasien otomatis berdasarkan `no_rawat`.
- Monitoring realisasi aktivitas terhadap data operasional mLITE.
- Perhitungan compliance dan kategori kepatuhan.
- Deteksi variance untuk diagnosis, LOS, dan aktivitas wajib yang tidak terealisasi.
- Cetak HTML dan export PDF lembar Clinical Pathway pasien.
- Import seeder dummy untuk pengujian.

## Struktur Data Plugin

Saat plugin di-install, sistem akan membuat tabel berikut secara otomatis:

- `mlite_clinical_pathway`
- `mlite_clinical_pathway_diagnosis`
- `mlite_clinical_pathway_day`
- `mlite_clinical_pathway_activity`
- `mlite_clinical_pathway_patient`
- `mlite_clinical_pathway_execution`
- `mlite_clinical_pathway_variance`
- `mlite_clinical_pathway_compliance`
- `mlite_clinical_pathway_audit`

## Panduan Pengguna

### 1. Membuat Master CP

- Buka menu **Clinical Pathway -> Master CP**.
- Tambahkan data master seperti:
  - `kode_cp`
  - `nama_cp`
  - `jenis_layanan` (`Ralan` atau `Ranap`)
  - `target_los`
  - `target_tarif`
  - status aktif
- Simpan master CP.

Contoh:

- `CP-RI-TYPHOID`
- `Clinical Pathway Demam Tifoid Ranap`
- `jenis_layanan = Ranap`

### 2. Membuat Template Harian

- Buka menu **Clinical Pathway -> Template Harian**.
- Pilih master CP.
- Tambahkan hari template, misalnya Hari 1, Hari 2, Hari 3, dan seterusnya.
- Tambahkan aktivitas pada masing-masing hari.

Kategori aktivitas yang didukung:

- `Assessment`
- `Laboratorium`
- `Radiologi`
- `Obat`
- `Tindakan`
- `Nutrisi`
- `Edukasi`
- `Monitoring`
- `Outcome`

Untuk aktivitas yang ingin dibaca otomatis, isi:

- `sumber_tabel`
- `item_kode`
- `item_nama`

Contoh:

- Laboratorium: `periksa_lab` dengan `kd_jenis_prw`
- Radiologi: `periksa_radiologi` dengan `kd_jenis_prw`
- Obat: `resep_dokter` dengan `kode_brng`
- Tindakan: `prosedur_pasien` atau tindakan rawat inap dengan `kd_jenis_prw` / nama tindakan

### 3. Mapping ICD

- Buka menu **Clinical Pathway -> Mapping ICD**.
- Pilih master CP.
- Cari diagnosis ICD-10.
- Simpan mapping diagnosis ke master CP.

Catatan:

- Mapping utama digunakan untuk proses auto-pick CP pasien.
- Prioritas diagnosis menentukan urutan pemilihan bila ada lebih dari satu mapping.

### 4. Evidence Engine

- Buka menu **Clinical Pathway -> Evidence Engine**.
- Pilih ICD-10 yang ingin dianalisis.
- Sistem akan menampilkan ringkasan historis 3 tahun, termasuk:
  - jumlah kasus
  - top laboratorium
  - top radiologi
  - top obat
  - top tindakan
  - outcome
  - evidence score

Evidence ini dipakai sebagai dasar pembentukan template otomatis.

### 5. Generate Template Otomatis

- Buka menu **Clinical Pathway -> Generator CP**.
- Pada panel **Generator Template Otomatis**, pilih:
  - master CP
  - diagnosis ICD-10
- Klik **Generate Template Harian**.

Sistem akan:

- membaca evidence historis 3 tahun
- membentuk aktivitas harian otomatis
- membuat hari template
- mengisi aktivitas template
- menyimpan confidence score dan catatan evidence pada master CP

### 6. Generate CP Pasien Otomatis

- Masih di menu **Clinical Pathway -> Generator CP**.
- Masukkan `no_rawat`.
- Klik **Generate CP Otomatis**.

Sistem akan:

- membaca registrasi pasien dari `reg_periksa`
- membaca diagnosis pasien dari `diagnosa_pasien`
- memilih master CP terbaik berdasarkan mapping ICD dan jenis layanan
- membentuk daftar execution berdasarkan template harian
- melakukan sinkronisasi realisasi awal otomatis
- menghitung compliance
- membuat variance awal

### 7. Monitoring Pasien

- Buka menu **Clinical Pathway -> Monitoring**.
- Tabel monitoring menampilkan:
  - nomor rawat
  - nama pasien
  - nama CP
  - ICD mapping
  - tanggal mulai
  - status layanan
  - compliance
  - kategori kepatuhan
  - status CP

Aksi yang tersedia:

- `Refresh Realisasi`
- `Cetak`
- `PDF`

Fungsi tombol:

- `Refresh Realisasi` untuk membaca ulang data operasional pasien, menghitung ulang compliance, dan membangun ulang variance.
- `Cetak` untuk membuka lembar CP versi HTML.
- `PDF` untuk export lembar CP pasien ke PDF formal.

### 8. Variance

- Buka menu **Clinical Pathway -> Variance**.
- Halaman ini menampilkan daftar variance yang ditemukan sistem.

Kategori variance yang saat ini didukung:

- `Diagnosis`
- `LOS`
- `Obat`
- `Tindakan`
- `Lab`
- `Radiologi`
- `Nutrisi`
- `Edukasi`
- `Outcome`
- `Administrasi`

## Sumber Realisasi Otomatis

Realisasi aktivitas pada monitoring dibaca dari data operasional mLITE. Saat proses generate pasien atau saat tombol **Refresh Realisasi** dijalankan, plugin akan mencocokkan aktivitas CP dengan data aktual berikut:

### Assessment

- `pemeriksaan_ranap`
- `pemeriksaan_ralan`

### Monitoring

- `rawat_inap_pr`
- `rawat_inap_drpr`
- fallback ke `pemeriksaan_ranap` atau `pemeriksaan_ralan`

### Edukasi

- `pemeriksaan_ranap`
- `pemeriksaan_ralan`

### Nutrisi

- `catatan_adime_gizi`
- fallback ke `pemeriksaan_ranap` atau `pemeriksaan_ralan`

### Laboratorium

- `periksa_lab`

### Radiologi

- `periksa_radiologi`

### Obat

- `resep_obat`
- `resep_dokter`

### Tindakan

- `prosedur_pasien`
- `rawat_inap_pr`
- `rawat_inap_drpr`

Catatan penting:

- Untuk pasien **Ranap**, kategori `Tindakan` sudah dapat membaca tindakan keperawatan seperti `Pasang Infus` dari `rawat_inap_pr` atau `rawat_inap_drpr`, walaupun tidak tercatat di `prosedur_pasien`.
- Pencocokan tindakan rawat inap dapat menggunakan `kd_jenis_prw` maupun `nama tindakan`.

## Compliance dan Variance

### Compliance

Rumus yang digunakan:

`Compliance (%) = completed_activity / planned_activity x 100`

Kategori kepatuhan:

- `Sangat Patuh` jika >= 90%
- `Patuh` jika >= 75%
- `Kurang Patuh` jika >= 50%
- `Tidak Patuh` jika < 50%

### Variance

Variance dibuat otomatis bila:

- aktivitas wajib sudah melewati tanggal rencana tetapi belum ditemukan realisasinya
- LOS pasien melebihi target CP
- diagnosis utama pasien berubah dari diagnosis mapping awal

Saat tombol **Refresh Realisasi** dijalankan, variance lama pasien akan dibangun ulang agar hasil tetap bersih dan tidak dobel.

## Cetak dan PDF

Plugin menyediakan hasil cetak formal Clinical Pathway pasien:

- header rumah sakit dan logo
- data pasien
- diagnosis pasien
- aktivitas per hari
- status realisasi
- compliance
- daftar variance
- tanda tangan dokter
- tanda tangan perawat

Sumber tanda tangan otomatis:

- dokter dari `reg_periksa` / master dokter
- perawat dari tindakan terakhir pada `rawat_inap_pr` atau `rawat_inap_drpr`

## Seeder Dummy

Folder seeder berada di:

- `plugins/clinical_pathway/seeders/`

File yang tersedia:

- `seeder_typhoid_ranap_dummy.sql`
- `seeder_pneumonia_ranap_dummy.sql`
- `seeder_dengue_ranap_dummy.sql`
- `seeder_stroke_ranap_dummy.sql`
- `rollback_dummy_seeders.sql`

Cara menggunakan:

1. Buka menu **Clinical Pathway -> Generator CP**.
2. Pada panel **Import Seeder Dummy**, pilih file seeder.
3. Klik **Import Seeder Ke Database**.

Kegunaan seeder:

- menyiapkan contoh master CP
- menyiapkan mapping ICD
- menyiapkan template harian
- menyiapkan dummy pasien
- menyiapkan execution, compliance, dan variance
- menyiapkan dummy tindakan perawat, laboratorium, radiologi, dan resep agar hasil cetak realistis

Untuk menghapus data dummy, jalankan file:

- `plugins/clinical_pathway/seeders/rollback_dummy_seeders.sql`

## Panduan Operasional Harian

Berikut alur operasional yang direkomendasikan:

1. Tim mutu atau admin membuat master CP terlebih dahulu.
2. Admin melengkapi mapping ICD untuk setiap master CP.
3. Template harian diisi manual atau dibentuk melalui evidence engine.
4. Saat pasien terdaftar dan diagnosis sudah ada, petugas menjalankan **Generate CP Otomatis** berdasarkan `no_rawat`.
5. Perawat, dokter, farmasi, laboratorium, dan radiologi tetap bekerja di modul operasional masing-masing seperti biasa.
6. Petugas CP atau tim mutu membuka menu **Monitoring** untuk melihat kepatuhan dan variance.
7. Bila ada data pelayanan baru, klik **Refresh Realisasi** agar status CP menyesuaikan data aktual terbaru.
8. Saat dibutuhkan audit atau visit, gunakan **Cetak** atau **PDF** untuk menghasilkan lembar CP pasien.

## Panduan Admin

### 1. Aktivasi Plugin

- Pastikan plugin `clinical_pathway` sudah terpasang di folder `plugins/clinical_pathway/`.
- Install plugin melalui mekanisme plugin mLITE.
- Saat install, struktur tabel plugin akan dibuat otomatis dari `Info.php`.

### 2. Kualitas Data

Pastikan data berikut tersedia dengan baik agar auto-actualization berjalan optimal:

- `reg_periksa`
- `diagnosa_pasien`
- `pemeriksaan_ranap`
- `pemeriksaan_ralan`
- `periksa_lab`
- `periksa_radiologi`
- `resep_obat`
- `resep_dokter`
- `prosedur_pasien`
- `rawat_inap_pr`
- `rawat_inap_drpr`
- `catatan_adime_gizi`

### 3. Penamaan Tindakan

Untuk hasil pencocokan tindakan yang baik:

- gunakan `item_kode` bila kode tindakan sudah diketahui
- gunakan `item_nama` yang konsisten dengan `jns_perawatan_inap.nm_perawatan`

### 4. Audit Trail

Setiap proses penting akan tercatat pada `mlite_clinical_pathway_audit`, termasuk:

- generate template
- generate pasien
- refresh realisasi

## Catatan

- Plugin ini paling optimal digunakan pada kasus **Ranap**, karena monitoring realisasi paling lengkap tersedia dari tindakan rawat inap, CPPT, gizi, laboratorium, radiologi, dan resep.
- Untuk **Ralan**, CP tetap dapat dibuat selama diagnosis dan template tersedia, namun sumber realisasi otomatis bergantung pada kelengkapan input SOAP, resep, dan penunjang.
- Evidence engine menggunakan data historis lokal 3 tahun terakhir, sehingga hasil template otomatis akan menyesuaikan praktik nyata rumah sakit.
- Sebelum digunakan untuk operasional penuh, lakukan uji coba menggunakan seeder dummy terlebih dahulu.
