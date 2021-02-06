<?php
namespace Plugins\Manajemen;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'dashboard',
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
      return $this->draw('rawatjalan.html');
    }

    public function getRawatInap()
    {
      return $this->draw('rawatinap.html');
    }

    public function getDokter()
    {
      return $this->draw('dokter.html');
    }

    public function getLaboratorium()
    {
      return $this->draw('laboratorium.html');
    }

    public function getRadiologi()
    {
      return $this->draw('radiologi.html');
    }

    public function getApotek()
    {
      return $this->draw('apotek.html');
    }

    public function getFarmasi()
    {
      return $this->draw('farmasi.html');
    }

    public function getKasir()
    {
      return $this->draw('kasir.html');
    }

}
