-- Seeder dummy Clinical Pathway Pneumonia Ranap
-- Mencakup:
-- 1. Master CP dewasa + anak
-- 2. Mapping ICD J18.9
-- 3. Template harian Hari 1-4
-- 4. Pasien dummy Ranap
-- 5. Tindakan perawat Ranap agar tanda tangan perawat otomatis muncul
-- 6. Data lab, radiologi, resep obat dummy
-- 7. Hasil generate CP pasien, compliance, variance, audit

START TRANSACTION;

SET @adult_no_rawat = '2026/06/13/000301';
SET @child_no_rawat = '2026/06/13/000302';
SET @adult_rm = '930301';
SET @child_rm = '930302';
SET @adult_cp_code = 'CP-PNEUMONIA-RANAP-DWS';
SET @child_cp_code = 'CP-PNEUMONIA-RANAP-ANK';
SET @adult_start = '2026-06-13 08:00:00';
SET @child_start = '2026-06-13 09:00:00';
SET @adult_recipe = '260613030101';
SET @child_recipe = '260613030102';

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

SET @kode_obat_paracetamol = (
  SELECT kode_brng
  FROM databarang
  WHERE nama_brng LIKE '%Paracetamol%'
  ORDER BY kode_brng ASC
  LIMIT 1
);

SET @kode_obat_sefalosporin = (
  SELECT kode_brng
  FROM databarang
  WHERE nama_brng LIKE '%Ceftriaxone%'
  ORDER BY kode_brng ASC
  LIMIT 1
);

DELETE FROM resep_dokter
WHERE no_resep IN (@adult_recipe, @child_recipe);

DELETE FROM resep_obat
WHERE no_resep IN (@adult_recipe, @child_recipe)
   OR no_rawat IN (@adult_no_rawat, @child_no_rawat);

DELETE FROM periksa_radiologi
WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat);

DELETE FROM periksa_lab
WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat);

DELETE FROM rawat_inap_pr
WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat);

DELETE FROM mlite_clinical_pathway_variance
WHERE clinical_pathway_patient_id IN (
  SELECT id
  FROM mlite_clinical_pathway_patient
  WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat)
);

DELETE FROM mlite_clinical_pathway_compliance
WHERE clinical_pathway_patient_id IN (
  SELECT id
  FROM mlite_clinical_pathway_patient
  WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat)
);

DELETE FROM mlite_clinical_pathway_execution
WHERE clinical_pathway_patient_id IN (
  SELECT id
  FROM mlite_clinical_pathway_patient
  WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat)
);

DELETE FROM mlite_clinical_pathway_audit
WHERE clinical_pathway_patient_id IN (
  SELECT id
  FROM mlite_clinical_pathway_patient
  WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat)
);

DELETE FROM mlite_clinical_pathway_patient
WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat);

DELETE FROM mlite_clinical_pathway_activity
WHERE clinical_pathway_day_id IN (
  SELECT id
  FROM mlite_clinical_pathway_day
  WHERE clinical_pathway_id IN (
    SELECT id
    FROM mlite_clinical_pathway
    WHERE kode_cp IN (@adult_cp_code, @child_cp_code)
  )
);

DELETE FROM mlite_clinical_pathway_day
WHERE clinical_pathway_id IN (
  SELECT id
  FROM mlite_clinical_pathway
  WHERE kode_cp IN (@adult_cp_code, @child_cp_code)
);

DELETE FROM mlite_clinical_pathway_diagnosis
WHERE clinical_pathway_id IN (
  SELECT id
  FROM mlite_clinical_pathway
  WHERE kode_cp IN (@adult_cp_code, @child_cp_code)
);

DELETE FROM mlite_clinical_pathway
WHERE kode_cp IN (@adult_cp_code, @child_cp_code);

DELETE FROM diagnosa_pasien
WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat);

DELETE FROM reg_periksa
WHERE no_rawat IN (@adult_no_rawat, @child_no_rawat);

DELETE FROM pasien
WHERE no_rkm_medis IN (@adult_rm, @child_rm);

INSERT INTO penyakit (kd_penyakit, nm_penyakit, ciri_ciri, keterangan, kd_ktg, status)
SELECT 'J18.9', 'Pneumonia, unspecified organism', 'Pneumonia', 'Seeder dummy Clinical Pathway pneumonia ranap', NULL, 'Menular'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1
  FROM penyakit
  WHERE kd_penyakit = 'J18.9'
);

INSERT INTO jns_perawatan_lab
(kd_jenis_prw, nm_perawatan, bagian_rs, bhp, tarif_perujuk, tarif_tindakan_dokter, tarif_tindakan_petugas, kso, menejemen, total_byr, kd_pj, status, kelas, kategori)
SELECT 'LABPN001', 'Darah Rutin Pneumonia', 0, 0, 0, 85000, 0, 0, 0, 85000, '-', '1', 'Kelas 1', 'PK'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM jns_perawatan_lab WHERE kd_jenis_prw = 'LABPN001'
);

INSERT INTO jns_perawatan_lab
(kd_jenis_prw, nm_perawatan, bagian_rs, bhp, tarif_perujuk, tarif_tindakan_dokter, tarif_tindakan_petugas, kso, menejemen, total_byr, kd_pj, status, kelas, kategori)
SELECT 'LABPN002', 'C-Reactive Protein', 0, 0, 0, 125000, 0, 0, 0, 125000, '-', '1', 'Kelas 1', 'PK'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM jns_perawatan_lab WHERE kd_jenis_prw = 'LABPN002'
);

INSERT INTO jns_perawatan_radiologi
(kd_jenis_prw, nm_perawatan, bagian_rs, bhp, tarif_perujuk, tarif_tindakan_dokter, tarif_tindakan_petugas, kso, menejemen, total_byr, kd_pj, status, kelas)
SELECT 'RADPN001', 'Foto Thorax PA/AP', 0, 0, 0, 150000, 0, 0, 0, 150000, '-', '1', 'Kelas 1'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM jns_perawatan_radiologi WHERE kd_jenis_prw = 'RADPN001'
);

INSERT INTO databarang
(kode_brng, nama_brng, kode_satbesar, kode_sat, letak_barang, dasar, h_beli, ralan, kelas1, kelas2, kelas3, utama, vip, vvip, beliluar, jualbebas, karyawan, stokminimal, kdjns, isi, kapasitas, expire, status, kode_industri, kode_kategori, kode_golongan)
SELECT 'BCT001', 'Ceftriaxone Inj 1 gram', '-', '-', '-', 25000, 25000, 30000, 30000, 30000, 30000, 30000, 30000, 30000, 30000, 30000, 30000, 10, '-', 1, 1, '2028-12-31', '1', '-', '-', '-'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM databarang WHERE kode_brng = 'BCT001'
);

INSERT INTO databarang
(kode_brng, nama_brng, kode_satbesar, kode_sat, letak_barang, dasar, h_beli, ralan, kelas1, kelas2, kelas3, utama, vip, vvip, beliluar, jualbebas, karyawan, stokminimal, kdjns, isi, kapasitas, expire, status, kode_industri, kode_kategori, kode_golongan)
SELECT 'BOR001', 'Oralit Sachet', '-', '-', '-', 2500, 2500, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 10, '-', 1, 1, '2028-12-31', '1', '-', '-', '-'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM databarang WHERE kode_brng = 'BOR001'
);

SET @kode_obat_paracetamol = IFNULL(@kode_obat_paracetamol, 'B00001');
SET @kode_obat_sefalosporin = IFNULL(@kode_obat_sefalosporin, 'BCT001');

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
  @adult_rm, 'Slamet Raharjo', '6301000000930301', 'L', 'Barabai', '1990-01-15', 'Siti Aminah',
  'Jl. Murakata No. 20', 'O', 'Wiraswasta', 'MENIKAH', 'ISLAM', '2026-06-13', '081111110201',
  '36 Th', 'SMA', 'ISTRI', 'Siti Badriah', 'UMU', NULL, '1', 1, 1,
  'Ibu Rumah Tangga', 'Jl. Murakata No. 20', '-', '-', '-', '-', 1, 1, 1,
  'slamet.pneumonia@example.com', '-', 1, '-'
),
(
  @child_rm, 'Nabila Azzahra', '6301000000930302', 'P', 'Barabai', '2016-05-12', 'Nurhayati',
  'Jl. Perintis No. 30', 'A', 'Pelajar', 'BELUM MENIKAH', 'ISLAM', '2026-06-13', '082222220202',
  '10 Th', 'SD', 'IBU', 'Nurhayati', 'UMU', NULL, '1', 1, 1,
  'Ibu Rumah Tangga', 'Jl. Perintis No. 30', '-', '-', '-', '-', 1, 1, 1,
  'nabila.pneumonia@example.com', '-', 1, '-'
);

INSERT INTO reg_periksa
(
  no_reg, no_rawat, tgl_registrasi, jam_reg, kd_dokter, no_rkm_medis, kd_poli,
  p_jawab, almt_pj, hubunganpj, biaya_reg, stts, stts_daftar, status_lanjut,
  kd_pj, umurdaftar, sttsumur, status_bayar, status_poli
)
VALUES
(
  'T00301', @adult_no_rawat, '2026-06-13', '08:00:00', @kd_dokter, @adult_rm, 'UMU',
  'Siti Badriah', 'Jl. Murakata No. 20', 'ISTRI', 0, 'Dirawat', 'Baru', 'Ranap',
  'UMU', 36, 'Th', 'Belum Bayar', 'Baru'
),
(
  'T00302', @child_no_rawat, '2026-06-13', '09:00:00', @kd_dokter, @child_rm, 'UMU',
  'Nurhayati', 'Jl. Perintis No. 30', 'IBU', 0, 'Dirawat', 'Baru', 'Ranap',
  'UMU', 10, 'Th', 'Belum Bayar', 'Baru'
);

INSERT INTO diagnosa_pasien (no_rawat, kd_penyakit, status, prioritas, status_penyakit)
VALUES
(@adult_no_rawat, 'J18.9', 'Ranap', 1, 'Baru'),
(@child_no_rawat, 'J18.9', 'Ranap', 1, 'Baru');

INSERT INTO mlite_clinical_pathway
(
  kode_cp, nama_cp, jenis_layanan, target_los, target_tarif, confidence_score,
  evidence_note, guideline_note, aktif, created_at, updated_at
)
VALUES
(
  @adult_cp_code, 'Clinical Pathway Pneumonia Dewasa Ranap', 'Ranap', 4, 3500000, 88.40,
  'Seeder dummy ranap dewasa dengan data tindakan, lab, radiologi dan resep.',
  'Hidrasi, antibiotik, monitoring demam, evaluasi toleransi oral dan edukasi pulang.',
  'Ya', NOW(), NOW()
),
(
  @child_cp_code, 'Clinical Pathway Pneumonia Anak Ranap', 'Ranap', 4, 3000000, 86.75,
  'Seeder dummy ranap anak dengan data tindakan, lab, radiologi dan resep.',
  'Antibiotik sesuai berat badan, hidrasi, monitoring intake output, edukasi orang tua.',
  'Ya', NOW(), NOW()
);

SET @adult_cp_id = (
  SELECT id
  FROM mlite_clinical_pathway
  WHERE kode_cp = @adult_cp_code
  LIMIT 1
);

SET @child_cp_id = (
  SELECT id
  FROM mlite_clinical_pathway
  WHERE kode_cp = @child_cp_code
  LIMIT 1
);

INSERT INTO mlite_clinical_pathway_diagnosis (clinical_pathway_id, kd_penyakit, prioritas, tipe)
VALUES
(@adult_cp_id, 'J18.9', 1, 'Utama'),
(@child_cp_id, 'J18.9', 1, 'Utama');

INSERT INTO mlite_clinical_pathway_day (clinical_pathway_id, hari_ke, label_hari, tujuan_harian)
VALUES
(@adult_cp_id, 1, 'Hari ke-1', 'Assessment awal, diagnosis, terapi awal, pemeriksaan penunjang'),
(@adult_cp_id, 2, 'Hari ke-2', 'Monitoring respon terapi dan stabilisasi klinis'),
(@adult_cp_id, 3, 'Hari ke-3', 'Evaluasi kemajuan dan transisi terapi'),
(@adult_cp_id, 4, 'Hari ke-4', 'Evaluasi kriteria pulang dan edukasi'),
(@child_cp_id, 1, 'Hari ke-1', 'Assessment anak, rehidrasi, antibiotik awal dan pemeriksaan penunjang'),
(@child_cp_id, 2, 'Hari ke-2', 'Monitoring demam, intake output dan nutrisi'),
(@child_cp_id, 3, 'Hari ke-3', 'Evaluasi klinis anak dan edukasi keluarga'),
(@child_cp_id, 4, 'Hari ke-4', 'Persiapan pulang dan edukasi orang tua');

INSERT INTO mlite_clinical_pathway_activity
(clinical_pathway_day_id, kategori, sumber_tabel, item_kode, item_nama, evidence_frequency, evidence_percentage, evidence_status, wajib, urutan)
VALUES
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 1), 'Assessment', 'manual', '', 'Assessment awal dan penetapan diagnosis kerja', 95, 100.00, 'Wajib', 'Ya', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 1), 'Laboratorium', 'periksa_lab', 'LABPN001', 'Darah Rutin Pneumonia', 92, 94.00, 'Wajib', 'Ya', 20),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 1), 'Laboratorium', 'periksa_lab', 'LABPN002', 'C-Reactive Protein', 78, 76.00, 'Direkomendasikan', 'Tidak', 30),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 1), 'Tindakan', 'rawat_inap_pr', 'RI001', 'Pasang Infus', 90, 91.00, 'Wajib', 'Ya', 40),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 1), 'Obat', 'resep_obat', @kode_obat_sefalosporin, 'Ceftriaxone injeksi', 88, 89.00, 'Wajib', 'Ya', 50),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 1), 'Monitoring', 'manual', '', 'Monitoring suhu dan tanda vital tiap shift', 100, 100.00, 'Wajib', 'Ya', 60),

((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 2), 'Radiologi', 'periksa_radiologi', 'RADPN001', 'Foto Thorax PA/AP', 54, 52.00, 'Direkomendasikan', 'Tidak', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 2), 'Obat', 'resep_obat', @kode_obat_paracetamol, 'Paracetamol 500 mg', 84, 86.00, 'Wajib', 'Ya', 20),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 2), 'Nutrisi', 'manual', '', 'Diet lunak tinggi kalori tinggi protein', 70, 72.00, 'Direkomendasikan', 'Tidak', 30),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 2), 'Monitoring', 'manual', '', 'Evaluasi respon terapi dan hemodinamik', 96, 98.00, 'Wajib', 'Ya', 40),

((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 3), 'Laboratorium', 'periksa_lab', 'LABPN001', 'Evaluasi darah rutin ulang bila perlu', 44, 38.00, 'Opsional', 'Tidak', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 3), 'Obat', 'manual', '', 'Transisi antibiotik oral bila bebas demam', 64, 61.00, 'Direkomendasikan', 'Tidak', 20),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 3), 'Edukasi', 'manual', '', 'Edukasi higienitas makanan dan kepatuhan obat', 86, 84.00, 'Wajib', 'Ya', 30),

((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 4), 'Outcome', 'manual', '', 'Evaluasi kriteria pulang', 96, 98.00, 'Wajib', 'Ya', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @adult_cp_id AND hari_ke = 4), 'Edukasi', 'manual', '', 'Edukasi pulang dan jadwal kontrol', 90, 92.00, 'Wajib', 'Ya', 20);

INSERT INTO mlite_clinical_pathway_activity
(clinical_pathway_day_id, kategori, sumber_tabel, item_kode, item_nama, evidence_frequency, evidence_percentage, evidence_status, wajib, urutan)
VALUES
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 1), 'Assessment', 'manual', '', 'Assessment awal anak dan stratifikasi dehidrasi', 96, 100.00, 'Wajib', 'Ya', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 1), 'Laboratorium', 'periksa_lab', 'LABPN001', 'Darah Rutin Pneumonia', 88, 90.00, 'Wajib', 'Ya', 20),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 1), 'Tindakan', 'rawat_inap_pr', 'RI001', 'Pasang Infus', 94, 95.00, 'Wajib', 'Ya', 30),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 1), 'Obat', 'resep_obat', @kode_obat_sefalosporin, 'Ceftriaxone injeksi sesuai berat badan', 86, 88.00, 'Wajib', 'Ya', 40),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 1), 'Monitoring', 'manual', '', 'Monitoring suhu, nadi dan intake output', 100, 100.00, 'Wajib', 'Ya', 50),

((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 2), 'Laboratorium', 'periksa_lab', 'LABPN002', 'C-Reactive Protein', 72, 74.00, 'Direkomendasikan', 'Tidak', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 2), 'Nutrisi', 'manual', '', 'Diet lunak anak dan hidrasi adekuat', 82, 80.00, 'Wajib', 'Ya', 20),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 2), 'Monitoring', 'manual', '', 'Evaluasi demam dan toleransi makan minum', 98, 98.00, 'Wajib', 'Ya', 30),

((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 3), 'Radiologi', 'periksa_radiologi', 'RADPN001', 'Foto Thorax PA/AP', 34, 30.00, 'Opsional', 'Tidak', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 3), 'Obat', 'resep_obat', @kode_obat_paracetamol, 'Paracetamol sirup atau tablet sesuai kebutuhan', 84, 85.00, 'Wajib', 'Ya', 20),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 3), 'Edukasi', 'manual', '', 'Edukasi orang tua terkait obat dan kebersihan makanan', 88, 86.00, 'Wajib', 'Ya', 30),

((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 4), 'Outcome', 'manual', '', 'Evaluasi kondisi stabil untuk pulang', 96, 97.00, 'Wajib', 'Ya', 10),
((SELECT id FROM mlite_clinical_pathway_day WHERE clinical_pathway_id = @child_cp_id AND hari_ke = 4), 'Edukasi', 'manual', '', 'Edukasi kontrol dan tanda bahaya pada anak', 92, 94.00, 'Wajib', 'Ya', 20);

UPDATE mlite_clinical_pathway_activity a
INNER JOIN mlite_clinical_pathway_day d ON d.id = a.clinical_pathway_day_id
SET a.uraian_kegiatan = CASE a.kategori
    WHEN 'Assessment' THEN 'Asesmen'
    WHEN 'Laboratorium' THEN 'Pemeriksaan Laboratorium'
    WHEN 'Radiologi' THEN 'Pemeriksaan Radiologi'
    WHEN 'Obat' THEN 'Terapi Obat'
    WHEN 'Tindakan' THEN 'Tindakan Keperawatan'
    WHEN 'Nutrisi' THEN 'Asuhan Gizi'
    WHEN 'Edukasi' THEN 'Edukasi'
    WHEN 'Monitoring' THEN 'Monitoring'
    WHEN 'Outcome' THEN 'Outcome'
    ELSE COALESCE(a.uraian_kegiatan, '')
END,
a.keterangan = COALESCE(NULLIF(a.keterangan, ''), '-')
WHERE d.clinical_pathway_id IN (@adult_cp_id, @child_cp_id);

INSERT INTO rawat_inap_pr
(no_rawat, kd_jenis_prw, nip, tgl_perawatan, jam_rawat, material, bhp, tarif_tindakanpr, kso, menejemen, biaya_rawat)
VALUES
(@adult_no_rawat, 'RI001', @nip_perawat, '2026-06-13', '08:35:00', 0, 0, 25000, 0, 0, 25000),
(@child_no_rawat, 'RI001', @nip_perawat, '2026-06-13', '09:20:00', 0, 0, 25000, 0, 0, 25000);

INSERT INTO periksa_lab
(no_rawat, nip, kd_jenis_prw, tgl_periksa, jam, dokter_perujuk, bagian_rs, bhp, tarif_perujuk, tarif_tindakan_dokter, tarif_tindakan_petugas, kso, menejemen, biaya, kd_dokter, status, kategori)
VALUES
(@adult_no_rawat, @nip_perawat, 'LABPN001', '2026-06-13', '09:10:00', @kd_dokter, 0, 0, 0, 85000, 0, 0, 0, 85000, @kd_dokter, 'Ranap', 'PK'),
(@adult_no_rawat, @nip_perawat, 'LABPN002', '2026-06-13', '09:20:00', @kd_dokter, 0, 0, 0, 125000, 0, 0, 0, 125000, @kd_dokter, 'Ranap', 'PK'),
(@child_no_rawat, @nip_perawat, 'LABPN001', '2026-06-13', '10:00:00', @kd_dokter, 0, 0, 0, 85000, 0, 0, 0, 85000, @kd_dokter, 'Ranap', 'PK'),
(@child_no_rawat, @nip_perawat, 'LABPN002', '2026-06-13', '08:45:00', @kd_dokter, 0, 0, 0, 125000, 0, 0, 0, 125000, @kd_dokter, 'Ranap', 'PK');

INSERT INTO periksa_radiologi
(no_rawat, nip, kd_jenis_prw, tgl_periksa, jam, dokter_perujuk, bagian_rs, bhp, tarif_perujuk, tarif_tindakan_dokter, tarif_tindakan_petugas, kso, menejemen, biaya, kd_dokter, status, proyeksi, kV, mAS, FFD, BSF, inak, jml_penyinaran, dosis)
VALUES
(@adult_no_rawat, @nip_perawat, 'RADPN001', '2026-06-13', '10:15:00', @kd_dokter, 0, 0, 0, 150000, 0, 0, 0, 150000, @kd_dokter, 'Ranap', 'AP', '55', '8', '100', '0', '0', '1', '0.2'),
(@child_no_rawat, @nip_perawat, 'RADPN001', '2026-06-14', '09:40:00', @kd_dokter, 0, 0, 0, 150000, 0, 0, 0, 150000, @kd_dokter, 'Ranap', 'AP', '50', '6', '90', '0', '0', '1', '0.1');

INSERT INTO resep_obat
(no_resep, tgl_perawatan, jam, no_rawat, kd_dokter, tgl_peresepan, jam_peresepan, status, tgl_penyerahan, jam_penyerahan)
VALUES
(@adult_recipe, '2026-06-13', '08:45:00', @adult_no_rawat, @kd_dokter, '2026-06-13', '08:45:00', 'ranap', '2026-06-13', '10:00:00'),
(@child_recipe, '2026-06-13', '09:30:00', @child_no_rawat, @kd_dokter, '2026-06-13', '09:30:00', 'ranap', '2026-06-13', '10:30:00');

INSERT INTO resep_dokter (no_resep, kode_brng, jml, aturan_pakai)
VALUES
(@adult_recipe, @kode_obat_sefalosporin, 3, '1 x 1 injeksi'),
(@adult_recipe, @kode_obat_paracetamol, 10, '3 x 1 tablet bila demam'),
(@child_recipe, @kode_obat_sefalosporin, 2, '1 x 1 injeksi sesuai berat badan'),
(@child_recipe, @kode_obat_paracetamol, 6, '3 x 1 bila demam');

INSERT INTO mlite_clinical_pathway_patient
(no_rawat, clinical_pathway_id, kd_penyakit, tanggal_mulai, tanggal_selesai, status, auto_generated)
VALUES
(@adult_no_rawat, @adult_cp_id, 'J18.9', @adult_start, NULL, 'Aktif', 'Ya'),
(@child_no_rawat, @child_cp_id, 'J18.9', @child_start, NULL, 'Aktif', 'Ya');

SET @adult_cpp_id = (
  SELECT id
  FROM mlite_clinical_pathway_patient
  WHERE no_rawat = @adult_no_rawat
  LIMIT 1
);

SET @child_cpp_id = (
  SELECT id
  FROM mlite_clinical_pathway_patient
  WHERE no_rawat = @child_no_rawat
  LIMIT 1
);

INSERT INTO mlite_clinical_pathway_execution
(
  clinical_pathway_patient_id, clinical_pathway_activity_id, hari_ke, tanggal_rencana,
  tanggal_realisasi, status, sumber_data, sumber_referensi, petugas, catatan
)
SELECT
  @adult_cpp_id,
  a.id,
  d.hari_ke,
  DATE_ADD(DATE(@adult_start), INTERVAL d.hari_ke - 1 DAY),
  CASE
    WHEN d.hari_ke = 1 THEN DATE_ADD(@adult_start, INTERVAL a.urutan MINUTE)
    WHEN d.hari_ke = 2 AND a.urutan IN (10,20,40) THEN DATE_ADD(DATE_ADD(@adult_start, INTERVAL 1 DAY), INTERVAL a.urutan MINUTE)
    WHEN d.hari_ke = 3 AND a.urutan IN (30) THEN DATE_ADD(DATE_ADD(@adult_start, INTERVAL 2 DAY), INTERVAL a.urutan MINUTE)
    ELSE NULL
  END,
  CASE
    WHEN d.hari_ke = 1 THEN 'Completed'
    WHEN d.hari_ke = 2 AND a.urutan IN (10,20,40) THEN 'Completed'
    WHEN d.hari_ke = 2 AND a.urutan = 30 THEN 'Missed'
    WHEN d.hari_ke = 3 AND a.urutan = 10 THEN 'Missed'
    WHEN d.hari_ke = 3 AND a.urutan = 20 THEN 'Missed'
    WHEN d.hari_ke = 3 AND a.urutan = 30 THEN 'Completed'
    ELSE 'Planned'
  END,
  CASE
    WHEN a.kategori = 'Laboratorium' THEN 'periksa_lab'
    WHEN a.kategori = 'Radiologi' THEN 'periksa_radiologi'
    WHEN a.kategori = 'Obat' THEN 'resep_obat'
    WHEN a.kategori = 'Tindakan' THEN 'rawat_inap_pr'
    ELSE 'manual'
  END,
  CASE
    WHEN a.kategori = 'Laboratorium' THEN CONCAT(a.item_kode, '|', @adult_no_rawat)
    WHEN a.kategori = 'Radiologi' THEN CONCAT(a.item_kode, '|', @adult_no_rawat)
    WHEN a.kategori = 'Obat' THEN CONCAT(@adult_recipe, '|', a.item_kode)
    WHEN a.kategori = 'Tindakan' THEN CONCAT('RI001|', @adult_no_rawat)
    ELSE CONCAT('manual|', a.id)
  END,
  @nip_perawat,
  CASE
    WHEN d.hari_ke = 2 AND a.urutan = 30 THEN 'Diet lunak belum optimal karena mual.'
    WHEN d.hari_ke = 3 AND a.urutan = 10 THEN 'Lab ulang tidak dikerjakan karena klinis membaik.'
    WHEN d.hari_ke = 3 AND a.urutan = 20 THEN 'Transisi antibiotik oral ditunda karena demam fluktuatif.'
    WHEN d.hari_ke = 4 THEN 'Menunggu visit dokter untuk keputusan pulang.'
    ELSE 'Seeder dummy dewasa ranap.'
  END
FROM mlite_clinical_pathway_activity a
INNER JOIN mlite_clinical_pathway_day d ON d.id = a.clinical_pathway_day_id
WHERE d.clinical_pathway_id = @adult_cp_id
ORDER BY d.hari_ke, a.urutan;

INSERT INTO mlite_clinical_pathway_execution
(
  clinical_pathway_patient_id, clinical_pathway_activity_id, hari_ke, tanggal_rencana,
  tanggal_realisasi, status, sumber_data, sumber_referensi, petugas, catatan
)
SELECT
  @child_cpp_id,
  a.id,
  d.hari_ke,
  DATE_ADD(DATE(@child_start), INTERVAL d.hari_ke - 1 DAY),
  CASE
    WHEN d.hari_ke = 1 THEN DATE_ADD(@child_start, INTERVAL a.urutan MINUTE)
    WHEN d.hari_ke = 2 THEN DATE_ADD(DATE_ADD(@child_start, INTERVAL 1 DAY), INTERVAL a.urutan MINUTE)
    WHEN d.hari_ke = 3 AND a.urutan IN (20,30) THEN DATE_ADD(DATE_ADD(@child_start, INTERVAL 2 DAY), INTERVAL a.urutan MINUTE)
    WHEN d.hari_ke = 4 AND a.urutan = 10 THEN DATE_ADD(DATE_ADD(@child_start, INTERVAL 3 DAY), INTERVAL a.urutan MINUTE)
    ELSE NULL
  END,
  CASE
    WHEN d.hari_ke = 1 THEN 'Completed'
    WHEN d.hari_ke = 2 THEN 'Completed'
    WHEN d.hari_ke = 3 AND a.urutan = 10 THEN 'Missed'
    WHEN d.hari_ke = 3 AND a.urutan IN (20,30) THEN 'Completed'
    WHEN d.hari_ke = 4 AND a.urutan = 10 THEN 'Completed'
    ELSE 'Planned'
  END,
  CASE
    WHEN a.kategori = 'Laboratorium' THEN 'periksa_lab'
    WHEN a.kategori = 'Radiologi' THEN 'periksa_radiologi'
    WHEN a.kategori = 'Obat' THEN 'resep_obat'
    WHEN a.kategori = 'Tindakan' THEN 'rawat_inap_pr'
    ELSE 'manual'
  END,
  CASE
    WHEN a.kategori = 'Laboratorium' THEN CONCAT(a.item_kode, '|', @child_no_rawat)
    WHEN a.kategori = 'Radiologi' THEN CONCAT(a.item_kode, '|', @child_no_rawat)
    WHEN a.kategori = 'Obat' THEN CONCAT(@child_recipe, '|', a.item_kode)
    WHEN a.kategori = 'Tindakan' THEN CONCAT('RI001|', @child_no_rawat)
    ELSE CONCAT('manual|', a.id)
  END,
  @nip_perawat,
  CASE
    WHEN d.hari_ke = 3 AND a.urutan = 10 THEN 'Radiologi tidak dikerjakan karena anak stabil klinis.'
    WHEN d.hari_ke = 4 AND a.urutan = 20 THEN 'Edukasi pulang dijadwalkan saat pengantaran obat.'
    ELSE 'Seeder dummy anak ranap.'
  END
FROM mlite_clinical_pathway_activity a
INNER JOIN mlite_clinical_pathway_day d ON d.id = a.clinical_pathway_day_id
WHERE d.clinical_pathway_id = @child_cp_id
ORDER BY d.hari_ke, a.urutan;

INSERT INTO mlite_clinical_pathway_compliance
(
  clinical_pathway_patient_id, planned_activity, completed_activity, missed_activity,
  compliance_percentage, kategori_kepatuhan, last_calculated_at
)
VALUES
(@adult_cpp_id, 15, 10, 3, 66.67, 'Kurang Patuh', NOW()),
(@child_cpp_id, 13, 11, 1, 84.62, 'Patuh', NOW());

INSERT INTO mlite_clinical_pathway_variance
(
  clinical_pathway_patient_id, clinical_pathway_execution_id, kategori_variance,
  penyebab, deskripsi, severity, tanggal_variance, status_tindak_lanjut
)
VALUES
(
  @adult_cpp_id, NULL, 'Nutrisi',
  'Asupan oral belum adekuat',
  'Diet lunak tinggi kalori tinggi protein hari ke-2 belum terpenuhi optimal.',
  'Sedang', NOW(), 'Open'
),
(
  @adult_cpp_id, NULL, 'Lab',
  'Pemeriksaan evaluasi tidak dilakukan',
  'Evaluasi darah rutin ulang hari ke-3 tidak dilakukan karena perbaikan klinis parsial.',
  'Rendah', NOW(), 'Open'
),
(
  @adult_cpp_id, NULL, 'Obat',
  'Transisi terapi tertunda',
  'Transisi antibiotik oral hari ke-3 ditunda karena pasien masih demam fluktuatif.',
  'Sedang', NOW(), 'Open'
),
(
  @child_cpp_id, NULL, 'Radiologi',
  'Pemeriksaan opsional tidak dilakukan',
  'Foto Thorax PA/AP hari ke-3 tidak dilakukan karena kondisi anak stabil tanpa sesak.',
  'Rendah', NOW(), 'Open'
);

INSERT INTO mlite_clinical_pathway_audit
(clinical_pathway_patient_id, clinical_pathway_id, aksi, referensi, deskripsi, user_aksi, created_at)
VALUES
(@adult_cpp_id, @adult_cp_id, 'seed_generate_cp', @adult_no_rawat, 'Seeder dummy CP pneumonia ranap dewasa dengan data penunjang lengkap.', 'system', NOW()),
(@child_cpp_id, @child_cp_id, 'seed_generate_cp', @child_no_rawat, 'Seeder dummy CP pneumonia ranap anak dengan data penunjang lengkap.', 'system', NOW());

COMMIT;
