<?php

namespace Plugins\Satu_Sehat\Services;

use Plugins\Satu_Sehat\Resources\AllergyIntoleranceBuilder;
use Plugins\Satu_Sehat\Resources\CarePlanBuilder;
use Plugins\Satu_Sehat\Resources\ConditionBuilder;
use Plugins\Satu_Sehat\Resources\DiagnosticReportBuilder;
use Plugins\Satu_Sehat\Resources\EncounterBuilder;
use Plugins\Satu_Sehat\Resources\MedicationBuilder;
use Plugins\Satu_Sehat\Resources\MedicationDispenseBuilder;
use Plugins\Satu_Sehat\Resources\MedicationRequestBuilder;
use Plugins\Satu_Sehat\Resources\ObservationBuilder;
use Plugins\Satu_Sehat\Resources\ProcedureBuilder;
use Plugins\Satu_Sehat\Resources\QuestionnaireResponseBuilder;
use Plugins\Satu_Sehat\Resources\ServiceRequestBuilder;

/**
 * Orkestrator sub modul ERM Rawat Jalan.
 *
 * Tugas:
 * 1. Membaca data klinis rawat jalan dari tabel SIMRS (reg_periksa, pemeriksaan_ralan, dst).
 * 2. Merakit resource FHIR R4 via resource builder (resources/*Builder.php).
 * 3. Menyusun Bundle transaction (sinkronisasi) & Bundle dokumen (pratinjau ERM).
 * 4. Menyusun struktur BAB 1-29 sesuai CSV Variabel Resource Rawat Jalan Kemenkes.
 */
class SatuSehatErmRalanService
{
  /** @var \Systems\Main */
  protected $core;

  /** @var SatuSehatAuthService */
  private $auth;

  /** @var SatuSehatResourceMappingService */
  private $mapping;

  /** @var SatuSehatBundleBuilder */
  private $bundleBuilder;

  /** @var array Cache pipeline per "no_rawat|erm_type" agar UUID konsisten antar hook. */
  protected $pipelineCache = [];

  public function __construct($core)
  {
    $this->core = $core;
    $this->auth = new SatuSehatAuthService($core);
    $this->mapping = new SatuSehatResourceMappingService($core, $this->auth);
    $this->bundleBuilder = new SatuSehatBundleBuilder();
  }

  public function auth()
  {
    return $this->auth;
  }

  public function mapping()
  {
    return $this->mapping;
  }

  /** Label sub modul untuk tampilan & pesan sinkronisasi. */
  public function ermLabel()
  {
    return 'ERM Rawat Jalan';
  }

  /** @return \PDO */
  protected function pdo()
  {
    return $this->core->db()->pdo();
  }

  protected function fetchAll($sql, array $binds = [])
  {
    try {
      $stmt = $this->pdo()->prepare($sql);
      $stmt->execute($binds);
      $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return is_array($rows) ? $rows : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  protected function fetchOne($sql, array $binds = [])
  {
    $rows = $this->fetchAll($sql, $binds);
    return $rows ? $rows[0] : null;
  }

  /**
   * Normalisasi no. rawat dari URL (tanpa slash) ke format SIMRS (yyyy/mm/dd/nnnnnn).
   */
  public function normalizeNoRawat($no_rawat)
  {
    $no_rawat = trim((string) $no_rawat);
    if ($no_rawat !== '' && strpos($no_rawat, '/') === false && strlen($no_rawat) >= 14 && function_exists('revertNorawat')) {
      return revertNorawat($no_rawat);
    }
    return $no_rawat;
  }

  /**
   * Kode KPTL untuk sebuah tindakan: mapping eksplisit admin (halaman Mapping
   * Tindakan) lalu codebook mlite_ktpl — lihat SatuSehatResourceMappingService::kptlMap().
   */
  protected function kptlMap($kode)
  {
    return $this->mapping->kptlMap($kode);
  }

  // ==================================================================
  // 1. Data klinis rawat jalan
  // ==================================================================

  /**
   * Ambil seluruh data ERM Rawat Jalan sebuah kunjungan.
   *
   * Kunci 'diagnosa'/'tindakan'/'laboratorium'/'radiologi'/'resep' berupa baris
   * tabel siap render (kompatibel ErmFromSatuSehatHelper), sedangkan kunci
   * '*_items' berstruktur untuk keperluan resource builder.
   */
  public function getEmr($no_rawat, $ermType = null)
  {
    $no_rawat = $this->normalizeNoRawat($no_rawat);
    // Cache per no_rawat saja agar UUID & tipe konsisten antar pemanggilan hook.
    if (isset($this->pipelineCache['emr'][$no_rawat])) {
      return $this->pipelineCache['emr'][$no_rawat];
    }

    $result = [
      'no_rawat' => $no_rawat,
      'erm_type' => $this->detectErmType(null, $ermType),
      'reg_periksa' => null,
      'pasien' => null,
      'dokter' => null,
      'poliklinik' => null,
      'penjab' => null,
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
      'permintaan_lab_items' => [],
      'permintaan_rad_items' => [],
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
    $result['erm_type'] = $this->detectErmType($reg, $ermType);

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

    $dokter = $this->fetchOne('SELECT * FROM dokter WHERE kd_dokter = ? LIMIT 1', [(string) ($reg['kd_dokter'] ?? '')]);
    $result['dokter'] = $dokter;
    $result['poliklinik'] = $this->fetchOne('SELECT * FROM poliklinik WHERE kd_poli = ? LIMIT 1', [(string) ($reg['kd_poli'] ?? '')]);
    $result['penjab'] = $this->fetchOne('SELECT * FROM penjab WHERE kd_pj = ? LIMIT 1', [(string) ($reg['kd_pj'] ?? '')]);

    // ---- Anamnesis & tanda vital (BAB 3-4) ----
    $pemeriksaan = $this->fetchOne(
      'SELECT * FROM pemeriksaan_ralan WHERE no_rawat = ? ORDER BY tgl_perawatan DESC, jam_rawat DESC LIMIT 1',
      [$no_rawat]
    );
    $result['pemeriksaan'] = [
      'tgl_perawatan' => (string) ($pemeriksaan['tgl_perawatan'] ?? ''),
      'jam_rawat' => (string) ($pemeriksaan['jam_rawat'] ?? ''),
      'suhu' => trim((string) ($pemeriksaan['suhu_tubuh'] ?? '')),
      'tensi' => trim((string) ($pemeriksaan['tensi'] ?? '')),
      'nadi' => trim((string) ($pemeriksaan['nadi'] ?? '')),
      'respirasi' => trim((string) ($pemeriksaan['respirasi'] ?? '')),
      'tinggi' => trim((string) ($pemeriksaan['tinggi'] ?? '')),
      'berat' => trim((string) ($pemeriksaan['berat'] ?? '')),
      'spo2' => trim((string) ($pemeriksaan['spo2'] ?? '')),
      'gcs' => trim((string) ($pemeriksaan['gcs'] ?? '')),
      'kesadaran' => trim((string) ($pemeriksaan['kesadaran'] ?? '')),
      'lingkar_perut' => trim((string) ($pemeriksaan['lingkar_perut'] ?? '')),
      'keluhan' => trim((string) ($pemeriksaan['keluhan'] ?? '')),
      'pemeriksaan' => trim((string) ($pemeriksaan['pemeriksaan'] ?? '')),
      'alergi' => trim((string) ($pemeriksaan['alergi'] ?? '')),
      'penilaian' => trim((string) ($pemeriksaan['penilaian'] ?? '')),
      'rtl' => trim((string) ($pemeriksaan['rtl'] ?? '')),
      'instruksi' => trim((string) ($pemeriksaan['instruksi'] ?? '')),
      'evaluasi' => trim((string) ($pemeriksaan['evaluasi'] ?? '')),
    ];

    // ---- Diagnosis (BAB 12) ----
    foreach ($this->fetchAll(
      'SELECT d.kd_penyakit, p.nm_penyakit, d.prioritas, d.status, d.status_penyakit
       FROM diagnosa_pasien d LEFT JOIN penyakit p ON p.kd_penyakit = d.kd_penyakit
       WHERE d.no_rawat = ? ORDER BY d.prioritas',
      [$no_rawat]
    ) as $row) {
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

    // ---- Tindakan / prosedur (BAB 14) ----
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
        'tgl' => (string) ($result['pemeriksaan']['tgl_perawatan'] ?? ''),
        'jam' => (string) ($result['pemeriksaan']['jam_rawat'] ?? ''),
      ];
    }
    foreach (['rawat_jl_dr', 'rawat_jl_pr'] as $table) {
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
        'map' => $this->mapping->labTest((string) ($row['kd_jenis_prw'] ?? '')),
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
          'map' => $this->mapping->labTest($group['kd_jenis_prw'], (string) ($detail['id_template'] ?? '')),
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
        'map' => $this->mapping->radiologyTest((string) ($row['kd_jenis_prw'] ?? '')),
      ];
      $result['rad_items'][] = $item;
      $result['radiologi'][] = [
        trim($item['tgl_periksa'] . ' ' . $item['jam']),
        $item['nm_perawatan'],
        $item['hasil'],
        $item['proyeksi'],
      ];
    }

    // ---- Peresepan obat (BAB 15) ----
    foreach ($this->fetchAll(
      'SELECT d.kode_brng, d.jml, d.aturan_pakai, h.no_resep, h.tgl_peresepan, h.jam_peresepan,
              h.tgl_penyerahan, h.jam_penyerahan, h.kd_dokter, b.nama_brng, b.kode_sat
       FROM resep_dokter d
       INNER JOIN resep_obat h ON h.no_resep = d.no_resep
       LEFT JOIN databarang b ON b.kode_brng = d.kode_brng
       WHERE h.no_rawat = ? ORDER BY h.tgl_peresepan, h.jam_peresepan, h.no_resep',
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
        'map' => $this->mapping->drug((string) ($row['kode_brng'] ?? '')),
      ];
      $result['resep_items'][] = $item;
      $result['resep'][] = [
        $item['no_resep'],
        trim((string) ($item['map']['nama_kfa'] ?? '') ?: $item['nama_brng']),
        (string) $item['jml'] . ' ' . $item['kode_sat'],
        $item['aturan_pakai'],
      ];
    }

    // ---- Pengeluaran obat (BAB 16) ----
    foreach ($this->fetchAll(
      'SELECT p.kode_brng, p.jml, p.tgl_perawatan, p.jam, p.no_batch, b.nama_brng, a.aturan
       FROM detail_pemberian_obat p
       LEFT JOIN databarang b ON b.kode_brng = p.kode_brng
       LEFT JOIN aturan_pakai a ON a.no_rawat = p.no_rawat AND a.kode_brng = p.kode_brng
          AND a.tgl_perawatan = p.tgl_perawatan AND a.jam = p.jam
       WHERE p.no_rawat = ? ORDER BY p.tgl_perawatan, p.jam',
      [$no_rawat]
    ) as $row) {
      $result['pemberian_items'][] = [
        'kode_brng' => (string) ($row['kode_brng'] ?? ''),
        'nama_brng' => (string) ($row['nama_brng'] ?? ''),
        'jml' => (float) ($row['jml'] ?? 0),
        'aturan' => trim((string) ($row['aturan'] ?? '')),
        'tgl_perawatan' => (string) ($row['tgl_perawatan'] ?? ''),
        'jam' => (string) ($row['jam'] ?? ''),
        'no_batch' => (string) ($row['no_batch'] ?? ''),
        'map' => $this->mapping->drug((string) ($row['kode_brng'] ?? '')),
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
            'map' => $kind === 'lab' ? $this->mapping->labTest($kd) : $this->mapping->radiologyTest($kd),
          ];
        }
        $result[$target][] = $permintaan;
      }
    }

    // ---- Pemetaan resource SATUSEHAT ----
    $visitMapping = $this->mapping->getVisitMapping($no_rawat);
    $result['mapping'] = [
      'patient_id' => (string) ($visitMapping['patient_id'] ?? ''),
      'practitioner_id' => $this->mapping->practitionerId((string) ($reg['kd_dokter'] ?? '')),
      'location_id' => $this->mapping->locationId((string) ($reg['kd_poli'] ?? '')),
      'organization_id' => $this->auth->organizationId(),
    ];

    $this->pipelineCache['emr'][$no_rawat] = $result;
    return $result;
  }

  protected function detectErmType($reg = null, $override = null)
  {
    if ($override !== null && $override !== '') {
      return (string) $override;
    }
    $stt = (string) ($reg['status_lanjut'] ?? 'Ralan');
    if (stripos($stt, 'ranap') !== false) {
      return 'ranap';
    }
    return 'ralan';
  }

  // ==================================================================
  // 2. Pipeline perakitan resource FHIR
  // ==================================================================

  /**
   * Rakit seluruh resource ERM Rawat Jalan. Hasil di-cache per kunjungan
   * agar UUID konsisten antar pemanggilan (termasuk via reflection helper klaim).
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
      $emr = $this->getEmr($no_rawat, $ermType);
    } elseif ($ermType !== null && $ermType !== '') {
      $emr['erm_type'] = (string) $ermType;
    }
    $ctx = $this->buildContext($emr);
    $ctx['built'] = [];

    // Urutan build penting: referensi antar resource diambil dari ctx['built'].
    $conditionsCc = ConditionBuilder::buildChiefComplaints($ctx);
    $conditionsDx = ConditionBuilder::buildDiagnoses($ctx);
    $ctx['built']['conditions'] = array_merge($conditionsCc, $conditionsDx);
    $ctx['built']['conditions_dx'] = $conditionsDx;

    $encounter = EncounterBuilder::build($ctx);

    $obsVital = ObservationBuilder::buildVitalSigns($ctx);
    $obsLab = ObservationBuilder::buildLabObservations($ctx);
    $ctx['built']['observations'] = array_merge($obsVital, $obsLab);
    $ctx['built']['observations_lab'] = $obsLab;

    $allergy = AllergyIntoleranceBuilder::build($ctx);
    $procedures = ProcedureBuilder::build($ctx);
    $srLab = ServiceRequestBuilder::build($ctx, 'lab');
    $srRad = ServiceRequestBuilder::build($ctx, 'rad');
    $ctx['built']['servicerequests'] = array_merge($srLab, $srRad);
    $drLab = DiagnosticReportBuilder::build($ctx, 'lab');
    $drRad = DiagnosticReportBuilder::build($ctx, 'rad');

    $medications = MedicationBuilder::build($ctx);
    $ctx['built']['medications'] = $medications;
    $medRequests = MedicationRequestBuilder::build($ctx);
    $ctx['built']['medrequests'] = $medRequests;
    $medDispenses = MedicationDispenseBuilder::build($ctx);
    $questionnaires = QuestionnaireResponseBuilder::build($ctx);

    $carePlans = CarePlanBuilder::build($ctx);
    $clinicalImpressions = $this->buildClinicalImpressions($ctx);

    $groups = [
      'encounter' => $encounter,
      'condition' => array_merge($conditionsCc, $conditionsDx),
      'observation' => array_merge($obsVital, $obsLab),
      'allergy' => $allergy,
      'procedure' => $procedures,
      'care_plan' => $carePlans,
      'laboratory' => array_merge($srLab, $obsLab, $drLab),
      'radiology' => array_merge($srRad, $drRad),
      'medication' => array_merge($medications, $medRequests, $medDispenses, $questionnaires),
      'clinical_impression' => $clinicalImpressions,
    ];

    $result = [
      'ctx' => $ctx,
      'entries' => array_merge(...array_values($groups)),
      'groups' => $groups,
    ];

    $this->pipelineCache['pipeline'][$no_rawat] = $result;
    return $result;
  }

  /**
   * Susun konteks bersama untuk resource builder.
   */
  public function buildContext(array $emr)
  {
    $reg = (array) ($emr['reg_periksa'] ?? []);
    $pasien = (array) ($emr['pasien'] ?? []);
    $dokter = (array) ($emr['dokter'] ?? []);
    $poli = (array) ($emr['poliklinik'] ?? []);
    $mapping = (array) ($emr['mapping'] ?? []);

    // ID IHS pasien: tersimpan di mapping, fallback lookup NIK saat pertama kali.
    if (trim((string) ($mapping['patient_id'] ?? '')) === '') {
      $mapping['patient_id'] = $this->mapping->patientId($emr, true);
      $emr['mapping']['patient_id'] = $mapping['patient_id'];
    }

    $practitionerId = (string) ($mapping['practitioner_id'] ?? '');
    $locationId = (string) ($mapping['location_id'] ?? '');
    $organizationId = (string) ($mapping['organization_id'] ?? $this->auth->organizationId());

    // Praktisi apoteker untuk MedicationDispense; fallback ke dokter peresep.
    $pharmacistId = '';
    $pharmacistName = '';
    foreach ($this->mapping->practitionersByJenis('Apoteker') as $apoteker) {
      $pharmacistId = (string) ($apoteker['practitioner_id'] ?? '');
      $pharmacistName = (string) ($apoteker['kd_dokter'] ?? '');
      if ($pharmacistId !== '') {
        break;
      }
    }
    if ($pharmacistId === '') {
      $pharmacistId = $practitionerId;
      $pharmacistName = (string) ($dokter['nm_dokter'] ?? '');
    }

    $uuidEncounter = SatuSehatAuthService::uuid();
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);

    return [
      'auth' => $this->auth,
      'emr' => $emr,
      'no_rawat' => (string) ($emr['no_rawat'] ?? ''),
      'erm_type' => (string) ($emr['erm_type'] ?? 'ralan'),
      'organization_id' => $organizationId,
      'uuid_encounter' => $uuidEncounter,
      'patient_name' => (string) ($pasien['nm_pasien'] ?? ''),
      'practitioner_name' => (string) ($dokter['nm_dokter'] ?? ''),
      'pharmacist_name' => $pharmacistName,
      'location_display' => trim((string) (($reg['kd_poli'] ?? '') . ' ' . ($poli['nm_poli'] ?? ''))),
      'ref' => [
        'encounter' => 'urn:uuid:' . $uuidEncounter,
        'patient' => trim((string) ($mapping['patient_id'] ?? '')) !== '' ? 'Patient/' . $mapping['patient_id'] : '',
        'practitioner' => $practitionerId !== '' ? 'Practitioner/' . $practitionerId : '',
        'organization' => $organizationId !== '' ? 'Organization/' . $organizationId : '',
        'location' => $locationId !== '' ? 'Location/' . $locationId : '',
        'pharmacist' => $pharmacistId !== '' ? 'Practitioner/' . $pharmacistId : '',
      ],
      'time' => [
        'reg' => $this->auth->fhirTime(trim((string) (($reg['tgl_registrasi'] ?? '') . ' ' . ($reg['jam_reg'] ?? '')))),
        'exam' => $this->auth->fhirTime(trim((string) (($pemeriksaan['tgl_perawatan'] ?? '') . ' ' . ($pemeriksaan['jam_rawat'] ?? '')))),
        'end' => $this->auth->fhirTime(trim((string) (($pemeriksaan['tgl_perawatan'] ?? '') . ' ' . ($pemeriksaan['jam_rawat'] ?? '')))),
        'now' => $this->auth->fhirTime(date('Y-m-d H:i:s')),
      ],
    ];
  }

  /**
   * ClinicalImpression (BAB 6 riwayat perjalanan penyakit + BAB 20 prognosis).
   * Dibangun di service karena tidak memiliki file builder tersendiri.
   */
  private function buildClinicalImpressions(array $ctx)
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $penilaian = trim((string) ($pemeriksaan['penilaian'] ?? ''));
    $evaluasi = trim((string) ($pemeriksaan['evaluasi'] ?? ''));
    if ($penilaian === '' && $evaluasi === '') {
      return [];
    }

    $impression = [
      'resourceType' => 'ClinicalImpression',
      'status' => 'completed',
      'description' => 'Riwayat Perjalanan Penyakit Kunjungan Rawat Jalan',
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'effectiveDateTime' => (string) (($ctx['time'] ?? [])['exam'] ?? ''),
      'date' => (string) (($ctx['time'] ?? [])['now'] ?? ''),
      'assessor' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
    ];
    if ($penilaian !== '') {
      $impression['problem'] = [];
      $impression['summary'] = $penilaian;
    }
    if ($evaluasi !== '') {
      $impression['note'] = [['text' => $evaluasi]];
    }

    return [SatuSehatBundleBuilder::entry($impression)];
  }

  /**
   * Akses per kelompok untuk hook reflection plugin klaim_bpjs_satusehat.
   */
  public function hookGroup($no_rawat, $group, $ermType = null, $emr = null)
  {
    $pipeline = $this->buildResourceEntries($no_rawat, $ermType, is_array($emr) ? $emr : null);
    return $pipeline['groups'][$group] ?? [];
  }

  // ==================================================================
  // 3. Bundle
  // ==================================================================

  /** Bundle transaction siap kirim ke SATUSEHAT. */
  public function buildTransactionBundle($no_rawat, $ermType = null)
  {
    $pipeline = $this->buildResourceEntries($no_rawat, $ermType);
    return $this->bundleBuilder->transaction($pipeline['entries']);
  }

  /**
   * Bundle dokumen ERM (Composition + section).
   *
   * @param array|null $payloads Entri dari hook (opsional); bila kosong memakai pipeline.
   */
  public function buildDocumentBundle($no_rawat, $payloads = null, $ermType = null)
  {
    $pipeline = $this->buildResourceEntries($no_rawat, $ermType);
    $entries = $pipeline['entries'];

    // Entri dari hook (mis. ErmFromSatuSehatHelper) digabungkan tanpa duplikat.
    if (is_array($payloads) && !empty($payloads)) {
      $known = [];
      foreach ($entries as $entry) {
        $known[(string) ($entry['fullUrl'] ?? '')] = true;
      }
      foreach (SatuSehatBundleBuilder::flatten($payloads) as $entry) {
        $fullUrl = (string) ($entry['fullUrl'] ?? '');
        if ($fullUrl === '' || !isset($known[$fullUrl])) {
          $entries[] = $entry;
          $known[$fullUrl] = true;
        }
      }
    }

    $composition = $this->buildCompositionResource($pipeline['ctx'], $pipeline['groups']);
    return $this->bundleBuilder->document($entries, $composition);
  }

  /**
   * Resource Composition dengan section per kelompok klinis (dokumen ERM).
   */
  public function buildCompositionResource(array $ctx, array $groups)
  {
    $section = function ($code, $display, $title, array $entries) {
      $refs = [];
      foreach ($entries as $entry) {
        if (!empty($entry['fullUrl'])) {
          $refs[] = ['reference' => (string) $entry['fullUrl']];
        }
      }
      if (empty($refs)) {
        return null;
      }
      return [
        'title' => $title,
        'code' => [
          'coding' => [
            ['system' => 'http://loinc.org', 'code' => $code, 'display' => $display],
          ],
        ],
        'text' => ['status' => 'generated', 'div' => '<div xmlns="http://www.w3.org/1999/xhtml">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>'],
        'entry' => $refs,
      ];
    };

    $sections = [
      $section('11450-4', 'Problem list', 'Anamnesis & Diagnosis', (array) ($groups['condition'] ?? [])),
      $section('8716-3', 'Vital signs', 'Pemeriksaan Fisik & Fungsional', array_filter((array) ($groups['observation'] ?? []), function ($entry) {
        return ($entry['resource']['category'][0]['coding'][0]['code'] ?? '') !== 'laboratory';
      })),
      $section('47519-4', 'History of Procedures', 'Tindakan / Prosedur Medis', array_merge((array) ($groups['procedure'] ?? []), (array) ($groups['allergy'] ?? []))),
      $section('30954-2', 'Relevant diagnostic tests/laboratory data', 'Penunjang Diagnostik', array_merge((array) ($groups['laboratory'] ?? []), (array) ($groups['radiology'] ?? []))),
      $section('10160-0', 'History of Medication Use', 'Terapi & Peresepan', (array) ($groups['medication'] ?? [])),
      $section('18776-5', 'Plan of Care Note', 'Rencana Asuhan & Perjalanan Penyakit', array_merge((array) ($groups['care_plan'] ?? []), (array) ($groups['clinical_impression'] ?? []))),
    ];
    $sections = array_values(array_filter($sections));

    return [
      'resourceType' => 'Composition',
      'id' => SatuSehatAuthService::uuid(),
      'status' => 'final',
      'type' => [
        'coding' => [
          ['system' => 'http://loinc.org', 'code' => '11503-8', 'display' => 'Medical records'],
        ],
      ],
      'category' => [
        [
          'coding' => [
            ['system' => 'http://snomed.info/sct', 'code' => '371538006', 'display' => 'Medical record'],
          ],
        ],
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'date' => (string) (($ctx['time'] ?? [])['now'] ?? ''),
      'author' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
      'title' => 'ERM Rawat Jalan ' . (string) ($ctx['no_rawat'] ?? ''),
      'custodian' => ['reference' => (string) (($ctx['ref'] ?? [])['organization'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'section' => $sections,
    ];
  }

  /** Jumlah resource per resourceType untuk ringkasan/pratinjau. */
  public function getResourceSummary($no_rawat, $ermType = null)
  {
    $pipeline = $this->buildResourceEntries($no_rawat, $ermType);
    return SatuSehatBundleBuilder::groupIdsByType($pipeline['entries']);
  }

  // ==================================================================
  // 4. Struktur BAB 1-29 (CSV Variabel Resource Rawat Jalan Kemenkes)
  // ==================================================================

  /**
   * Susun section ERM Rawat Jalan siap render tabel (BAB 1 s/d 29).
   */
  public function getErmSections($no_rawat, $ermType = null)
  {
    $emr = $this->getEmr($no_rawat, $ermType);
    if (empty($emr['reg_periksa'])) {
      return [];
    }

    $reg = (array) $emr['reg_periksa'];
    $pasien = (array) ($emr['pasien'] ?? []);
    $dokter = (array) ($emr['dokter'] ?? []);
    $poli = (array) ($emr['poliklinik'] ?? []);
    $penjab = (array) ($emr['penjab'] ?? []);
    $p = (array) ($emr['pemeriksaan'] ?? []);
    $ermType = (string) ($emr['erm_type'] ?? 'ralan');

    $imt = '';
    if ((float) ($p['tinggi'] ?? 0) > 0 && (float) ($p['berat'] ?? 0) > 0) {
      $imt = (string) round((float) $p['berat'] / pow(((float) $p['tinggi']) / 100, 2), 1);
    }
    $encounterClass = $ermType === 'ranap' ? 'IMP=Inpatient' : ($ermType === 'igd' ? 'EMER=Emergency' : 'AMB=Ambulatory');

    return [
      'identitas_pasien' => [
        'bab' => '1', 'label' => '1. Identitas Pasien · Patient', 'icon' => 'fa-id-card',
        'rows' => [
          ['Nomor SATUSEHAT Pasien', (string) ($pasien['no_peserta'] ?? ''), 'No. RM', (string) ($pasien['no_rkm_medis'] ?? ''), 'Nama Lengkap', (string) ($pasien['nm_pasien'] ?? '')],
          ['NIK', (string) ($pasien['no_ktp'] ?? ''), 'Tempat / Tgl Lahir', trim(($pasien['tmp_lahir'] ?? '') . ' ' . ($pasien['tgl_lahir'] ?? '')), 'Jenis Kelamin / Umur', trim(($pasien['jk'] ?? '') . ' / ' . ($pasien['umur'] ?? ''))],
          ['Nama Ibu Kandung', (string) ($pasien['nm_ibu'] ?? ''), 'Agama', (string) ($pasien['agama'] ?? ''), 'Status Pernikahan', (string) ($pasien['stts_nikah'] ?? '')],
          ['Alamat Lengkap', (string) ($pasien['alamat'] ?? ''), 'Pekerjaan', (string) ($pasien['pekerjaan'] ?? ''), 'Bahasa yang Dikuasai', (string) ($pasien['bahasa_pasien'] ?? 'Indonesia')],
          ['Kelurahan / Desa', (string) ($pasien['kelurahan'] ?? ''), 'Kecamatan', (string) ($pasien['kecamatan'] ?? ''), 'Kabupaten / Kota', (string) ($pasien['kabupaten'] ?? '')],
          ['Provinsi', (string) ($pasien['propinsi'] ?? ''), 'No. Telepon', (string) ($pasien['no_tlp'] ?? ''), 'Negara', 'Indonesia'],
          ['Penanggung Jawab', trim(($penjab['png_jawab'] ?? '') . ' - ' . ($pasien['namakeluarga'] ?? '')), 'Poli / Status Lanjut', trim(($poli['nm_poli'] ?? '') . ' · ' . ($reg['status_lanjut'] ?? ''), ' ·'), 'DPJP / Dokter Pelayanan', (string) ($dokter['nm_dokter'] ?? '')],
          ['Tgl / Jam Registrasi', trim(($reg['tgl_registrasi'] ?? '') . ' ' . ($reg['jam_reg'] ?? '')), 'No. Rawat', (string) ($reg['no_rawat'] ?? ''), 'Cara Bayar', (string) ($penjab['png_jawab'] ?? '')],
        ],
      ],
      'data_kunjungan' => [
        'bab' => '2', 'label' => '2. Data Kunjungan · Encounter', 'icon' => 'fa-calendar-check-o',
        'rows' => [
          ['No. Kunjungan (Encounter ID)', (string) ($reg['no_rawat'] ?? ''), 'Status', 'finished'],
          ['Jenis Kunjungan (Encounter.class)', $encounterClass, 'Kelas Pelayanan', (string) ($reg['status_lanjut'] ?? '')],
          ['Poli / Ruangan (location)', (string) ($poli['nm_poli'] ?? ''), 'Tipe Pelayanan (serviceType)', 'Rawat Jalan'],
          ['Tgl / Jam Masuk (period.start)', trim(($reg['tgl_registrasi'] ?? '') . ' ' . ($reg['jam_reg'] ?? '')), 'Tgl / Jam Selesai (period.end)', trim(($p['tgl_perawatan'] ?? '') . ' ' . ($p['jam_rawat'] ?? ''))],
        ],
      ],
      'anamnesis' => [
        'bab' => '3', 'label' => '3. Anamnesis · Condition / AllergyIntolerance', 'icon' => 'fa-stethoscope',
        'rows' => [
          ['Keluhan Utama (Chief Complaint) · Condition', (string) ($p['keluhan'] ?? '')],
          ['Keluhan Penyerta · Condition', (string) ($p['pemeriksaan'] ?? '')],
          ['Riwayat Alergi · AllergyIntolerance', (string) ($p['alergi'] ?? '')],
          ['Riwayat Penyakit Pribadi (Past Illness) · Condition', ''],
          ['Riwayat Penyakit Keluarga · FamilyMemberHistory', ''],
          ['Riwayat Pengobatan Sebelumnya · MedicationStatement', ''],
        ],
      ],
      'pemeriksaan_fisik' => [
        'bab' => '4', 'label' => '4. Pemeriksaan Fisik · Observation (Vital Sign, Kesadaran, H2T, Antropometri)', 'icon' => 'fa-heartbeat',
        'rows' => [
          ['Tanda Vital · Denyut Jantung (8867-4)', (string) ($p['nadi'] ?? ''), 'bpm', ($p['nadi'] ?? '') !== '' ? $this->auth->fhirTime(trim(($p['tgl_perawatan'] ?? '') . ' ' . ($p['jam_rawat'] ?? ''))) : ''],
          ['Tanda Vital · Frekuensi Pernapasan (9279-1)', (string) ($p['respirasi'] ?? ''), 'x/menit', ''],
          ['Tanda Vital · Tekanan Darah', (string) ($p['tensi'] ?? ''), 'mmHg', 'Sistolik/Diastolik'],
          ['Tanda Vital · Suhu Tubuh (8310-5)', (string) ($p['suhu'] ?? ''), '°C', ''],
          ['Tanda Vital · Saturasi Oksigen (59408-5)', (string) ($p['spo2'] ?? ''), '%', ''],
          ['Tanda Vital · Tingkat Kesadaran / GCS', trim(($p['kesadaran'] ?? '') . ($p['gcs'] !== '' ? ' (GCS ' . $p['gcs'] . ')' : '')), '', ''],
          ['Pemeriksaan Head to Toe · Pemeriksaan Fisik', (string) ($p['pemeriksaan'] ?? ''), '', ''],
          ['Antropometri · Tinggi Badan (8302-2)', (string) ($p['tinggi'] ?? ''), 'cm', ''],
          ['Antropometri · Berat Badan (29463-7)', (string) ($p['berat'] ?? ''), 'kg', ''],
          ['Antropometri · IMT (39156-5)', $imt, 'kg/m2', ''],
          ['Antropometri · Lingkar Perut', (string) ($p['lingkar_perut'] ?? ''), 'cm', ''],
        ],
      ],
      'pemeriksaan_fungsional' => [
        'bab' => '5', 'label' => '5. Pemeriksaan Fungsional · Observation (Status Psikologis)', 'icon' => 'fa-brain',
        'rows' => [
          ['Status Psikologis · Kondisi Mental / Emosi · Observation', (string) ($p['penilaian'] ?? ''), '', ''],
        ],
      ],
      'riwayat_perjalanan_penyakit' => [
        'bab' => '6', 'label' => '6. Riwayat Perjalanan Penyakit · ClinicalImpression', 'icon' => 'fa-road',
        'rows' => [
          ['Ringkasan Perjalanan Klinis (ClinicalImpression.summary)', (string) ($p['evaluasi'] ?? ($p['penilaian'] ?? '')), '', ''],
        ],
      ],
      'tujuan_perawatan' => [
        'bab' => '7', 'label' => '7. Tujuan Perawatan · Goal', 'icon' => 'fa-bullseye',
        'rows' => [
          ['Kategori Tujuan (Goal.category)', '', '', ''],
          ['Deskripsi Tujuan (Goal.description)', '', '', ''],
        ],
      ],
      'rencana_rawat' => [
        'bab' => '8', 'label' => '8. Rencana Rawat · CarePlan', 'icon' => 'fa-clipboard-list',
        'rows' => [
          ['Kategori Rencana (CarePlan.category)', 'Rencana Rawat', '', ''],
          ['Deskripsi Rencana (CarePlan.description)', (string) ($p['rtl'] ?? ''), '', ''],
          ['Daftar Tujuan (CarePlan.goal) · ref BAB 7', '', '', ''],
        ],
      ],
      'instruksi_medik_keperawatan' => [
        'bab' => '9', 'label' => '9. Instruksi Medik dan Keperawatan · CarePlan', 'icon' => 'fa-clipboard',
        'rows' => [
          ['Instruksi Medik (Dokter) · CarePlan', (string) ($p['instruksi'] ?? ''), '', ''],
          ['Instruksi Keperawatan · CarePlan', '', '', ''],
        ],
      ],
      'penunjang_laboratorium' => [
        'bab' => '10a', 'label' => '10a. Pemeriksaan Penunjang · Laboratorium', 'icon' => 'fa-flask',
        'rows' => array_merge([
          ['Permintaan Lab (ServiceRequest.identifier)', 'Kode/Nama Pemeriksaan', 'Tgl/Jam Permintaan', 'Dokter Pengirim'],
        ], $this->permintaanRows($emr['permintaan_lab_items'] ?? [], $dokter), $emr['laboratorium'] ?? []),
      ],
      'penunjang_radiologi' => [
        'bab' => '10b', 'label' => '10b. Pemeriksaan Penunjang · Radiologi', 'icon' => 'fa-x-ray',
        'rows' => array_merge([
          ['Permintaan Rad (ServiceRequest.identifier)', 'Kode/Nama Pemeriksaan', 'Tgl/Jam Permintaan', 'Dokter Pengirim'],
        ], $this->permintaanRows($emr['permintaan_rad_items'] ?? [], $dokter), $emr['radiologi'] ?? []),
      ],
      'rasional_klinis' => [
        'bab' => '11', 'label' => '11. Rasional Klinis · ClinicalImpression', 'icon' => 'fa-lightbulb-o',
        'rows' => [
          ['Kode Rasional Klinis (ClinicalImpression.code.coding)', '', '', ''],
          ['Ringkasan Rasional Klinis (summary)', (string) ($p['penilaian'] ?? ''), '', ''],
        ],
      ],
      'diagnosis' => [
        'bab' => '12', 'label' => '12. Diagnosis (Awal / Primer / Sekunder) · Condition + Encounter.diagnosis', 'icon' => 'fa-list-alt',
        'rows' => array_merge([
          ['Prioritas', 'Kode ICD-10', 'Nama Diagnosis', 'Status'],
        ], $emr['diagnosa'] ?? []),
      ],
      'penilaian_risiko' => [
        'bab' => '13', 'label' => '13. Penilaian Risiko · RiskAssessment', 'icon' => 'fa-exclamation-triangle',
        'rows' => [
          ['Outcome Risiko (prediction.outcome)', '', '', ''],
          ['Probabilitas Risiko (probabilityDecimal)', '', '', ''],
          ['Mitigasi Risiko (mitigation)', '', '', ''],
        ],
      ],
      'tindakan_prosedur' => [
        'bab' => '14', 'label' => '14. Tindakan / Prosedur Medis · ServiceRequest → Procedure → Observation', 'icon' => 'fa-procedures',
        'rows' => array_merge([
          ['Sumber', 'Kode (ICD-9-CM / KPTL)', 'Nama Tindakan', 'Tgl/Jam Pelaksanaan'],
        ], $emr['tindakan'] ?? []),
      ],
      'peresepan_obat' => [
        'bab' => '15', 'label' => '15. Peresepan Obat · MedicationRequest + Medication', 'icon' => 'fa-prescription-bottle-alt',
        'rows' => array_merge([
          ['ID Resep (MedicationRequest.identifier)', 'Nama Obat (Medication.code)', 'Jumlah', 'Aturan Pakai (dosageInstruction)'],
        ], $emr['resep'] ?? [['', 'Belum ada resep obat', '', '']]),
      ],
      'pengeluaran_obat' => [
        'bab' => '16', 'label' => '16. Pengeluaran Obat · MedicationDispense', 'icon' => 'fa-hand-holding-medical',
        'rows' => array_merge([
          ['Tgl/Jam Serah (whenHandedOver)', 'Nama Obat', 'Jumlah (quantity)', 'Aturan Pakai / No. Batch'],
        ], $this->pemberianRows($emr)),
      ],
      'pemberian_obat' => [
        'bab' => '17', 'label' => '17. Pemberian Obat · MedicationAdministration', 'icon' => 'fa-syringe',
        'rows' => [
          ['ID Obat + Nama Obat (medicationReference)', '', 'Rute Pemberian (dosage.route)', ''],
          ['Dokter/Perawat Pemberi Obat (performer.actor)', '', 'Tgl/Jam Pemberian (effectivePeriod)', ''],
        ],
      ],
      'diet' => [
        'bab' => '18', 'label' => '18. Diet · NutritionOrder', 'icon' => 'fa-utensils',
        'rows' => [
          ['Jenis Diet Oral (oralDiet.type)', '', '', ''],
        ],
      ],
      'edukasi' => [
        'bab' => '19', 'label' => '19. Edukasi · Procedure (category = education)', 'icon' => 'fa-graduation-cap',
        'rows' => [
          ['Topik Edukasi (Procedure.code.coding)', '', '', ''],
        ],
      ],
      'prognosis' => [
        'bab' => '20', 'label' => '20. Prognosis · ClinicalImpression.prognosisCodeableConcept', 'icon' => 'fa-chart-line',
        'rows' => [
          ['Prognosis Klinis · Ad Vitam / Ad Functionam / Ad Sanationem', '', '', ''],
        ],
      ],
      'rencana_tindak_lanjut' => [
        'bab' => '21', 'label' => '21. Rencana Tindak Lanjut · ServiceRequest', 'icon' => 'fa-calendar-plus-o',
        'rows' => [
          ['Jenis Tindak Lanjut (ServiceRequest.code.coding)', (string) ($p['rtl'] ?? ''), '', ''],
        ],
      ],
      'instruksi_tindak_lanjut' => [
        'bab' => '22', 'label' => '22. Instruksi untuk Tindak Lanjut · ServiceRequest', 'icon' => 'fa-directions',
        'rows' => [
          ['Kontrol Ke Poli / FKTP / RS Rujukan', '', '', ''],
          ['Tgl/Jam Kontrol (occurrenceDateTime)', '', '', ''],
          ['Dalam Keadaan Darurat Menghubungi', '', '', ''],
        ],
      ],
      'sarana_transportasi_rujuk' => [
        'bab' => '23', 'label' => '23. Sarana Transportasi Untuk Rujuk · ServiceRequest', 'icon' => 'fa-ambulance',
        'rows' => [
          ['Jenis Sarana Transportasi Rujukan', '', '', ''],
        ],
      ],
      'kondisi_saat_keluar' => [
        'bab' => '24', 'label' => '24. Kondisi Saat Meninggalkan RS · Condition', 'icon' => 'fa-walking',
        'rows' => [
          ['Kondisi Klinis Pasien Saat Meninggalkan RS', (string) ($reg['stts'] ?? ''), '', ''],
        ],
      ],
      'cara_keluar_rs' => [
        'bab' => '25', 'label' => '25. Cara Keluar dari Rumah Sakit · Encounter.dischargeDisposition', 'icon' => 'fa-door-open',
        'rows' => [
          ['Atas Instruksi Dokter / Pulang Paksa / Rujuk / Lainnya', (string) ($reg['stts'] ?? ''), '', ''],
        ],
      ],
      'discharge_administrasi' => [
        'bab' => '26', 'label' => '26. Tanggal & Waktu Discharge Administrasi · Encounter.period.end', 'icon' => 'fa-sign-out-alt',
        'rows' => [
          ['Tanggal Discharge Administrasi', (string) ($p['tgl_perawatan'] ?? ($reg['tgl_registrasi'] ?? '')), 'Jam Discharge Administrasi', (string) ($p['jam_rawat'] ?? ($reg['jam_reg'] ?? ''))],
        ],
      ],
      'ttd_pasien_pj' => [
        'bab' => '27', 'label' => '27. Pasien / Penanggung Jawab (Nama & Tanda Tangan)', 'icon' => 'fa-signature',
        'rows' => [
          ['Nama Pasien / Penanggung Jawab', trim(($pasien['nm_pasien'] ?? '') . ' / ' . ($pasien['namakeluarga'] ?? '')), 'Tanda Tangan', ''],
        ],
      ],
      'ttd_dpjp' => [
        'bab' => '28', 'label' => '28. Dokter Penanggung Jawab Pelayanan (Nama & Tanda Tangan)', 'icon' => 'fa-user-md',
        'rows' => [
          ['Nama DPJP / Dokter Pelayanan', (string) ($dokter['nm_dokter'] ?? ''), 'Tanda Tangan', ''],
        ],
      ],
      'resume_medis' => [
        'bab' => '29', 'label' => '29. Resume Medis · Composition', 'icon' => 'fa-file-alt',
        'rows' => [
          ['Tipe Dokumen Resume Medis (Composition.type)', '11503-8 Medical records', '', ''],
          ['Ringkasan Medis', trim(($p['penilaian'] ?? '') . ' ' . ($p['evaluasi'] ?? '')), '', ''],
        ],
      ],
    ];
  }

  private function permintaanRows(array $permintaanList, array $dokter)
  {
    $rows = [];
    foreach ($permintaanList as $permintaan) {
      foreach ((array) ($permintaan['items'] ?? []) as $item) {
        $rows[] = [
          (string) ($permintaan['noorder'] ?? ''),
          trim((string) ($item['nama'] ?? '') . ' (' . (string) ($item['kd_jenis_prw'] ?? '') . ')'),
          trim((string) (($permintaan['tgl_permintaan'] ?? '') . ' ' . ($permintaan['jam_permintaan'] ?? ''))),
          (string) ($permintaan['dokter_perujuk'] ?? ($dokter['nm_dokter'] ?? '')),
        ];
      }
    }
    return $rows;
  }

  private function pemberianRows(array $emr)
  {
    $rows = [];
    foreach ((array) ($emr['pemberian_items'] ?? []) as $item) {
      $item = (array) $item;
      $rows[] = [
        trim(($item['tgl_perawatan'] ?? '') . ' ' . ($item['jam'] ?? '')),
        trim((string) ($item['map']['nama_kfa'] ?? '') ?: (string) ($item['nama_brng'] ?? '')),
        (string) ($item['jml'] ?? ''),
        trim(($item['aturan'] ?? '') . ($item['no_batch'] !== '' ? ' · Batches ' . $item['no_batch'] : '')),
      ];
    }
    return $rows ?: [['', 'Belum ada pengeluaran obat', '', '']];
  }

  // ==================================================================
  // 5. Daftar kunjungan rawat jalan
  // ==================================================================

  /**
   * Daftar kunjungan rawat jalan beserta status sinkronisasi ERM.
   */
  public function getVisitList(array $filters = [], $limit = 50, $offset = 0)
  {
    [$where, $binds] = $this->visitWhere($filters);
    return $this->fetchAll(
      "SELECT r.no_rawat, r.tgl_registrasi, r.jam_reg, r.no_rkm_medis, r.status_lanjut, r.stts, r.kd_poli,
              p.nm_pasien, pl.nm_poli, d.nm_dokter, pj.png_jawab,
              m.status_kirim, m.encounter_id, m.patient_id, m.tgl_kirim
       FROM reg_periksa r
       LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
       LEFT JOIN poliklinik pl ON pl.kd_poli = r.kd_poli
       LEFT JOIN dokter d ON d.kd_dokter = r.kd_dokter
       LEFT JOIN penjab pj ON pj.kd_pj = r.kd_pj
       LEFT JOIN " . SatuSehatResourceMappingService::TABLE_VISIT . " m ON m.no_rawat = r.no_rawat
       WHERE {$where}
       ORDER BY r.tgl_registrasi DESC, r.jam_reg DESC
       LIMIT " . (int) $limit . " OFFSET " . (int) $offset,
      $binds
    );
  }

  public function countVisits(array $filters = [])
  {
    [$where, $binds] = $this->visitWhere($filters);
    $row = $this->fetchOne(
      "SELECT COUNT(*) AS jumlah FROM reg_periksa r
       LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
       LEFT JOIN " . SatuSehatResourceMappingService::TABLE_VISIT . " m ON m.no_rawat = r.no_rawat
       WHERE {$where}",
      $binds
    );
    return (int) ($row['jumlah'] ?? 0);
  }

  protected function visitWhere(array $filters)
  {
    $where = ["r.status_lanjut = 'Ralan'"];
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
    if (!empty($filters['kd_poli'])) {
      $where[] = 'r.kd_poli = ?';
      $binds[] = (string) $filters['kd_poli'];
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
}
