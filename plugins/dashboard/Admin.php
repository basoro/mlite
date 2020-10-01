<?php

namespace Plugins\Dashboard;

use Systems\AdminModule;
use Systems\Lib\HttpRequest;

class Admin extends AdminModule
{
    public function navigation()
    {
        if ($this->core->getUserInfo('id') == 1) {
            return [
                'Main' => 'main',
                'Pengaturan' => 'settings'
            ];
        } else {
            return [
                'Main' => 'main'
            ];
        }
    }

    public function getMain()
    {

        $this->core->addCSS(url(MODULES.'/dashboard/css/admin/style.css?v={$opensimrs.version}'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
        $this->core->addJS(url(MODULES.'/dashboard/js/webcam.js?v={$opensimrs.version}'), 'footer');
        $this->core->addJS(url(MODULES.'/dashboard/js/app.js?v={$opensimrs.version}'), 'footer');

        $settings = htmlspecialchars_array($this->options('dashboard'));
        $stats['getPasiens'] = number_format($this->countPasien(),0,'','.');
        $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
        $stats['getYearVisities'] = number_format($this->countYearVisite(),0,'','.');
        $stats['getMonthVisities'] = number_format($this->countMonthVisite(),0,'','.');
        $stats['getCurrentVisities'] = number_format($this->countCurrentVisite(),0,'','.');
        $stats['getLastYearVisities'] = number_format($this->countLastYearVisite(),0,'','.');
        $stats['getLastMonthVisities'] = number_format($this->countLastMonthVisite(),0,'','.');
        $stats['getLastCurrentVisities'] = number_format($this->countLastCurrentVisite(),0,'','.');
        $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
        $stats['percentYear'] = number_format((($this->countYearVisite()-$this->countLastYearVisite())/$this->countYearVisite())*100,0,'','.');
        $stats['percentMonth'] = 0;
        if($this->countMonthVisite() != 0) {
          $stats['percentMonth'] = number_format((($this->countMonthVisite()-$this->countLastMonthVisite())/$this->countMonthVisite())*100,0,'','.');
        }
        $stats['percentDays'] = 0;
        if($this->countCurrentVisite() != 0) {
          $stats['percentDays'] = number_format((($this->countCurrentVisite()-$this->countLastCurrentVisite())/$this->countCurrentVisite())*100,0,'','.');
        }
        $stats['poliChart'] = $this->poliChart();
        $stats['KunjunganTahunChart'] = $this->KunjunganTahunChart();
        $stats['RanapTahunChart'] = $this->RanapTahunChart();
        $stats['RujukTahunChart'] = $this->RujukTahunChart();
        $stats['tunai'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', $settings['umum'])->like('tgl_registrasi', date('Y').'%')->oneArray();
        $stats['bpjs'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', $settings['bpjs'])->like('tgl_registrasi', date('Y').'%')->oneArray();
        $stats['lainnya'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', '!=', $settings['umum'])->where('kd_pj', '!=', $settings['bpjs'])->like('tgl_registrasi', date('Y').'%')->oneArray();

        $day = array(
          'Sun' => 'AKHAD',
          'Mon' => 'SENIN',
          'Tue' => 'SELASA',
          'Wed' => 'RABU',
          'Thu' => 'KAMIS',
          'Fri' => 'JUMAT',
          'Sat' => 'SABTU'
        );
        $hari=$day[date('D',strtotime(date('Y-m-d')))];

        $idpeg        = $this->db('barcode')->where('barcode', $this->core->getUserInfo('username', null, true))->oneArray();
        $cek_presensi = $this->db('temporary_presensi')->where('id', $idpeg['id'])->oneArray();

        return $this->draw('dashboard.html', [
          'settings' => $settings,
          'stats' => $stats,
          'cek_presensi' => $cek_presensi,
          'jam_jaga' => $this->db('jam_jaga')->group('shift')->toArray(),
          'pasien' => $this->db('pasien')->join('penjab', 'penjab.kd_pj = pasien.kd_pj')->desc('tgl_daftar')->limit('5')->toArray(),
          'dokter' => $this->db('dokter')->join('spesialis', 'spesialis.kd_sps = dokter.kd_sps')->join('jadwal', 'jadwal.kd_dokter = dokter.kd_dokter')->where('jadwal.hari_kerja', $hari)->where('dokter.status', '1')->group('dokter.kd_dokter')->rand()->limit('6')->toArray()
        ]);

    }

    public function countVisite()
    {
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->oneArray();

        return $record['count'];
    }

    public function countVisiteNoRM()
    {
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->group('no_rkm_medis')
            ->oneArray();

        return $record['count'];
    }

    public function countYearVisite()
    {
        $date = date('Y');
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_registrasi', $date.'%')
            ->oneArray();

        return $record['count'];
    }

    public function countLastYearVisite()
    {
        $date = date('Y', strtotime('-1 year'));
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_registrasi', $date.'%')
            ->oneArray();

        return $record['count'];
    }

    public function countMonthVisite()
    {
        $date = date('Y-m');
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_registrasi', $date.'%')
            ->oneArray();

        return $record['count'];
    }

    public function countLastMonthVisite()
    {
        $date = date('Y-m', strtotime('-1 month'));
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_registrasi', $date.'%')
            ->oneArray();

        return $record['count'];
    }


    public function countCurrentVisite()
    {
        $date = date('Y-m-d');
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->oneArray();

        return $record['count'];
    }

    public function countLastCurrentVisite()
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->oneArray();

        return $record['count'];
    }

    public function countPasien()
    {
        $record = $this->db('pasien')
            ->select([
                'count' => 'COUNT(DISTINCT no_rkm_medis)',
            ])
            ->oneArray();

        return $record['count'];
    }

    public function poliChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'nm_poli'     => 'nm_poli',
            ])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('tgl_registrasi', '>=', date('Y-m-d'))
            ->group(['reg_periksa.kd_poli'])
            ->desc('nm_poli');


            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => [],
            ];

            foreach ($data as $value) {
                $return['labels'][] = $value['nm_poli'];
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function KunjunganTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'tgl_registrasi'
            ])
            ->like('tgl_registrasi', date('Y').'%')
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => []
            ];
            foreach ($data as $value) {
                $return['labels'][] = date("M", strtotime($value['label']));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function RanapTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'tgl_registrasi'
            ])
            ->where('stts', 'Dirawat')
            ->like('tgl_registrasi', date('Y').'%')
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => []
            ];
            foreach ($data as $value) {
                $return['labels'][] = date("M", strtotime($value['label']));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function RujukTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'tgl_registrasi'
            ])
            ->where('stts', 'Dirujuk')
            ->like('tgl_registrasi', date('Y').'%')
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => []
            ];
            foreach ($data as $value) {
                $return['labels'][] = date("M", strtotime($value['label']));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function getSettings()
    {
        $this->assign['penjab'] = $this->core->db('penjab')->toArray();
        $this->assign['dashboard'] = htmlspecialchars_array($this->options('dashboard'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['dashboard'] as $key => $val) {
            $this->options('dashboard', $key, $val);
        }
        $this->notify('success', 'Pengaturan pasien telah disimpan');
        redirect(url([ADMIN, 'dashboard', 'settings']));
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
              $gambar = uniqid('photo').".".$img->getInfos('type');
          }

          if (isset($img) && $img->getInfos('width')) {

              $img->save(WEBAPPS_PATH."/presensi/".$gambar);

              $urlnya         = WEBAPPS_URL.'/presensi/'.$gambar;
              $barcode        = $this->core->getUserInfo('username', null, true);

              $idpeg          = $this->db('barcode')->where('barcode', $barcode)->oneArray();
              $jam_jaga       = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $idpeg['id'])->where('jam_jaga.shift', $_GET['shift'])->oneArray();
              $jadwal_pegawai = $this->db('jadwal_pegawai')->where('id', $idpeg['id'])->where('h'.date('j'), $_GET['shift'])->oneArray();

              $set_keterlambatan  = $this->db('set_keterlambatan')->toArray();
              $toleransi      = $set_keterlambatan['toleransi'];
              $terlambat1     = $set_keterlambatan['terlambat1'];
              $terlambat2     = $set_keterlambatan['terlambat2'];

              $valid = $this->db('rekap_presensi')->where('id', $idpeg['id'])->where('shift', $jam_jaga['shift'])->like('jam_datang', '%'.date('Y-m-d').'%')->oneArray();

              if($valid){
                  $this->notify('failure', 'Anda sudah presensi untuk tanggal '.date('Y-m-d'));
              }elseif((!empty($idpeg['id']))&&(!empty($jam_jaga['shift']))&&($jadwal_pegawai)&&(!$valid)) {
                  $cek = $this->db('temporary_presensi')->where('id', $idpeg['id'])->oneArray();

                  if(!$cek){
                      if(empty($urlnya)){
                          $this->notify('failure', 'Pilih shift dulu...!!!!');
                      }else{

                          $status = 'Tepat Waktu';

                          if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga['jam_masuk']))>($toleransi*60)) {
                            $status = 'Terlambat Toleransi';
                          }
                          if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga['jam_masuk']))>($terlambat1*60)) {
                            $status = 'Terlambat I';
                          }
                          if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga['jam_masuk']))>($terlambat2*60)) {
                            $status = 'Terlambat II';
                          }

                          if(strtotime(date('Y-m-d H:i:s'))-(date('Y-m-d').' '.$jam_jaga['jam_masuk'])>($toleransi*60)) {
                            $awal  = new \DateTime(date('Y-m-d').' '.$jam_jaga['jam_masuk']);
                            $akhir = new \DateTime();
                            $diff = $akhir->diff($awal,true); // to make the difference to be always positive.
                            $keterlambatan = $diff->format('%H:%I:%S');

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

                          if($insert) {
                            $this->notify('success', 'Presensi Masuk jam '.$jam_jaga['jam_masuk'].' '.$status.' '.$keterlambatan);
                          }
                      }
                  }elseif($cek){

                      $status = $cek['status'];
                      if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga['jam_pulang']))<0) {
                        $status = $cek['status'].' & PSW';
                      }

                      $awal  = new \DateTime($cek['jam_datang']);
                      $akhir = new \DateTime();
                      $diff = $akhir->diff($awal,true); // to make the difference to be always positive.
                      $durasi = $diff->format('%H:%I:%S');

                      $ubah = $this->db('temporary_presensi')
                        ->where('id', $idpeg['id'])
                        ->save([
                          'jam_pulang' => date('Y-m-d H:i:s'),
                          'status' => $status,
                          'durasi' => $durasi
                        ]);

                      if($ubah) {
                          $presensi = $this->db('temporary_presensi')->where('id', $cek['id'])->oneArray();
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
                          if($insert) {
                              $this->notify('success', 'Presensi pulang telah disimpan');
                              $this->db('temporary_presensi')->where('id', $cek['id'])->delete();
                          }
                      }
                  }
              }else{
                  $this->notify('failure', 'ID Pegawai atau jadwal shift tidak sesuai. Silahkan pilih berdasarkan shift departemen anda!');
              }
          }
      }

      exit();
    }

    public function postGeolocation()
    {

      $idpeg = $this->db('barcode')->where('barcode', $this->core->getUserInfo('username', null, true))->oneArray();

      if(isset($_POST['lat'], $_POST['lng'])) {
          if(!$this->db('geolocation_presensi')->where('id', $idpeg['id'])->where('tanggal', date('Y-m-d'))->oneArray()) {
              $this->db('geolocation_presensi')
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

}
