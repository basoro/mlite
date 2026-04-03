<?php
namespace Plugins\Radiologi;

use Systems\AdminModule;
use Systems\Lib\QRCode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Admin extends AdminModule
{
  protected array $assign = [];

  public function navigation()
  {
    return [
      'Kelola' => 'manage',
    ];
  }

  public function apiList()
  {
    $username = $this->core->checkAuth('GET');
    if (!$this->core->checkPermission($username, 'can_read', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    $draw = $_GET['draw'] ?? 0;
    $start = $_GET['start'] ?? 0;
    $length = $_GET['length'] ?? 10;
    $columnIndex = $_GET['order'][0]['column'] ?? 0;
    $columnName = $_GET['columns'][$columnIndex]['data'] ?? 'no_rawat';
    $columnSortOrder = strtolower($_GET['order'][0]['dir'] ?? 'asc');

    $allowedColumns = ['no_reg', 'jam_reg', 'no_rkm_medis', 'kd_poli', 'kd_dokter', 'stts', 'status_bayar', 'kd_pj', 'nm_pasien', 'nm_dokter', 'nm_poli', 'png_jawab', 'no_rawat'];
    if (!in_array($columnName, $allowedColumns)) {
      $columnName = 'no_rawat';
    }
    if (!in_array($columnSortOrder, ['asc', 'desc'])) {
      $columnSortOrder = 'asc';
    }

    $searchValue = is_array($_GET['search'] ?? null) ? ($_GET['search']['value'] ?? '') : ($_GET['search'] ?? '');

    $tgl_kunjungan = $_GET['tgl_awal'] ?? date('Y-m-d');
    $tgl_kunjungan_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
    $status_periksa = $_GET['status_periksa'] ?? '';
    $type = $_GET['type'] ?? 'ralan';
    $status_bayar = $_GET['status_bayar'] ?? '';

    $params = [];
    if ($type == 'permintaan') {
      $sql = "SELECT permintaan_radiologi.*, reg_periksa.no_rkm_medis, pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab 
                    FROM permintaan_radiologi 
                    JOIN reg_periksa ON permintaan_radiologi.no_rawat = reg_periksa.no_rawat 
                    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
                    JOIN dokter ON permintaan_radiologi.dokter_perujuk = dokter.kd_dokter 
                    JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli 
                    JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj 
                    WHERE permintaan_radiologi.tgl_permintaan BETWEEN ? AND ?";
      $params[] = $tgl_kunjungan;
      $params[] = $tgl_kunjungan_akhir;

      if ($status_periksa != '') {
        $sql .= " AND permintaan_radiologi.status = ?";
        $params[] = ucfirst(strtolower($status_periksa));
      }
    } elseif ($type == 'ranap') {
      $sql = "SELECT kamar_inap.*, reg_periksa.*, pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab 
                    FROM kamar_inap 
                    JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat 
                    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
                    JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar 
                    JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal 
                    JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj 
                    LEFT JOIN dpjp_ranap ON dpjp_ranap.no_rawat = kamar_inap.no_rawat 
                    LEFT JOIN dokter ON dokter.kd_dokter = dpjp_ranap.kd_dokter 
                    LEFT JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli
                    WHERE 1=1";

      if ($status_periksa == '') {
        $sql .= " AND kamar_inap.stts_pulang = '-'";
      }
      if ($status_periksa == 'belum') {
        $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;
      }
      if ($status_periksa == 'selesai') {
        $sql .= " AND kamar_inap.stts_pulang != '-' AND kamar_inap.tgl_masuk BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;
      }
      if ($status_periksa == 'lunas') {
        $sql .= " AND kamar_inap.stts_pulang != '-' AND kamar_inap.tgl_keluar BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;
      }
    } else {
      $sql = "SELECT reg_periksa.*, pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab 
                    FROM reg_periksa 
                    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
                    JOIN dokter ON reg_periksa.kd_dokter = dokter.kd_dokter 
                    JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli 
                    JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj 
                    WHERE reg_periksa.tgl_registrasi BETWEEN ? AND ?";
      $params[] = $tgl_kunjungan;
      $params[] = $tgl_kunjungan_akhir;

      if ($status_periksa == 'belum') {
        $sql .= " AND reg_periksa.stts = 'Belum'";
      }
      if ($status_periksa == 'selesai') {
        $sql .= " AND reg_periksa.stts = 'Sudah'";
      }
      if ($status_periksa == 'lunas') {
        $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
      }
    }

    if (!empty($searchValue)) {
      $sql .= " AND (reg_periksa.no_rawat LIKE ? OR reg_periksa.no_rkm_medis LIKE ? OR pasien.nm_pasien LIKE ? OR dokter.nm_dokter LIKE ?)";
      $params[] = "%$searchValue%";
      $params[] = "%$searchValue%";
      $params[] = "%$searchValue%";
      $params[] = "%$searchValue%";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute($params);
    $totalRecords = $stmt->rowCount();

    $sql .= " ORDER BY $columnName $columnSortOrder LIMIT $start, $length";

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
      foreach ($row as $key => $value) {
        if (is_string($value)) {
          $row[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
      }
      if ($type == 'ranap') {
        $dpjp_ranap = $this->db('dpjp_ranap')
          ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();
        $row['dokter'] = htmlspecialchars_array($dpjp_ranap);
      }
    }
    unset($row);

    return [
      "status" => "success",
      "data" => $rows,
      "meta" => [
        "page" => floor($start / $length) + 1,
        "per_page" => intval($length),
        "total" => $totalRecords
      ]
    ];
  }

  public function apiShow($no_rawat = null)
  {
    $username = $this->core->checkAuth('GET');
    if (!$this->core->checkPermission($username, 'can_read', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    if (!$no_rawat) {
      return ['status' => 'error', 'message' => 'No rawat missing'];
    }
    $no_rawat = revertNoRawat($no_rawat);
    $row = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->where('no_rawat', $no_rawat)
      ->oneArray();

    if ($row) {
      return ['status' => 'success', 'data' => htmlspecialchars_array($row)];
    } else {
      return ['status' => 'error', 'message' => 'Not found'];
    }
  }

  public function apiSave()
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_create', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input))
      $input = $_POST;

    if (empty($input['no_rkm_medis'])) {
      return ['status' => 'error', 'message' => 'Data incomplete'];
    }

    $input['tgl_registrasi'] = $input['tgl_registrasi'] ?? date('Y-m-d');
    $input['jam_reg'] = $input['jam_reg'] ?? date('H:i:s');
    $input['kd_dokter'] = $input['kd_dokter'] ?? $this->settings->get('settings.pj_radiologi');

    try {
      if (!$this->db('reg_periksa')->where('no_rawat', $input['no_rawat'])->oneArray()) {
        $input['status_lanjut'] = 'Ralan';
        $input['stts'] = 'Belum';
        $input['status_bayar'] = 'Belum Bayar';
        $input['p_jawab'] = $input['p_jawab'] ?? '-';
        $input['almt_pj'] = $input['almt_pj'] ?? '-';
        $input['hubunganpj'] = $input['hubunganpj'] ?? '-';

        $poliklinik = $this->db('poliklinik')->where('kd_poli', $this->settings('settings', 'radiologi'))->oneArray();
        $input['biaya_reg'] = $poliklinik['registrasi'];

        $pasien = $this->db('pasien')->where('no_rkm_medis', $input['no_rkm_medis'])->oneArray();

        $birthDate = new \DateTime($pasien['tgl_lahir']);
        $today = new \DateTime("today");
        $umur_daftar = "0";
        $status_umur = 'Hr';
        if ($birthDate < $today) {
          $y = $today->diff($birthDate)->y;
          $m = $today->diff($birthDate)->m;
          $d = $today->diff($birthDate)->d;
          $umur_daftar = $d;
          $status_umur = "Hr";
          if ($y != '0') {
            $umur_daftar = $y;
            $status_umur = "Th";
          }
          if ($y == '0' && $m != '0') {
            $umur_daftar = $m;
            $status_umur = "Bl";
          }
        }

        $input['umurdaftar'] = $umur_daftar;
        $input['sttsumur'] = $status_umur;
        $input['status_poli'] = 'Lama';
        $input['kd_poli'] = $this->settings('settings', 'radiologi');

        unset($input['kat']);
        $this->db('reg_periksa')->save($input);
        return ['status' => 'created', 'data' => htmlspecialchars_array($input)];
      } else {
        $updateData = [];
        if (isset($input['kd_dokter']))
          $updateData['kd_dokter'] = $input['kd_dokter'];
        if (isset($input['kd_pj']))
          $updateData['kd_pj'] = $input['kd_pj'];

        $this->db('reg_periksa')->where('no_rawat', $input['no_rawat'])->update($updateData);
        return ['status' => 'updated', 'data' => htmlspecialchars_array($updateData)];
      }
    } catch (\PDOException $e) {
      $message = $e->getMessage();
      $message = preg_replace('/`[^`]+`\./', '', $message);
      return ['status' => 'error', 'message' => htmlspecialchars_array($message)];
    }
  }

  public function apiUpdate($no_rawat = null)
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_update', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    if (!$no_rawat) {
      return ['status' => 'error', 'message' => 'No rawat missing'];
    }

    $no_rawat = revertNoRawat($no_rawat);

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input))
      $input = $_POST;

    try {
      $this->db('reg_periksa')->where('no_rawat', $no_rawat)->update($input);
      return ['status' => 'updated', 'data' => htmlspecialchars_array($input)];
    } catch (\PDOException $e) {
      $message = $e->getMessage();
      $message = preg_replace('/`[^`]+`\./', '', $message);
      return ['status' => 'error', 'message' => htmlspecialchars_array($message)];
    }
  }

  public function apiDelete($no_rawat = null)
  {
    $username = $this->core->checkAuth('DELETE');
    if (!$this->core->checkPermission($username, 'can_delete', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    if (!$no_rawat) {
      return ['status' => 'error', 'message' => 'No rawat missing'];
    }
    $no_rawat = revertNoRawat($no_rawat);

    if (!$this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray()) {
      return ['status' => 'error', 'message' => 'No rawat not found'];
    }

    try {
      $this->db('reg_periksa')->where('no_rawat', $no_rawat)->delete();
      return ['status' => 'deleted', 'no_rawat' => $no_rawat];
    } catch (\PDOException $e) {
      $message = $e->getMessage();
      $message = preg_replace('/`[^`]+`\./', '', $message);
      return ['status' => 'error', 'message' => htmlspecialchars_array($message)];
    }
  }

  public function apiSaveDetail()
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_create', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input))
      $input = $_POST;

    if (empty($input['kat']) || empty($input['no_rawat']) || empty($input['kd_jenis_prw'])) {
      return ['status' => 'error', 'message' => 'Data incomplete'];
    }

    try {
      if ($input['kat'] == 'radiologi') {
        $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $input['kd_jenis_prw'])->oneArray();
        $this->db('periksa_radiologi')
          ->save([
            'no_rawat' => $input['no_rawat'],
            'nip' => $input['nip'] ?? $this->core->getUserInfo('username', null, true),
            'kd_jenis_prw' => $input['kd_jenis_prw'],
            'tgl_periksa' => $input['tgl_perawatan'],
            'jam' => $input['jam_rawat'],
            'dokter_perujuk' => $input['kode_provider'],
            'bagian_rs' => $jns_perawatan['bagian_rs'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
            'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
            'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya' => $jns_perawatan['total_byr'],
            'kd_dokter' => $input['kd_dokter'] ?? $this->settings->get('settings.pj_radiologi'),
            'status' => $input['status'],
            'proyeksi' => '',
            'kV' => '',
            'mAS' => '',
            'FFD' => '',
            'BSF' => '',
            'inak' => '',
            'jml_penyinaran' => '',
            'dosis' => ''
          ]);
        return ['status' => 'success', 'message' => 'Detail saved'];
      } else {
        return ['status' => 'error', 'message' => 'Category not supported'];
      }
    } catch (\PDOException $e) {
      $message = $e->getMessage();
      $message = preg_replace('/`[^`]+`\./', '', $message);
      return ['status' => 'error', 'message' => htmlspecialchars_array($message)];
    }
  }

  public function apiDeleteDetail()
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_delete', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input))
      $input = $_POST;

    if (empty($input['no_rawat']) || empty($input['kd_jenis_prw']) || empty($input['tgl_perawatan']) || empty($input['jam_rawat'])) {
      return ['status' => 'error', 'message' => 'Data incomplete'];
    }

    try {
      $this->db('periksa_radiologi')
        ->where('no_rawat', $input['no_rawat'])
        ->where('kd_jenis_prw', $input['kd_jenis_prw'])
        ->where('tgl_periksa', $input['tgl_perawatan'])
        ->where('jam', $input['jam_rawat'])
        ->where('status', 'Ralan')
        ->delete();

      return ['status' => 'success', 'message' => 'Detail deleted'];
    } catch (\PDOException $e) {
      $message = $e->getMessage();
      $message = preg_replace('/`[^`]+`\./', '', $message);
      return ['status' => 'error', 'message' => htmlspecialchars_array($message)];
    }
  }

  public function apiSaveValidasi()
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_create', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input))
      $input = $_POST;

    if (empty($input['no_rawat']) || empty($input['noorder']) || empty($input['tgl_permintaan']) || empty($input['jam_permintaan'])) {
      return ['status' => 'error', 'message' => 'Data incomplete'];
    }

    try {
      $permintaan_radiologi = $this->db('permintaan_radiologi')->where('no_rawat', $input['no_rawat'])->where('noorder', $input['noorder'])->oneArray();
      $this->db('permintaan_radiologi')->where('no_rawat', $input['no_rawat'])->where('noorder', $input['noorder'])->save(['tgl_sampel' => date('Y-m-d'), 'jam_sampel' => date('H:i:s')]);
      $permintaan_pemeriksaan_radiologi = $this->db('permintaan_pemeriksaan_radiologi')->where('noorder', $input['noorder'])->toArray();
      foreach ($permintaan_pemeriksaan_radiologi as $row) {
        $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
        $this->db('periksa_radiologi')
          ->save([
            'no_rawat' => $input['no_rawat'],
            'nip' => $this->core->getUserInfo('username', null, true),
            'kd_jenis_prw' => $row['kd_jenis_prw'],
            'tgl_periksa' => $input['tgl_permintaan'],
            'jam' => $input['jam_permintaan'],
            'dokter_perujuk' => $permintaan_radiologi['dokter_perujuk'],
            'bagian_rs' => $jns_perawatan['bagian_rs'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
            'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
            'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya' => $jns_perawatan['total_byr'],
            'kd_dokter' => $this->settings->get('settings.pj_radiologi'),
            'status' => $input['status'] ?? 'Ralan',
            'proyeksi' => '',
            'kV' => '',
            'mAS' => '',
            'FFD' => '',
            'BSF' => '',
            'inak' => '',
            'jml_penyinaran' => '',
            'dosis' => ''
          ]);
      }
      return ['status' => 'success', 'message' => 'Permintaan validated'];
    } catch (\PDOException $e) {
      $message = $e->getMessage();
      $message = preg_replace('/`[^`]+`\./', '', $message);
      return ['status' => 'error', 'message' => htmlspecialchars_array($message)];
    }
  }

  public function apiShowDetail($status = null, $no_rawat = null, $noorder = null)
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_read', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    if (!$no_rawat) {
      return ['status' => 'error', 'message' => 'No rawat missing'];
    }
    $no_rawat = revertNorawat($no_rawat);
    $status = trim($status);

    $pasien = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
      ->where('no_rawat', $no_rawat)
      ->oneArray();

    $patient_info = [
      'nm_pasien' => $pasien['nm_pasien'] ?? '',
      'no_rkm_medis' => $pasien['no_rkm_medis'] ?? ''
    ];

    try {
      $query = $this->db('permintaan_radiologi')
        ->join('permintaan_pemeriksaan_radiologi', 'permintaan_pemeriksaan_radiologi.noorder = permintaan_radiologi.noorder')
        ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw = permintaan_pemeriksaan_radiologi.kd_jenis_prw')
        ->where('permintaan_radiologi.no_rawat', $no_rawat);

      if ($noorder) {
        $query->where('permintaan_radiologi.noorder', $noorder);
      }

      $permintaan_radiologi = $query->toArray();

      return [
        'status' => 'success',
        'patient' => $patient_info,
        'data' => [
          'permintaan_radiologi' => htmlspecialchars_array($permintaan_radiologi)
        ]
      ];
    } catch (\PDOException $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function apiSaveHasil()
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_update', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input))
      $input = $_POST;

    try {
      $hasil = $this->db('hasil_radiologi')
        ->save([
          'no_rawat' => $input['no_rawat'],
          'tgl_periksa' => $input['tgl_periksa'],
          'jam' => $input['jam'],
          'hasil' => $input['hasil']
        ]);

      if ($hasil) {
        return ['status' => 'success', 'message' => 'Hasil saved'];
      }
      return ['status' => 'error', 'message' => 'Failed to save hasil'];
    } catch (\PDOException $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function apiDeleteHasil()
  {
    $username = $this->core->checkAuth('POST');
    if (!$this->core->checkPermission($username, 'can_delete', 'radiologi')) {
      return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input))
      $input = $_POST;

    try {
      $this->db('hasil_radiologi')
        ->where('no_rawat', $input['no_rawat'])
        ->where('tgl_periksa', $input['tgl_perawatan'])
        ->where('jam', $input['jam_rawat'])
        ->delete();
      $this->db('gambar_radiologi')
        ->where('no_rawat', $input['no_rawat'])
        ->where('tgl_periksa', $input['tgl_perawatan'])
        ->where('jam', $input['jam_rawat'])
        ->delete();
      return ['status' => 'success', 'message' => 'Hasil deleted'];
    } catch (\PDOException $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function anyManage($type = "ralan")
  {
    $tgl_kunjungan = date('Y-m-d');
    $tgl_kunjungan_akhir = date('Y-m-d');
    $status_periksa = '';
    $status_bayar = '';
    $status_pulang = '';

    if (isset($_POST['periode_rawat_jalan'])) {
      $tgl_kunjungan = $_POST['periode_rawat_jalan'];
    }
    if (isset($_POST['periode_rawat_jalan_akhir'])) {
      $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
    }
    if (isset($_POST['status_periksa'])) {
      $status_periksa = $_POST['status_periksa'];
    }
    if (isset($_POST['status_bayar'])) {
      $status_bayar = $_POST['status_bayar'];
    }
    $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
    $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_pulang, $status_bayar, $type);
    return $this->draw('manage.html', ['rawat_jalan' => htmlspecialchars_array($this->assign), 'cek_vclaim' => htmlspecialchars_array($cek_vclaim), 'type' => $type, 'no_rawat_baru' => '', 'no_reg_baru' => '']);
  }

  public function anyDisplay()
  {
    $tgl_kunjungan = date('Y-m-d');
    $tgl_kunjungan_akhir = date('Y-m-d');
    $status_periksa = '';
    $status_bayar = '';
    $status_pulang = '';
    $type = htmlspecialchars(isset_or($_POST['status']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    if (isset($_POST['periode_rawat_jalan'])) {
      $tgl_kunjungan = $_POST['periode_rawat_jalan'];
    }
    if (isset($_POST['periode_rawat_jalan_akhir'])) {
      $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
    }
    if (isset($_POST['status_periksa'])) {
      $status_periksa = $_POST['status_periksa'];
    }
    $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
    $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_pulang, $status_bayar, $type);
    echo $this->draw('display.html', ['rawat_jalan' => htmlspecialchars_array($this->assign), 'cek_vclaim' => htmlspecialchars_array($cek_vclaim), 'type' => $type, 'no_rawat_baru' => '', 'no_reg_baru' => '']);
    exit();
  }

  public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa = '', $status_pulang = '', $status_bayar = '', $type = '')
  {
    $this->_addHeaderFiles();

    $this->assign['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
    $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
    $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
    $this->assign['no_rawat'] = '';
    $this->assign['no_reg'] = '';
    $this->assign['no_rawat_baru'] = '';
    $this->assign['no_reg_baru'] = '';
    $this->assign['tgl_registrasi'] = date('Y-m-d');
    $this->assign['jam_reg'] = date('H:i:s');

    $params = [];
    $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
          AND reg_periksa.kd_dokter = dokter.kd_dokter
          AND reg_periksa.kd_poli = poliklinik.kd_poli
          AND reg_periksa.kd_pj = penjab.kd_pj";
    $params[] = $tgl_kunjungan;
    $params[] = $tgl_kunjungan_akhir;

    if ($status_periksa == 'belum') {
      $sql .= " AND reg_periksa.stts = 'Belum'";
    }
    if ($status_periksa == 'selesai') {
      $sql .= " AND reg_periksa.stts = 'Sudah'";
    }
    if ($status_periksa == 'lunas') {
      $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
    }

    if ($type == 'permintaan') {
      $sql = "SELECT permintaan_radiologi.*, reg_periksa.no_rkm_medis, pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab 
                    FROM permintaan_radiologi 
                    JOIN reg_periksa ON permintaan_radiologi.no_rawat = reg_periksa.no_rawat 
                    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
                    JOIN dokter ON permintaan_radiologi.dokter_perujuk = dokter.kd_dokter 
                    JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli 
                    JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj 
                    WHERE permintaan_radiologi.tgl_permintaan BETWEEN ? AND ?";
      $params[] = $tgl_kunjungan;
      $params[] = $tgl_kunjungan_akhir;

      if ($status_periksa != '') {
        $sql .= " AND permintaan_radiologi.status = ?";
        $params[] = ucfirst(strtolower($status_periksa));
      }
    } elseif ($type == 'ranap') {
      $sql = "SELECT
              kamar_inap.*,
              reg_periksa.*,
              pasien.*,
              kamar.*,
              bangsal.*,
              penjab.*
            FROM
              kamar_inap,
              reg_periksa,
              pasien,
              kamar,
              bangsal,
              penjab
            WHERE
              kamar_inap.no_rawat=reg_periksa.no_rawat
            AND
              reg_periksa.no_rkm_medis=pasien.no_rkm_medis
            AND
              kamar_inap.kd_kamar=kamar.kd_kamar
            AND
              bangsal.kd_bangsal=kamar.kd_bangsal
            AND
              reg_periksa.kd_pj=penjab.kd_pj";
      $params = []; // Reset params for new query

      if ($status_periksa == '') {
        $sql .= " AND kamar_inap.stts_pulang = '-'";
      }
      if ($status_periksa == 'belum') {
        $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;
      }
      if ($status_periksa == 'selesai') {
        $sql .= " AND kamar_inap.stts_pulang != '-' AND kamar_inap.tgl_masuk BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;
      }
      if ($status_periksa == 'lunas') {
        $sql .= " AND kamar_inap.stts_pulang != '-' AND kamar_inap.tgl_keluar BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;
      }

    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
      $dpjp_ranap = $this->db('dpjp_ranap')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->where('no_rawat', $row['no_rawat'])
        ->toArray();
      $row['dokter'] = $dpjp_ranap;
      $this->assign['list'][] = $row;
    }

    if (isset($_POST['no_rawat'])) {
      $this->assign['reg_periksa'] = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rawat', $_POST['no_rawat'])
        ->oneArray();
      if ($type == 'permintaan') {
        $sql = "SELECT permintaan_radiologi.*, reg_periksa.no_rkm_medis, pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab 
                    FROM permintaan_radiologi 
                    JOIN reg_periksa ON permintaan_radiologi.no_rawat = reg_periksa.no_rawat 
                    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
                    JOIN dokter ON permintaan_radiologi.dokter_perujuk = dokter.kd_dokter 
                    JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli 
                    JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj 
                    WHERE permintaan_radiologi.tgl_permintaan BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;

        if ($status_periksa != '') {
          $sql .= " AND permintaan_radiologi.status = ?";
          $params[] = ucfirst(strtolower($status_periksa));
        }
      } elseif ($type == 'ranap') {
        $this->assign['kamar_inap'] = $this->db('kamar_inap')
          ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
          ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
          ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
          ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
          ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
          ->oneArray();
      }
    } else {
      $this->assign['reg_periksa'] = [
        'no_rkm_medis' => '',
        'nm_pasien' => '',
        'no_reg' => '',
        'no_rawat' => '',
        'tgl_registrasi' => '',
        'jam_reg' => '',
        'kd_dokter' => '',
        'no_rm' => '',
        'kd_poli' => '',
        'p_jawab' => '',
        'almt_pj' => '',
        'hubunganpj' => '',
        'biaya_reg' => '',
        'stts' => '',
        'stts_daftar' => '',
        'status_lanjut' => '',
        'kd_pj' => '',
        'umurdaftar' => '',
        'sttsumur' => '',
        'status_bayar' => '',
        'status_poli' => '',
        'nm_pasien' => '',
        'tgl_lahir' => '',
        'jk' => '',
        'alamat' => '',
        'no_tlp' => '',
        'pekerjaan' => ''
      ];
      if ($type == 'permintaan') {
        $sql = "SELECT permintaan_radiologi.*, reg_periksa.no_rkm_medis, pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab 
                    FROM permintaan_radiologi 
                    JOIN reg_periksa ON permintaan_radiologi.no_rawat = reg_periksa.no_rawat 
                    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
                    JOIN dokter ON permintaan_radiologi.dokter_perujuk = dokter.kd_dokter 
                    JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli 
                    JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj 
                    WHERE permintaan_radiologi.tgl_permintaan BETWEEN ? AND ?";
        $params[] = $tgl_kunjungan;
        $params[] = $tgl_kunjungan_akhir;

        if ($status_periksa != '') {
          $sql .= " AND permintaan_radiologi.status = ?";
          $params[] = ucfirst(strtolower($status_periksa));
        }
      } elseif ($type == 'ranap') {
        $this->assign['reg_periksa'] = [
          'tgl_masuk' => date('Y-m-d'),
          'jam_masuk' => date('H:i:s'),
          'tgl_keluar' => date('Y-m-d'),
          'jam_keluar' => date('H:i:s'),
          'no_rkm_medis' => '',
          'nm_pasien' => '',
          'tgl_lahir' => '',
          'jk' => '',
          'alamat' => '',
          'no_tlp' => '',
          'no_rawat' => '',
          'no_reg' => '',
          'kd_dokter' => '',
          'kd_kamar' => '',
          'kd_pj' => '',
          'diagnosa_awal' => '',
          'diagnosa_akhir' => '',
          'stts_pulang' => '',
          'lama' => ''
        ];
      }
    }
  }

  public function anyForm()
  {

    $this->assign['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
    $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
    $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
    $this->assign['no_rawat'] = '';
    $this->assign['no_reg'] = '';
    $this->assign['no_rawat_baru'] = '';
    $this->assign['no_reg_baru'] = '';
    $this->assign['tgl_registrasi'] = date('Y-m-d');
    $this->assign['jam_reg'] = date('H:i:s');
    if (isset($_POST['no_rawat'])) {
      $no_rawat = htmlspecialchars($_POST['no_rawat'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $this->assign['reg_periksa'] = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      echo $this->draw('form.html', [
        'rawat_jalan' => htmlspecialchars_array($this->assign),
        'no_rawat_baru' => '',
        'no_reg_baru' => ''
      ]);
    } else {
      $this->assign['reg_periksa'] = [
        'no_rkm_medis' => '',
        'nm_pasien' => '',
        'no_reg' => '',
        'no_rawat' => '',
        'tgl_registrasi' => '',
        'jam_reg' => '',
        'kd_dokter' => '',
        'no_rm' => '',
        'kd_poli' => '',
        'p_jawab' => '',
        'almt_pj' => '',
        'hubunganpj' => '',
        'biaya_reg' => '',
        'stts' => '',
        'stts_daftar' => '',
        'status_lanjut' => '',
        'kd_pj' => '',
        'umurdaftar' => '',
        'sttsumur' => '',
        'status_bayar' => '',
        'status_poli' => '',
        'nm_pasien' => '',
        'tgl_lahir' => '',
        'jk' => '',
        'alamat' => '',
        'no_tlp' => '',
        'pekerjaan' => ''
      ];
      echo $this->draw('form.html', [
        'rawat_jalan' => htmlspecialchars_array($this->assign),
        'no_rawat_baru' => '',
        'no_reg_baru' => ''
      ]);
    }
    exit();
  }

  public function anyStatusDaftar()
  {
    if (isset($_POST['no_rkm_medis'])) {
      $no_rkm_medis = htmlspecialchars($_POST['no_rkm_medis'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $rawat = $this->db('reg_periksa')
        ->where('no_rkm_medis', $no_rkm_medis)
        ->where('status_bayar', 'Belum Bayar')
        ->limit(1)
        ->oneArray();
      if ($rawat) {
        $stts_daftar = "Transaki tanggal " . date('Y-m-d', strtotime($rawat['tgl_registrasi'])) . " belum diselesaikan";
        $stts_daftar_hidden = $stts_daftar;
        if ($this->settings->get('settings.cekstatusbayar') == 'false') {
          $stts_daftar_hidden = 'Lama';
        }
        $bg_status = 'text-danger';
      } else {
        $result = $this->db('reg_periksa')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        if ($result >= 1) {
          $stts_daftar = 'Lama';
          $bg_status = 'text-info';
          $stts_daftar_hidden = $stts_daftar;
        } else {
          $stts_daftar = 'Baru';
          $bg_status = 'text-success';
          $stts_daftar_hidden = $stts_daftar;
        }
      }
      echo $this->draw('stts.daftar.html', ['stts_daftar' => htmlspecialchars($stts_daftar, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), 'stts_daftar_hidden' => htmlspecialchars($stts_daftar_hidden, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), 'bg_status' => htmlspecialchars($bg_status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')]);
    } else {
      $no_rawat = htmlspecialchars($_POST['no_rawat'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $rawat = $this->db('reg_periksa')
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      echo $this->draw('stts.daftar.html', [
        'stts_daftar' => htmlspecialchars($rawat['stts_daftar'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        'stts_daftar_hidden' => htmlspecialchars($rawat['stts_daftar'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        'bg_status' => ''
      ]);
    }
    exit();
  }

  public function postSave()
  {
    if (!$this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {

      $_POST['status_lanjut'] = 'Ralan';
      $_POST['stts'] = 'Belum';
      $_POST['status_bayar'] = 'Belum Bayar';
      $_POST['p_jawab'] = '-';
      $_POST['almt_pj'] = '-';
      $_POST['hubunganpj'] = '-';

      $poliklinik = $this->db('poliklinik')->where('kd_poli', $this->settings('settings', 'radiologi'))->oneArray();

      $_POST['biaya_reg'] = $poliklinik['registrasi'];

      $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();

      $birthDate = new \DateTime($pasien['tgl_lahir']);
      $today = new \DateTime("today");
      $umur_daftar = "0";
      $status_umur = 'Hr';
      if ($birthDate < $today) {
        $y = $today->diff($birthDate)->y;
        $m = $today->diff($birthDate)->m;
        $d = $today->diff($birthDate)->d;
        $umur_daftar = $d;
        $status_umur = "Hr";
        if ($y != '0') {
          $umur_daftar = $y;
          $status_umur = "Th";
        }
        if ($y == '0' && $m != '0') {
          $umur_daftar = $m;
          $status_umur = "Bl";
        }
      }

      $_POST['umurdaftar'] = $umur_daftar;
      $_POST['sttsumur'] = $status_umur;
      $_POST['status_poli'] = 'Lama';
      $_POST['kd_poli'] = $this->settings('settings', 'radiologi');

      $query = $this->db('reg_periksa')->save($_POST);
    } else {
      $query = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
        'kd_dokter' => $_POST['kd_dokter'],
        'kd_pj' => $_POST['kd_pj']
      ]);
    }
    exit();
  }

  public function anyPasien()
  {
    if (isset($_POST['cari'])) {
      $cari = htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $pasien = $this->db('pasien')
        ->like('no_rkm_medis', '%' . $cari . '%')
        ->orLike('nm_pasien', '%' . $cari . '%')
        ->asc('no_rkm_medis')
        ->limit(5)
        ->toArray();
    }
    echo $this->draw('pasien.html', ['pasien' => htmlspecialchars_array($pasien)]);
    exit();
  }

  public function getAntrian()
  {
    $settings = $this->settings('settings');
    $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
    $no_rawat = htmlspecialchars($_GET['no_rawat'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $rawat_jalan = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->where('no_rawat', $no_rawat)
      ->oneArray();
    echo $this->draw('antrian.html', ['rawat_jalan' => htmlspecialchars_array($rawat_jalan)]);
    exit();
  }

  public function getCetakHasil()
  {
    /* =======================
     * SETTINGS & DATA DASAR
     * ======================= */
    $settings = $this->settings('settings');
    $this->tpl->set(
      'settings',
      $this->tpl->noParse_array(htmlspecialchars_array($settings))
    );

    $no_rawat = $_GET['no_rawat'];
    $tgl = $_GET['tgl_periksa'];
    $jam = $_GET['jam'];
    $status = $_GET['status'];

    /* =======================
     * PJ RADIOLOGI + QR
     * ======================= */
    $pj_radiologi = $this->db('dokter')
      ->where('kd_dokter', $this->settings->get('settings.pj_radiologi'))
      ->oneArray();

    $qr = QRCode::getMinimumQRCode(
      $pj_radiologi['nm_dokter'],
      QR_ERROR_CORRECT_LEVEL_L
    );
    $im = $qr->createImage(4, 4);
    $qrPath = BASE_DIR . '/' . ADMIN . '/tmp/qrcode.png';
    imagepng($im, $qrPath);
    imagedestroy($im);

    $qrCode = url() . '/' . ADMIN . '/tmp/qrcode.png';

    /* =======================
     * DATA PASIEN
     * ======================= */
    $pasien = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->where('no_rawat', $no_rawat)
      ->oneArray();

    $dokter_perujuk = $this->db('periksa_radiologi')
      ->join('pegawai', 'pegawai.nik=periksa_radiologi.dokter_perujuk')
      ->where('no_rawat', $no_rawat)
      ->where('tgl_periksa', $tgl)
      ->where('jam', $jam)
      ->oneArray();

    /* =======================
     * PERIKSA RADIOLOGI
     * ======================= */
    $rows = $this->db('periksa_radiologi')
      ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
      ->join('reg_periksa', 'reg_periksa.no_rawat=periksa_radiologi.no_rawat')
      ->join('dokter', 'dokter.kd_dokter=periksa_radiologi.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->join('petugas', 'petugas.nip=periksa_radiologi.nip')
      ->where('periksa_radiologi.status', $status)
      ->where('periksa_radiologi.no_rawat', $no_rawat)
      ->where('periksa_radiologi.tgl_periksa', $tgl)
      ->where('periksa_radiologi.jam', $jam)
      ->toArray();

    $periksa_radiologi = [];
    $jumlah_total_radiologi = 0;
    $no = 1;

    foreach ($rows as $row) {
      $jumlah_total_radiologi += $row['biaya'];
      $row['nomor'] = $no++;
      $row['status_periksa'] = $status;
      $periksa_radiologi[] = $row;
    }

    $hasil_radiologi = $this->db('hasil_radiologi')
      ->where('no_rawat', $no_rawat)
      ->where('tgl_periksa', $tgl)
      ->where('jam', $jam)
      ->toArray();

    $gambar_radiologi = $this->db('gambar_radiologi')
      ->where('no_rawat', $no_rawat)
      ->where('tgl_periksa', $tgl)
      ->where('jam', $jam)
      ->toArray();

    /* =======================
     * FILENAME PDF
     * ======================= */
    $filename = convertNorawat($dokter_perujuk['no_rawat'])
      . '_' . $dokter_perujuk['kd_jenis_prw']
      . '_' . $dokter_perujuk['tgl_periksa'];

    $pdfPath = UPLOADS . '/radiologi/' . $filename . '.pdf';
    if (file_exists($pdfPath)) {
      unlink($pdfPath);
    }

    /* =======================
     * INJECT KE TEMPLATE
     * ======================= */


    echo $this->draw('cetakhasil.html', [
      'periksa_radiologi' => $periksa_radiologi,
      'hasil_radiologi' => $hasil_radiologi,
      'gambar_radiologi' => $gambar_radiologi,
      'jumlah_total_radiologi' => $jumlah_total_radiologi,
      'qrCode' => $qrCode,
      'pj_radiologi' => $pj_radiologi['nm_dokter'],
      'dokter_perujuk' => htmlspecialchars_array($dokter_perujuk)['nama'],
      'pasien' => $pasien,
      'filename' => $filename,
      'no_rawat' => htmlspecialchars($_GET['no_rawat'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
      'wagateway' => $this->settings->get('wagateway')
    ]);

    $this->tpl->set('periksa_radiologi', $periksa_radiologi);
    $this->tpl->set('hasil_radiologi', $hasil_radiologi);
    $this->tpl->set('gambar_radiologi', $gambar_radiologi);
    $this->tpl->set('jumlah_total_radiologi', $jumlah_total_radiologi);
    $this->tpl->set('qrCode', $qrCode);
    $this->tpl->set('pj_radiologi', $pj_radiologi['nm_dokter']);
    $this->tpl->set('dokter_perujuk', $dokter_perujuk['nama']);
    $this->tpl->set('pasien', $pasien);
    $this->tpl->set('filename', $filename);
    $this->tpl->set('no_rawat', $no_rawat);
    $this->tpl->set('wagateway', $this->settings->get('wagateway'));

    // render HTML TANPA draw()
    $html = $this->draw('cetakhasil.html');

    /* =======================
     * mPDF
     * ======================= */
    $mpdf = new \Mpdf\Mpdf([
      'mode' => 'utf-8',
      'format' => 'A4',
      'orientation' => 'P'
    ]);

    $mpdf->SetHTMLHeader($this->core->setPrintHeader());
    $mpdf->SetHTMLFooter($this->core->setPrintFooter());

    $css = '
            del { display:none; }
            table { padding-top:1cm; padding-bottom:1cm; }
            td, th { border-bottom:1px solid #ddd; padding:5px; }
        ';

    $mpdf->WriteHTML(
      $this->core->setPrintCss(),
      \Mpdf\HTMLParserMode::HEADER_CSS
    );
    $mpdf->WriteHTML('<style>' . $css . '</style>');
    $mpdf->WriteHTML($html);

    // simpan ke server
    $mpdf->Output($pdfPath, 'F');
    exit;
  }

  public function getCetakPermintaan()
  {
    $settings = $this->settings('settings');
    $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
    $pj_radiologi = $this->db('dokter')->where('kd_dokter', $this->settings->get('settings.pj_radiologi'))->oneArray();

    $qr = QRCode::getMinimumQRCode($pj_radiologi['nm_dokter'], QR_ERROR_CORRECT_LEVEL_L);
    $im = $qr->createImage(4, 4);
    imagepng($im, BASE_DIR . '/' . ADMIN . '/tmp/qrcode.png');
    imagedestroy($im);
    $qrCode = url() . "/" . ADMIN . "/tmp/qrcode.png";

    $pasien = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->where('no_rawat', $_GET['no_rawat'])
      ->oneArray();
    $dokter_perujuk = $this->db('permintaan_radiologi')
      ->join('pegawai', 'pegawai.nik=permintaan_radiologi.dokter_perujuk')
      ->where('no_rawat', $_GET['no_rawat'])
      ->where('permintaan_radiologi.status', strtolower($_GET['status']))
      ->group('no_rawat')
      ->oneArray();

    $rows_permintaan_radiologi = $this->db('permintaan_radiologi')
      ->join('dokter', 'dokter.kd_dokter=permintaan_radiologi.dokter_perujuk')
      ->where('no_rawat', $_GET['no_rawat'])
      ->where('permintaan_radiologi.status', strtolower($_GET['status']))
      ->toArray();
    $permintaan_radiologi = [];
    foreach ($rows_permintaan_radiologi as $row) {
      $rows2 = $this->db('permintaan_pemeriksaan_radiologi')
        ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=permintaan_pemeriksaan_radiologi.kd_jenis_prw')
        ->where('permintaan_pemeriksaan_radiologi.noorder', $row['noorder'])
        ->toArray();
      foreach ($rows2 as $row2) {
        $row2['noorder'] = $row2['noorder'];
        $row2['kd_jenis_prw'] = $row2['kd_jenis_prw'];
        $row2['stts_bayar'] = $row2['stts_bayar'];
        $row2['nm_perawatan'] = $row2['nm_perawatan'];
        $row2['kd_pj'] = $row2['kd_pj'];
        $row2['status'] = $row2['status'];
        $row2['kelas'] = $row2['kelas'];
        $row['permintaan_pemeriksaan_radiologi'][] = $row2;
      }
      $permintaan_radiologi[] = $row;
    }

    echo $this->draw('cetakpermintaan.html', [
      'permintaan_radiologi' => htmlspecialchars_array($permintaan_radiologi),
      'qrCode' => htmlspecialchars($qrCode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
      'pj_radiologi' => htmlspecialchars($pj_radiologi['nm_dokter'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
      'dokter_perujuk' => htmlspecialchars($dokter_perujuk['nama'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
      'pasien' => htmlspecialchars_array($pasien),
      'no_rawat' => htmlspecialchars($_GET['no_rawat'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
    ]);
    exit();
  }

  public function postHapus()
  {
    $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->delete();
    exit();
  }

  public function postSaveDetail()
  {

    if ($_POST['kat'] == 'radiologi') {
      $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
      $this->db('periksa_radiologi')
        ->save([
          'no_rawat' => $_POST['no_rawat'],
          'nip' => $this->core->getUserInfo('username', null, true),
          'kd_jenis_prw' => $_POST['kd_jenis_prw'],
          'tgl_periksa' => $_POST['tgl_perawatan'],
          'jam' => $_POST['jam_rawat'],
          'dokter_perujuk' => $_POST['kode_provider'],
          'bagian_rs' => $jns_perawatan['bagian_rs'],
          'bhp' => $jns_perawatan['bhp'],
          'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
          'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
          'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
          'kso' => $jns_perawatan['kso'],
          'menejemen' => $jns_perawatan['menejemen'],
          'biaya' => $jns_perawatan['total_byr'],
          'kd_dokter' => $this->settings->get('settings.pj_radiologi'),
          'status' => $_POST['status'],
          'proyeksi' => '',
          'kV' => '',
          'mAS' => '',
          'FFD' => '',
          'BSF' => '',
          'inak' => '',
          'jml_penyinaran' => '',
          'dosis' => ''
        ]);
    }

    exit();
  }

  public function postHapusRadiologi()
  {
    $this->db('periksa_radiologi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->where('status', 'Ralan')
      ->delete();
    exit();
  }

  public function postHapusHasilRadiologi()
  {
    $this->db('hasil_radiologi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->delete();
    $this->db('gambar_radiologi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->delete();
    exit();
  }

  public function anyRincian()
  {

    $rows = $this->db('permintaan_radiologi')
      ->join('dokter', 'dokter.kd_dokter=permintaan_radiologi.dokter_perujuk')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('permintaan_radiologi.status', strtolower($_POST['status']))
      ->toArray();
    $radiologi = [];
    foreach ($rows as $row) {
      $rows2 = $this->db('permintaan_pemeriksaan_radiologi')
        ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=permintaan_pemeriksaan_radiologi.kd_jenis_prw')
        ->where('permintaan_pemeriksaan_radiologi.noorder', $row['noorder'])
        ->toArray();
      $row['permintaan_pemeriksaan_radiologi'] = [];
      foreach ($rows2 as $row2) {
        $row2['noorder'] = $row2['noorder'];
        $row2['kd_jenis_prw'] = $row2['kd_jenis_prw'];
        $row2['stts_bayar'] = $row2['stts_bayar'];
        $row2['nm_perawatan'] = $row2['nm_perawatan'];
        $row2['kd_pj'] = $row2['kd_pj'];
        $row2['status'] = $row2['status'];
        $row2['kelas'] = $row2['kelas'];
        $row['permintaan_pemeriksaan_radiologi'][] = $row2;
      }
      $radiologi[] = $row;
    }

    $rows_periksa_radiologi = $this->db('periksa_radiologi')
      ->select([
        'periksa_radiologi.no_rawat',
        'periksa_radiologi.tgl_periksa',
        'periksa_radiologi.jam',
        'periksa_radiologi.nip',
        'periksa_radiologi.kd_dokter',
        'periksa_radiologi.kd_jenis_prw',
        'periksa_radiologi.dokter_perujuk',
        'periksa_radiologi.bagian_rs',
        'periksa_radiologi.bhp',
        'periksa_radiologi.tarif_perujuk',
        'periksa_radiologi.tarif_tindakan_dokter',
        'periksa_radiologi.tarif_tindakan_petugas',
        'periksa_radiologi.kso',
        'periksa_radiologi.menejemen',
        'periksa_radiologi.biaya',
        'periksa_radiologi.status',
        'periksa_radiologi.proyeksi',
        'periksa_radiologi.kV',
        'periksa_radiologi.mAS',
        'periksa_radiologi.FFD',
        'periksa_radiologi.BSF',
        'periksa_radiologi.inak',
        'periksa_radiologi.jml_penyinaran',
        'periksa_radiologi.dosis',
        'dokter.nm_dokter',
        'penjab.png_jawab',
        'petugas.nama as nama_petugas'
      ])
      ->join('reg_periksa', 'reg_periksa.no_rawat=periksa_radiologi.no_rawat')
      ->join('dokter', 'dokter.kd_dokter=periksa_radiologi.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->join('petugas', 'petugas.nip=periksa_radiologi.nip')
      ->where('periksa_radiologi.status', $_POST['status'])
      ->where('periksa_radiologi.no_rawat', $_POST['no_rawat'])
      ->group('periksa_radiologi.no_rawat')
      ->group('periksa_radiologi.tgl_periksa')
      ->group('periksa_radiologi.jam')
      ->group('periksa_radiologi.nip')
      ->group('periksa_radiologi.kd_dokter')
      ->group('periksa_radiologi.kd_jenis_prw')
      ->group('dokter.nm_dokter')
      ->group('penjab.png_jawab')
      ->group('petugas.nama')
      ->toArray();

    $periksa_radiologi = [];
    $jumlah_total_radiologi = 0;
    $no_radiologi = 1;
    foreach ($rows_periksa_radiologi as $row) {
      $jumlah_total_radiologi += $row['biaya'];
      $row['nomor'] = $no_radiologi++;
      $row['status_periksa'] = $_POST['status'];
      $row['periksa_radiologi'] = $this->db('periksa_radiologi')
        ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
        ->where('periksa_radiologi.status', $_POST['status'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_periksa', $row['tgl_periksa'])
        ->where('jam', $row['jam'])
        ->toArray();
      $row['hasil_radiologi'] = $this->db('hasil_radiologi')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_periksa', $row['tgl_periksa'])
        ->where('jam', $row['jam'])
        ->toArray();
      $row['gambar_radiologi'] = $this->db('gambar_radiologi')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_periksa', $row['tgl_periksa'])
        // ->where('jam', $row['jam'])
        ->toArray();
      $periksa_radiologi[] = $row;
    }

    $mini_pacs = $this->db('mlite_mini_pacs_study')->where('no_rawat', $_POST['no_rawat'])->toArray();
    foreach ($mini_pacs as &$mp) {
        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $mp['id'])->toArray();
        foreach ($series as &$s) {
            $s['instances'] = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
        }
        $mp['series'] = $series;
    }
    unset($mp);

    echo $this->draw('rincian.html', [
      'periksa_radiologi' => htmlspecialchars_array($periksa_radiologi), 
      'jumlah_total_radiologi' => $jumlah_total_radiologi, 
      'no_rawat' => htmlspecialchars($_POST['no_rawat'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), 
      'radiologi' => htmlspecialchars_array($radiologi),
      'mini_pacs' => htmlspecialchars_array($mini_pacs)
    ]);
    exit();
  }

  public function postValidasiPermintaanRadiologi()
  {
    $permintaan_radiologi = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->oneArray();
    $validasi_permintaan = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->save(['tgl_sampel' => date('Y-m-d'), 'jam_sampel' => date('H:i:s')]);
    $permintaan_pemeriksaan_radiologi = $this->db('permintaan_pemeriksaan_radiologi')->where('noorder', $_POST['noorder'])->toArray();
    foreach ($permintaan_pemeriksaan_radiologi as $row) {
      $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
      $periksa_radiologi = $this->db('periksa_radiologi')
        ->save([
          'no_rawat' => $_POST['no_rawat'],
          'nip' => $this->core->getUserInfo('username', null, true),
          'kd_jenis_prw' => $row['kd_jenis_prw'],
          'tgl_periksa' => $_POST['tgl_permintaan'],
          'jam' => $_POST['jam_permintaan'],
          'dokter_perujuk' => $permintaan_radiologi['dokter_perujuk'],
          'bagian_rs' => $jns_perawatan['bagian_rs'],
          'bhp' => $jns_perawatan['bhp'],
          'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
          'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
          'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
          'kso' => $jns_perawatan['kso'],
          'menejemen' => $jns_perawatan['menejemen'],
          'biaya' => $jns_perawatan['total_byr'],
          'kd_dokter' => $this->settings->get('settings.pj_radiologi'),
          'status' => $_POST['status'],
          'proyeksi' => '',
          'kV' => '',
          'mAS' => '',
          'FFD' => '',
          'BSF' => '',
          'inak' => '',
          'jml_penyinaran' => '',
          'dosis' => ''
        ]);
    }
    exit();
  }

  public function postValidasiHasilRadiologi()
  {
    $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->save(['tgl_hasil' => date('Y-m-d'), 'jam_hasil' => date('H:i:s')]);
    exit();
  }


  public function anyLayananRadiologi()
  {
    $layanan = $this->db('jns_perawatan_radiologi')
      ->where('status', '1')
      ->like('nm_perawatan', '%' . htmlspecialchars($_POST['layanan'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '%')
      ->limit(10)
      ->toArray();
    echo $this->draw('layanan.html', ['layanan' => htmlspecialchars_array($layanan)]);
    exit();
  }

  public function postProviderList()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">' . htmlspecialchars($row["kd_dokter"] . ': ' . $row["nm_dokter"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
        }
      }
      echo $output;
    }

    exit();

  }

  public function postProviderList2()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">' . htmlspecialchars($row["nip"] . ': ' . $row["nama"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
        }
      }
      echo $output;
    }

    exit();

  }

  public function postMaxid()
  {
    $urut = $this->db('reg_periksa')
      ->where('kd_poli', $this->settings->get('settings.radiologi'))
      ->where('tgl_registrasi', date('Y-m-d'))
      ->nextRightNumber('no_rawat', 6);

    $next_no_rawat = date('Y/m/d') . '/' . $urut;
    echo $next_no_rawat;
    exit();
  }

  public function postMaxAntrian()
  {
    $urut = $this->db('reg_periksa')
      ->where('kd_poli', $this->settings->get('settings.radiologi'))
      ->where('tgl_registrasi', date('Y-m-d'))
      ->nextRightNumber('no_reg', 3);

    echo sprintf('%03d', $urut);
    exit();
  }

  public function postSaveHasil()
  {
    $result = $this->db('hasil_radiologi')
      ->save([
        'no_rawat' => $_POST['no_rawat'],
        'tgl_periksa' => $_POST['tgl_periksa'],
        'jam' => $_POST['jam_periksa'],
        'hasil' => $_POST['hasil']
      ]);

    if ($result) {
      $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->save(['tgl_hasil' => date('Y-m-d'), 'jam_hasil' => date('H:i:s')]);
    }
    exit();
  }
  public function postUploadHasil()
  {
    header('Content-type: application/json');
    $dir = WEBAPPS_PATH . '/radiologi/pages/upload';
    $error = null;

    if (!file_exists($dir)) {
      mkdir(WEBAPPS_PATH . "/radiologi", 0777);
      mkdir(WEBAPPS_PATH . "/radiologi/pages", 0777);
      mkdir(WEBAPPS_PATH . "/radiologi/pages/upload", 0777);
      mkdir($dir, 0777, true);
    }

    if (isset($_FILES['file']['tmp_name'])) {
      $img = new \Systems\Lib\Image;

      if ($img->load($_FILES['file']['tmp_name'])) {
        $imgPath = $dir . '/' . time() . '.' . $img->getInfos('type');
        $img->save($imgPath);
        $result = $this->db('gambar_radiologi')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'tgl_periksa' => $_POST['tgl_periksa'],
            'jam' => $_POST['jam_periksa'],
            'lokasi_gambar' => 'pages/upload/' . time() . '.' . $img->getInfos('type')
          ]);
        echo json_encode(['status' => 'success', 'result' => url($imgPath)]);
      } else {
        $error = "Upload gagal";
      }

      if ($error) {
        echo json_encode(['status' => 'failure', 'result' => $error]);
      }
    }
    exit();
  }

  public function postKirimEmail()
  {
    $email = $_POST['email'];
    $nama_lengkap = $_POST['receiver'];
    $file = $_POST['file'];
    $this->sendEmail($email, $nama_lengkap, $file);
    exit();
  }

  private function sendEmail($email, $receiver, $file)
  {
    $binary_content = file_get_contents($file);

    if ($binary_content === false) {
      throw new \Exception("Could not fetch remote content from: '$file'");
    }

    $mail = new PHPMailer(true);
    $temp = @file_get_contents(MODULES . "/radiologi/email/email.send.html");

    $temp = str_replace("{SITENAME}", $this->core->settings->get('settings.nama_instansi'), $temp);
    $temp = str_replace("{ADDRESS}", $this->core->settings->get('settings.alamat') . " - " . $this->core->settings->get('settings.kota'), $temp);
    $temp = str_replace("{TELP}", $this->core->settings->get('settings.nomor_telepon'), $temp);
    //$temp  = str_replace("{NUMBER}", $number, $temp);

    //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // for detailed debug output
    $mail->isSMTP();
    $mail->Host = $this->settings->get('api.apam_smtp_host');
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $this->settings->get('api.apam_smtp_port');

    $mail->Username = $this->settings->get('api.apam_smtp_username');
    $mail->Password = $this->settings->get('api.apam_smtp_password');

    // Sender and recipient settings
    $mail->setFrom($this->core->settings->get('settings.email'), $this->core->settings->get('settings.nama_instansi'));
    $mail->addAddress($email, $receiver);
    $mail->AddStringAttachment($binary_content, "hasil_radiologi.pdf", $encoding = 'base64', $type = 'application/pdf');

    // Setting the email content
    $mail->IsHTML(true);
    $mail->Subject = "Hasil pemeriksaan radiologi anda di " . $this->core->settings->get('settings.nama_instansi');
    $mail->Body = $temp;

    $mail->send();

    if (!$mail->send()) {
      echo 'Error: ' . $mail->ErrorInfo;
    } else {
      echo 'Pesan email telah dikirim.';
    }

  }

  public function postCekWaktu()
  {
    echo date('H:i:s');
    exit();
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    $this->assign['websocket'] = $this->settings->get('settings.websocket');
    $this->assign['websocket_proxy'] = $this->settings->get('settings.websocket_proxy');
    echo $this->draw(MODULES . '/radiologi/js/admin/radiologi.js', ['mlite' => htmlspecialchars_array($this->assign)]);
    exit();
  }

  private function _addHeaderFiles()
  {
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
    $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
    $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
    $this->core->addJS(url([ADMIN, 'radiologi', 'javascript']), 'footer');
  }

}
