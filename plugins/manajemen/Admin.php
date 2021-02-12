<?php
namespace Plugins\Manajemen;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'dashboard',
            'Pengaturan' => 'settings'
        ];
    }

    public function getDashboard()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

      $settings = htmlspecialchars_array($this->settings('manajemen'));
      $stats['getPasiens'] = number_format($this->countPasien(),0,'','.');
      $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
      $stats['getYearVisities'] = number_format($this->countYearVisite(),0,'','.');
      $stats['getMonthVisities'] = number_format($this->countMonthVisite(),0,'','.');
      $stats['getCurrentVisities'] = number_format($this->countCurrentVisite(),0,'','.');
      $stats['getLastYearVisities'] = number_format($this->countLastYearVisite(),0,'','.');
      $stats['getLastMonthVisities'] = number_format($this->countLastMonthVisite(),0,'','.');
      $stats['getLastCurrentVisities'] = number_format($this->countLastCurrentVisite(),0,'','.');
      $stats['percentTotal'] = 0;
      if($this->countVisite() != 0) {
        $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
      }
      $stats['percentYear'] = 0;
      if($this->countYearVisite() != 0) {
        $stats['percentYear'] = number_format((($this->countYearVisite()-$this->countLastYearVisite())/$this->countYearVisite())*100,0,'','.');
      }
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
      $stats['tunai'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', $settings['penjab_umum'])->like('tgl_registrasi', date('Y').'%')->oneArray();
      $stats['bpjs'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', $settings['penjab_bpjs'])->like('tgl_registrasi', date('Y').'%')->oneArray();
      $stats['lainnya'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', '!=', $settings['penjab_umum'])->where('kd_pj', '!=', $settings['penjab_bpjs'])->like('tgl_registrasi', date('Y').'%')->oneArray();

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

      return $this->draw('dashboard.html', [
        'settings' => $settings,
        'stats' => $stats,
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

    public function poliChartBatal()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'nm_poli'     => 'nm_poli',
            ])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('tgl_registrasi', '>=', date('Y-m-d'))
            ->where('stts','Batal')
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

    public function poliChartBaru()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'nm_poli'     => 'nm_poli',
            ])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('tgl_registrasi', '>=', date('Y-m-d'))
            ->where('stts_daftar','Baru')
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

    public function countCurrentVisiteBatal()
    {
        $date = date('Y-m-d');
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->where('stts','Batal')
            ->oneArray();

        return $record['count'];
    }

    public function countLastCurrentVisiteBatal()
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->where('stts','Batal')
            ->oneArray();

        return $record['count'];
    }

    public function countCurrentVisiteBaru()
    {
        $date = date('Y-m-d');
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->where('stts_daftar','Baru')
            ->oneArray();

        return $record['count'];
    }

    public function countLastCurrentVisiteBaru()
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->where('stts_daftar','Baru')
            ->oneArray();

        return $record['count'];
    }

    public function countCheckLab()
    {
        $date = date('Y-m-d');
        $record = $this->db('periksa_lab')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_periksa', $date)
            ->where('nip','Lab1')
            ->oneArray();

        return $record['count'];
    }

    public function countLastCheckLab()
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $record = $this->db('periksa_lab')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_periksa', $date)
            ->where('nip','Lab1')
            ->oneArray();

        return $record['count'];
    }

    public function countYearLab()
    {
        $date = date('Y');
        $record = $this->db('periksa_lab')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','Lab1')
            ->oneArray();

        return $record['count'];
    }

    public function countLastYearLab()
    {
        $date = date('Y', strtotime('-1 year'));
        $record = $this->db('periksa_lab')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','Lab1')
            ->oneArray();

        return $record['count'];
    }

    public function countMonthLab()
    {
        $date = date('Y-m');
        $record = $this->db('periksa_lab')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','Lab1')
            ->oneArray();

        return $record['count'];
    }

    public function countLastMonthLab()
    {
        $date = date('Y-m', strtotime('-1 month'));
        $record = $this->db('periksa_lab')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','Lab1')
            ->oneArray();

        return $record['count'];
    }

    public function countDrPerujukLab()
    {
        $date = date('Y-m-d');
        $query = $this->db('periksa_lab')
            ->select([
              'count'       => 'COUNT(DISTINCT periksa_lab.no_rawat)',
              'nm_dokter'     => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'periksa_lab.dokter_perujuk = dokter.kd_dokter')
            ->where('periksa_lab.tgl_periksa', $date)
            ->where('periksa_lab.nip','Lab1')
            ->group(['periksa_lab.dokter_perujuk'])
            ->desc('dokter.nm_dokter');


            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => [],
            ];

            foreach ($data as $value) {
                $return['labels'][] = $value['nm_dokter'];
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function countCheckRad()
    {
        $date = date('Y-m-d');
        $record = $this->db('periksa_radiologi')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_periksa', $date)
            ->where('nip','rad1')
            ->oneArray();

        return $record['count'];
    }

    public function countLastCheckRad()
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $record = $this->db('periksa_radiologi')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_periksa', $date)
            ->where('nip','rad1')
            ->oneArray();

        return $record['count'];
    }

    public function countYearRad()
    {
        $date = date('Y');
        $record = $this->db('periksa_radiologi')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','rad1')
            ->oneArray();

        return $record['count'];
    }

    public function countLastYearRad()
    {
        $date = date('Y', strtotime('-1 year'));
        $record = $this->db('periksa_radiologi')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','rad1')
            ->oneArray();

        return $record['count'];
    }

    public function countMonthRad()
    {
        $date = date('Y-m');
        $record = $this->db('periksa_radiologi')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','rad1')
            ->oneArray();

        return $record['count'];
    }

    public function countLastMonthRad()
    {
        $date = date('Y-m', strtotime('-1 month'));
        $record = $this->db('periksa_radiologi')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip','rad1')
            ->oneArray();

        return $record['count'];
    }

    public function countDrPerujukRad()
    {
        $date = date('Y-m-d');
        $query = $this->db('periksa_radiologi')
            ->select([
              'count'       => 'COUNT(DISTINCT periksa_radiologi.no_rawat)',
              'nm_dokter'     => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'periksa_radiologi.dokter_perujuk = dokter.kd_dokter')
            ->where('periksa_radiologi.tgl_periksa', $date)
            ->where('periksa_radiologi.nip','rad1')
            ->group(['periksa_radiologi.dokter_perujuk'])
            ->desc('dokter.nm_dokter');


            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => [],
            ];

            foreach ($data as $value) {
                $return['labels'][] = $value['nm_dokter'];
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function getPendaftaran()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));
        $stats['poliChart'] = $this->poliChartBatal();
        $stats['poliChartBaru'] = $this->poliChartBaru();
        $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
        $stats['getCurrentVisities'] = number_format($this->countCurrentVisite(),0,'','.');
        $stats['getCurrentVisitiesBatal'] = number_format($this->countCurrentVisiteBatal(),0,'','.');
        $stats['getCurrentVisitiesBaru'] = number_format($this->countCurrentVisiteBaru(),0,'','.');
        $stats['percentTotal'] = 0;
        if($this->countVisite() != 0) {
            $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
        }
        $stats['percentDays'] = 0;
        if($this->countCurrentVisite() != 0) {
            $stats['percentDays'] = number_format((($this->countCurrentVisite()-$this->countLastCurrentVisite())/$this->countCurrentVisite())*100,0,'','.');
        }
        $stats['percentDaysBatal'] = 0;
        if($this->countCurrentVisite() != 0) {
            $stats['percentDaysBatal'] = number_format((($this->countCurrentVisiteBatal()-$this->countLastCurrentVisiteBatal())/$this->countCurrentVisiteBatal())*100,0,'','.');
        }
        $stats['percentDaysBaru'] = 0;
        if($this->countCurrentVisite() != 0) {
            $stats['percentDaysBaru'] = number_format((($this->countCurrentVisiteBaru()-$this->countLastCurrentVisiteBaru())/$this->countCurrentVisiteBaru())*100,0,'','.');
        }
        
      return $this->draw('pendaftaran.html',[
        'settings' => $settings,
        'stats' => $stats,
      ]);
    }

    public function getRawatJalan()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      return $this->draw('rawatjalan.html');
    }

    public function getRawatInap()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      return $this->draw('rawatinap.html');
    }

    public function getDokter()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      return $this->draw('dokter.html');
    }

    public function getLaboratorium()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));
        $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
        $stats['getLab'] = number_format($this->countCheckLab(),0,'','.');
        $stats['getLabMonthly'] = number_format($this->countMonthLab(),0,'','.');
        $stats['getLabYearly'] = number_format($this->countYearLab(),0,'','.');
        $stats['getDrRujuk'] = $this->countDrPerujukLab();
        $stats['percentTotal'] = 0;
        if($this->countVisite() != 0) {
            $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
        }
        $stats['percentDays'] = 0;
        if($this->countCheckLab() != 0) {
            $stats['percentDays'] = number_format((($this->countCheckLab()-$this->countLastCheckLab())/$this->countCheckLab())*100,0,'','.');
        }
        $stats['percentMonths'] = 0;
        if($this->countMonthLab() != 0) {
            $stats['percentMonths'] = number_format((($this->countMonthLab()-$this->countLastMonthLab())/$this->countMonthLab())*100,0,'','.');
        }
        $stats['percentYears'] = 0;
        if($this->countYearLab() != 0) {
            $stats['percentYears'] = number_format((($this->countYearLab()-$this->countLastYearLab())/$this->countYearLab())*100,0,'','.');
        }
        
      return $this->draw('laboratorium.html',[
        'settings' => $settings,
        'stats' => $stats,
      ]);
    }

    public function getRadiologi()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));
        $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
        $stats['getLab'] = number_format($this->countCheckRad(),0,'','.');
        $stats['getLabMonthly'] = number_format($this->countMonthRad(),0,'','.');
        $stats['getLabYearly'] = number_format($this->countYearRad(),0,'','.');
        $stats['getDrRujuk'] = $this->countDrPerujukRad();
        $stats['percentTotal'] = 0;
        if($this->countVisite() != 0) {
            $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
        }
        $stats['percentDays'] = 0;
        if($this->countCheckRad() != 0) {
            $stats['percentDays'] = number_format((($this->countCheckRad()-$this->countLastCheckRad())/$this->countCheckRad())*100,0,'','.');
        }
        $stats['percentMonths'] = 0;
        if($this->countMonthRad() != 0) {
            $stats['percentMonths'] = number_format((($this->countMonthRad()-$this->countLastMonthRad())/$this->countMonthRad())*100,0,'','.');
        }
        $stats['percentYears'] = 0;
        if($this->countYearRad() != 0) {
            $stats['percentYears'] = number_format((($this->countYearRad()-$this->countLastYearRad())/$this->countYearRad())*100,0,'','.');
        }
        
      return $this->draw('radiologi.html',[
        'settings' => $settings,
        'stats' => $stats,
      ]);
    }

    public function getApotek()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      return $this->draw('apotek.html');
    }

    public function getFarmasi()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      return $this->draw('farmasi.html');
    }

    public function getKasir()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      return $this->draw('kasir.html');
    }

    public function getSettings()
    {
        $this->assign['penjab'] = $this->core->db('penjab')->toArray();
        $this->assign['manajemen'] = htmlspecialchars_array($this->settings('manajemen'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['manajemen'] as $key => $val) {
            $this->settings('manajemen', $key, $val);
        }
        $this->notify('success', 'Pengaturan manajemen telah disimpan');
        redirect(url([ADMIN, 'manajemen', 'settings']));
    }

}
