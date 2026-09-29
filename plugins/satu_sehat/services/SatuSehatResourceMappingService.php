<?php

namespace Plugins\Satu_Sehat\Services;

use Plugins\Satu_Sehat\Resources\MedicationRequestBuilder;

/**
 * Pemetaan resource SATUSEHAT (Patient/Practitioner/Location/Organization, KFA, LOINC)
 * plus tabel tracking ERM (kunjungan, log sinkronisasi, mapping tindakan).
 *
 * Skema tabel resmi didaftarkan pada closure install di Info.php sesuai konvensi
 * plugin mLITE. ensureTables() tetap berjalan idempoten sebagai jaminan ketika
 * file plugin diperbarui tanpa instalasi ulang.
 */
class SatuSehatResourceMappingService
{
  const TABLE_VISIT = 'mlite_satu_sehat_erm_ralan';
  const TABLE_LOG   = 'mlite_satu_sehat_erm_log';
  const TABLE_TINDAKAN = 'mlite_satu_sehat_mapping_tindakan';

  /** @var \Systems\Main */
  private $core;

  /** @var SatuSehatAuthService */
  private $auth;

  public function __construct($core, SatuSehatAuthService $auth)
  {
    $this->core = $core;
    $this->auth = $auth;
    $this->ensureTables();
  }

  /** @return \PDO */
  private function pdo()
  {
    return $this->core->db()->pdo();
  }

  /**
   * Buat tabel tracking bila belum ada (kompatibel MySQL & SQLite).
   */
  public function ensureTables()
  {
    static $done = false;
    if ($done) {
      return;
    }
    $done = true;

    try {
      $this->pdo()->exec(
        'CREATE TABLE IF NOT EXISTS ' . self::TABLE_VISIT . ' (
          no_rawat VARCHAR(20) NOT NULL,
          patient_id VARCHAR(64) DEFAULT \'\',
          encounter_id VARCHAR(64) DEFAULT \'\',
          practitioner_id VARCHAR(64) DEFAULT \'\',
          location_id VARCHAR(64) DEFAULT \'\',
          organization_id VARCHAR(64) DEFAULT \'\',
          resource_map TEXT,
          status_kirim VARCHAR(20) DEFAULT \'belum\',
          tgl_kirim DATETIME DEFAULT NULL,
          keterangan TEXT,
          PRIMARY KEY (no_rawat)
        )'
      );
      $this->pdo()->exec(
        'CREATE TABLE IF NOT EXISTS ' . self::TABLE_LOG . ' (
          id VARCHAR(40) NOT NULL,
          no_rawat VARCHAR(20) DEFAULT \'\',
          status VARCHAR(20) DEFAULT \'\',
          http_code INTEGER DEFAULT 0,
          duration_ms INTEGER DEFAULT 0,
          jumlah_resource INTEGER DEFAULT 0,
          message TEXT,
          request TEXT,
          response TEXT,
          created_at DATETIME DEFAULT NULL,
          PRIMARY KEY (id)
        )'
      );
      $this->pdo()->exec(
        'CREATE TABLE IF NOT EXISTS ' . self::TABLE_TINDAKAN . ' (
          kd_jenis_prw VARCHAR(15) NOT NULL,
          kode_ktpl VARCHAR(50) DEFAULT \'\',
          nama_ktpl VARCHAR(255) DEFAULT \'\',
          PRIMARY KEY (kd_jenis_prw)
        )'
      );
    } catch (\Throwable $e) {
      // Skema lama dibiarkan apa adanya; query lain sudah defensive.
    }
  }

  // ------------------------------------------------------------------
  // Tracking per kunjungan (no_rawat)
  // ------------------------------------------------------------------

  public function getVisitMapping($no_rawat)
  {
    $no_rawat = trim((string) $no_rawat);
    if ($no_rawat === '') {
      return [];
    }
    try {
      $stmt = $this->pdo()->prepare('SELECT * FROM ' . self::TABLE_VISIT . ' WHERE no_rawat = ? LIMIT 1');
      $stmt->execute([$no_rawat]);
      $row = $stmt->fetch(\PDO::FETCH_ASSOC);
      return is_array($row) ? $row : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  public function saveVisitMapping($no_rawat, array $fields)
  {
    $no_rawat = trim((string) $no_rawat);
    if ($no_rawat === '' || empty($fields)) {
      return false;
    }
    $allowed = ['patient_id', 'encounter_id', 'practitioner_id', 'location_id', 'organization_id', 'resource_map', 'status_kirim', 'tgl_kirim', 'keterangan'];
    $values = [];
    foreach ($allowed as $field) {
      if (array_key_exists($field, $fields)) {
        $values[$field] = is_array($fields[$field]) ? json_encode($fields[$field], JSON_UNESCAPED_UNICODE) : (string) $fields[$field];
      }
    }
    if (empty($values)) {
      return false;
    }

    try {
      if ($this->getVisitMapping($no_rawat)) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($values)));
        $stmt = $this->pdo()->prepare('UPDATE ' . self::TABLE_VISIT . " SET $set WHERE no_rawat = ?");
        $values[] = $no_rawat;
        return $stmt->execute(array_values($values));
      }
      $values['no_rawat'] = $no_rawat;
      $cols = implode(', ', array_keys($values));
      $mask = implode(', ', array_fill(0, count($values), '?'));
      $stmt = $this->pdo()->prepare('INSERT INTO ' . self::TABLE_VISIT . " ($cols) VALUES ($mask)");
      return $stmt->execute(array_values($values));
    } catch (\Throwable $e) {
      return false;
    }
  }

  /**
   * Catat ID resource SATUSEHAT hasil sinkronisasi (per resourceType).
   */
  public function registerCreatedResources($no_rawat, array $created)
  {
    $current = $this->getVisitMapping($no_rawat);
    $map = [];
    if (!empty($current['resource_map'])) {
      $decoded = json_decode((string) $current['resource_map'], true);
      if (is_array($decoded)) {
        $map = $decoded;
      }
    }
    foreach ($created as $type => $ids) {
      $type = (string) $type;
      $existing = is_array($map[$type] ?? null) ? $map[$type] : [];
      foreach ((array) $ids as $id) {
        $id = (string) $id;
        if ($id !== '' && !in_array($id, $existing, true)) {
          $existing[] = $id;
        }
      }
      if (!empty($existing)) {
        $map[$type] = $existing;
      }
    }
    return $this->saveVisitMapping($no_rawat, ['resource_map' => $map]);
  }

  public function markSent($no_rawat, $status, $keterangan = '')
  {
    return $this->saveVisitMapping($no_rawat, [
      'status_kirim' => (string) $status,
      'tgl_kirim' => date('Y-m-d H:i:s'),
      'keterangan' => (string) $keterangan,
    ]);
  }

  // ------------------------------------------------------------------
  // Lookup pemetaan master
  // ------------------------------------------------------------------

  /** ID IHS praktisi dari mapping praktisi (kd_dokter). */
  public function practitionerId($kd_dokter)
  {
    try {
      $row = $this->core->db('mlite_satu_sehat_mapping_praktisi')
        ->select('practitioner_id')
        ->where('kd_dokter', (string) $kd_dokter)
        ->oneArray();
      return (string) ($row['practitioner_id'] ?? '');
    } catch (\Throwable $e) {
      return '';
    }
  }

  /** Daftar praktisi berdasarkan jenis_praktisi (mis. 'Apoteker'). */
  public function practitionersByJenis($jenis)
  {
    try {
      $rows = $this->core->db('mlite_satu_sehat_mapping_praktisi')
        ->select('practitioner_id', 'kd_dokter')
        ->where('jenis_praktisi', (string) $jenis)
        ->toArray();
      return is_array($rows) ? $rows : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  /** ID lokasi SATUSEHAT dari mapping lokasi (kode poli/kamar). */
  public function locationId($kode)
  {
    try {
      $row = $this->core->db('mlite_satu_sehat_lokasi')
        ->where('kode', (string) $kode)
        ->oneArray();
      return (string) ($row['id_lokasi_satusehat'] ?? '');
    } catch (\Throwable $e) {
      return '';
    }
  }

  /**
   * ID IHS pasien: diprioritaskan dari mapping kunjungan, fallback lookup NIK ke SATUSEHAT.
   *
   * @param array $emr Hasil SatuSehatErmRalanService::getEmr().
   * @param bool  $allowLookup Izinkan lookup online saat mapping belum ada.
   */
  public function patientId(array $emr, $allowLookup = true)
  {
    $no_rawat = (string) ($emr['no_rawat'] ?? '');
    $stored = (string) ($this->getVisitMapping($no_rawat)['patient_id'] ?? '');
    if ($stored !== '') {
      return $stored;
    }

    $nik = trim((string) ($emr['pasien']['no_ktp'] ?? ''));
    if ($nik === '' || !$allowLookup) {
      return '';
    }

    $id = $this->auth->lookupPatientId($nik);
    if ($id !== '') {
      $this->saveVisitMapping($no_rawat, ['patient_id' => $id]);
    }
    return $id;
  }

  /** Mapping obat lokal -> KFA (kode_kfa, nama_kfa, sediaan, route). */
  public function drug($kode_brng)
  {
    try {
      $row = $this->core->db('mlite_satu_sehat_mapping_obat')
        ->where('kode_brng', (string) $kode_brng)
        ->oneArray();
      return is_array($row) ? $row : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  /**
   * Mapping pemeriksaan lab lokal -> LOINC (code, display).
   * Bila $id_template diberikan, dipilih baris mapping milik template tersebut.
   */
  public function labTest($kd_jenis_prw, $id_template = null)
  {
    try {
      $rows = $this->core->db('mlite_satu_sehat_mapping_lab')
        ->where('kd_jenis_prw', (string) $kd_jenis_prw)
        ->toArray();
      if (!is_array($rows) || empty($rows)) {
        return [];
      }
      if ($id_template !== null && $id_template !== '') {
        foreach ($rows as $row) {
          if ((string) ($row['id_template'] ?? '') === (string) $id_template) {
            return $row;
          }
        }
      }
      return $rows[0];
    } catch (\Throwable $e) {
      return [];
    }
  }

  /** Mapping pemeriksaan radiologi lokal -> kode standar (code, display, system). */
  public function radiologyTest($kd_jenis_prw)
  {
    try {
      $row = $this->core->db('mlite_satu_sehat_mapping_rad')
        ->where('kd_jenis_prw', (string) $kd_jenis_prw)
        ->oneArray();
      return is_array($row) ? $row : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  // ------------------------------------------------------------------
  // Pemetaan tindakan (jns_perawatan) -> kode KPTL
  // ------------------------------------------------------------------

  /** Mapping tindakan lokal -> kode KPTL (kode_ktpl, nama_ktpl). */
  public function tindakan($kd_jenis_prw)
  {
    try {
      $row = $this->core->db(self::TABLE_TINDAKAN)
        ->where('kd_jenis_prw', (string) $kd_jenis_prw)
        ->oneArray();
      return is_array($row) ? $row : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  public function saveTindakanMapping($kd_jenis_prw, $kode_ktpl, $nama_ktpl = '')
  {
    $kd_jenis_prw = trim((string) $kd_jenis_prw);
    if ($kd_jenis_prw === '') {
      return false;
    }
    $data = [
      'kd_jenis_prw' => $kd_jenis_prw,
      'kode_ktpl' => trim((string) $kode_ktpl),
      'nama_ktpl' => trim((string) $nama_ktpl),
    ];
    try {
      return (bool) $this->core->db(self::TABLE_TINDAKAN)->save($data);
    } catch (\Throwable $e) {
      return false;
    }
  }

  public function deleteTindakanMapping($kd_jenis_prw)
  {
    try {
      return (bool) $this->core->db(self::TABLE_TINDAKAN)
        ->where('kd_jenis_prw', (string) $kd_jenis_prw)
        ->delete();
    } catch (\Throwable $e) {
      return false;
    }
  }

  /** Daftar mapping tindakan bergabung nama perawatan. */
  public function tindakanMappings()
  {
    try {
      $rows = $this->core->db(self::TABLE_TINDAKAN)
        ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = ' . self::TABLE_TINDAKAN . '.kd_jenis_prw')
        ->toArray();
      return is_array($rows) ? $rows : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  /**
   * Kode KPTL untuk sebuah tindakan (jns_perawatan.kd_jenis_prw).
   *
   * Urutan pencarian:
   * 1. Mapping eksplisit admin (mlite_satu_sehat_mapping_tindakan) — kode lokal
   *    seperti RJ048 dipetakan ke kode KPTL resmi.
   * 2. Codebook mlite_ktpl bila kebetulan kode tindakan = kode KPTL resmi.
   *
   * Hasil kosong berarti Procedure/ServiceRequest cukup memakai code.text,
   * karena coding kptl dengan kode tak dikenal ditolak validator (rule 10015).
   *
   * @return array{kode_ktpl:string,nama_ktpl:string}
   */
  public function kptlMap($kode)
  {
    $kode = trim((string) $kode);
    if ($kode === '') {
      return [];
    }
    $map = $this->tindakan($kode);
    $kodeKtpl = trim((string) ($map['kode_ktpl'] ?? ''));
    if ($kodeKtpl !== '') {
      $nama = trim((string) ($map['nama_ktpl'] ?? ''));
      if ($nama === '') {
        $nama = $this->ktplName($kodeKtpl);
      }
      return ['kode_ktpl' => $kodeKtpl, 'nama_ktpl' => $nama];
    }
    $nama = $this->ktplName($kode);
    return $nama !== '' ? ['kode_ktpl' => $kode, 'nama_ktpl' => $nama] : [];
  }

  /** Nama resmi sebuah kode KPTL dari codebook mlite_ktpl ('' bila tidak ada). */
  public function ktplName($kode_ktpl)
  {
    try {
      $row = $this->core->db('mlite_ktpl')->where('kode_ktpl', (string) $kode_ktpl)->oneArray();
      return trim((string) ($row['nama_ktpl'] ?? ''));
    } catch (\Throwable $e) {
      return '';
    }
  }

  // ------------------------------------------------------------------
  // Normalisasi isian mapping obat (satuan & rute)
  // ------------------------------------------------------------------

  /**
   * Alias satuan denominator (huruf besar, spasi/koma titik dinormalkan)
   * -> kode unit valid: UCUM atau SNOMED bentuk sediaan (dipakai KFA sebagai
   * satuan denominator, mis. '385057009' Film-coated tablet).
   */
  private static $satuanAlias = [
    'SYRUP' => 'mL', 'SIRUP' => 'mL', 'SUSPENSI' => 'mL', 'SUSPENSION' => 'mL',
    'EMULSI' => 'mL', 'CAIR' => 'mL', 'CAIRAN' => 'mL', 'LARUTAN' => 'mL',
    'SOLUSI' => 'mL', 'SOLUTION' => 'mL', 'LIQUID' => 'mL',
    'TABLET' => '385057009', 'TAB' => '385057009', 'KAPLET' => '385057009',
    'TABLET SALUT SELAPUT' => '385057009', 'TAB SALUT SELAPUT' => '385057009',
    'TABLET SALUT FILM' => '385057009', 'FILM COATED TABLET' => '385057009',
    'FILM COATED' => '385057009',
  ];

  /** Alias rute pemberian -> [kode ATC (http://www.whocc.no/atc), display]. */
  private static $routeAlias = [
    'ORAL' => ['oral', 'Oral'], 'PO' => ['oral', 'Oral'], 'P O' => ['oral', 'Oral'],
    'PER OS' => ['oral', 'Oral'], 'MULUT' => ['oral', 'Oral'],
    'MINUM' => ['oral', 'Oral'], 'DIMINUM' => ['oral', 'Oral'],
    'IM' => ['inj.intramuscular', 'Injection Intramuscular'],
    'INJ IM' => ['inj.intramuscular', 'Injection Intramuscular'],
    'INJEKSI IM' => ['inj.intramuscular', 'Injection Intramuscular'],
    'INTRAMUSKULAR' => ['inj.intramuscular', 'Injection Intramuscular'],
    'INTRAMUSCULAR' => ['inj.intramuscular', 'Injection Intramuscular'],
    'INJEKSI INTRAMUSKULAR' => ['inj.intramuscular', 'Injection Intramuscular'],
    'INJEKSI INTRAMUSCULAR' => ['inj.intramuscular', 'Injection Intramuscular'],
  ];

  private static function aliasKey($value)
  {
    return strtoupper(preg_replace('/[\s\.\-]+/', ' ', trim((string) $value)));
  }

  /**
   * Normalisasi satu nilai satuan denominator menjadi kode unit yang valid.
   *
   * @return array{0:string,1:bool} [kode baru, apakah berubah]
   */
  public static function normalizeSatuanDen($value)
  {
    $v = trim((string) $value);
    if ($v === '') {
      return ['', false];
    }
    if (preg_match('/^\d+$/', $v)) {
      return [$v, false]; // kode SNOMED — sudah sah
    }
    $ucum = MedicationRequestBuilder::ucumCode($v);
    if ($ucum !== null) {
      return [$ucum, $ucum !== $v]; // ejaan UCUM dikanonkan (ml -> mL)
    }
    $alias = self::$satuanAlias[self::aliasKey($v)] ?? '';
    return $alias !== '' ? [$alias, true] : [$v, false];
  }

  /**
   * Normalisasi satu kode rute pemberian (ATC).
   *
   * @return array{0:string,1:string,2:bool} [kode, display, apakah berubah]
   */
  public static function normalizeKodeRoute($value)
  {
    $v = trim((string) $value);
    if ($v === '') {
      return ['', '', false];
    }
    $alias = self::$routeAlias[self::aliasKey($v)] ?? null;
    if ($alias !== null) {
      return [$alias[0], $alias[1], $alias[0] !== $v];
    }
    return [$v, '', false];
  }

  /**
   * Rapikan isian satuan & rute pada seluruh mapping obat (bulk).
   *
   * @return array{satuan:int,route:int,lewat:array} Jumlah perubahan dan baris
   *         yang satuannya belum dapat dikodekan (perlu diisi manual).
   */
  public function normalisasiMappingObat()
  {
    $result = ['satuan' => 0, 'route' => 0, 'lewat' => []];
    try {
      $rows = $this->core->db('mlite_satu_sehat_mapping_obat')->toArray();
      foreach ((array) $rows as $row) {
        $kodeBrng = (string) ($row['kode_brng'] ?? '');
        $update = [];

        list($den, $denChanged) = self::normalizeSatuanDen($row['satuan_den'] ?? '');
        if ($denChanged) {
          $update['satuan_den'] = $den;
          $result['satuan']++;
        }

        list($route, $routeDisplay, $routeChanged) = self::normalizeKodeRoute($row['kode_route'] ?? '');
        if ($routeChanged) {
          $update['kode_route'] = $route;
          $result['route']++;
        }
        if ($routeDisplay !== '' && trim((string) ($row['nama_route'] ?? '')) === '') {
          $update['nama_route'] = $routeDisplay;
        }

        if (!empty($update)) {
          $this->core->db('mlite_satu_sehat_mapping_obat')
            ->where('kode_brng', $kodeBrng)
            ->save($update);
        }

        // Satuan yang tetap tidak dapat dikodekan -> isi manual via form mapping.
        if ($den !== '' && MedicationRequestBuilder::ucumCode($den) === null && !preg_match('/^\d+$/', $den)) {
          $result['lewat'][] = $kodeBrng . ' (' . trim((string) ($row['satuan_den'] ?? '')) . ')';
        }
      }
    } catch (\Throwable $e) {
      // tabel belum ada — biarkan hasil kosong
    }
    return $result;
  }

  /**
   * Ringkasan kesiapan mapping untuk halaman Mapping Resource.
   */
  public function counters()
  {
    $count = function ($table, $where = '1=1', array $binds = []) {
      try {
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}");
        $stmt->execute($binds);
        return (int) $stmt->fetchColumn();
      } catch (\Throwable $e) {
        return 0;
      }
    };

    return [
      'praktisi'      => $count('mlite_satu_sehat_mapping_praktisi'),
      'lokasi'        => $count('mlite_satu_sehat_lokasi', "COALESCE(id_lokasi_satusehat, '') <> ''"),
      'obat'          => $count('mlite_satu_sehat_mapping_obat'),
      'lab'           => $count('mlite_satu_sehat_mapping_lab'),
      'radiologi'     => $count('mlite_satu_sehat_mapping_rad'),
      'tindakan'      => $count(self::TABLE_TINDAKAN, "COALESCE(kode_ktpl, '') <> ''"),
      'erm_terkirim'  => $count(self::TABLE_VISIT, "status_kirim = 'terkirim'"),
    ];
  }
}
