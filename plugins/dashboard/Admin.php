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
    return $this->draw('dashboard.html', ['modules' => $this->_modulesList()]);
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
        $tahun = date('y');
        $hari = date('j');
        $shift = $_GET['shift'];

        $idpeg          = $this->db('barcode')->where('barcode', $barcode)->oneArray();
        $jam_jaga       = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $idpeg['id'])->where('jam_jaga.shift', $shift)->oneArray();

        $jadwal_pegawai = $this->db('jadwal_pegawai')->where('id', $idpeg['id'])->where('h' . $hari, $jam_jaga['shift'])->where('bulan', $bulan)->where('tahun', $tahun)->oneArray();
        $jadwal_tambahan = $this->db('jadwal_tambahan')->where('id', $idpeg['id'])->where('h' . $hari, $jam_jaga['shift'])->where('bulan', $bulan)->where('tahun', $tahun)->oneArray();
        $isFullAbsen = $this->db('rekap_presensi')->where('id', $idpeg['id'])->where('shift', $jam_jaga['shift'])->like('jam_datang', date('Y-m-d') . '%')->oneArray();
        $isAbsen = $this->db('temporary_presensi')->where('id', $idpeg['id'])->oneArray();

        $set_keterlambatan  = $this->db('set_keterlambatan')->oneArray();
        $toleransi      = $set_keterlambatan['toleransi'];
        $terlambat1     = $set_keterlambatan['terlambat1'];
        $terlambat2     = $set_keterlambatan['terlambat2'];

        $toleransi      = (int)$toleransi;
        $terlambat1     = (int)$terlambat1;
        $terlambat2     = (int)$terlambat2;

        if (!$isFullAbsen) {
          if (!$isAbsen) {
            if (!$jadwal_pegawai) {
              if ($jadwal_tambahan) {
                if (empty($urlnya)) {
                  $this->notify('failure', 'Pilih shift dulu...!!!!');
                } else {

                  $status = 'Tepat Waktu';

                  if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_jaga['jam_masuk'])) > ($toleransi * 60)) {
                    $status = 'Terlambat Toleransi';
                  }
                  if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_jaga['jam_masuk'])) > ($terlambat1 * 60)) {
                    $status = 'Terlambat I';
                  }
                  if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_jaga['jam_masuk'])) > ($terlambat2 * 60)) {
                    $status = 'Terlambat II';
                  }

                  if (strtotime(date('Y-m-d H:i:s')) - (date('Y-m-d') . $jam_jaga['jam_masuk']) > ($toleransi * 60)) {
                    $awal  = new \DateTime(date('Y-m-d') . ' ' . $jam_jaga['jam_masuk']);
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
                    $this->notify('success', 'Presensi Masuk jam ' . $jam_jaga['jam_masuk'] . ' ' . $status . ' ' . $keterlambatan);
                  }
                }
              } else {
                $this->notify('failure', 'ID Pegawai atau jadwal shift tidak sesuai!');
              }
            } else {
              if (empty($urlnya)) {
                $this->notify('failure', 'Pilih shift dulu...!!!!');
              } else {

                $status = 'Tepat Waktu';

                if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_jaga['jam_masuk'])) > ($toleransi * 60)) {
                  $status = 'Terlambat Toleransi';
                }
                if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_jaga['jam_masuk'])) > ($terlambat1 * 60)) {
                  $status = 'Terlambat I';
                }
                if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . $jam_jaga['jam_masuk'])) > ($terlambat2 * 60)) {
                  $status = 'Terlambat II';
                }

                if (strtotime(date('Y-m-d H:i:s')) - (date('Y-m-d') . $jam_jaga['jam_masuk']) > ($toleransi * 60)) {
                  $awal  = new \DateTime(date('Y-m-d') . ' ' . $jam_jaga['jam_masuk']);
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
                  $this->notify('success', 'Presensi Masuk jam ' . $jam_jaga['jam_masuk'] . ' ' . $status . ' ' . $keterlambatan);
                }
              }
            }
          } else {
            if ($jam_jaga['shift'] != $isAbsen['shift']) {
              $this->notify('failure', 'ID Pegawai atau jadwal shift tidak sesuai!');
            } else {
              $jamDatang = substr($isAbsen['jam_datang'], 16);
              if ((strtotime(date('Y-m-d H:i')) - strtotime($jamDatang)) < 2 * 60) {
                $this->notify('failure', 'Sabar ... Jangan pencet terus');
              } else {
                $status = $isAbsen['status'];
                $dayShift = date('Y-m-d');
                if ($isAbsen['shift'] == 'Malam') {
                  $dayShift = substr($isAbsen['jam_datang'], 10);
                  $dayShift = date('Y-m-d', strtotime($dayShift . ' +1 day'));
                }
                if ((strtotime(date('Y-m-d H:i:s')) - strtotime($dayShift . $jam_jaga['jam_pulang'])) < 0) {
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
                    $this->db('temporary_presensi')->where('id', $isAbsen['id'])->delete();
                  }
                }
              }
            }
          }
        } else {
          $this->notify('failure', 'Anda sudah presensi untuk tanggal ' . date('Y-m-d'));
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

}
