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
  - Mapping Radiologi   - ERM Rawat Jalan
   - ERM Rawat Inap
  - ERM Bundle
  - Log ERM
  - Mapping Resource ERM
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

## Sub Modul ERM Rawat Jalan

Sub modul **ERM Rawat Jalan** (`services/`, `resources/`, `view/admin/erm.html`, `view/admin/erm.bundle.html`, `view/admin/erm.log.html`, `view/admin/erm.mapping.html`) menyiapkan Rekam Medis Elektronik kunjungan rawat jalan dalam bentuk resource FHIR R4 dan mengirimkannya ke SATUSEHAT sebagai **Bundle transaction**. Mengikuti konvensi plugin mLITE (`docs/index.md`): view admin berupa template `.html` di `view/admin/` yang digambar lewat `$this->draw('erm.html', ...)`, class `Plugins\Satu_Sehat\*` di `services/` & `resources/` dimuat otomatis oleh Autoloader PSR-4 milik mLITE (class `src/` dengan namespace `SatuSehat` dimuat lewat PSR-4 `composer.json` root), dan skema tabel ERM (`mlite_satu_sehat_erm_ralan`, `mlite_satu_sehat_erm_log`, `mlite_satu_sehat_mapping_tindakan`) didaftarkan pada closure `install` di `Info.php`.

1. **ERM Rawat Jalan** — daftar kunjungan rawat jalan beserta status pengiriman. Buka detail untuk melihat isi ERM per BAB 1–29 (Identitas, Anamnesis, Pemeriksaan Fisik, Penunjang, Diagnosis, Tindakan, Peresepan, dst.) dan tombol **Kirim ke SATUSEHAT**.
2. **ERM Bundle** — pratinjau Bundle JSON (Encounter, Condition, Observation, CarePlan, Procedure, ServiceRequest, DiagnosticReport, AllergyIntolerance, Medication, MedicationRequest, QuestionnaireResponse, MedicationDispense) beserta hasil validasi sebelum dikirim.
3. **Log ERM** — riwayat request/response setiap sinkronisasi (status, HTTP, durasi, jumlah resource).
4. **Mapping Resource ERM** — pemetaan Patient/Practitioner/Location/Organization per kunjungan serta ringkasi kesiapan mapping master (praktisi, lokasi, obat/KFA, lab/LOINC, radiologi).

Catatan:

- Tabel tracking `mlite_satu_sehat_erm_ralan` dan `mlite_satu_sehat_erm_log` dibuat otomatis saat sub modul pertama kali dipakai.
- Struktur isi ERM mengikuti CSV *Variabel Resource Rawat Jalan* Kemenkes (BAB 1–29).
- Pastikan **Mapping Praktisi**, **Mapping Lokasi**, dan **Mapping Obat/Lab/Rad** sudah terisi sebelum mengirim; validasi akan menolak pengiriman bila Patient/Practitioner/Location belum terpetakan.

## Sub Modul ERM Rawat Inap

Sub modul **ERM Rawat Inap** (`services/SatuSehatErmRanapService.php`, `resources/*RanapBuilder.php` + builder pendukung) melengkapi ERM Rawat Jalan untuk kunjungan rawat inap, mengikuti template *Bundle Transaction Rawat Inap* Kemenkes:

1. **ERM Rawat Inap** (`ermranap`) — daftar kunjungan rawat inap (status_lanjut = Ranap) beserta kamar/bangsal dan status pengiriman; detail ERM per BAB (Data Kunjungan, Anamnesis, Tujuan Perawatan, Diet, Edukasi, Prognosis, Rencana Tindak Lanjut, Kondisi Saat Keluar, Resume Medis, dst.).
2. **ERM Bundle Ranap** (`ermranapbundle`) — pratinjau Bundle JSON resource rawat inap: Encounter (class IMP + statusHistory + ServiceClass), Condition (chief-complaint, problem-list-item, previous-condition, encounter-diagnosis), FamilyMemberHistory, AllergyIntolerance, MedicationStatement, Observation (tanda vital + interpretasi H/L/N, pemeriksaan fisik head-to-toe, status psikologis, skor ADL, hasil lab/rad, kriteria pemulangan), ClinicalImpression (riwayat perjalanan penyakit, rasional klinis, prognosis), Goal, CarePlan (inpatient & discharge), RiskAssessment, ServiceRequest/Procedure/Specimen/DiagnosticReport, Medication/MedicationRequest/MedicationDispense/MedicationAdministration, QuestionnaireResponse Q0007, dan NutritionOrder.
3. **Log ERM Ranap** (`ermranaplog`) & **Mapping Resource Ranap** (`ermranapmapping`) — sama seperti sub modul rawat jalan (tabel tracking dipakai bersama).
4. **Resume Medis** — Composition `34105-7 Hospital Discharge summary` dengan section Anamnesis, Pemeriksaan Fisik/Fungsional, Perencanaan Perawatan, Penunjang, Diagnosis, Tindakan, Farmasi (obat kunjungan & obat pulang), Diet, Edukasi, Kondisi Saat Meninggalkan RS, Rencana Tindak Lanjut, dan Perjalanan Kunjungan Pasien.

Catatan rawat inap:

- Sumber data: `kamar_inap`, `pemeriksaan_ranap`, `diagnosa_pasien` (status Ranap), `prosedur_pasien` + `rawat_inap_dr/pr`, `periksa_lab`/`periksa_radiologi`, resep status `ranap`, dan `detail_pemberian_obat`.
- Encounter dibuat `finished` (dengan `hospitalization.dischargeDisposition` dari `stts_pulang`) bila pasien sudah pulang, selain itu `in-progress`.
- Mapping lokasi memakai kamar/bangsal (`Mapping Lokasi` dengan kode kamar/bangsal), kelas kamar dipetakan ke ekstensi `ServiceClass`.

## Sub Modul ERM IGD

Sub modul **ERM IGD** (`services/SatuSehatErmIgdService.php`, `resources/*IgdBuilder.php` + builder pendukung) menangani kunjungan Instalasi Gawat Darurat, mengikuti template *Bundle Transaction IGD* Kemenkes:

1. **ERM IGD** (`ermigd`) — daftar kunjungan IGD (registrasi poli IGD sesuai pengaturan `settings.igd`) beserta status pengiriman; detail ERM per BAB dengan tambahan data triase (airway/breathing/circulation, skala & kategori triase), risiko jatuh, status kehamilan, dan disposisi IGD.
2. **ERM Bundle IGD** (`ermigdbundle`) — pratinjau Bundle JSON resource IGD: Encounter (class EMER + statusHistory arrived/triaged/in-progress/finished + lokasi berjenjang ruang triase→ruang tindakan + ServiceClass + diagnosis role AD + dischargeDisposition termasuk pindah ke rawat inap), Condition (diagnosis awal / kerja provisional / banding differential), Procedure (emergensi 373110003 + persiapan puasa `not-done`), Observation (tingkat kesadaran 67775-7, risiko jatuh Morse 59461-4, status kehamilan 82810-3, tanda vital triase, hasil lab/rad, kriteria pemulangan OC000055), CarePlan (rencana rawat & instruksi 702779007, perencanaan pemulangan 736372004), ServiceRequest (lab/rad prioritas `stat` + orderDetail modality + reasonReference + supportingInfo), Specimen, DiagnosticReport, Medication/MedicationRequest/MedicationDispense, dan QuestionnaireResponse Q0007.
3. **Log ERM IGD** (`ermigdlog`) & **Mapping Resource IGD** (`ermigdmapping`) — sama seperti sub modul lainnya (tabel tracking dipakai bersama).

Catatan IGD:

- Sumber data: `mlite_triase_igd` (triase: kesadaran, airway/breathing/circulation, tanda vital, skala & kategori triase, keluhan utama, diagnosa awal), `penilaian_awal_keperawatan_igd` (status kehamilan G/P/A/HPHT, risiko jatuh, nyeri, rencana keperawatan), `pemeriksaan_ralan`, `diagnosa_pasien`, `prosedur_pasien` + `rawat_jl_dr/pr`, `periksa_lab`/`periksa_radiologi`, `permintaan_lab`/`permintaan_radiologi`, resep, dan `detail_pemberian_obat`.
- Kunjungan IGD difilter berdasarkan kode poli IGD pada pengaturan **Satu Sehat → Pengaturan** (`settings.igd`, default `IGD`).
- Encounter dibuat `finished` bila ada `stts_pulang` atau pasien pindah ke rawat inap (`dischargeDisposition` "oth" dengan teks pemindahan), selain itu `in-progress` dengan statusHistory `arrived → triaged → in-progress`.
- Tanda vital mengikuti waktu triase; bila `pemeriksaan_ralan` sudah terisi, datanya yang diprioritaskan.

## Catatan Validasi SATUSEHAT (aturan penting)

Hasil uji dengan data nyata (HTTP 400, rule 10xxx) yang kini dipatuhi seluruh builder:

| Rule | Ketentuan |
| ---- | --------- |
| 10457 | `Encounter.diagnosis` wajib minimal 1 — bila tidak ada `diagnosa_pasien`, dirujuk dari Condition keluhan utama (peran `CC`). |
| 10015 | `Procedure`/`ServiceRequest.code` system `kptl` hanya untuk kode terverifikasi: dipetakan lewat menu **Mapping Tindakan** atau kebetulan sama dengan kode di codebook `mlite_ktpl` (diimpor menu Master). Kode lokal `jns_perawatan` (mis. `RJ048`) cukup `code.text`. |
| 10025 | Bentuk sediaan obat memakai `http://terminology.kemkes.go.id/CodeSystem/medication-form` (kode KFA, mis. `BS034`) — bukan `v3-orderableDrugForm`. |
| 10031 | Ekstensi `MedicationType` (`NC`/`CO`) wajib ada pada `Medication`; identifier `Medication` = `http://sys-ids.kemkes.go.id/medication/{Org_id}` (rule 10380). |
| 10050/10349 | Elemen `Quantity` wajib berisi `system`+`code` unit yang valid (UCUM atau SNOMED). Satuan tak dikenal (mis. `SYRUP`) tidak dikirim sebagai code — Quantity dihilangkan, dosis tetap tercatat di teks aturan pakai. |
| 10138/10393 | `MedicationDispense.medicationReference` dan `.authorizingPrescription` wajib berupa reference — entri `Medication`/`MedicationRequest` otomatis disintesis bila obat tidak ada di resep. |
| 10389 | Identifier `MedicationDispense`/`MedicationRequest` hanya `prescription/{Org_id}` + `prescription-item/{Org_id}`. |
| 11127/10372 | `QuestionnaireResponse` tidak boleh memakai identifier namespace prescription; `source` wajib diisi. |

Catatan pemetaan data:

- **Mapping Tindakan** (tabel `mlite_satu_sehat_mapping_tindakan`, dibuat otomatis): memetakan `jns_perawatan.kd_jenis_prw` (mis. `RJ048`) ke kode KPTL resmi dari codebook `mlite_ktpl`, dipakai `Procedure`/`ServiceRequest.code.coding` system `kptl`.
- **Mapping Obat** — `satuan_den` idealnya kode UCUM (`mL`, `g`, `mg`, ...) atau kode unit SNOMED (digit, mis. `385057009` Film-coated tablet). Kolom satuan & rute kini dapat diisi manual di form Mapping Obat (lengkap dengan saran nilai), dan tombol **Normalisasi Satuan & Rute** mengonversi nilai lama secara massal: `SYRUP`/`SUSPENSI`/`CAIR`/... &rarr; `mL`, `TABLET`/`KAPLET`/... &rarr; `385057009`, `PO`/`ORAL`/`MINUM` &rarr; `oral`, `IM`/`INTRAMUSKULAR` &rarr; `inj.intramuscular`, serta ejaan UCUM dikanonkan (`ml` &rarr; `mL`). Nilai yang tidak dikenal (mis. `KAPSUL`) dilaporkan untuk diisi manual.
- Kode rute (`kode_route`) memakai kode ATC (`http://www.whocc.no/atc`) sesuai template Kemenkes.
- Ekstensi custom (mis. penyimpan rank diagnosis) tidak lagi dikirim; rank mengikuti urutan `diagnosa_pasien.prioritas`.

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
