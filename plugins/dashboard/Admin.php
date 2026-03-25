<?php

namespace Plugins\Dashboard;

use Systems\AdminModule;

class Admin extends AdminModule
{
  public function navigation()
  {
    return [
      'Main' => 'main',
    ];
  }

  public function getMain()
  {
    $this->core->addJS(url(MODULES . '/dashboard/js/admin/webcam.js?v={$mlite.version}'));
    $settings = $this->settings('settings');

    $day = array(
      'Sun' => 'AKHAD',
      'Mon' => 'SENIN',
      'Tue' => 'SELASA',
      'Wed' => 'RABU',
      'Thu' => 'KAMIS',
      'Fri' => 'JUMAT',
      'Sat' => 'SABTU'
    );
    $hari = $day[date('D', strtotime(date('Y-m-d')))];

    $presensi = $this->db('mlite_modules')->where('dir', 'presensi')->oneArray();
    $cek_presensi = [];
    $jam_jaga = [];
    $cek_rekap = [];
    $nama_pegawai = '';
    $pengaturan_presensi = '';
    $teks = array('');
    if ($presensi) {
      if ($this->core->getUserInfo('username', null, true) == 'admin') {
        $nama_pegawai = 'Administrator';
      } else {
        $nama_pegawai = $this->core->getPegawaiInfo('nama', $this->core->getUserInfo('username', null, true));
      }
      $idpeg = $this->db('barcode')->where('barcode', $this->core->getUserInfo('username', null, true))->oneArray();
      if($idpeg) {
        $cek_presensi = $this->db('temporary_presensi')->where('id', $idpeg['id'])->oneArray();
        $cek_rekap = $this->db('rekap_presensi')->where('id', $idpeg['id'])->like('jam_datang', '%' . date('Y-m-d') . '%')->oneArray();
        $jam_jaga = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $idpeg['id'])->toArray();
      }
      $teks = explode(';', $this->settings->get('presensi.helloworld'));
      $pengaturan_presensi = $this->settings('presensi');
    }
    $random_keys = array_rand($teks);
    $teks = $teks[$random_keys];
    return $this->draw('main.html', [
      'settings' => $settings,
      'cek_presensi' => $cek_presensi,
      'cek_rekap' => $cek_rekap,
      'jam_jaga' => $jam_jaga,
      'presensi' => $presensi,
      'nama' => $nama_pegawai,
      'teks' => $teks,
      'pengaturan_presensi' => $pengaturan_presensi,
      'notif_presensi' => $this->settings('settings', 'notif_presensi')
    ]);
  }

  public function getMenu()
  {
    $this->core->addCSS(url(MODULES . '/dashboard/css/admin/dashboard.css?v={$mlite.version}'));
    $this->core->addJS(url(MODULES . '/dashboard/js/admin/dashboard.js?v={$mlite.version}'), 'footer');
    echo $this->draw('dashboard.html', ['modules' => htmlspecialchars_array($this->_modulesList())]);
    exit();
  }

  private function _modulesList()
  {
    $modules = array_column($this->db('mlite_modules')->asc('sequence')->toArray(), 'dir');
    $result = [];

    if ($this->core->getUserInfo('access') != 'all') {
      $modules = array_intersect($modules, explode(',', $this->core->getUserInfo('access')));
    }

    foreach ($modules as $name) {
      $files = [
        'info'  => MODULES . '/' . $name . '/Info.php',
        'admin' => MODULES . '/' . $name . '/Admin.php',
      ];

      if (file_exists($files['info']) && file_exists($files['admin'])) {
        $details        = $this->core->getModuleInfo($name);
        $features       = $this->core->getModuleNav($name);

        if (empty($features)) {
          continue;
        }

        $details['url'] = url([ADMIN, $name, array_shift($features)]);
        $details['dir'] = $name;

        $result[] = $details;
      }
    }
    return $result;
  }

  public function postChangeOrderOfNavItem()
  {
    foreach ($_POST as $module => $order) {
      $this->db('mlite_modules')->where('dir', $module)->save(['sequence' => $order]);
    }
    exit();
  }

  public function postUpload()
  {
    if ($photo = isset_or($_FILES['webcam']['tmp_name'], false)) {
      $img = new \Systems\Lib\Image;
      if ($img->load($photo)) {
        if ($img->getInfos('width') < $img->getInfos('height')) {
          $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
        } else {
          $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
        }

        if ($img->getInfos('width') > 512) {
          $img->resize(512, 512);
        }
        $gambar = uniqid('photo') . "." . $img->getInfos('type');
      }

      if (isset($img) && $img->getInfos('width')) {
        date_default_timezone_set($this->settings->get('settings.timezone'));
        $img->save(WEBAPPS_PATH . "/presensi/" . $gambar);

        $urlnya         = WEBAPPS_URL . '/presensi/' . $gambar;
        $barcode        = $this->core->getUserInfo('username', null, true);

        $bulan = date('m');
        $tahun = date('Y');
        $hari = date('j');
        $shift = $_GET['shift'] ?? '';

        $idpeg          = $this->db('barcode')->where('barcode', $barcode)->oneArray();
        
        if (!$idpeg) {
          $this->notify('failure', 'ID Pegawai tidak ditemukan!');
          echo 'ID Pegawai tidak ditemukan!';
          exit();
        }

        $jam_jaga       = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $idpeg['id'])->where('jam_jaga.shift', $shift)->oneArray();

        $jadwal_pegawai = $this->db('jadwal_pegawai')->where('id', $idpeg['id'])->where('h' . $hari, $jam_jaga['shift'] ?? '')->where('bulan', $bulan)->where('tahun', $tahun)->oneArray();
        $jadwal_tambahan = $this->db('jadwal_tambahan')->where('id', $idpeg['id'])->where('h' . $hari, $jam_jaga['shift'] ?? '')->where('bulan', $bulan)->where('tahun', $tahun)->oneArray();
        $isFullAbsen = $this->db('rekap_presensi')->where('id', $idpeg['id'])->where('shift', $jam_jaga['shift'] ?? '')->like('jam_datang', date('Y-m-d') . '%')->oneArray();
        $isAbsen = $this->db('temporary_presensi')->where('id', $idpeg['id'])->oneArray();

        $set_keterlambatan  = $this->db('set_keterlambatan')->oneArray();
        $toleransi      = $set_keterlambatan['toleransi'] ?? 0;
        $terlambat1     = $set_keterlambatan['terlambat1'] ?? 0;
        $terlambat2     = $set_keterlambatan['terlambat2'] ?? 0;

        $toleransi      = (int)$toleransi;
        $terlambat1     = (int)$terlambat1;
        $terlambat2     = (int)$terlambat2;

        if (!$isFullAbsen) {
          if (!$isAbsen) {
            if (!$jadwal_pegawai) {
              if ($jadwal_tambahan) {
                if (empty($urlnya)) {
                  $this->notify('failure', 'Pilih shift dulu...!!!!');
                  echo 'Pilih shift dulu...!!!!';
                } else {

                  $status = 'Tepat Waktu';
                  $jam_masuk = $jam_jaga['jam_masuk'] ?? '00:00:00';

                  if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_masuk)) > ($toleransi * 60)) {
                    $status = 'Terlambat Toleransi';
                  }
                  if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_masuk)) > ($terlambat1 * 60)) {
                    $status = 'Terlambat I';
                  }
                  if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_masuk)) > ($terlambat2 * 60)) {
                    $status = 'Terlambat II';
                  }

                  $keterlambatan = '00:00:00';
                  if (strtotime(date('Y-m-d H:i:s')) - (date('Y-m-d') . $jam_masuk) > ($toleransi * 60)) {
                    $awal  = new \DateTime(date('Y-m-d') . ' ' . $jam_masuk);
                    $akhir = new \DateTime();
                    $diff = $akhir->diff($awal, true); // to make the difference to be always positive.
                    if ($awal > $akhir) {
                     $keterlambatan = $diff->format('');
                     }else{
                     $keterlambatan = $diff->format('%H:%I:%S');
                   }
                  }

                  $insert = $this->db('temporary_presensi')
                    ->save([
                      'id' => $idpeg['id'],
                      'shift' => $jam_jaga['shift'],
                      'jam_datang' => date('Y-m-d H:i:s'),
                      'jam_pulang' => NULL,
                      'status' => $status,
                      'keterlambatan' => $keterlambatan,
                      'durasi' => '',
                      'photo' => $urlnya
                    ]);

                  if ($insert) {
                    $this->notify('success', 'Presensi Masuk jam ' . htmlspecialchars($jam_masuk . ' ' . $status . ' ' . $keterlambatan, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                    echo htmlspecialchars('Presensi Masuk jam ' . $jam_masuk . ' ' . $status . ' ' . $keterlambatan, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                  }
                }
              } else {
                $this->notify('failure', 'ID Pegawai atau jadwal shift tidak sesuai!');
                echo 'ID Pegawai atau jadwal shift tidak sesuai!';
              }
            } else {
              if (empty($urlnya)) {
                $this->notify('failure', 'Pilih shift dulu...!!!!');
                echo 'Pilih shift dulu...!!!!';
              } else {

                $status = 'Tepat Waktu';
                $jam_masuk = $jam_jaga['jam_masuk'] ?? '00:00:00';

                if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_masuk)) > ($toleransi * 60)) {
                  $status = 'Terlambat Toleransi';
                }
                if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_masuk)) > ($terlambat1 * 60)) {
                  $status = 'Terlambat I';
                }
                if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_masuk)) > ($terlambat2 * 60)) {
                  $status = 'Terlambat II';
                }

                $keterlambatan = '00:00:00';
                if (strtotime(date('Y-m-d H:i:s')) - (date('Y-m-d') . $jam_masuk) > ($toleransi * 60)) {
                  $awal  = new \DateTime(date('Y-m-d') . ' ' . $jam_masuk);
                  $akhir = new \DateTime();
                  $diff = $akhir->diff($awal, true); // to make the difference to be always positive.
                  if ($awal > $akhir) {
                     $keterlambatan = $diff->format('');
                     }else{
                     $keterlambatan = $diff->format('%H:%I:%S');
                   }
                }

                $insert = $this->db('temporary_presensi')
                  ->save([
                    'id' => $idpeg['id'],
                    'shift' => $jam_jaga['shift'],
                    'jam_datang' => date('Y-m-d H:i:s'),
                    'jam_pulang' => NULL,
                    'status' => $status,
                    'keterlambatan' => $keterlambatan,
                    'durasi' => '',
                    'photo' => $urlnya
                  ]);

                if ($insert) {
                  $this->notify('success', 'Presensi Masuk jam ' . htmlspecialchars($jam_masuk . ' ' . $status . ' ' . $keterlambatan, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                  echo htmlspecialchars('Presensi Masuk jam ' . $jam_masuk . ' ' . $status . ' ' . $keterlambatan, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
              }
            }
          } else {
            if (($jam_jaga['shift'] ?? '') != $isAbsen['shift']) {
              $this->notify('failure', 'ID Pegawai atau jadwal shift tidak sesuai!');
              echo 'ID Pegawai atau jadwal shift tidak sesuai!';
            } else {
              $jamDatang = substr($isAbsen['jam_datang'], 16);
              if ((strtotime(date('Y-m-d H:i')) - strtotime($jamDatang)) < 2 * 60) {
                $this->notify('failure', 'Sabar ... Jangan pencet terus');
                echo 'Sabar ... Jangan pencet terus';
              } else {
                $status = $isAbsen['status'];
                $dayShift = date('Y-m-d');
                if ($isAbsen['shift'] == 'Malam') {
                  $dayShift = substr($isAbsen['jam_datang'], 10);
                  $dayShift = date('Y-m-d', strtotime($dayShift . ' +1 day'));
                }
                $jam_pulang = $jam_jaga['jam_pulang'] ?? '00:00:00';
                if ((strtotime(date('Y-m-d H:i:s')) - strtotime($dayShift . $jam_pulang)) < 0) {
                  $status = $isAbsen['status'] . ' & PSW';
                }

                $awal  = new \DateTime($isAbsen['jam_datang']);
                $akhir = new \DateTime();
                $diff = $akhir->diff($awal, true); // to make the difference to be always positive.
                $durasi = $diff->format('%H:%I:%S');

                $ubah = $this->db('temporary_presensi')
                  ->where('id', $idpeg['id'])
                  ->save([
                    'jam_pulang' => date('Y-m-d H:i:s'),
                    'status' => $status,
                    'durasi' => $durasi
                  ]);

                if ($ubah) {
                  $presensi = $this->db('temporary_presensi')->where('id', $isAbsen['id'])->oneArray();
                  $insert = $this->db('rekap_presensi')
                    ->save([
                      'id' => $presensi['id'],
                      'shift' => $presensi['shift'],
                      'jam_datang' => $presensi['jam_datang'],
                      'jam_pulang' => $presensi['jam_pulang'],
                      'status' => $presensi['status'],
                      'keterlambatan' => $presensi['keterlambatan'],
                      'durasi' => $presensi['durasi'],
                      'keterangan' => '-',
                      'photo' => $presensi['photo']
                    ]);
                  if ($insert) {
                    $this->notify('success', 'Presensi pulang telah disimpan');
                    echo 'Presensi pulang telah disimpan';
                    $this->db('temporary_presensi')->where('id', $isAbsen['id'])->delete();
                  }
                }
              }
            }
          }
        } else {
          $this->notify('failure', 'Anda sudah presensi untuk tanggal ' . date('Y-m-d'));
          echo 'Anda sudah presensi untuk tanggal ' . date('Y-m-d');
        }
      }
    }

    exit();
  }

  public function postGeolocation()
  {

    $idpeg = $this->db('barcode')->where('barcode', $this->core->getUserInfo('username', null, true))->oneArray();

    if (isset($_POST['lat'], $_POST['lng'])) {
      if (!$this->db('mlite_geolocation_presensi')->where('id', $idpeg['id'])->where('tanggal', date('Y-m-d'))->oneArray()) {
        $this->db('mlite_geolocation_presensi')
          ->save([
            'id' => $idpeg['id'],
            'tanggal' => date('Y-m-d'),
            'latitude' => $_POST['lat'],
            'longitude' => $_POST['lng']
          ]);
      }
    }

    exit();
  }

  public function getHelp($dir)
  {
    $files = [
      'info'      => MODULES . '/' . $dir . '/Info.php',
      'help'    => MODULES . '/' . $dir . '/Help.md'
    ];

    $module = $this->core->getModuleInfo($dir);
    $module['description'] = $this->tpl->noParse($module['description']);

    // ReadMe.md
    if (file_exists($files['help'])) {
      $parsedown = new \Systems\Lib\Parsedown();
      $module['help'] = $parsedown->text($this->tpl->noParse(file_get_contents($files['help'])));
    }

    $this->tpl->set('module', $module);
    echo $this->tpl->draw(MODULES . '/modules/view/admin/help.html', true);
    exit();
  }

  public function apiStats()
  {
    header('Content-Type: application/json');
    date_default_timezone_set($this->settings->get('settings.timezone'));
    
    $username = $this->core->checkAuth('GET'); 
    if (!$this->core->checkPermission($username, 'can_read', 'dashboard')) { 
        echo json_encode(['status' => 'error', 'message' => 'Invalid User Permission Credentials']);
        exit;
    } 
    
    // Validasi apakah user adalah dokter
    $dokter = $this->db('dokter')->where('kd_dokter', $username)->oneArray();
    if (!$dokter) {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. User bukan dokter. ' . $username]);
        exit;
    }
    
    $kd_dokter = $dokter['kd_dokter'];
    $today = date('Y-m-d');
    $firstDayOfMonth = date('Y-m-01');
    $lastDayOfMonth = date('Y-m-t');

    // 1. Total Pasien (Unik)
    $stmt = $this->db()->pdo()->prepare("SELECT count(DISTINCT no_rkm_medis) FROM reg_periksa WHERE kd_dokter = ? AND stts != 'Batal'");
    $stmt->execute([$kd_dokter]);
    $total_pasien = $stmt->fetchColumn();

    // 2. Bulan Ini (Kunjungan)
    $bulan_ini = $this->db('reg_periksa')
        ->where('kd_dokter', $kd_dokter)
        ->where('tgl_registrasi', '>=', $firstDayOfMonth)
        ->where('tgl_registrasi', '<=', $lastDayOfMonth)
        ->where('stts', '!=', 'Batal')
        ->count();

    // 3. Poli Bulan Ini (Total kunjungan di poli tempat dokter praktek bulan ini - akumulasi)
    $polis_month = $this->db('reg_periksa')
        ->select('kd_poli')
        ->where('kd_dokter', $kd_dokter)
        ->where('tgl_registrasi', '>=', $firstDayOfMonth)
        ->where('tgl_registrasi', '<=', $lastDayOfMonth)
        ->where('stts', '!=', 'Batal')
        ->group('kd_poli')
        ->toArray();
    
    $poli_bulan_ini = 0;
    if($polis_month) {
        $kd_polis = array_column($polis_month, 'kd_poli');
        $placeholders = implode(',', array_fill(0, count($kd_polis), '?'));
        $stmt = $this->db()->pdo()->prepare("SELECT count(no_rawat) FROM reg_periksa WHERE kd_poli IN ($placeholders) AND tgl_registrasi BETWEEN ? AND ? AND stts != 'Batal'");
        $params = array_merge($kd_polis, [$firstDayOfMonth, $lastDayOfMonth]);
        $stmt->execute($params);
        $poli_bulan_ini = $stmt->fetchColumn();
    }

    // 4. Poli Hari Ini
    $polis_today = $this->db('reg_periksa')
        ->select('kd_poli')
        ->where('kd_dokter', $kd_dokter)
        ->where('tgl_registrasi', $today)
        ->where('stts', '!=', 'Batal')
        ->group('kd_poli')
        ->toArray();

    $poli_hari_ini = 0;
    if($polis_today) {
        $kd_polis = array_column($polis_today, 'kd_poli');
        $placeholders = implode(',', array_fill(0, count($kd_polis), '?'));
        $stmt = $this->db()->pdo()->prepare("SELECT count(no_rawat) FROM reg_periksa WHERE kd_poli IN ($placeholders) AND tgl_registrasi = ? AND stts != 'Batal'");
        $params = array_merge($kd_polis, [$today]);
        $stmt->execute($params);
        $poli_hari_ini = $stmt->fetchColumn();
    }

    // 5. Chart Poliklinik Hari Ini
    $chart_data = $this->db('reg_periksa')
        ->select(['reg_periksa.kd_poli', 'count(reg_periksa.no_rawat) as jumlah', 'poliklinik.nm_poli'])
        ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
        ->where('reg_periksa.kd_dokter', $kd_dokter)
        ->where('reg_periksa.tgl_registrasi', $today)
        ->where('reg_periksa.stts', '!=', 'Batal')
        ->group('reg_periksa.kd_poli')
        ->toArray();
    
    $chart_labels = array_column($chart_data, 'nm_poli');
    $chart_values = array_column($chart_data, 'jumlah');

    // 6. Pasien Paling Aktif (Top 10)
    $pasien_aktif = $this->db('reg_periksa')
        ->select(['pasien.nm_pasien', 'count(reg_periksa.no_rawat) as jumlah'])
        ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
        ->where('reg_periksa.kd_dokter', $kd_dokter)
        ->where('reg_periksa.stts', '!=', 'Batal')
        ->group('reg_periksa.no_rkm_medis')
        ->desc('jumlah')
        ->limit(10)
        ->toArray();

    // 7. Antrian 10 Pasien Terakhir
    $antrian_terakhir = $this->db('reg_periksa')
        ->select(['pasien.nm_pasien', 'reg_periksa.stts', 'reg_periksa.no_reg', 'reg_periksa.jam_reg'])
        ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
        ->where('reg_periksa.kd_dokter', $kd_dokter)
        ->where('reg_periksa.tgl_registrasi', $today)
        ->desc('reg_periksa.jam_reg')
        ->limit(10)
        ->toArray();

    return [
        'status' => 'success',
        'data' => [
            'total_pasien' => $total_pasien,
            'bulan_ini' => $bulan_ini,
            'poli_bulan_ini' => $poli_bulan_ini,
            'poli_hari_ini' => $poli_hari_ini,
            'chart' => [
                'labels' => $chart_labels,
                'values' => $chart_values
            ],
            'pasien_aktif' => $pasien_aktif,
            'antrian_terakhir' => $antrian_terakhir
        ]
    ];
  }

}
