<?php

namespace Plugins\Satu_Sehat\Services;

use Plugins\Satu_Sehat\Resources\AllergyIntoleranceBuilder;
use Plugins\Satu_Sehat\Resources\CarePlanRanapBuilder;
use Plugins\Satu_Sehat\Resources\ClinicalImpressionRanapBuilder;
use Plugins\Satu_Sehat\Resources\ConditionRanapBuilder;
use Plugins\Satu_Sehat\Resources\DiagnosticReportRanapBuilder;
use Plugins\Satu_Sehat\Resources\EncounterRanapBuilder;
use Plugins\Satu_Sehat\Resources\FamilyMemberHistoryBuilder;
use Plugins\Satu_Sehat\Resources\GoalBuilder;
use Plugins\Satu_Sehat\Resources\MedicationAdministrationBuilder;
use Plugins\Satu_Sehat\Resources\MedicationBuilder;
use Plugins\Satu_Sehat\Resources\MedicationDispenseBuilder;
use Plugins\Satu_Sehat\Resources\MedicationRequestBuilder;
use Plugins\Satu_Sehat\Resources\MedicationStatementBuilder;
use Plugins\Satu_Sehat\Resources\NutritionOrderBuilder;
use Plugins\Satu_Sehat\Resources\ObservationBuilder;
use Plugins\Satu_Sehat\Resources\ObservationRanapBuilder;
use Plugins\Satu_Sehat\Resources\ProcedureBuilder;
use Plugins\Satu_Sehat\Resources\QuestionnaireResponseBuilder;
use Plugins\Satu_Sehat\Resources\RiskAssessmentBuilder;
use Plugins\Satu_Sehat\Resources\ServiceRequestBuilder;
use Plugins\Satu_Sehat\Resources\SpecimenBuilder;

/**
 * Orkestrator sub modul ERM Rawat Inap.
 *
 * Mengembangkan SatuSehatErmRalanService dengan sumber data rawat inap
 * (kamar_inap, pemeriksaan_ranap, rawat_inap_dr/pr, resep status ranap) dan
 * pipeline resource FHIR mengikuti template Bundle Transaction Rawat Inap
 * Kemenkes (Encounter IMP, Condition chief-complaint/previous-condition/
 * encounter-diagnosis, FamilyMemberHistory, AllergyIntolerance,
 * MedicationStatement, Observation vital/H2T/fungsional/lab/rad, ClinicalImpression,
 * Goal, CarePlan inpatient & discharge, Procedure, ServiceRequest, Specimen,
 * DiagnosticReport, RiskAssessment, Medication* + QuestionnaireResponse Q0007,
 * NutritionOrder, serta Composition Hospital Discharge summary).
 */
class SatuSehatErmRanapService extends SatuSehatErmRalanService
{
  // ==================================================================
  // 1. Data klinis rawat inap
  // ==================================================================

  /** {@inheritdoc} Selalu 'ranap'. */
  protected function detectErmType($reg = null, $override = null)
  {
    return 'ranap';
  }

  /** {@inheritdoc} 'ERM Rawat Inap'. */
  public function ermLabel()
  {
    return 'ERM Rawat Inap';
  }

  /**
   * Ambil seluruh data ERM Rawat Inap sebuah kunjungan.
   *
   * Kunci 'diagnosa'/'tindakan'/'laboratorium'/'radiologi'/'resep' berupa baris
   * tabel siap render, kunci '*_items' berstruktur untuk resource builder.
   */
  public function getEmr($no_rawat, $ermType = null)
  {
    $no_rawat = $this->normalizeNoRawat($no_rawat);
    if (isset($this->pipelineCache['emr'][$no_rawat])) {
      return $this->pipelineCache['emr'][$no_rawat];
    }

    $result = [
      'no_rawat' => $no_rawat,
      'erm_type' => 'ranap',
      'reg_periksa' => null,
      'pasien' => null,
      'dokter' => null,
      'poliklinik' => null,
      'penjab' => null,
      'kamar_inap' => [],
      'pemeriksaan' => [],
      'diagnosa' => [],
      'tindakan' => [],
      'laboratorium' => [],
      'radiologi' => [],
      'resep' => [],
      'diagnosa_items' => [],
      'tindakan_items' => [],
      'lab_items' => [],
      'rad_items' => [],
      'resep_items' => [],
      'pemberian_items' => [],
      'riwayat_pengobatan_items' => [],
      'riwayat_penyakit_items' => [],
      'riwayat_keluarga_items' => [],
      'diet_items' => [],
      'tujuan_perawatan_items' => [],
      'penilaian_risiko_items' => [],
      'permintaan_lab_items' => [],
      'permintaan_rad_items' => [],
      'skor_adl' => '',
      'kriteria_pemulangan' => '',
      'rencana_pulang' => '',
      'mapping' => ['patient_id' => '', 'practitioner_id' => '', 'location_id' => '', 'organization_id' => ''],
    ];

    if ($no_rawat === '') {
      return $result;
    }

    $reg = $this->fetchOne('SELECT * FROM reg_periksa WHERE no_rawat = ? LIMIT 1', [$no_rawat]);
    if (!$reg) {
      return $result;
    }
    $result['reg_periksa'] = $reg;

    // ---- Identitas (BAB 1) ----
    $pasien = $this->fetchOne('SELECT * FROM pasien WHERE no_rkm_medis = ? LIMIT 1', [(string) ($reg['no_rkm_medis'] ?? '')]);
    if (is_array($pasien)) {
      $kel = $this->fetchOne('SELECT nm_kel FROM kelurahan WHERE kd_kel = ? LIMIT 1', [(string) ($pasien['kd_kel'] ?? '')]);
      $kec = $this->fetchOne('SELECT nm_kec FROM kecamatan WHERE kd_kec = ? LIMIT 1', [(string) ($pasien['kd_kec'] ?? '')]);
      $kab = $this->fetchOne('SELECT nm_kab FROM kabupaten WHERE kd_kab = ? LIMIT 1', [(string) ($pasien['kd_kab'] ?? '')]);
      $prop = $this->fetchOne('SELECT nm_prop FROM propinsi WHERE kd_prop = ? LIMIT 1', [(string) ($pasien['kd_prop'] ?? '')]);
      $pasien['kelurahan'] = (string) ($kel['nm_kel'] ?? '');
      $pasien['kecamatan'] = (string) ($kec['nm_kec'] ?? '');
      $pasien['kabupaten'] = (string) ($kab['nm_kab'] ?? '');
      $pasien['propinsi'] = (string) ($prop['nm_prop'] ?? '');
      $pasien['nama_ibu'] = (string) ($pasien['nm_ibu'] ?? '');
    }
    $result['pasien'] = $pasien;

    $result['dokter'] = $this->fetchOne('SELECT * FROM dokter WHERE kd_dokter = ? LIMIT 1', [(string) ($reg['kd_dokter'] ?? '')]);
    $result['penjab'] = $this->fetchOne('SELECT * FROM penjab WHERE kd_pj = ? LIMIT 1', [(string) ($reg['kd_pj'] ?? '')]);

    // ---- Kamar rawat inap (BAB 2) ----
    $kamarRows = $this->fetchAll(
      'SELECT * FROM kamar_inap WHERE no_rawat = ? ORDER BY tgl_masuk, jam_masuk',
      [$no_rawat]
    );
    if (!empty($kamarRows)) {
      $pertama = (array) $kamarRows[0];
      $terakhir = (array) $kamarRows[count($kamarRows) - 1];
      $lama = 0.0;
      foreach ($kamarRows as $kamarRow) {
        $lama += (float) (($kamarRow['lama'] ?? 0));
      }
      $kdKamar = (string) ($terakhir['kd_kamar'] ?? ($pertama['kd_kamar'] ?? ''));
      $kamar = $kdKamar !== '' ? $this->fetchOne('SELECT * FROM kamar WHERE kd_kamar = ? LIMIT 1', [$kdKamar]) : null;
      $kdBangsal = (string) (($kamar['kd_bangsal'] ?? ''));
      $bangsal = $kdBangsal !== '' ? $this->fetchOne('SELECT * FROM bangsal WHERE kd_bangsal = ? LIMIT 1', [$kdBangsal]) : null;
      $result['kamar_inap'] = [
        'kd_kamar' => $kdKamar,
        'kd_bangsal' => $kdBangsal,
        'nm_bangsal' => (string) ($bangsal['nm_bangsal'] ?? ''),
        'kelas' => trim((string) ($kamar['kelas'] ?? '')),
        'tgl_masuk' => (string) ($pertama['tgl_masuk'] ?? ''),
        'jam_masuk' => (string) ($pertama['jam_masuk'] ?? ''),
        'tgl_keluar' => trim((string) ($terakhir['tgl_keluar'] ?? '')),
        'jam_keluar' => trim((string) ($terakhir['jam_keluar'] ?? '')),
        'lama' => $lama > 0 ? $lama : (float) ($terakhir['lama'] ?? 0),
        'stts_pulang' => trim((string) ($terakhir['stts_pulang'] ?? '')),
      ];
      $result['poliklinik'] = [
        'kd_poli' => $kdKamar,
        'nm_poli' => trim('Kamar ' . $kdKamar . ', ' . ($bangsal['nm_bangsal'] ?? '')),
      ];
    } else {
      $result['poliklinik'] = $this->fetchOne('SELECT * FROM poliklinik WHERE kd_poli = ? LIMIT 1', [(string) ($reg['kd_poli'] ?? '')]);
    }
    $inap = (array) $result['kamar_inap'];
    if (isset($inap['tgl_keluar']) && (strpos((string) $inap['tgl_keluar'], '0000-00-00') === 0 || $inap['tgl_keluar'] === '')) {
      $inap['tgl_keluar'] = '';
      $inap['jam_keluar'] = '';
      $result['kamar_inap'] = $inap;
    }

    // ---- Anamnesis & tanda vital (BAB 3-4): baris pertama (masuk) + terakhir (evaluasi) ----
    $awal = (array) ($this->fetchOne(
      'SELECT * FROM pemeriksaan_ranap WHERE no_rawat = ? ORDER BY tgl_perawatan, jam_rawat LIMIT 1',
      [$no_rawat]
    ) ?: []);
    $akhir = (array) ($this->fetchOne(
      'SELECT * FROM pemeriksaan_ranap WHERE no_rawat = ? ORDER BY tgl_perawatan DESC, jam_rawat DESC LIMIT 1',
      [$no_rawat]
    ) ?: []);
    $result['pemeriksaan'] = [
      'tgl_perawatan' => (string) ($awal['tgl_perawatan'] ?? ($akhir['tgl_perawatan'] ?? '')),
      'jam_rawat' => (string) ($awal['jam_rawat'] ?? ($akhir['jam_rawat'] ?? '')),
      'suhu' => trim((string) ($awal['suhu_tubuh'] ?? '')),
      'tensi' => trim((string) ($awal['tensi'] ?? '')),
      'nadi' => trim((string) ($awal['nadi'] ?? '')),
      'respirasi' => trim((string) ($awal['respirasi'] ?? '')),
      'tinggi' => trim((string) ($awal['tinggi'] ?? '')),
      'berat' => trim((string) ($awal['berat'] ?? '')),
      'spo2' => trim((string) ($awal['spo2'] ?? '')),
      'gcs' => trim((string) ($awal['gcs'] ?? '')),
      'kesadaran' => trim((string) ($awal['kesadaran'] ?? '')),
      'lingkar_perut' => trim((string) ($awal['lingkar_perut'] ?? '')),
      'keluhan' => trim((string) ($awal['keluhan'] ?? '')),
      'pemeriksaan' => trim((string) ($awal['pemeriksaan'] ?? '')),
      'alergi' => trim((string) ($awal['alergi'] ?? ($akhir['alergi'] ?? ''))),
      'penilaian' => trim((string) ($akhir['penilaian'] ?? '')),
      'rtl' => trim((string) ($akhir['rtl'] ?? '')),
      'instruksi' => trim((string) ($akhir['instruksi'] ?? '')),
      'evaluasi' => trim((string) ($akhir['evaluasi'] ?? '')),
    ];
    $p = (array) $result['pemeriksaan'];

    // ---- Diagnosis (BAB 12) ----
    $diagnosaRows = $this->fetchAll(
      'SELECT d.kd_penyakit, p.nm_penyakit, d.prioritas, d.status, d.status_penyakit
       FROM diagnosa_pasien d LEFT JOIN penyakit p ON p.kd_penyakit = d.kd_penyakit
       WHERE d.no_rawat = ? ORDER BY d.prioritas',
      [$no_rawat]
    );
    $ranapRows = array_values(array_filter($diagnosaRows, function ($row) {
      return stripos((string) ($row['status'] ?? ''), 'ranap') !== false;
    }));
    if (!empty($ranapRows)) {
      $diagnosaRows = $ranapRows;
    }
    foreach ($diagnosaRows as $row) {
      $result['diagnosa_items'][] = [
        'kode' => (string) ($row['kd_penyakit'] ?? ''),
        'nama' => (string) ($row['nm_penyakit'] ?? ''),
        'prioritas' => (int) ($row['prioritas'] ?? 0),
        'status' => (string) ($row['status'] ?? ''),
        'status_penyakit' => (string) ($row['status_penyakit'] ?? ''),
      ];
      $result['diagnosa'][] = [
        (string) ($row['prioritas'] ?? ''),
        (string) ($row['kd_penyakit'] ?? ''),
        (string) ($row['nm_penyakit'] ?? ''),
        (string) (($row['status_penyakit'] ?? '') !== '' ? $row['status_penyakit'] : ($row['status'] ?? '')),
      ];
    }

    // ---- Tindakan / prosedur (BAB 14): ICD-9 + rawat_inap_dr/pr ----
    foreach ($this->fetchAll(
      'SELECT t.kode, i.deskripsi_panjang AS nama, t.prioritas
       FROM prosedur_pasien t LEFT JOIN icd9 i ON i.kode = t.kode
       WHERE t.no_rawat = ? ORDER BY t.prioritas',
      [$no_rawat]
    ) as $row) {
      $result['tindakan_items'][] = [
        'sumber' => 'icd9',
        'kode' => (string) ($row['kode'] ?? ''),
        'nama' => (string) ($row['nama'] ?? ''),
        'prioritas' => (int) ($row['prioritas'] ?? 0),
        'tgl' => (string) ($p['tgl_perawatan'] ?? ''),
        'jam' => (string) ($p['jam_rawat'] ?? ''),
      ];
    }
    foreach (['rawat_inap_dr', 'rawat_inap_pr'] as $table) {
      foreach ($this->fetchAll(
        "SELECT r.kd_jenis_prw AS kode, j.nm_perawatan AS nama, r.kd_dokter, r.tgl_perawatan AS tgl, r.jam_rawat AS jam
         FROM {$table} r LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
         WHERE r.no_rawat = ? ORDER BY r.tgl_perawatan, r.jam_rawat",
        [$no_rawat]
      ) as $row) {
        $result['tindakan_items'][] = [
          'sumber' => 'tindakan',
          'kode' => (string) ($row['kode'] ?? ''),
          'nama' => (string) ($row['nama'] ?? ''),
          'kd_dokter' => (string) ($row['kd_dokter'] ?? ''),
          'tgl' => (string) ($row['tgl'] ?? ''),
          'jam' => (string) ($row['jam'] ?? ''),
          'map' => $this->kptlMap((string) ($row['kode'] ?? '')),
        ];
      }
    }
    foreach ($result['tindakan_items'] as $item) {
      $result['tindakan'][] = [
        strtoupper((string) ($item['sumber'] ?? '')),
        (string) ($item['kode'] ?? ''),
        (string) ($item['nama'] ?? ''),
        trim((string) (($item['tgl'] ?? '') . ' ' . ($item['jam'] ?? ''))),
      ];
    }

    // ---- Laboratorium (BAB 10a) ----
    foreach ($this->fetchAll(
      'SELECT l.*, j.nm_perawatan FROM periksa_lab l
       LEFT JOIN jns_perawatan_lab j ON j.kd_jenis_prw = l.kd_jenis_prw
       WHERE l.no_rawat = ? ORDER BY l.tgl_periksa, l.jam',
      [$no_rawat]
    ) as $row) {
      $group = [
        'kd_jenis_prw' => (string) ($row['kd_jenis_prw'] ?? ''),
        'nm_perawatan' => (string) ($row['nm_perawatan'] ?? ''),
        'tgl_periksa' => (string) ($row['tgl_periksa'] ?? ''),
        'jam' => (string) ($row['jam'] ?? ''),
        'kd_dokter' => (string) ($row['kd_dokter'] ?? ''),
        'dokter_perujuk' => (string) ($row['dokter_perujuk'] ?? ''),
        'map' => $this->mapping()->labTest((string) ($row['kd_jenis_prw'] ?? '')),
        'detail' => [],
      ];
      foreach ($this->fetchAll(
        'SELECT d.id_template, d.nilai, d.nilai_rujukan, d.keterangan, t.Pemeriksaan AS nama, t.satuan
         FROM detail_periksa_lab d LEFT JOIN template_laboratorium t ON t.id_template = d.id_template
         WHERE d.no_rawat = ? AND d.kd_jenis_prw = ? AND d.tgl_periksa = ? AND d.jam = ? ORDER BY t.urut',
        [$no_rawat, $group['kd_jenis_prw'], $group['tgl_periksa'], $group['jam']]
      ) as $detail) {
        $detailRow = [
          'id_template' => (string) ($detail['id_template'] ?? ''),
          'nama' => (string) ($detail['nama'] ?? ''),
          'satuan' => (string) ($detail['satuan'] ?? ''),
          'nilai' => trim((string) ($detail['nilai'] ?? '')),
          'nilai_rujukan' => trim((string) ($detail['nilai_rujukan'] ?? '')),
          'keterangan' => trim((string) ($detail['keterangan'] ?? '')),
          'map' => $this->mapping()->labTest($group['kd_jenis_prw'], (string) ($detail['id_template'] ?? '')),
        ];
        $group['detail'][] = $detailRow;
        $result['laboratorium'][] = [
          trim($group['tgl_periksa'] . ' ' . $group['jam']),
          trim($group['nm_perawatan'] . ' — ' . $detailRow['nama']),
          $detailRow['nilai'] !== '' ? $detailRow['nilai'] . ($detailRow['satuan'] !== '' ? ' ' . $detailRow['satuan'] : '') : '-',
          $detailRow['nilai_rujukan'],
        ];
      }
      $result['lab_items'][] = $group;
    }

    // ---- Radiologi (BAB 10b) ----
    foreach ($this->fetchAll(
      'SELECT r.*, j.nm_perawatan, h.hasil FROM periksa_radiologi r
       LEFT JOIN jns_perawatan_radiologi j ON j.kd_jenis_prw = r.kd_jenis_prw
       LEFT JOIN hasil_radiologi h ON h.no_rawat = r.no_rawat AND h.tgl_periksa = r.tgl_periksa AND h.jam = r.jam
       WHERE r.no_rawat = ? ORDER BY r.tgl_periksa, r.jam',
      [$no_rawat]
    ) as $row) {
      $item = [
        'kd_jenis_prw' => (string) ($row['kd_jenis_prw'] ?? ''),
        'nm_perawatan' => (string) ($row['nm_perawatan'] ?? ''),
        'tgl_periksa' => (string) ($row['tgl_periksa'] ?? ''),
        'jam' => (string) ($row['jam'] ?? ''),
        'kd_dokter' => (string) ($row['kd_dokter'] ?? ''),
        'dokter_perujuk' => (string) ($row['dokter_perujuk'] ?? ''),
        'proyeksi' => (string) ($row['proyeksi'] ?? ''),
        'hasil' => trim((string) ($row['hasil'] ?? '')),
        'map' => $this->mapping()->radiologyTest((string) ($row['kd_jenis_prw'] ?? '')),
      ];
      $result['rad_items'][] = $item;
      $result['radiologi'][] = [
        trim($item['tgl_periksa'] . ' ' . $item['jam']),
        $item['nm_perawatan'],
        $item['hasil'],
        $item['proyeksi'],
      ];
    }

    // ---- Peresepan obat rawat inap (BAB 15) ----
    foreach ($this->fetchAll(
      "SELECT d.kode_brng, d.jml, d.aturan_pakai, h.no_resep, h.tgl_peresepan, h.jam_peresepan,
              h.tgl_penyerahan, h.jam_penyerahan, h.kd_dokter, b.nama_brng, b.kode_sat
       FROM resep_dokter d
       INNER JOIN resep_obat h ON h.no_resep = d.no_resep
       LEFT JOIN databarang b ON b.kode_brng = d.kode_brng
       WHERE h.no_rawat = ? AND h.status = 'ranap'
       ORDER BY h.tgl_peresepan, h.jam_peresepan, h.no_resep",
      [$no_rawat]
    ) as $row) {
      $item = [
        'no_resep' => (string) ($row['no_resep'] ?? ''),
        'kode_brng' => (string) ($row['kode_brng'] ?? ''),
        'nama_brng' => (string) ($row['nama_brng'] ?? ''),
        'kode_sat' => (string) ($row['kode_sat'] ?? ''),
        'jml' => (float) ($row['jml'] ?? 0),
        'aturan_pakai' => trim((string) ($row['aturan_pakai'] ?? '')),
        'tgl_peresepan' => (string) ($row['tgl_peresepan'] ?? ''),
        'jam_peresepan' => (string) ($row['jam_peresepan'] ?? ''),
        'tgl_penyerahan' => (string) ($row['tgl_penyerahan'] ?? ''),
        'jam_penyerahan' => (string) ($row['jam_penyerahan'] ?? ''),
        'kd_dokter' => (string) ($row['kd_dokter'] ?? ''),
        'map' => $this->mapping()->drug((string) ($row['kode_brng'] ?? '')),
      ];
      $result['resep_items'][] = $item;
      $result['resep'][] = [
        $item['no_resep'],
        trim((string) ($item['map']['nama_kfa'] ?? '') ?: $item['nama_brng']),
        (string) $item['jml'] . ' ' . $item['kode_sat'],
        $item['aturan_pakai'],
      ];
    }

    // ---- Pemberian & pengeluaran obat (BAB 16-17) + riwayat pengobatan ----
    foreach ($this->fetchAll(
      'SELECT p.kode_brng, p.jml, p.tgl_perawatan, p.jam, p.no_batch, b.nama_brng, a.aturan
       FROM detail_pemberian_obat p
       LEFT JOIN databarang b ON b.kode_brng = p.kode_brng
       LEFT JOIN aturan_pakai a ON a.no_rawat = p.no_rawat AND a.kode_brng = p.kode_brng
          AND a.tgl_perawatan = p.tgl_perawatan AND a.jam = p.jam
       WHERE p.no_rawat = ? ORDER BY p.tgl_perawatan, p.jam',
      [$no_rawat]
    ) as $row) {
      $item = [
        'kode_brng' => (string) ($row['kode_brng'] ?? ''),
        'nama_brng' => (string) ($row['nama_brng'] ?? ''),
        'jml' => (float) ($row['jml'] ?? 0),
        'aturan' => trim((string) ($row['aturan'] ?? '')),
        'tgl_perawatan' => (string) ($row['tgl_perawatan'] ?? ''),
        'jam' => (string) ($row['jam'] ?? ''),
        'no_batch' => (string) ($row['no_batch'] ?? ''),
        'map' => $this->mapping()->drug((string) ($row['kode_brng'] ?? '')),
      ];
      $result['pemberian_items'][] = $item;
      $result['riwayat_pengobatan_items'][] = [
        'kode_brng' => $item['kode_brng'],
        'nama_brng' => $item['nama_brng'],
        'aturan' => $item['aturan'],
        'tgl' => $item['tgl_perawatan'],
        'jam' => $item['jam'],
        'map' => $item['map'],
      ];
    }

    // ---- Diet (BAB 18) dari instruksi yang memuat anjuran diet ----
    $instruksi = strtolower((string) ($p['instruksi'] ?? ''));
    if ($instruksi !== '' && strpos($instruksi, 'diet') !== false) {
      $result['diet_items'][] = [
        'type_text' => (string) ($p['instruksi'] ?? ''),
      ];
    }

    // ---- Permintaan penunjang (ServiceRequest) ----
    foreach ([
      'permintaan_lab_items' => ['permintaan_lab', 'permintaan_pemeriksaan_lab', 'jns_perawatan_lab', 'lab'],
      'permintaan_rad_items' => ['permintaan_radiologi', 'permintaan_pemeriksaan_radiologi', 'jns_perawatan_radiologi', 'rad'],
    ] as $target => [$header, $detail, $jns, $kind]) {
      foreach ($this->fetchAll(
        "SELECT * FROM {$header} WHERE no_rawat = ? ORDER BY tgl_permintaan, jam_permintaan",
        [$no_rawat]
      ) as $row) {
        $permintaan = [
          'noorder' => (string) ($row['noorder'] ?? ''),
          'tgl_permintaan' => (string) ($row['tgl_permintaan'] ?? ''),
          'jam_permintaan' => (string) ($row['jam_permintaan'] ?? ''),
          'tgl_sampel' => (string) ($row['tgl_sampel'] ?? ''),
          'jam_sampel' => (string) ($row['jam_sampel'] ?? ''),
          'dokter_perujuk' => (string) ($row['dokter_perujuk'] ?? ''),
          'diagnosa_klinis' => trim((string) ($row['diagnosa_klinis'] ?? '')),
          'informasi_tambahan' => trim((string) ($row['informasi_tambahan'] ?? '')),
          'items' => [],
        ];
        foreach ($this->fetchAll(
          "SELECT m.kd_jenis_prw, j.nm_perawatan FROM {$detail} m
           LEFT JOIN {$jns} j ON j.kd_jenis_prw = m.kd_jenis_prw
           WHERE m.noorder = ?",
          [$permintaan['noorder']]
        ) as $item) {
          $kd = (string) ($item['kd_jenis_prw'] ?? '');
          $permintaan['items'][] = [
            'kd_jenis_prw' => $kd,
            'nama' => (string) ($item['nm_perawatan'] ?? ''),
            'map' => $kind === 'lab' ? $this->mapping()->labTest($kd) : $this->mapping()->radiologyTest($kd),
          ];
        }
        $result[$target][] = $permintaan;
      }
    }

    // ---- Pemetaan resource SATUSEHAT (lokasi kamar/bangsal) ----
    $visitMapping = $this->mapping()->getVisitMapping($no_rawat);
    $lokasiId = '';
    if (!empty($inap['kd_kamar'])) {
      $lokasiId = $this->mapping()->locationId((string) $inap['kd_kamar']);
    }
    if ($lokasiId === '' && !empty($inap['kd_bangsal'])) {
      $lokasiId = $this->mapping()->locationId((string) $inap['kd_bangsal']);
    }
    $result['mapping'] = [
      'patient_id' => (string) ($visitMapping['patient_id'] ?? ''),
      'practitioner_id' => $this->mapping()->practitionerId((string) ($reg['kd_dokter'] ?? '')),
      'location_id' => $lokasiId,
      'organization_id' => $this->auth()->organizationId(),
    ];

    $this->pipelineCache['emr'][$no_rawat] = $result;
    return $result;
  }

  // ==================================================================
  // 2. Pipeline perakitan resource FHIR rawat inap
  // ==================================================================

  /** {@inheritdoc} Tambah info kamar rawat inap + UUID ClinicalImpression rasional. */
  public function buildContext(array $emr)
  {
    $ctx = parent::buildContext($emr);
    $inap = (array) ($emr['kamar_inap'] ?? []);
    $auth = $this->auth();

    $ctx['erm_type'] = 'ranap';
    $ctx['time']['discharge'] = '';
    $tglKeluar = trim((string) ($inap['tgl_keluar'] ?? ''));
    if ($tglKeluar !== '' && strpos($tglKeluar, '0000-00-00') !== 0) {
      $ctx['time']['discharge'] = $auth->fhirTime(trim($tglKeluar . ' ' . ($inap['jam_keluar'] ?? '')), false);
    }

    $lokasiDisplay = trim('Kamar ' . ($inap['kd_kamar'] ?? '') . ', ' . ($inap['nm_bangsal'] ?? ''));
    if ($lokasiDisplay !== 'Kamar ,') {
      $ctx['location_display'] = $lokasiDisplay;
    }

    // UUID deterministik untuk ClinicalImpression rasional klinis (dirujuk Condition.stage).
    $ctx['uuid_ci_rational'] = SatuSehatAuthService::uuid();
    $ctx['built'] = [
      'ci_rational_ref' => 'urn:uuid:' . $ctx['uuid_ci_rational'],
    ];

    return $ctx;
  }

  /**
   * Rakit seluruh resource ERM Rawat Inap (template Bundle Transaction Rawat Inap).
   *
   * @return array{ctx:array,entries:array,groups:array<string,array>}
   */
  public function buildResourceEntries($no_rawat, $ermType = null, $emr = null)
  {
    $no_rawat = $this->normalizeNoRawat($no_rawat);
    if (isset($this->pipelineCache['pipeline'][$no_rawat])) {
      return $this->pipelineCache['pipeline'][$no_rawat];
    }

    if (!is_array($emr) || empty($emr['reg_periksa'])) {
      $emr = $this->getEmr($no_rawat, 'ranap');
    }
    $ctx = $this->buildContext($emr);

    // Urutan build penting: referensi antar resource diambil dari ctx['built'].
    $conditionsCc = ConditionRanapBuilder::buildChiefComplaints($ctx);
    $conditionsProblem = ConditionRanapBuilder::buildProblemList($ctx);
    $conditionsPrev = ConditionRanapBuilder::buildPreviousConditions($ctx);
    $ctx['built']['conditions_cc'] = $conditionsCc;

    $obsVital = ObservationRanapBuilder::buildVitalSigns($ctx);
    $obsExam = ObservationRanapBuilder::buildPhysicalExam($ctx);
    $obsFunc = ObservationRanapBuilder::buildFunctional($ctx);
    $obsLab = ObservationBuilder::buildLabObservations($ctx);
    $obsRad = ObservationRanapBuilder::buildRadiologyResults($ctx);
    $obsKriteria = ObservationRanapBuilder::buildKriteriaPulang($ctx);
    $ctx['built']['observations_lab'] = $obsLab;
    $ctx['built']['observations_rad'] = $obsRad;

    $allergy = AllergyIntoleranceBuilder::build($ctx);

    // Diagnosis dibangun sebelum ClinicalImpression agar prognosis punya finding;
    // ref rasional klinis sudah deterministik dari buildContext().
    $conditionsDx = ConditionRanapBuilder::buildDiagnoses($ctx);
    $ctx['built']['conditions_dx'] = $conditionsDx;

    $encounter = EncounterRanapBuilder::build($ctx);

    $ci = ClinicalImpressionRanapBuilder::build($ctx);
    $ciHistory = isset($ci['history']) ? [$ci['history']] : [];
    $ciRational = isset($ci['rational']) ? [$ci['rational']] : [];
    $ciPrognosis = isset($ci['prognosis']) ? [$ci['prognosis']] : [];

    // Farmasi: Medication -> MedicationRequest -> MedicationDispense/MedicationAdministration.
    $medications = MedicationBuilder::build($ctx);
    $ctx['built']['medications'] = $medications;
    $medRequests = MedicationRequestBuilder::build($ctx);
    $ctx['built']['medrequests'] = $medRequests;
    $medDispenses = MedicationDispenseBuilder::build($ctx);
    $ctx['built']['meddispenses'] = $medDispenses;
    $medAdmins = MedicationAdministrationBuilder::build($ctx);
    $medStatements = MedicationStatementBuilder::build($ctx);
    $questionnaires = QuestionnaireResponseBuilder::buildQ0007($ctx);

    // Tindakan/prosedur: ServiceRequest -> Procedure (+ edukasi).
    $ctx['built']['tindakan_requests'] = ServiceRequestBuilder::buildTindakan($ctx);
    $tindakanRequests = array_values($ctx['built']['tindakan_requests']);
    $procedures = ProcedureBuilder::build($ctx);
    $education = ProcedureBuilder::buildEducation($ctx);

    // Penunjang: permintaan -> spesimen -> hasil -> laporan.
    $srLab = ServiceRequestBuilder::build($ctx, 'lab');
    $srRad = ServiceRequestBuilder::build($ctx, 'rad');
    $ctx['built']['servicerequests_lab'] = $srLab;
    $ctx['built']['servicerequests_rad'] = $srRad;
    $specimens = SpecimenBuilder::build($ctx);
    $ctx['built']['specimens'] = $specimens;
    $drLab = DiagnosticReportRanapBuilder::build($ctx, 'lab');
    $drRad = DiagnosticReportRanapBuilder::build($ctx, 'rad');

    // Rencana asuhan: Goal -> CarePlan (+ penilaian risiko).
    $goals = GoalBuilder::build($ctx);
    $ctx['built']['goals'] = $goals;
    $carePlans = CarePlanRanapBuilder::build($ctx);
    $risk = RiskAssessmentBuilder::build($ctx);

    $nutrition = NutritionOrderBuilder::build($ctx);
    $familyHistory = FamilyMemberHistoryBuilder::build($ctx);

    $groups = [
      'encounter' => $encounter,
      'condition' => array_merge($conditionsCc, $conditionsProblem, $conditionsPrev, $conditionsDx),
      'condition_cc' => $conditionsCc,
      'condition_problem' => $conditionsProblem,
      'condition_prev' => $conditionsPrev,
      'condition_dx' => $conditionsDx,
      'observation' => array_merge($obsVital, $obsExam, $obsFunc, $obsRad, $obsKriteria),
      'observation_vital' => $obsVital,
      'observation_exam' => $obsExam,
      'observation_func' => $obsFunc,
      'observation_kriteria' => $obsKriteria,
      'allergy' => $allergy,
      'procedure' => $procedures,
      'procedure_request' => $tindakanRequests,
      'procedure_education' => $education,
      'care_plan' => $carePlans,
      'laboratory' => array_merge($srLab, $obsLab, array_values($specimens), $drLab),
      'radiology' => array_merge($srRad, $obsRad, $drRad),
      'medication' => array_merge($medications, $medRequests, $medDispenses, $medAdmins, $medStatements, $questionnaires),
      'medication_statement' => $medStatements,
      'clinical_impression' => array_merge($ciHistory, $ciRational, $ciPrognosis),
      'clinical_impression_history' => $ciHistory,
      'clinical_impression_rational' => $ciRational,
      'clinical_impression_prognosis' => $ciPrognosis,
      'goal' => $goals,
      'risk_assessment' => $risk,
      'nutrition' => $nutrition,
      'family_member_history' => $familyHistory,
    ];

    $entries = [];
    $seen = [];
    foreach (SatuSehatBundleBuilder::flatten($groups) as $entry) {
      $fullUrl = (string) ($entry['fullUrl'] ?? '');
      if ($fullUrl !== '' && isset($seen[$fullUrl])) {
        continue;
      }
      $seen[$fullUrl] = true;
      $entries[] = $entry;
    }

    $result = [
      'ctx' => $ctx,
      'entries' => $entries,
      'groups' => $groups,
    ];

    $this->pipelineCache['pipeline'][$no_rawat] = $result;
    return $result;
  }

  // ==================================================================
  // 3. Composition Hospital Discharge summary (resume medis rawat inap)
  // ==================================================================

  /** {@inheritdoc} Composition type 34105-7 Hospital Discharge summary. */
  public function buildCompositionResource(array $ctx, array $groups)
  {
    $refList = function (array ...$lists) {
      $refs = [];
      foreach ($lists as $list) {
        foreach ((array) $list as $entry) {
          if (!empty($entry['fullUrl'])) {
            $refs[] = ['reference' => (string) $entry['fullUrl']];
          }
        }
      }
      return $refs;
    };

    $section = function ($title, $code = null, $display = null, array $refs = [], array $subSections = null, $text = null) use ($refList) {
      $section = ['title' => $title];
      if ($code !== null) {
        $section['code'] = [
          'coding' => [
            ['system' => 'http://loinc.org', 'code' => $code, 'display' => $display],
          ],
        ];
      }
      if (!empty($refs)) {
        $section['entry'] = $refs;
      }
      if ($text !== null) {
        $section['text'] = [
          'status' => 'additional',
          'div' => '<div xmlns="http://www.w3.org/1999/xhtml">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</div>',
        ];
      }
      if (is_array($subSections)) {
        $section['section'] = array_values(array_filter($subSections));
      }
      return $section;
    };

    $g = $groups;
    $obsFunc = (array) ($g['observation_func'] ?? []);
    $statusPsikologis = array_values(array_filter($obsFunc, function ($entry) {
      return (string) ($entry['resource']['code']['coding'][0]['code'] ?? '') === '8693-4';
    }));
    $skorAdl = array_values(array_filter($obsFunc, function ($entry) {
      return (string) ($entry['resource']['code']['coding'][0]['code'] ?? '') === '715823002';
    }));

    $carePlans = (array) ($g['care_plan'] ?? []);
    $carePlanPulang = array_values(array_filter($carePlans, function ($entry) {
      return (string) ($entry['resource']['title'] ?? '') === 'Perencanaan Pemulangan Pasien';
    }));
    $carePlanRanap = array_values(array_filter($carePlans, function ($entry) {
      return (string) ($entry['resource']['title'] ?? '') !== 'Perencanaan Pemulangan Pasien';
    }));

    // Obat pulang: MedicationRequest/MedicationDispense resep terakhir.
    $resepItems = (array) (($ctx['emr'] ?? [])['resep_items'] ?? []);
    $noResepPulang = '';
    foreach ($resepItems as $resepItem) {
      $noResepPulang = (string) (((array) $resepItem)['no_resep'] ?? '');
    }
    $medRequests = (array) (($ctx['built'] ?? [])['medrequests'] ?? []);
    $medDispenses = (array) (($ctx['built'] ?? [])['meddispenses'] ?? []);
    $requestPulang = array_values(array_filter($medRequests, function ($entry) use ($noResepPulang) {
      return $noResepPulang !== '' && (string) ($entry['resource']['identifier'][0]['value'] ?? '') === $noResepPulang;
    }));
    $refPulang = [];
    foreach ($requestPulang as $entry) {
      $refPulang[(string) ($entry['fullUrl'] ?? '')] = true;
    }
    $dispensePulang = array_values(array_filter($medDispenses, function ($entry) use ($refPulang) {
      $ref = (string) ($entry['resource']['authorizingPrescription'][0]['reference'] ?? '');
      return $ref !== '' && isset($refPulang[$ref]);
    }));
    $obatPulang = array_merge($requestPulang, $dispensePulang);
    $obatKunjungan = array_merge(
      (array) ($g['medication'] ?? []),
      (array) ($g['medication_statement'] ?? [])
    );

    $sections = [
      $section('Anamnesis', 'TK000003', 'Anamnesis', [], [
        $section('Keluhan Utama', '10154-3', 'Chief complaint Narrative - Reported', $refList((array) ($g['condition_cc'] ?? []))),
        $section('Keluhan Penyerta', '11450-4', 'Problem list - Reported', $refList((array) ($g['condition_problem'] ?? []))),
        $section('Riwayat Penyakit Pribadi Terdahulu', '11348-0', 'History of Past illness Narrative', $refList((array) ($g['condition_prev'] ?? []))),
        $section('Riwayat Penyakit Keluarga', '10157-6', 'History of family member diseases Narrative', $refList((array) ($g['family_member_history'] ?? []))),
        $section('Riwayat Alergi', '48765-2', 'Allergies and adverse reactions Document', $refList((array) ($g['allergy'] ?? []))),
        $section('Riwayat Pengobatan', '10160-0', 'History of Medication use Narrative', $refList((array) ($g['medication_statement'] ?? []))),
      ]),
      $section('Pemeriksaan Fisik', 'TK000007', 'Pemeriksaan Fisik', [], [
        $section('Tanda Vital', '8716-3', 'Vital signs', $refList((array) ($g['observation_vital'] ?? []))),
        $section('Pemeriksaan Fisik Head to Toe', '10187-3', 'Review of systems Narrative - Reported', $refList((array) ($g['observation_exam'] ?? []))),
      ]),
      $section('Pemeriksaan Fungsional', '47420-5', 'Functional status assessment note', [], [
        $section('Status Psikologis', null, null, $refList($statusPsikologis)),
        $section('Skor ADL', null, null, $refList($skorAdl)),
      ]),
      $section('Perencanaan Perawatan', '18776-5', 'Plan of treatment (narrative)', $refList(
        (array) ($g['clinical_impression_history'] ?? []),
        (array) ($g['goal'] ?? []),
        $carePlanRanap
      )),
      $section('Pemeriksaan Penunjang', 'TK000009', 'Hasil Pemeriksaan Penunjang', [], [
        $section('Hasil Pemeriksaan Laboratorium', '11502-2', 'Laboratory report', $refList((array) ($g['laboratory'] ?? []))),
        $section('Hasil Pemeriksaan Radiologi', '18782-3', 'Radiology Study observation (narrative)', $refList((array) ($g['radiology'] ?? []))),
      ]),
      $section('Diagnosis', null, null, [], [
        $section('Diagnosis Akhir', '78375-3', 'Discharge diagnosis Narrative', $refList(
          (array) ($g['clinical_impression_rational'] ?? []),
          (array) ($g['condition_dx'] ?? []),
          (array) ($g['risk_assessment'] ?? [])
        )),
      ]),
      $section('Tindakan/Prosedur Medis', 'TK000005', 'Tindakan/Prosedur Medis', $refList(
        (array) ($g['procedure_request'] ?? []),
        (array) ($g['procedure'] ?? [])
      )),
      $section('Farmasi', 'TK000013', 'Obat', [], [
        $section('Obat Saat Kunjungan', '42346-7', 'Medications on admission (narrative)', $refList($obatKunjungan)),
        $section('Obat Pulang', '75311-1', 'Discharge medications Narrative', $refList($obatPulang)),
      ]),
      $section('Diet', null, null, [], [
        $section('Rekomendasi Diet', '42344-2', 'Discharge diet (narrative)', $refList((array) ($g['nutrition'] ?? []))),
      ]),
      $section('Edukasi', '34895-3', 'Education note', $refList((array) ($g['procedure_education'] ?? []))),
      $section('Kondisi Saat Meninggalkan Rumah Sakit', '10184-0', 'Hospital discharge physical findings Narrative', $refList(
        (array) ($g['observation_kriteria'] ?? []),
        $carePlanPulang,
        (array) ($g['clinical_impression_prognosis'] ?? [])
      )),
      $section('Rencana Tindak Lanjut', '8653-8', 'Hospital Discharge instructions', $refList(
        (array) ($g['observation_kriteria'] ?? []),
        $carePlanPulang
      )),
      $section('Perjalanan Kunjungan Pasien', '8648-8', 'Hospital course Narrative', [], null, $this->perjalananNarrative($ctx)),
    ];

    return [
      'resourceType' => 'Composition',
      'id' => SatuSehatAuthService::uuid(),
      'status' => 'final',
      'type' => [
        'coding' => [
          ['system' => 'http://loinc.org', 'code' => '34105-7', 'display' => 'Hospital Discharge summary'],
        ],
      ],
      'category' => [
        [
          'coding' => [
            ['system' => 'http://loinc.org', 'code' => 'LP173421-1', 'display' => 'Report'],
          ],
        ],
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'date' => (string) (($ctx['time'] ?? [])['discharge'] ?? (($ctx['time'] ?? [])['now'] ?? '')),
      'author' => [['reference' => (string) (($ctx['ref'] ?? [])['organization'] ?? '')]],
      'title' => 'Resume Medis Pasien Rawat Inap ' . (string) ($ctx['patient_name'] ?? ''),
      'custodian' => ['reference' => (string) (($ctx['ref'] ?? [])['organization'] ?? '')],
      'section' => array_values(array_filter($sections)),
    ];
  }

  /** Narasi perjalanan kunjungan pasien (Composition section 8648-8). */
  private function perjalananNarrative(array $ctx)
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $p = (array) ($emr['pemeriksaan'] ?? []);
    $inap = (array) ($emr['kamar_inap'] ?? []);
    $pasien = (string) ($ctx['patient_name'] ?? '');
    $diagnosa = [];
    foreach ((array) ($emr['diagnosa_items'] ?? []) as $item) {
      $item = (array) $item;
      if (trim((string) ($item['nama'] ?? '')) !== '') {
        $diagnosa[] = (string) $item['nama'];
      }
    }

    $bagian = [];
    $bagian[] = 'Pasien ' . $pasien . ' masuk rawat inap pada tanggal ' . ($inap['tgl_masuk'] ?? '-')
      . (!empty($inap['nm_bangsal']) ? ' di ' . $inap['nm_bangsal'] : '') . '.';
    if (trim((string) ($p['keluhan'] ?? '')) !== '') {
      $bagian[] = 'Keluhan utama: ' . $p['keluhan'] . '.';
    }
    if (!empty($diagnosa)) {
      $bagian[] = 'Diagnosis: ' . implode(', ', $diagnosa) . '.';
    }
    if (trim((string) ($p['penilaian'] ?? '')) !== '') {
      $bagian[] = 'Penilaian klinis: ' . $p['penilaian'] . '.';
    }
    if (trim((string) ($p['rtl'] ?? '')) !== '') {
      $bagian[] = 'Rencana tindak lanjut: ' . $p['rtl'] . '.';
    }
    if (trim((string) ($inap['stts_pulang'] ?? '')) !== '') {
      $bagian[] = 'Pasien pulang pada tanggal ' . ($inap['tgl_keluar'] ?? '-') . ' dengan status ' . $inap['stts_pulang'] . '.';
    }

    return implode(' ', $bagian);
  }

  // ==================================================================
  // 4. Struktur BAB 1-29 (CSV Variabel Resource Rawat Inap Kemenkes)
  // ==================================================================

  /** {@inheritdoc} Struktur BAB rawat inap di atas kerangka BAB 1-29. */
  public function getErmSections($no_rawat, $ermType = null)
  {
    $sections = parent::getErmSections($no_rawat, 'ranap');
    if (empty($sections)) {
      return [];
    }

    $emr = $this->getEmr($no_rawat, 'ranap');
    $reg = (array) $emr['reg_periksa'];
    $p = (array) ($emr['pemeriksaan'] ?? []);
    $inap = (array) ($emr['kamar_inap'] ?? []);
    $terkirim = trim((string) ($inap['tgl_keluar'] ?? '')) !== '' ? 'finished' : 'in-progress';
    $masuk = trim((string) (($inap['tgl_masuk'] ?? ($reg['tgl_registrasi'] ?? '')) . ' ' . ($inap['jam_masuk'] ?? ($reg['jam_reg'] ?? ''))));
    $keluar = trim((string) (($inap['tgl_keluar'] ?? '') . ' ' . ($inap['jam_keluar'] ?? '')));
    $disposition = EncounterRanapBuilder::DISCHARGE_DISPOSITION[trim(strtolower((string) ($inap['stts_pulang'] ?? '')))] ?? null;

    $sections['data_kunjungan'] = [
      'bab' => '2', 'label' => '2. Data Kunjungan · Encounter (Rawat Inap)', 'icon' => 'fa-calendar-check-o',
      'rows' => [
        ['No. Kunjungan (Encounter ID)', (string) ($reg['no_rawat'] ?? ''), 'Status', $terkirim],
        ['Jenis Kunjungan (Encounter.class)', 'IMP=Inpatient encounter', 'Kelas Pelayanan (ServiceClass)', (string) ($inap['kelas'] ?? '')],
        ['Kamar / Bangsal (location)', trim('Kamar ' . ($inap['kd_kamar'] ?? '') . ', ' . ($inap['nm_bangsal'] ?? '')), 'Lama Rawat (length)', trim((string) ($inap['lama'] ?? '')) . ' hari'],
        ['Tgl / Jam Masuk (period.start)', $masuk, 'Tgl / Jam Selesai (period.end)', $keluar !== '' ? $keluar : '-'],
        ['Cara Keluar (hospitalization.dischargeDisposition)', trim((string) ($inap['stts_pulang'] ?? '')) ?: '-', 'Kode Disposition', $disposition !== null ? $disposition[0] . ' — ' . $disposition[1] : '-'],
      ],
    ];

    $sections['anamnesis'] = [
      'bab' => '3', 'label' => '3. Anamnesis · Condition / FamilyMemberHistory / AllergyIntolerance / MedicationStatement', 'icon' => 'fa-stethoscope',
      'rows' => [
        ['Keluhan Utama (Chief Complaint) · Condition chief-complaint', (string) ($p['keluhan'] ?? '')],
        ['Keluhan Penyerta · Condition problem-list-item', (string) ($p['pemeriksaan'] ?? '')],
        ['Riwayat Penyakit Pribadi Terdahulu · Condition previous-condition', count((array) ($emr['riwayat_penyakit_items'] ?? [])) > 0 ? implode('; ', array_map(function ($item) {
          return (string) (((array) $item)['nama'] ?? '');
        }, (array) $emr['riwayat_penyakit_items'])) : ''],
        ['Riwayat Penyakit Keluarga · FamilyMemberHistory', count((array) ($emr['riwayat_keluarga_items'] ?? [])) > 0 ? implode('; ', array_map(function ($item) {
          return (string) (((array) $item)['condition_display'] ?? '');
        }, (array) $emr['riwayat_keluarga_items'])) : ''],
        ['Riwayat Alergi · AllergyIntolerance', (string) ($p['alergi'] ?? '')],
        ['Riwayat Pengobatan · MedicationStatement', count((array) ($emr['riwayat_pengobatan_items'] ?? [])) > 0 ? implode('; ', array_map(function ($item) {
          $item = (array) $item;
          return trim((string) ($item['map']['nama_kfa'] ?? '') ?: (string) ($item['nama_brng'] ?? ''));
        }, (array) $emr['riwayat_pengobatan_items'])) : ''],
      ],
    ];

    $sections['tujuan_perawatan'] = [
      'bab' => '7', 'label' => '7. Tujuan Perawatan · Goal', 'icon' => 'fa-bullseye',
      'rows' => [
        ['Kategori Tujuan (Goal.category)', 'nursing — Nursing', '', ''],
        ['Deskripsi Tujuan (Goal.description)', (string) ($p['rtl'] ?? ''), '', ''],
      ],
    ];

    $sections['diet'] = [
      'bab' => '18', 'label' => '18. Diet · NutritionOrder', 'icon' => 'fa-utensils',
      'rows' => [
        ['Jenis Diet Oral (oralDiet.type)', count((array) ($emr['diet_items'] ?? [])) > 0 ? (string) (((array) $emr['diet_items'][0])['type_text'] ?? '') : '', '', ''],
      ],
    ];

    $sections['edukasi'] = [
      'bab' => '19', 'label' => '19. Edukasi · Procedure (category = Education)', 'icon' => 'fa-graduation-cap',
      'rows' => [
        ['Topik Edukasi (Procedure.code.coding)', '10913 — Edukasi Kesehatan Individu', 'Materi', (string) ($p['instruksi'] ?? '')],
      ],
    ];

    $sections['prognosis'] = [
      'bab' => '20', 'label' => '20. Prognosis · ClinicalImpression.prognosisCodeableConcept', 'icon' => 'fa-chart-line',
      'rows' => [
        ['Prognosis Klinis · Ad Vitam / Ad Functionam / Ad Sanationem', trim((string) ($inap['stts_pulang'] ?? '')) ?: '', '', ''],
      ],
    ];

    $sections['rencana_tindak_lanjut'] = [
      'bab' => '21', 'label' => '21. Rencana Tindak Lanjut · ServiceRequest', 'icon' => 'fa-calendar-plus-o',
      'rows' => [
        ['Jenis Tindak Lanjut (ServiceRequest.code.coding)', (string) ($p['rtl'] ?? ''), '', ''],
      ],
    ];

    $sections['instruksi_tindak_lanjut'] = [
      'bab' => '22', 'label' => '22. Instruksi untuk Tindak Lanjut · ServiceRequest', 'icon' => 'fa-directions',
      'rows' => [
        ['Instruksi Pulang / Kontrol (patientInstruction)', (string) ($p['instruksi'] ?? ''), '', ''],
        ['Kontrol Ke Poli / FKTP / RS Rujukan', '', '', ''],
        ['Dalam Keadaan Darurat Menghubungi', '', '', ''],
      ],
    ];

    $sections['kondisi_saat_keluar'] = [
      'bab' => '24', 'label' => '24. Kondisi Saat Meninggalkan RS · Condition / Observation', 'icon' => 'fa-walking',
      'rows' => [
        ['Kondisi Klinis Pasien Saat Meninggalkan RS', trim((string) ($inap['stts_pulang'] ?? '')) ?: (string) ($reg['stts'] ?? ''), '', ''],
      ],
    ];

    $sections['cara_keluar_rs'] = [
      'bab' => '25', 'label' => '25. Cara Keluar dari Rumah Sakit · Encounter.hospitalization.dischargeDisposition', 'icon' => 'fa-door-open',
      'rows' => [
        ['Atas Instruksi Dokter / Pulang Paksa / Rujuk / Lainnya', trim((string) ($inap['stts_pulang'] ?? '')) ?: '-', 'Kode', $disposition !== null ? $disposition[0] : '-'],
      ],
    ];

    $sections['discharge_administrasi'] = [
      'bab' => '26', 'label' => '26. Tanggal & Waktu Discharge Administrasi · Encounter.period.end', 'icon' => 'fa-sign-out-alt',
      'rows' => [
        ['Tanggal Discharge Administrasi', (string) ($inap['tgl_keluar'] ?? '-'), 'Jam Discharge Administrasi', (string) ($inap['jam_keluar'] ?? '-')],
      ],
    ];

    $sections['resume_medis'] = [
      'bab' => '29', 'label' => '29. Resume Medis · Composition', 'icon' => 'fa-file-alt',
      'rows' => [
        ['Tipe Dokumen Resume Medis (Composition.type)', '34105-7 Hospital Discharge summary', '', ''],
        ['Ringkasan Medis', trim((string) ($p['penilaian'] ?? '') . ' ' . ($p['evaluasi'] ?? '')), '', ''],
      ],
    ];

    return $sections;
  }

  // ==================================================================
  // 5. Daftar kunjungan rawat inap
  // ==================================================================

  /** {@inheritdoc} Filter kunjungan rawat inap (status_lanjut = Ranap). */
  protected function visitWhere(array $filters)
  {
    $where = ["r.status_lanjut = 'Ranap'"];
    $binds = [];

    if (!empty($filters['tgl_awal'])) {
      $where[] = 'r.tgl_registrasi >= ?';
      $binds[] = (string) $filters['tgl_awal'];
    }
    if (!empty($filters['tgl_akhir'])) {
      $where[] = 'r.tgl_registrasi <= ?';
      $binds[] = (string) $filters['tgl_akhir'];
    }
    if (!empty($filters['cari'])) {
      $where[] = '(r.no_rawat LIKE ? OR r.no_rkm_medis LIKE ? OR p.nm_pasien LIKE ?)';
      $cari = '%' . (string) $filters['cari'] . '%';
      array_push($binds, $cari, $cari, $cari);
    }
    if (!empty($filters['status_kirim'])) {
      if ($filters['status_kirim'] === 'belum') {
        $where[] = "(m.status_kirim IS NULL OR m.status_kirim = '' OR m.status_kirim = 'belum')";
      } else {
        $where[] = 'm.status_kirim = ?';
        $binds[] = (string) $filters['status_kirim'];
      }
    }

    return [implode(' AND ', $where), $binds];
  }

  /** {@inheritdoc} Daftar kunjungan rawat inap dengan info kamar/bangsal. */
  public function getVisitList(array $filters = [], $limit = 50, $offset = 0)
  {
    [$where, $binds] = $this->visitWhere($filters);
    $rows = $this->fetchAll(
      "SELECT r.no_rawat, COALESCE(ki.tgl_masuk, r.tgl_registrasi) AS tgl_registrasi,
              COALESCE(ki.jam_masuk, r.jam_reg) AS jam_reg, r.no_rkm_medis,
              r.status_lanjut, r.stts, ki.kd_kamar AS kd_poli,
              p.nm_pasien, CONCAT('Kamar ', ki.kd_kamar, ', ', b.nm_bangsal) AS nm_poli, d.nm_dokter, pj.png_jawab,
              m.status_kirim, m.encounter_id, m.patient_id, m.tgl_kirim
       FROM reg_periksa r
       LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
       LEFT JOIN kamar_inap ki ON ki.no_rawat = r.no_rawat
         AND CONCAT(ki.tgl_masuk, ' ', ki.jam_masuk) = (
           SELECT MAX(CONCAT(k2.tgl_masuk, ' ', k2.jam_masuk)) FROM kamar_inap k2 WHERE k2.no_rawat = r.no_rawat
         )
       LEFT JOIN kamar k ON k.kd_kamar = ki.kd_kamar
       LEFT JOIN bangsal b ON b.kd_bangsal = k.kd_bangsal
       LEFT JOIN dokter d ON d.kd_dokter = r.kd_dokter
       LEFT JOIN penjab pj ON pj.kd_pj = r.kd_pj
       LEFT JOIN " . SatuSehatResourceMappingService::TABLE_VISIT . " m ON m.no_rawat = r.no_rawat
       WHERE {$where}
       ORDER BY r.tgl_registrasi DESC, r.jam_reg DESC
       LIMIT " . (int) $limit . " OFFSET " . (int) $offset,
      $binds
    );
    foreach ($rows as $i => $row) {
      if (trim((string) ($row['nm_poli'] ?? '')) === '' || strpos((string) $row['nm_poli'], 'Kamar ,') === 0) {
        $rows[$i]['nm_poli'] = '-';
      }
    }
    return $rows;
  }
}
