-- Seeder dummy Clinical Pathway Low Back Pain Ranap
-- Tujuan:
-- 1. Menyediakan 1 pasien dummy Low Back Pain 4 hari rawat
-- 2. Menyediakan layout kegiatan harian berbentuk matriks multi-halaman
-- 3. Menyediakan data CP pasien siap cetak HTML/PDF

START TRANSACTION;

SET @no_rawat = '2026/06/16/000601';
SET @no_rm = '960601';
SET @cp_code = 'CP-LBP-RANAP-DWS';
SET @cp_start = '2026-06-16 08:15:00';
SET @cp_finish = '2026-06-19 10:30:00';

SET @kd_dokter = (
  SELECT kd_dokter
  FROM dokter
  ORDER BY kd_dokter ASC
  LIMIT 1
);

SET @nip_perawat = (
  SELECT nip
  FROM petugas
  ORDER BY nip ASC
  LIMIT 1
);

DELETE FROM rawat_inap_pr
WHERE no_rawat = @no_rawat;

DELETE FROM mlite_clinical_pathway_variance
WHERE clinical_pathway_patient_id IN (
  SELECT id FROM mlite_clinical_pathway_patient WHERE no_rawat = @no_rawat
);

DELETE FROM mlite_clinical_pathway_compliance
WHERE clinical_pathway_patient_id IN (
  SELECT id FROM mlite_clinical_pathway_patient WHERE no_rawat = @no_rawat
);

DELETE FROM mlite_clinical_pathway_execution
WHERE clinical_pathway_patient_id IN (
  SELECT id FROM mlite_clinical_pathway_patient WHERE no_rawat = @no_rawat
);

DELETE FROM mlite_clinical_pathway_audit
WHERE clinical_pathway_patient_id IN (
  SELECT id FROM mlite_clinical_pathway_patient WHERE no_rawat = @no_rawat
);

DELETE FROM mlite_clinical_pathway_patient
WHERE no_rawat = @no_rawat;

DELETE FROM mlite_clinical_pathway_activity
WHERE clinical_pathway_day_id IN (
  SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id IN (
    SELECT id FROM mlite_clinical_pathway WHERE kode_cp = @cp_code
  )
);

DELETE FROM mlite_clinical_pathway_day
WHERE clinical_pathway_id IN (
  SELECT id FROM mlite_clinical_pathway WHERE kode_cp = @cp_code
);

DELETE FROM mlite_clinical_pathway_diagnosis
WHERE clinical_pathway_id IN (
  SELECT id FROM mlite_clinical_pathway WHERE kode_cp = @cp_code
);

DELETE FROM mlite_clinical_pathway
WHERE kode_cp = @cp_code;

DELETE FROM diagnosa_pasien
WHERE no_rawat = @no_rawat;

DELETE FROM reg_periksa
WHERE no_rawat = @no_rawat;

DELETE FROM pasien
WHERE no_rkm_medis = @no_rm;

INSERT INTO penyakit (kd_penyakit, nm_penyakit, ciri_ciri, keterangan, kd_ktg, status)
SELECT 'M54.5', 'Low back pain', 'Nyeri punggung bawah', 'Seeder dummy Clinical Pathway Low Back Pain', NULL, 'Tidak Menular'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM penyakit WHERE kd_penyakit = 'M54.5'
);

INSERT INTO pasien
(
  no_rkm_medis, nm_pasien, no_ktp, jk, tmp_lahir, tgl_lahir, nm_ibu, alamat,
  gol_darah, pekerjaan, stts_nikah, agama, tgl_daftar, no_tlp, umur, pnd,
  keluarga, namakeluarga, kd_pj, no_peserta, kd_kel, kd_kec, kd_kab,
  pekerjaanpj, alamatpj, kelurahanpj, kecamatanpj, kabupatenpj,
  perusahaan_pasien, suku_bangsa, bahasa_pasien, cacat_fisik, email, nip, kd_prop, propinsipj
)
VALUES
(
  @no_rm, 'Rizky Maulana', '6301000000960601', 'L', 'Barabai', '1989-03-10', 'Masniah',
  'Jl. Brigjen H. Hasan Basri No. 12', 'O', 'Pegawai Swasta', 'MENIKAH', 'ISLAM', '2026-06-16', '081234560601',
  '37 Th', 'S1', 'ISTRI', 'Lina Marlina', 'UMU', NULL, '1', 1, 1,
  'Ibu Rumah Tangga', 'Jl. Brigjen H. Hasan Basri No. 12', '-', '-', '-', '-', 1, 1, 1,
  'rizky.lowbackpain@example.com', '-', 1, '-'
);

INSERT INTO reg_periksa
(
  no_reg, no_rawat, tgl_registrasi, jam_reg, kd_dokter, no_rkm_medis, kd_poli,
  p_jawab, almt_pj, hubunganpj, biaya_reg, stts, stts_daftar, status_lanjut,
  kd_pj, umurdaftar, sttsumur, status_bayar, status_poli
)
VALUES
(
  'T00601', @no_rawat, '2026-06-16', '08:15:00', @kd_dokter, @no_rm, 'UMU',
  'Lina Marlina', 'Jl. Brigjen H. Hasan Basri No. 12', 'ISTRI', 0, 'Dirawat', 'Baru', 'Ranap',
  'UMU', 37, 'Th', 'Belum Bayar', 'Baru'
);

INSERT INTO diagnosa_pasien (no_rawat, kd_penyakit, status, prioritas, status_penyakit)
VALUES
(@no_rawat, 'M54.5', 'Ranap', 1, 'Baru');

INSERT INTO mlite_clinical_pathway
(
  kode_cp, nama_cp, jenis_layanan, target_los, target_tarif, confidence_score,
  evidence_note, guideline_note, aktif, created_at, updated_at
)
VALUES
(
  @cp_code, 'Clinical Pathway Low Back Pain Dewasa Ranap', 'Ranap', 4, 4200000, 91.20,
  'Seeder dummy matriks Clinical Pathway Low Back Pain 4 hari dengan format cetak multi-halaman.',
  'Meliputi asesmen, analgesia, evaluasi neurologis, mobilisasi bertahap, edukasi, discharge planning dan rehabilitasi.',
  'Ya', NOW(), NOW()
);

SET @cp_id = (
  SELECT id FROM mlite_clinical_pathway WHERE kode_cp = @cp_code LIMIT 1
);

INSERT INTO mlite_clinical_pathway_diagnosis (clinical_pathway_id, kd_penyakit, prioritas, tipe)
VALUES
(@cp_id, 'M54.5', 1, 'Utama');

INSERT INTO mlite_clinical_pathway_day (clinical_pathway_id, hari_ke, label_hari, tujuan_harian)
VALUES
(@cp_id, 1, 'Hari ke-1', 'Asesmen awal, penegakan diagnosis, analgetik awal, edukasi awal'),
(@cp_id, 2, 'Hari ke-2', 'Monitoring nyeri, mobilisasi bertahap, evaluasi obat, konseling lanjutan'),
(@cp_id, 3, 'Hari ke-3', 'Evaluasi respon terapi, optimalisasi intake, rehabilitasi dan persiapan pulang'),
(@cp_id, 4, 'Hari ke-4', 'Final check, edukasi pulang, home program dan resume medis');

SET @day1 = (SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @cp_id AND hari_ke = 1 LIMIT 1);
SET @day2 = (SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @cp_id AND hari_ke = 2 LIMIT 1);
SET @day3 = (SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @cp_id AND hari_ke = 3 LIMIT 1);
SET @day4 = (SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @cp_id AND hari_ke = 4 LIMIT 1);

INSERT INTO mlite_clinical_pathway_activity
(clinical_pathway_day_id, kategori, sumber_tabel, item_kode, item_nama, evidence_status, wajib, urutan)
VALUES
(@day1, 'Assessment', '1. ASESMEN AWAL', 'a. ASESMEN AWAL MEDIS', 'Dokter IGD/DPJP melakukan anamnesis, pemeriksaan fisik, red flag dan status neurologis', 'Wajib', 'Ya', 101),
(@day1, 'Assessment', '1. ASESMEN AWAL', 'b. ASESMEN AWAL KEPERAWATAN', 'Perawat primer melakukan asesmen kebutuhan bio-psiko-sosial-spiritual, nyeri, fungsi dan risiko jatuh', 'Wajib', 'Ya', 102),
(@day1, 'Nutrisi', '1. ASESMEN AWAL', 'c. SKRINING GIZI', 'Skrining gizi awal dan identifikasi masalah nutrisi terkait imobilisasi/nafsu makan', 'Wajib', 'Ya', 103),

(@day1, 'Laboratorium', '2. PENUNJANG DIAGNOSTIK', 'a. LABORATORIUM', 'DL', 'Direkomendasikan', 'Tidak', 201),
(@day1, 'Laboratorium', '2. PENUNJANG DIAGNOSTIK', 'a. LABORATORIUM', 'RFT, LFT', 'Opsional', 'Tidak', 202),
(@day1, 'Radiologi', '2. PENUNJANG DIAGNOSTIK', 'b. RADIOLOGI', 'ECG, Thorax AP, Lumbosacral AP/Lateral', 'Direkomendasikan', 'Tidak', 203),

(@day1, 'Assessment', '3. KONSULTASI DAN DISCHARGE PLANNING', 'a. KONSULTASI', 'Konsultasi rehab medik atau disiplin lain sesuai indikasi', 'Direkomendasikan', 'Tidak', 301),
(@day2, 'Assessment', '3. KONSULTASI DAN DISCHARGE PLANNING', 'a. KONSULTASI', 'Konsultasi rehab medik atau disiplin lain sesuai indikasi', 'Direkomendasikan', 'Tidak', 301),
(@day1, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'b. DISCHARGE PLANNING', 'Identifikasi kebutuhan edukasi, latihan, alat bantu dan dukungan keluarga selama perawatan', 'Wajib', 'Ya', 302),
(@day2, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'b. DISCHARGE PLANNING', 'Identifikasi kebutuhan edukasi, latihan, alat bantu dan dukungan keluarga selama perawatan', 'Wajib', 'Ya', 302),
(@day3, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'b. DISCHARGE PLANNING', 'Identifikasi kebutuhan edukasi, latihan, alat bantu dan dukungan keluarga selama perawatan', 'Wajib', 'Ya', 302),
(@day4, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'b. DISCHARGE PLANNING', 'Identifikasi kebutuhan edukasi, latihan, alat bantu dan dukungan keluarga selama perawatan', 'Wajib', 'Ya', 302),
(@day1, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'c. KEBUTUHAN DI RUMAH', 'Identifikasi kebutuhan lingkungan rumah, akses toilet, tangga, alas tidur dan aktivitas fungsional', 'Wajib', 'Ya', 303),
(@day2, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'c. KEBUTUHAN DI RUMAH', 'Identifikasi kebutuhan lingkungan rumah, akses toilet, tangga, alas tidur dan aktivitas fungsional', 'Wajib', 'Ya', 303),
(@day3, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'c. KEBUTUHAN DI RUMAH', 'Identifikasi kebutuhan lingkungan rumah, akses toilet, tangga, alas tidur dan aktivitas fungsional', 'Wajib', 'Ya', 303),
(@day4, 'Edukasi', '3. KONSULTASI DAN DISCHARGE PLANNING', 'c. KEBUTUHAN DI RUMAH', 'Identifikasi kebutuhan lingkungan rumah, akses toilet, tangga, alas tidur dan aktivitas fungsional', 'Wajib', 'Ya', 303),

(@day1, 'Monitoring', '4. ASESMEN LANJUTAN', 'a. MEDIS', 'Dokter DPJP visite harian dan follow up respon nyeri, alarm sign, serta kebutuhan penunjang', 'Wajib', 'Ya', 401),
(@day2, 'Monitoring', '4. ASESMEN LANJUTAN', 'a. MEDIS', 'Dokter DPJP visite harian dan follow up respon nyeri, alarm sign, serta kebutuhan penunjang', 'Wajib', 'Ya', 401),
(@day3, 'Monitoring', '4. ASESMEN LANJUTAN', 'a. MEDIS', 'Dokter DPJP visite harian dan follow up respon nyeri, alarm sign, serta kebutuhan penunjang', 'Wajib', 'Ya', 401),
(@day4, 'Monitoring', '4. ASESMEN LANJUTAN', 'a. MEDIS', 'Dokter DPJP visite harian dan follow up respon nyeri, alarm sign, serta kebutuhan penunjang', 'Wajib', 'Ya', 401),
(@day1, 'Monitoring', '4. ASESMEN LANJUTAN', 'b. KEPERAWATAN', 'TTV, skala nyeri, kemampuan mobilitas dan kebutuhan bantuan ADL', 'Wajib', 'Ya', 402),
(@day2, 'Monitoring', '4. ASESMEN LANJUTAN', 'b. KEPERAWATAN', 'TTV, skala nyeri, kemampuan mobilitas dan kebutuhan bantuan ADL', 'Wajib', 'Ya', 402),
(@day3, 'Monitoring', '4. ASESMEN LANJUTAN', 'b. KEPERAWATAN', 'TTV, skala nyeri, kemampuan mobilitas dan kebutuhan bantuan ADL', 'Wajib', 'Ya', 402),
(@day4, 'Monitoring', '4. ASESMEN LANJUTAN', 'b. KEPERAWATAN', 'TTV, skala nyeri, kemampuan mobilitas dan kebutuhan bantuan ADL', 'Wajib', 'Ya', 402),
(@day1, 'Nutrisi', '4. ASESMEN LANJUTAN', 'c. GIZI', 'Asesmen gizi lanjutan dan evaluasi toleransi oral', 'Direkomendasikan', 'Tidak', 403),
(@day2, 'Nutrisi', '4. ASESMEN LANJUTAN', 'c. GIZI', 'Asesmen gizi lanjutan dan evaluasi toleransi oral', 'Direkomendasikan', 'Tidak', 403),
(@day1, 'Obat', '4. ASESMEN LANJUTAN', 'd. FARMASI', 'Telaah resep, rekonsiliasi obat dan kajian interaksi/efek samping', 'Wajib', 'Ya', 404),
(@day2, 'Obat', '4. ASESMEN LANJUTAN', 'd. FARMASI', 'Telaah resep, rekonsiliasi obat dan kajian interaksi/efek samping', 'Wajib', 'Ya', 404),
(@day3, 'Obat', '4. ASESMEN LANJUTAN', 'd. FARMASI', 'Telaah resep, rekonsiliasi obat dan kajian interaksi/efek samping', 'Wajib', 'Ya', 404),

(@day1, 'Assessment', '5. DIAGNOSIS', 'a. DIAGNOSIS MEDIS', 'Low Back Pain / Nyeri Punggung Bawah', 'Wajib', 'Ya', 501),
(@day2, 'Assessment', '5. DIAGNOSIS', 'a. DIAGNOSIS MEDIS', 'Low Back Pain / Nyeri Punggung Bawah', 'Wajib', 'Ya', 501),
(@day3, 'Assessment', '5. DIAGNOSIS', 'a. DIAGNOSIS MEDIS', 'Low Back Pain / Nyeri Punggung Bawah', 'Wajib', 'Ya', 501),
(@day4, 'Assessment', '5. DIAGNOSIS', 'a. DIAGNOSIS MEDIS', 'Low Back Pain / Nyeri Punggung Bawah', 'Wajib', 'Ya', 501),
(@day1, 'Assessment', '5. DIAGNOSIS', 'b. DIAGNOSIS KEPERAWATAN', 'Nyeri akut', 'Wajib', 'Ya', 502),
(@day2, 'Assessment', '5. DIAGNOSIS', 'b. DIAGNOSIS KEPERAWATAN', 'Nyeri akut', 'Wajib', 'Ya', 502),
(@day3, 'Assessment', '5. DIAGNOSIS', 'b. DIAGNOSIS KEPERAWATAN', 'Nyeri akut', 'Wajib', 'Ya', 502),
(@day4, 'Assessment', '5. DIAGNOSIS', 'b. DIAGNOSIS KEPERAWATAN', 'Nyeri akut', 'Wajib', 'Ya', 502),
(@day1, 'Assessment', '5. DIAGNOSIS', 'c. DIAGNOSIS KEPERAWATAN', 'Gangguan mobilitas fisik', 'Wajib', 'Ya', 503),
(@day2, 'Assessment', '5. DIAGNOSIS', 'c. DIAGNOSIS KEPERAWATAN', 'Gangguan mobilitas fisik', 'Wajib', 'Ya', 503),
(@day3, 'Assessment', '5. DIAGNOSIS', 'c. DIAGNOSIS KEPERAWATAN', 'Gangguan mobilitas fisik', 'Wajib', 'Ya', 503),
(@day4, 'Assessment', '5. DIAGNOSIS', 'c. DIAGNOSIS KEPERAWATAN', 'Gangguan mobilitas fisik', 'Wajib', 'Ya', 503),
(@day1, 'Nutrisi', '5. DIAGNOSIS', 'd. DIAGNOSIS GIZI', 'Intake oral inadekuat terkait nyeri dan aktivitas terbatas', 'Direkomendasikan', 'Tidak', 504),
(@day2, 'Nutrisi', '5. DIAGNOSIS', 'd. DIAGNOSIS GIZI', 'Intake oral inadekuat terkait nyeri dan aktivitas terbatas', 'Direkomendasikan', 'Tidak', 504),
(@day3, 'Nutrisi', '5. DIAGNOSIS', 'd. DIAGNOSIS GIZI', 'Intake oral inadekuat terkait nyeri dan aktivitas terbatas', 'Direkomendasikan', 'Tidak', 504),
(@day4, 'Nutrisi', '5. DIAGNOSIS', 'd. DIAGNOSIS GIZI', 'Intake oral inadekuat terkait nyeri dan aktivitas terbatas', 'Direkomendasikan', 'Tidak', 504),

(@day1, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'a. EDUKASI MEDIS', 'Penjelasan diagnosis, rencana terapi, red flag dan informed consent tindakan', 'Wajib', 'Ya', 601),
(@day2, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'a. EDUKASI MEDIS', 'Penjelasan diagnosis, rencana terapi, red flag dan informed consent tindakan', 'Wajib', 'Ya', 601),
(@day3, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'a. EDUKASI MEDIS', 'Penjelasan diagnosis, rencana terapi, red flag dan informed consent tindakan', 'Wajib', 'Ya', 601),
(@day4, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'a. EDUKASI MEDIS', 'Penjelasan diagnosis, rencana terapi, red flag dan informed consent tindakan', 'Wajib', 'Ya', 601),
(@day1, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'b. KONSELING GIZI', 'Diet lunak rendah garam dan peningkatan hidrasi oral', 'Direkomendasikan', 'Tidak', 602),
(@day4, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'b. KONSELING GIZI', 'Diet lunak rendah garam dan peningkatan hidrasi oral', 'Direkomendasikan', 'Tidak', 602),
(@day1, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'c. KEPERAWATAN', 'Konseling manajemen nyeri non farmakologis, posisi nyaman dan body mechanic', 'Wajib', 'Ya', 603),
(@day2, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'c. KEPERAWATAN', 'Konseling manajemen nyeri non farmakologis, posisi nyaman dan body mechanic', 'Wajib', 'Ya', 603),
(@day1, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'd. POLA ISTIRAHAT', 'Edukasi pola istirahat, tidur dan pembatasan aktivitas pemicu nyeri', 'Wajib', 'Ya', 604),
(@day2, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'd. POLA ISTIRAHAT', 'Edukasi pola istirahat, tidur dan pembatasan aktivitas pemicu nyeri', 'Wajib', 'Ya', 604),
(@day3, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'd. POLA ISTIRAHAT', 'Edukasi pola istirahat, tidur dan pembatasan aktivitas pemicu nyeri', 'Wajib', 'Ya', 604),
(@day4, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'd. POLA ISTIRAHAT', 'Edukasi pola istirahat, tidur dan pembatasan aktivitas pemicu nyeri', 'Wajib', 'Ya', 604),
(@day1, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'e. FARMASI', 'Informasi obat, aturan minum, kewaspadaan gastritis dan efek sedasi', 'Wajib', 'Ya', 605),
(@day2, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'e. FARMASI', 'Informasi obat, aturan minum, kewaspadaan gastritis dan efek sedasi', 'Wajib', 'Ya', 605),
(@day3, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'e. FARMASI', 'Informasi obat, aturan minum, kewaspadaan gastritis dan efek sedasi', 'Wajib', 'Ya', 605),
(@day4, 'Edukasi', '6. EDUKASI TERINTEGRASI', 'e. FARMASI', 'Informasi obat, aturan minum, kewaspadaan gastritis dan efek sedasi', 'Wajib', 'Ya', 605),

(@day1, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'a. INJEKSI', 'Analgetik injeksi', 'Wajib', 'Ya', 701),
(@day2, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'a. INJEKSI', 'Analgetik injeksi', 'Wajib', 'Ya', 701),
(@day1, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'b. INJEKSI', 'PPI/H2 blocker injeksi', 'Direkomendasikan', 'Tidak', 702),
(@day2, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'b. INJEKSI', 'PPI/H2 blocker injeksi', 'Direkomendasikan', 'Tidak', 702),
(@day2, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'c. OBAT TAMBAHAN', 'Neurotropik', 'Direkomendasikan', 'Tidak', 703),
(@day3, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'c. OBAT TAMBAHAN', 'Neurotropik', 'Direkomendasikan', 'Tidak', 703),
(@day4, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'c. OBAT TAMBAHAN', 'Neurotropik', 'Direkomendasikan', 'Tidak', 703),
(@day1, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'd. CAIRAN INFUS', 'RL / cairan pemeliharaan', 'Direkomendasikan', 'Tidak', 704),
(@day2, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'd. CAIRAN INFUS', 'RL / cairan pemeliharaan', 'Direkomendasikan', 'Tidak', 704),
(@day2, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'e. OBAT ORAL', 'Analgetik oral', 'Wajib', 'Ya', 705),
(@day3, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'e. OBAT ORAL', 'Analgetik oral', 'Wajib', 'Ya', 705),
(@day4, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'e. OBAT ORAL', 'Analgetik oral', 'Wajib', 'Ya', 705),
(@day2, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'f. OBAT ORAL', 'Vitamin neurotropik / suplemen', 'Direkomendasikan', 'Tidak', 706),
(@day3, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'f. OBAT ORAL', 'Vitamin neurotropik / suplemen', 'Direkomendasikan', 'Tidak', 706),
(@day4, 'Obat', '7. TERAPI MEDIKAMENTOSA', 'f. OBAT ORAL', 'Vitamin neurotropik / suplemen', 'Direkomendasikan', 'Tidak', 706),

(@day1, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'a. MEDIS', 'Terapi konservatif, bed rest relatif, brace sesuai indikasi', 'Wajib', 'Ya', 801),
(@day2, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'a. MEDIS', 'Terapi konservatif, bed rest relatif, brace sesuai indikasi', 'Wajib', 'Ya', 801),
(@day3, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'a. MEDIS', 'Terapi konservatif, bed rest relatif, brace sesuai indikasi', 'Wajib', 'Ya', 801),
(@day4, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'a. MEDIS', 'Terapi konservatif, bed rest relatif, brace sesuai indikasi', 'Wajib', 'Ya', 801),
(@day1, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'b. KEPERAWATAN', 'Manajemen nyeri, posisi nyaman, kompres hangat dan edukasi body mechanic', 'Wajib', 'Ya', 802),
(@day2, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'b. KEPERAWATAN', 'Manajemen nyeri, posisi nyaman, kompres hangat dan edukasi body mechanic', 'Wajib', 'Ya', 802),
(@day3, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'b. KEPERAWATAN', 'Manajemen nyeri, posisi nyaman, kompres hangat dan edukasi body mechanic', 'Wajib', 'Ya', 802),
(@day4, 'Tindakan', '8. TATALAKSANA / INTERVENSI', 'b. KEPERAWATAN', 'Manajemen nyeri, posisi nyaman, kompres hangat dan edukasi body mechanic', 'Wajib', 'Ya', 802),
(@day1, 'Monitoring', '8. TATALAKSANA / INTERVENSI', 'c. RISIKO JATUH', 'Pencegahan risiko jatuh dan bantuan ambulasi', 'Wajib', 'Ya', 803),
(@day2, 'Monitoring', '8. TATALAKSANA / INTERVENSI', 'c. RISIKO JATUH', 'Pencegahan risiko jatuh dan bantuan ambulasi', 'Wajib', 'Ya', 803),
(@day3, 'Monitoring', '8. TATALAKSANA / INTERVENSI', 'c. RISIKO JATUH', 'Pencegahan risiko jatuh dan bantuan ambulasi', 'Wajib', 'Ya', 803),
(@day4, 'Monitoring', '8. TATALAKSANA / INTERVENSI', 'c. RISIKO JATUH', 'Pencegahan risiko jatuh dan bantuan ambulasi', 'Wajib', 'Ya', 803),
(@day1, 'Nutrisi', '8. TATALAKSANA / INTERVENSI', 'd. GIZI', 'Diet lunak rendah garam, cukup cairan dan tinggi serat', 'Direkomendasikan', 'Tidak', 804),
(@day2, 'Nutrisi', '8. TATALAKSANA / INTERVENSI', 'd. GIZI', 'Diet lunak rendah garam, cukup cairan dan tinggi serat', 'Direkomendasikan', 'Tidak', 804),
(@day3, 'Nutrisi', '8. TATALAKSANA / INTERVENSI', 'd. GIZI', 'Diet lunak rendah garam, cukup cairan dan tinggi serat', 'Direkomendasikan', 'Tidak', 804),
(@day4, 'Nutrisi', '8. TATALAKSANA / INTERVENSI', 'd. GIZI', 'Diet lunak rendah garam, cukup cairan dan tinggi serat', 'Direkomendasikan', 'Tidak', 804),
(@day2, 'Obat', '8. TATALAKSANA / INTERVENSI', 'e. FARMASI', 'Rekomendasi farmasi kepada DPJP bila ada interaksi atau duplikasi terapi', 'Direkomendasikan', 'Tidak', 805),
(@day3, 'Obat', '8. TATALAKSANA / INTERVENSI', 'e. FARMASI', 'Rekomendasi farmasi kepada DPJP bila ada interaksi atau duplikasi terapi', 'Direkomendasikan', 'Tidak', 805),
(@day4, 'Obat', '8. TATALAKSANA / INTERVENSI', 'e. FARMASI', 'Rekomendasi farmasi kepada DPJP bila ada interaksi atau duplikasi terapi', 'Direkomendasikan', 'Tidak', 805),

(@day1, 'Monitoring', '9. MONITORING DAN EVALUASI', 'a. MEDIS', 'Review verifikasi rencana asuhan dan respon terapi', 'Wajib', 'Ya', 901),
(@day2, 'Monitoring', '9. MONITORING DAN EVALUASI', 'a. MEDIS', 'Review verifikasi rencana asuhan dan respon terapi', 'Wajib', 'Ya', 901),
(@day3, 'Monitoring', '9. MONITORING DAN EVALUASI', 'a. MEDIS', 'Review verifikasi rencana asuhan dan respon terapi', 'Wajib', 'Ya', 901),
(@day4, 'Monitoring', '9. MONITORING DAN EVALUASI', 'a. MEDIS', 'Review verifikasi rencana asuhan dan respon terapi', 'Wajib', 'Ya', 901),
(@day1, 'Monitoring', '9. MONITORING DAN EVALUASI', 'b. KEPERAWATAN', 'Monitoring terapi intravena/oral dan evaluasi nyeri tiap shift', 'Wajib', 'Ya', 902),
(@day2, 'Monitoring', '9. MONITORING DAN EVALUASI', 'b. KEPERAWATAN', 'Monitoring terapi intravena/oral dan evaluasi nyeri tiap shift', 'Wajib', 'Ya', 902),
(@day3, 'Monitoring', '9. MONITORING DAN EVALUASI', 'b. KEPERAWATAN', 'Monitoring terapi intravena/oral dan evaluasi nyeri tiap shift', 'Wajib', 'Ya', 902),
(@day4, 'Monitoring', '9. MONITORING DAN EVALUASI', 'b. KEPERAWATAN', 'Monitoring terapi intravena/oral dan evaluasi nyeri tiap shift', 'Wajib', 'Ya', 902),
(@day2, 'Monitoring', '9. MONITORING DAN EVALUASI', 'c. GIZI', 'Monitoring kepatuhan diet, mual muntah dan intake oral', 'Direkomendasikan', 'Tidak', 903),
(@day3, 'Monitoring', '9. MONITORING DAN EVALUASI', 'c. GIZI', 'Monitoring kepatuhan diet, mual muntah dan intake oral', 'Direkomendasikan', 'Tidak', 903),
(@day4, 'Monitoring', '9. MONITORING DAN EVALUASI', 'c. GIZI', 'Monitoring kepatuhan diet, mual muntah dan intake oral', 'Direkomendasikan', 'Tidak', 903),
(@day2, 'Monitoring', '9. MONITORING DAN EVALUASI', 'd. FARMASI', 'Monitoring interaksi obat', 'Direkomendasikan', 'Tidak', 904),
(@day3, 'Monitoring', '9. MONITORING DAN EVALUASI', 'd. FARMASI', 'Monitoring interaksi obat', 'Direkomendasikan', 'Tidak', 904),
(@day4, 'Monitoring', '9. MONITORING DAN EVALUASI', 'd. FARMASI', 'Monitoring interaksi obat', 'Direkomendasikan', 'Tidak', 904),
(@day2, 'Monitoring', '9. MONITORING DAN EVALUASI', 'e. FARMASI', 'Monitoring efek samping obat', 'Direkomendasikan', 'Tidak', 905),
(@day3, 'Monitoring', '9. MONITORING DAN EVALUASI', 'e. FARMASI', 'Monitoring efek samping obat', 'Direkomendasikan', 'Tidak', 905),
(@day4, 'Monitoring', '9. MONITORING DAN EVALUASI', 'e. FARMASI', 'Monitoring efek samping obat', 'Direkomendasikan', 'Tidak', 905),

(@day1, 'Tindakan', '10. MOBILISASI DAN REHABILITASI', 'a. MEDIS', 'Mobilisasi bertahap sesuai toleransi nyeri', 'Wajib', 'Ya', 1001),
(@day2, 'Tindakan', '10. MOBILISASI DAN REHABILITASI', 'a. MEDIS', 'Mobilisasi bertahap sesuai toleransi nyeri', 'Wajib', 'Ya', 1001),
(@day3, 'Tindakan', '10. MOBILISASI DAN REHABILITASI', 'a. MEDIS', 'Mobilisasi bertahap sesuai toleransi nyeri', 'Wajib', 'Ya', 1001),
(@day4, 'Tindakan', '10. MOBILISASI DAN REHABILITASI', 'a. MEDIS', 'Mobilisasi bertahap sesuai toleransi nyeri', 'Wajib', 'Ya', 1001),
(@day2, 'Tindakan', '10. MOBILISASI DAN REHABILITASI', 'b. FISIOTERAPI', 'Fisioterapi sesuai indikasi', 'Direkomendasikan', 'Tidak', 1002),
(@day3, 'Tindakan', '10. MOBILISASI DAN REHABILITASI', 'b. FISIOTERAPI', 'Fisioterapi sesuai indikasi', 'Direkomendasikan', 'Tidak', 1002),
(@day4, 'Tindakan', '10. MOBILISASI DAN REHABILITASI', 'b. FISIOTERAPI', 'Fisioterapi sesuai indikasi', 'Direkomendasikan', 'Tidak', 1002),

(@day1, 'Outcome', '11. OUTCOME / HASIL', 'a. MEDIS', 'Tegaknya diagnosis dan red flag teridentifikasi', 'Wajib', 'Ya', 1101),
(@day2, 'Outcome', '11. OUTCOME / HASIL', 'b. MEDIS', 'Nyeri berkurang dan mobilisasi mulai membaik', 'Wajib', 'Ya', 1102),
(@day3, 'Outcome', '11. OUTCOME / HASIL', 'b. MEDIS', 'Nyeri berkurang dan mobilisasi mulai membaik', 'Wajib', 'Ya', 1102),
(@day4, 'Outcome', '11. OUTCOME / HASIL', 'b. MEDIS', 'Nyeri berkurang dan mobilisasi mulai membaik', 'Wajib', 'Ya', 1102),
(@day3, 'Outcome', '11. OUTCOME / HASIL', 'c. GIZI', 'Asupan makanan lebih dari 80 persen kebutuhan', 'Direkomendasikan', 'Tidak', 1103),
(@day4, 'Outcome', '11. OUTCOME / HASIL', 'c. GIZI', 'Asupan makanan lebih dari 80 persen kebutuhan', 'Direkomendasikan', 'Tidak', 1103),
(@day1, 'Outcome', '11. OUTCOME / HASIL', 'd. FARMASI', 'Terapi obat rasional sesuai indikasi', 'Wajib', 'Ya', 1104),
(@day2, 'Outcome', '11. OUTCOME / HASIL', 'd. FARMASI', 'Terapi obat rasional sesuai indikasi', 'Wajib', 'Ya', 1104),
(@day3, 'Outcome', '11. OUTCOME / HASIL', 'd. FARMASI', 'Terapi obat rasional sesuai indikasi', 'Wajib', 'Ya', 1104),
(@day4, 'Outcome', '11. OUTCOME / HASIL', 'd. FARMASI', 'Terapi obat rasional sesuai indikasi', 'Wajib', 'Ya', 1104),

(@day4, 'Outcome', '12. KRITERIA PULANG DAN RENCANA PULANG', 'a. KRITERIA PULANG', 'Tanda vital stabil, nyeri terkendali dan mampu mobilisasi dengan aman', 'Wajib', 'Ya', 1201),
(@day4, 'Edukasi', '12. KRITERIA PULANG DAN RENCANA PULANG', 'b. EDUKASI PULANG', 'Edukasi kontrol ulang, latihan rumah, body mechanic dan tanda bahaya', 'Wajib', 'Ya', 1202),
(@day4, 'Edukasi', '12. KRITERIA PULANG DAN RENCANA PULANG', 'c. DOKUMEN PULANG', 'Resume medis, resume keperawatan, surat kontrol dan edukasi obat pulang', 'Wajib', 'Ya', 1203);

INSERT INTO mlite_clinical_pathway_patient
(no_rawat, clinical_pathway_id, kd_penyakit, tanggal_mulai, tanggal_selesai, status, auto_generated)
VALUES
(@no_rawat, @cp_id, 'M54.5', @cp_start, @cp_finish, 'Selesai', 'Tidak');

SET @cpp_id = (
  SELECT id FROM mlite_clinical_pathway_patient WHERE no_rawat = @no_rawat LIMIT 1
);

INSERT INTO mlite_clinical_pathway_execution
(
  clinical_pathway_patient_id, clinical_pathway_activity_id, hari_ke, tanggal_rencana,
  tanggal_realisasi, status, sumber_data, sumber_referensi, petugas, catatan
)
SELECT
  @cpp_id,
  a.id,
  d.hari_ke,
  DATE_ADD(DATE(@cp_start), INTERVAL d.hari_ke - 1 DAY),
  DATE_ADD(DATE_ADD(DATE(@cp_start), INTERVAL d.hari_ke - 1 DAY), INTERVAL 9 HOUR),
  'Completed',
  'manual',
  CONCAT('matrix-dummy|', a.id),
  @nip_perawat,
  NULL
FROM mlite_clinical_pathway_activity a
INNER JOIN mlite_clinical_pathway_day d ON d.id = a.clinical_pathway_day_id
WHERE d.clinical_pathway_id = @cp_id
ORDER BY d.hari_ke, a.urutan, a.id;

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Dilanjutkan dengan asesmen bio-psiko-sosial-spiritual, nyeri, fungsi, risiko jatuh dan kebutuhan edukasi.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '1. ASESMEN AWAL'
  AND a.item_kode = 'b. ASESMEN AWAL KEPERAWATAN';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Sesuai indikasi klinis, bila terdapat red flag atau komorbid.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '2. PENUNJANG DIAGNOSTIK'
  AND a.item_kode = 'b. RADIOLOGI';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Program pendidikan pasien dan keluarga sejak awal masuk rawat.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '3. KONSULTASI DAN DISCHARGE PLANNING'
  AND a.item_kode = 'b. DISCHARGE PLANNING';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Visite harian / follow up oleh DPJP dan evaluasi progres nyeri.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '4. ASESMEN LANJUTAN'
  AND a.item_kode = 'a. MEDIS';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Dilakukan dalam 3 shift dan didokumentasikan di lembar observasi.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '4. ASESMEN LANJUTAN'
  AND a.item_kode = 'b. KEPERAWATAN';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Disesuaikan dengan status nyeri, kebutuhan kalori dan toleransi oral pasien.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '4. ASESMEN LANJUTAN'
  AND a.item_kode = 'c. GIZI';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Monitoring potensi interaksi NSAID, obat lambung, sedatif dan duplikasi terapi.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '4. ASESMEN LANJUTAN'
  AND a.item_kode = 'd. FARMASI';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Edukasi diberikan bertahap dan terintegrasi dengan catatan informed consent.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '6. EDUKASI TERINTEGRASI'
  AND a.item_kode = 'a. EDUKASI MEDIS';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Meningkatkan kepatuhan pasien serta mencegah kesalahan penggunaan obat di rumah.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '6. EDUKASI TERINTEGRASI'
  AND a.item_kode = 'e. FARMASI';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Tatalaksana konservatif terintegrasi dengan edukasi postur, body mechanic dan pengendalian nyeri.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '8. TATALAKSANA / INTERVENSI'
  AND a.item_kode = 'a. MEDIS';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Bentuk makanan lunak, porsi kecil sering, cairan cukup dan hindari konstipasi.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '8. TATALAKSANA / INTERVENSI'
  AND a.item_kode = 'd. GIZI';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Monitoring harian dengan intervensi farmasi bila diperlukan.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '9. MONITORING DAN EVALUASI'
  AND a.item_kode IN ('d. FARMASI', 'e. FARMASI');

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.catatan = 'Mobilisasi dimulai dari miring kanan-kiri, duduk, berdiri hingga berjalan bertahap.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND a.sumber_tabel = '10. MOBILISASI DAN REHABILITASI'
  AND a.item_kode = 'a. MEDIS';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.status = 'Missed',
    e.tanggal_realisasi = NULL,
    e.catatan = 'Belum dikerjakan karena kondisi klinis membaik tanpa indikasi kuat.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND e.hari_ke = 2
  AND a.item_nama = 'Fisioterapi sesuai indikasi';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.status = 'Missed',
    e.tanggal_realisasi = NULL,
    e.catatan = 'Neurotropik ditunda, pasien cukup membaik dengan analgetik dan latihan mobilisasi.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND e.hari_ke = 2
  AND a.item_nama = 'Neurotropik';

UPDATE mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
SET e.status = 'Missed',
    e.tanggal_realisasi = NULL,
    e.catatan = 'Monitoring efek samping obat tidak terdokumentasi lengkap pada hari kedua.'
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND e.hari_ke = 2
  AND a.item_nama = 'Monitoring efek samping obat';

INSERT INTO rawat_inap_pr
(no_rawat, kd_jenis_prw, nip, tgl_perawatan, jam_rawat, material, bhp, tarif_tindakanpr, kso, menejemen, biaya_rawat)
VALUES
(@no_rawat, 'RI001', @nip_perawat, '2026-06-16', '09:00:00', 0, 0, 25000, 0, 0, 25000),
(@no_rawat, 'RI001', @nip_perawat, '2026-06-17', '08:30:00', 0, 0, 25000, 0, 0, 25000),
(@no_rawat, 'RI001', @nip_perawat, '2026-06-18', '08:20:00', 0, 0, 25000, 0, 0, 25000),
(@no_rawat, 'RI001', @nip_perawat, '2026-06-19', '08:10:00', 0, 0, 25000, 0, 0, 25000);

INSERT INTO mlite_clinical_pathway_compliance
(clinical_pathway_patient_id, planned_activity, completed_activity, missed_activity, compliance_percentage, kategori_kepatuhan, last_calculated_at)
SELECT
  @cpp_id,
  COUNT(*) AS planned_activity,
  SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_activity,
  SUM(CASE WHEN status = 'Missed' THEN 1 ELSE 0 END) AS missed_activity,
  ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) AS compliance_percentage,
  CASE
    WHEN ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) >= 90 THEN 'Sangat Patuh'
    WHEN ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) >= 75 THEN 'Patuh'
    WHEN ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) >= 50 THEN 'Kurang Patuh'
    ELSE 'Tidak Patuh'
  END,
  NOW()
FROM mlite_clinical_pathway_execution
WHERE clinical_pathway_patient_id = @cpp_id;

INSERT INTO mlite_clinical_pathway_variance
(clinical_pathway_patient_id, clinical_pathway_execution_id, kategori_variance, penyebab, deskripsi, severity, tanggal_variance, status_tindak_lanjut)
SELECT
  @cpp_id,
  e.id,
  'Tindakan',
  'Fisioterapi belum dilakukan',
  'Fisioterapi pada hari kedua tidak dilakukan karena pasien membaik dengan terapi konservatif.',
  'Sedang',
  '2026-06-17 15:00:00',
  'Open'
FROM mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND e.hari_ke = 2
  AND a.item_nama = 'Fisioterapi sesuai indikasi'
LIMIT 1;

INSERT INTO mlite_clinical_pathway_variance
(clinical_pathway_patient_id, clinical_pathway_execution_id, kategori_variance, penyebab, deskripsi, severity, tanggal_variance, status_tindak_lanjut)
SELECT
  @cpp_id,
  e.id,
  'Obat',
  'Neurotropik ditunda',
  'Terapi neurotropik tidak diberikan pada hari kedua karena respons analgetik dan latihan cukup baik.',
  'Rendah',
  '2026-06-17 16:00:00',
  'Closed'
FROM mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND e.hari_ke = 2
  AND a.item_nama = 'Neurotropik'
LIMIT 1;

INSERT INTO mlite_clinical_pathway_variance
(clinical_pathway_patient_id, clinical_pathway_execution_id, kategori_variance, penyebab, deskripsi, severity, tanggal_variance, status_tindak_lanjut)
SELECT
  @cpp_id,
  e.id,
  'Administrasi',
  'Dokumentasi monitoring tidak lengkap',
  'Monitoring efek samping obat tidak terdokumentasi lengkap pada hari kedua.',
  'Sedang',
  '2026-06-17 20:00:00',
  'Open'
FROM mlite_clinical_pathway_execution e
INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
WHERE e.clinical_pathway_patient_id = @cpp_id
  AND e.hari_ke = 2
  AND a.item_nama = 'Monitoring efek samping obat'
LIMIT 1;

INSERT INTO mlite_clinical_pathway_audit
(clinical_pathway_patient_id, clinical_pathway_id, aksi, referensi, deskripsi, user_aksi, created_at)
VALUES
(@cpp_id, @cp_id, 'import_seeder', @no_rawat, 'Import seeder dummy Low Back Pain ranap 4 hari.', 'system', NOW()),
(@cpp_id, @cp_id, 'generate_print_matrix', @no_rawat, 'Menyiapkan data dummy cetak matriks multi-halaman.', 'system', NOW());

COMMIT;
