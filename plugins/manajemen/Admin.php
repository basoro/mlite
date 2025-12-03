<?php
namespace Plugins\Manajemen;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public $assign;

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'Pengaturan' => 'settings'
        ];
    }

    public function getManage()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));
      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

      $settings = htmlspecialchars_array($this->settings('manajemen'));

      // Baca parameter filter tanggal dari URL
      $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
      $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;

      $isValidDate = function($d) {
          return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
      };

      $useFilter = false;
      if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
          $useFilter = true;
      } else {
          $start_date = null;
          $end_date = null;
      }

      if ($useFilter) {
          // Statistik berdasar rentang tanggal
          $stats['getPasiens'] = number_format($this->countPasienByDateRange($start_date, $end_date),0,'','.');
          $stats['getVisities'] = number_format($this->countVisiteByDateRange($start_date, $end_date),0,'','.');
          $stats['getYearVisities'] = number_format($this->countYearVisiteByDateRange($start_date, $end_date),0,'','.');
          $stats['getMonthVisities'] = number_format($this->countMonthVisiteByDateRange($start_date, $end_date),0,'','.');
          $stats['getCurrentVisities'] = number_format($this->countCurrentVisiteByDateRange($start_date, $end_date),0,'','.');

          // Persentase untuk rentang custom dinolkan
          $stats['getLastYearVisities'] = 0;
          $stats['getLastMonthVisities'] = 0;
          $stats['getLastCurrentVisities'] = 0;
          $stats['percentTotal'] = 0;
          $stats['percentYear'] = 0;
          $stats['percentMonth'] = 0;
          $stats['percentDays'] = 0;

          // Chart berdasarkan rentang tanggal
          $stats['poliChart'] = $this->poliChartByDateRange($start_date, $end_date);
          $stats['KunjunganTahunChart'] = $this->KunjunganTahunChartByDateRange($start_date, $end_date);
          $stats['RanapTahunChart'] = $this->RanapTahunChart();
          $stats['RujukTahunChart'] = $this->RujukTahunChart();

          // Cara bayar berdasar rentang tanggal
          $stats['tunai'] = $this->db('reg_periksa')
              ->select(['count' => 'COUNT(DISTINCT no_rawat)'])
              ->where('kd_pj', $settings['penjab_umum'])
              ->where('tgl_registrasi', '>=', $start_date)
              ->where('tgl_registrasi', '<=', $end_date)
              ->oneArray();
          $stats['bpjs'] = $this->db('reg_periksa')
              ->select(['count' => 'COUNT(DISTINCT no_rawat)'])
              ->where('kd_pj', $settings['penjab_bpjs'])
              ->where('tgl_registrasi', '>=', $start_date)
              ->where('tgl_registrasi', '<=', $end_date)
              ->oneArray();
          $stats['lainnya'] = $this->db('reg_periksa')
              ->select(['count' => 'COUNT(DISTINCT no_rawat)'])
              ->where('kd_pj', '!=', $settings['penjab_umum'])
              ->where('kd_pj', '!=', $settings['penjab_bpjs'])
              ->where('tgl_registrasi', '>=', $start_date)
              ->where('tgl_registrasi', '<=', $end_date)
              ->oneArray();
      } else {
          // Statistik default (seperti semula)
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
      }

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

      return $this->draw('manage.html', [
        'settings' => $settings,
        'stats' => $stats,
        'pasien' => $this->db('pasien')->join('penjab', 'penjab.kd_pj = pasien.kd_pj')->desc('tgl_daftar')->limit('5')->toArray(),
        'dokter' => $this->db('dokter')->join('spesialis', 'spesialis.kd_sps = dokter.kd_sps')->join('jadwal', 'jadwal.kd_dokter = dokter.kd_dokter')->where('jadwal.hari_kerja', $hari)->where('dokter.status', '1')->group(['dokter.kd_dokter', 'jadwal.jam_mulai', 'jadwal.jam_selesai'])->rand()->limit('6')->toArray(),
        'start_date' => $start_date,
        'end_date' => $end_date
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

    // ====== Metode statistik berdasar rentang tanggal ======
    public function countVisiteByDateRange($start_date, $end_date)
    {
        $record = $this->db('reg_periksa')
            ->select(['count' => 'COUNT(DISTINCT no_rawat)'])
            ->where('tgl_registrasi', '>=', $start_date)
            ->where('tgl_registrasi', '<=', $end_date)
            ->oneArray();
        return $record['count'];
    }

    public function countPasienByDateRange($start_date, $end_date)
    {
        $record = $this->db('pasien')
            ->select(['count' => 'COUNT(DISTINCT no_rkm_medis)'])
            ->where('tgl_daftar', '>=', $start_date)
            ->where('tgl_daftar', '<=', $end_date)
            ->oneArray();
        return $record['count'];
    }

    public function countYearVisiteByDateRange($start_date, $end_date)
    {
        // Hitung jumlah kunjungan pada rentang tanggal yang diberikan
        return $this->countVisiteByDateRange($start_date, $end_date);
    }

    public function countMonthVisiteByDateRange($start_date, $end_date)
    {
        // Hitung jumlah kunjungan pada rentang tanggal yang diberikan
        return $this->countVisiteByDateRange($start_date, $end_date);
    }

    public function countCurrentVisiteByDateRange($start_date, $end_date)
    {
        // Hitung jumlah kunjungan pada rentang tanggal yang diberikan
        return $this->countVisiteByDateRange($start_date, $end_date);
    }

    public function poliChartByDateRange($start_date, $end_date)
    {
        $query = $this->db('reg_periksa')
            ->select([
                'count'   => 'COUNT(DISTINCT no_rawat)',
                'nm_poli' => 'nm_poli',
            ])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('tgl_registrasi', '>=', $start_date)
            ->where('tgl_registrasi', '<=', $end_date)
            ->group(['reg_periksa.kd_poli'])
            ->desc('nm_poli');

        $data = $query->toArray();
        $return = [
            'labels' => [],
            'visits' => [],
        ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_poli'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function KunjunganTahunChartByDateRange($start_date, $end_date)
    {
        $query = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
                'label' => 'EXTRACT(MONTH FROM tgl_registrasi)'
            ])
            ->where('tgl_registrasi', '>=', $start_date)
            ->where('tgl_registrasi', '<=', $end_date)
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

        $data = $query->toArray();
        $return = [
            'labels' => [],
            'visits' => []
        ];
        foreach ($data as $value) {
            $return['labels'][] = date('M', mktime(0, 0, 0, $value['label'], 1));
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countCurrentTempPresensi()
    {
        $tgl_presensi = date('Y-m-d');
        $record = $this->db('temporary_presensi')
            ->select([
                'count' => 'COUNT(DISTINCT id)',
            ])
            ->like ('jam_datang', $tgl_presensi.'%')
            ->oneArray();

        return $record['count'];
    }

    public function getTotalAbsen(){
        $total=$this->countCurrentTempPresensi()+$this->countRkpPresensi() ;
        return $total;
    }

    public function getBelumAbsen(){
        $total=$this->getJadwalJaga()-$this->getTotalAbsen() ;
        echo $total;
        return $total;
    }

    public function countPegawai()
    {
        $status = 'AKTIF';
        $record = $this->db('pegawai')
            ->select([
                'count' => 'COUNT(DISTINCT id)',
            ])
            ->where ('stts_aktif', $status)
            ->oneArray();

        return $record['count'];
    }

    public function countRkpPresensi()
    {
        $tgl_presensi = date('Y-m-d');
        $record = $this->db('rekap_presensi')
            ->select([
                'count' => 'COUNT(DISTINCT id)',
            ])
            ->like ('jam_datang', $tgl_presensi.'%')
            ->oneArray();

        return $record['count'];
    }

    public function getJadwalJaga()
    {
      $date = date('j');
      $bulan = date('m');
      $tahun = date('y');
      $data = array_column($this->db('jadwal_pegawai')->where('h'.$date, '!=', '')->where('bulan', $bulan)->where('tahun', $tahun)->toArray(), 'h'.$date);
    //   //print_r($data);
    //   print("<pre>".print_r($data,true)."</pre>");
       $hasil = count($data);
    //   echo $hasil;
    //   exit();
      return $hasil;
    }

    public function getIjin()
    {
        $record = $this->db('rekap_presensi')
            ->select([
                'count' => 'COUNT(DISTINCT id)',
            ])
            ->where ('keterangan', '!=' , '')
            ->where ('keterangan', '!=' , '-')
          	->where('jam_datang', '>=', date('Y-m-d').' 00:00:00')
            ->oneArray();
        //echo $record;
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
              'label'       => 'EXTRACT(MONTH FROM tgl_registrasi)'
            ])
            ->like('tgl_registrasi', date('Y').'%')
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => []
            ];
            foreach ($data as $value) {
                $return['labels'][] = date("M", mktime(0, 0, 0, $value['label'], 1));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function RanapTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'EXTRACT(MONTH FROM tgl_registrasi)'
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
                $return['labels'][] = date("M", mktime(0, 0, 0, $value['label'], 1));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function RujukTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'EXTRACT(MONTH FROM tgl_registrasi)'
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
                $return['labels'][] = date("M", mktime(0, 0, 0, $value['label'], 1));
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

    public function presensiChartHari()
    {
            $return = [
                'labels'  => 'Belum Absen',
                'visits'  => $this->getBelumAbsen(),
            ];


        return $return;
    }

    public function countCurrentVisiteBatal($stts)
    {
        $date = date('Y-m-d');
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->where('stts',$stts)
            ->oneArray();

        return $record['count'];
    }

    public function countLastCurrentVisiteBatal($stts)
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->where('stts',$stts)
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

    // =======================
    // Date-range helper methods
    // =======================

    public function countCurrentVisiteBatalByDateRange($stts, $start_date, $end_date)
    {
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', '>=', $start_date)
            ->where('tgl_registrasi', '<=', $end_date)
            ->where('stts', $stts)
            ->oneArray();
        return $record['count'];
    }

    public function countCurrentVisiteBaruByDateRange($start_date, $end_date)
    {
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', '>=', $start_date)
            ->where('tgl_registrasi', '<=', $end_date)
            ->where('stts_daftar','Baru')
            ->oneArray();
        return $record['count'];
    }

    public function poliChartBatalByDateRange($start_date, $end_date)
    {
        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'nm_poli'     => 'nm_poli',
            ])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('tgl_registrasi', '>=', $start_date)
            ->where('tgl_registrasi', '<=', $end_date)
            ->where('stts','Batal')
            ->group(['reg_periksa.kd_poli'])
            ->desc('nm_poli');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_poli'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function poliChartBaruByDateRange($start_date, $end_date)
    {
        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'nm_poli'     => 'nm_poli',
            ])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('tgl_registrasi', '>=', $start_date)
            ->where('tgl_registrasi', '<=', $end_date)
            ->where('stts_daftar','Baru')
            ->group(['reg_periksa.kd_poli'])
            ->desc('nm_poli');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_poli'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countDxByDateRange($start_date, $end_date)
    {
        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(diagnosa_pasien.kd_penyakit) as count ,penyakit.nm_penyakit FROM diagnosa_pasien JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat JOIN penyakit ON diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit WHERE diagnosa_pasien.status ='Ralan' and reg_periksa.tgl_registrasi BETWEEN :start AND :end GROUP BY diagnosa_pasien.kd_penyakit, penyakit.nm_penyakit ORDER BY count DESC Limit 10");
        $stmt->bindValue(':start', $start_date);
        $stmt->bindValue(':end', $end_date);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_penyakit'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countPxDrRjByDateRange($start_date, $end_date)
    {
        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT reg_periksa.no_rawat)',
              'nm_dokter'   => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'reg_periksa.kd_dokter = dokter.kd_dokter')
            ->where('reg_periksa.tgl_registrasi', '>=', $start_date)
            ->where('reg_periksa.tgl_registrasi', '<=', $end_date)
            ->group(['reg_periksa.kd_dokter'])
            ->desc('dokter.nm_dokter');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_dokter'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countPxDrRiByDateRange($start_date, $end_date)
    {
        $query = $this->db('kamar_inap')
            ->select([
              'count'       => 'COUNT(DISTINCT kamar_inap.no_rawat)',
              'nm_dokter'   => 'dokter.nm_dokter',
            ])
            ->join('dpjp_ranap', 'dpjp_ranap.no_rawat = kamar_inap.no_rawat')
            ->join('dokter', 'dpjp_ranap.kd_dokter = dokter.kd_dokter')
            ->where('kamar_inap.stts_pulang', '-')
            ->where('kamar_inap.tgl_masuk', '>=', $start_date)
            ->where('kamar_inap.tgl_masuk', '<=', $end_date)
            ->group(['dpjp_ranap.kd_dokter'])
            ->desc('dokter.nm_dokter');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_dokter'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countResepDrByDateRange($start_date, $end_date)
    {
        $query = $this->db('resep_obat')
            ->select([
              'count'       => 'COUNT(DISTINCT resep_obat.no_rawat)',
              'nm_dokter'   => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'resep_obat.kd_dokter = dokter.kd_dokter')
            ->where('resep_obat.tgl_peresepan', '>=', $start_date)
            ->where('resep_obat.tgl_peresepan', '<=', $end_date)
            ->group(['resep_obat.kd_dokter'])
            ->desc('dokter.nm_dokter');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_dokter'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countRanapByDateRange($tgl, $stts, $start_date, $end_date)
    {
        $arr = is_array($stts) ? 'Yes' : 'No';
        if ($arr == 'Yes') {
            $poliklinik = implode("','", $stts);
        } else {
            $poliklinik = str_replace(",","','", $stts);
        }
        $sql = "SELECT COUNT(DISTINCT no_rawat) as count FROM kamar_inap WHERE $tgl BETWEEN :start AND :end AND stts_pulang IN ('$poliklinik')";
        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->bindValue(':start', $start_date);
        $stmt->bindValue(':end', $end_date);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return $count;
    }

    public function countKamarInapByDateRange($start_date, $end_date)
    {
        $query = $this->db('kamar_inap')
            ->select([
              'count'       => 'COUNT(DISTINCT kamar_inap.no_rawat)',
              'nm_bangsal'  => 'bangsal.nm_bangsal',
            ])
            ->join('kamar', 'kamar_inap.kd_kamar = kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal = bangsal.kd_bangsal')
            ->where('kamar_inap.stts_pulang', '-')
            ->where('kamar_inap.tgl_masuk', '>=', $start_date)
            ->where('kamar_inap.tgl_masuk', '<=', $end_date)
            ->group(['bangsal.kd_bangsal'])
            ->desc('bangsal.nm_bangsal');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_bangsal'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countCheckByDateRange($table, $where, $start_date, $end_date)
    {
        $record = $this->db($table)
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_periksa', '>=', $start_date)
            ->where('tgl_periksa', '<=', $end_date)
            ->where('nip', $where)
            ->oneArray();
        return $record['count'];
    }

    public function countDrPerujukLabByDateRange($start_date, $end_date)
    {
        $query = $this->db('periksa_lab')
            ->select([
              'count'       => 'COUNT(DISTINCT periksa_lab.no_rawat)',
              'nm_dokter'   => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'periksa_lab.dokter_perujuk = dokter.kd_dokter')
            ->where('periksa_lab.tgl_periksa', '>=', $start_date)
            ->where('periksa_lab.tgl_periksa', '<=', $end_date)
            ->where('periksa_lab.nip','Lab1')
            ->group(['periksa_lab.dokter_perujuk'])
            ->desc('dokter.nm_dokter');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_dokter'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countDrPerujukRadByDateRange($start_date, $end_date)
    {
        $query = $this->db('periksa_radiologi')
            ->select([
              'count'       => 'COUNT(DISTINCT periksa_radiologi.no_rawat)',
              'nm_dokter'   => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'periksa_radiologi.dokter_perujuk = dokter.kd_dokter')
            ->where('periksa_radiologi.tgl_periksa', '>=', $start_date)
            ->where('periksa_radiologi.tgl_periksa', '<=', $end_date)
            ->where('periksa_radiologi.nip','rad1')
            ->group(['periksa_radiologi.dokter_perujuk'])
            ->desc('dokter.nm_dokter');
        $data = $query->toArray();
        $return = [ 'labels' => [], 'visits' => [] ];
        foreach ($data as $value) {
            $return['labels'][] = $value['nm_dokter'];
            $return['visits'][] = $value['count'];
        }
        return $return;
    }

    public function countTransaksiFarmasiByDateRange($start_date, $end_date)
    {
        $record = $this->db('detail_pemberian_obat')
            ->where('tgl_perawatan', '>=', $start_date)
            ->where('tgl_perawatan', '<=', $end_date)
            ->count();
        return $record ?? 0;
    }

    public function countCurrentTempPresensiByDateRange($start_date, $end_date)
    {
        $record = $this->db('temporary_presensi')
            ->select([
                'count' => 'COUNT(DISTINCT id)',
            ])
            ->where('jam_datang', '>=', $start_date.' 00:00:00')
            ->where('jam_datang', '<=', $end_date.' 23:59:59')
            ->oneArray();
        return $record['count'];
    }

    public function countRkpPresensiByDateRange($start_date, $end_date)
    {
        $record = $this->db('rekap_presensi')
            ->select([
                'count' => 'COUNT(DISTINCT id)',
            ])
            ->where('jam_datang', '>=', $start_date.' 00:00:00')
            ->where('jam_datang', '<=', $end_date.' 23:59:59')
            ->oneArray();
        return $record['count'];
    }

    public function getTotalAbsenByDateRange($start_date, $end_date)
    {
        return $this->countCurrentTempPresensiByDateRange($start_date, $end_date)
             + $this->countRkpPresensiByDateRange($start_date, $end_date);
    }

    public function presensiChartByDateRange($start_date, $end_date)
    {
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);

        $stmt = $this->db()->pdo()->prepare(
            "SELECT COUNT(photo) as count, COUNT(IF(keterangan != '-', 1, NULL)) as count2, DATE(jam_datang) as jam
             FROM rekap_presensi
             WHERE jam_datang >= :start AND jam_datang <= :end
             GROUP BY DATE(jam_datang)"
        );
        $stmt->bindValue(':start', $start_date.' 00:00:00');
        $stmt->bindValue(':end', $end_date.' 23:59:59');
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $return = [
            'labels'   => [],
            'readable' => [],
            'visits'   => [],
            'visits2'  => [],
        ];

        // Inisialisasi semua hari dalam rentang
        while ($start_ts <= $end_ts) {
            $return['labels'][] = '"'.date('Y-m-d', $start_ts).'"';
            $return['readable'][] = '"'.date('d M Y', $start_ts).'"';
            $return['visits'][] = 0;
            $return['visits2'][] = 0;
            $start_ts = strtotime('+1 day', $start_ts);
        }

        foreach ($data as $day) {
            $index = array_search('"'.$day['jam'].'"', $return['labels']);
            if ($index === false) {
                continue;
            }
            $return['visits'][$index] = (int)$day['count'];
            $return['visits2'][$index] = (int)$day['count2'];
        }

        return $return;
    }

    public function getJadwalJagaByDateRange($start_date, $end_date)
    {
        $start = new \DateTime($start_date);
        $end = new \DateTime($end_date);
        $end->setTime(0,0,0);
        $total = 0;
        while ($start <= $end) {
            $day = (int)$start->format('j');
            $month = $start->format('m');
            $year2 = $start->format('y');
            $column = 'h'.$day;
            // Hitung jumlah entri jadwal yang tidak kosong untuk hari tersebut
            $count = $this->db('jadwal_pegawai')
                ->where($column, '!=', '')
                ->where('bulan', $month)
                ->where('tahun', $year2)
                ->count();
            $total += (int)$count;
            $start->modify('+1 day');
        }
        return $total;
    }

    public function getBelumAbsenByDateRange($start_date, $end_date)
    {
        $harus = $this->getJadwalJagaByDateRange($start_date, $end_date);
        $total = $this->getTotalAbsenByDateRange($start_date, $end_date);
        $belum = $harus - $total;
        return $belum < 0 ? 0 : $belum;
    }

    public function getIjinByDateRange($start_date, $end_date)
    {
        $record = $this->db('rekap_presensi')
            ->select([
                'count' => 'COUNT(DISTINCT id)',
            ])
            ->where ('keterangan', '!=' , '')
            ->where ('keterangan', '!=' , '-')
            ->where('jam_datang', '>=', $start_date.' 00:00:00')
            ->where('jam_datang', '<=', $end_date.' 23:59:59')
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

    public function countCheck($table,$where)
    {
        $date = date('Y-m-d');
        $record = $this->db($table)
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_periksa', $date)
            ->where('nip',$where)
            ->oneArray();

        return $record['count'];
    }

    public function countLastCheck($table,$where)
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $record = $this->db($table)
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_periksa', $date)
            ->where('nip',$where)
            ->oneArray();

        return $record['count'];
    }

    public function countYear($table,$where)
    {
        $date = date('Y');
        $record = $this->db($table)
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip',$where)
            ->oneArray();

        return $record['count'];
    }

    public function countLastYear($table,$where)
    {
        $date = date('Y', strtotime('-1 year'));
        $record = $this->db($table)
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip',$where)
            ->oneArray();

        return $record['count'];
    }

    public function countMonth($table,$where)
    {
        $date = date('Y-m');
        $record = $this->db($table)
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip',$where)
            ->oneArray();

        return $record['count'];
    }

    public function countLastMonth($table,$where)
    {
        $date = date('Y-m', strtotime('-1 month'));
        $record = $this->db($table)
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->like('tgl_periksa', $date.'%')
            ->where('nip',$where)
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

    public function countRanap($tgl,$stts)
    {
        $date = date('Y-m-d');
        $arr = is_array($stts) ? 'Yes' : 'No';
        if ($arr == 'Yes') {
            $poliklinik = implode("','",$stts);
        } else {
            $poliklinik = str_replace(",","','", $stts);
        }
        $query = $this->db()->pdo()->prepare("SELECT COUNT(DISTINCT no_rawat) as count FROM kamar_inap WHERE $tgl = '$date' AND stts_pulang IN ('$poliklinik')");
        $query->execute();
        $count = $query->fetchColumn();
        return $count;
    }

    public function countLastRanap($tgl,$stts)
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $arr = is_array($stts) ? 'Yes' : 'No';
        if ($arr == 'Yes') {
            $poliklinik = implode("','",$stts);
        } else {
            $poliklinik = str_replace(",","','", $stts);
        }
        $query = $this->db()->pdo()->prepare("SELECT COUNT(DISTINCT no_rawat) as count FROM kamar_inap WHERE $tgl = '$date' AND stts_pulang IN ('$poliklinik')");
        $query->execute();
        $count = $query->fetchColumn();
        return $count;
    }

    public function countKamarInap()
    {
        $date = date('Y-m-d');
        $query = $this->db('kamar_inap')
            ->select([
              'count'       => 'COUNT(DISTINCT kamar_inap.no_rawat)',
              'nm_bangsal'     => 'bangsal.nm_bangsal',
            ])
            ->join('kamar', 'kamar_inap.kd_kamar = kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal = bangsal.kd_bangsal')
            ->where('kamar_inap.stts_pulang', '-')
            ->group(['bangsal.kd_bangsal'])
            ->desc('bangsal.nm_bangsal');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => [],
            ];

            foreach ($data as $value) {
                $return['labels'][] = $value['nm_bangsal'];
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function countDx()
    {
        $date = date('Y-m-d');
        $query = $this->db()->pdo()->prepare("SELECT COUNT(diagnosa_pasien.kd_penyakit) as count ,penyakit.nm_penyakit FROM diagnosa_pasien JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat JOIN penyakit ON diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit WHERE diagnosa_pasien.status ='Ralan' and reg_periksa.tgl_registrasi like '%$date%' GROUP BY diagnosa_pasien.kd_penyakit, penyakit.nm_penyakit ORDER BY `count`  DESC Limit 10");
        $query->execute();

            $data = $query->fetchAll(\PDO::FETCH_ASSOC);

            $return = [
                'labels'  => [],
                'visits'  => [],
            ];

            foreach ($data as $value) {
                $return['labels'][] = $value['nm_penyakit'];
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function countPxDrRj()
    {
        $date = date('Y-m-d');
        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT reg_periksa.no_rawat)',
              'nm_dokter'     => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'reg_periksa.kd_dokter = dokter.kd_dokter')
            ->where('reg_periksa.tgl_registrasi', $date)
            ->group(['reg_periksa.kd_dokter'])
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

    public function countPxDrRi()
    {
        $date = date('Y-m-d');
        $query = $this->db('kamar_inap')
            ->select([
              'count'       => 'COUNT(DISTINCT kamar_inap.no_rawat)',
              'nm_dokter'     => 'dokter.nm_dokter',
            ])
            ->join('dpjp_ranap', 'dpjp_ranap.no_rawat = kamar_inap.no_rawat')
            ->join('dokter', 'dpjp_ranap.kd_dokter = dokter.kd_dokter')
            ->where('kamar_inap.stts_pulang', '-')
            ->group(['dpjp_ranap.kd_dokter'])
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

    public function countResepDr()
    {
        $date = date('Y-m-d');
        $query = $this->db('resep_obat')
            ->select([
              'count'       => 'COUNT(DISTINCT resep_obat.no_rawat)',
              'nm_dokter'     => 'dokter.nm_dokter',
            ])
            ->join('dokter', 'resep_obat.kd_dokter = dokter.kd_dokter')
            ->where('resep_obat.tgl_peresepan', $date)
            ->group(['resep_obat.kd_dokter'])
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

    public function sumPdptLain()
    {
        $date = date('Y-m-d');
        $record = $this->db('mlite_penjualan_billing')
            ->select([
                'sum' => 'SUM(jumlah_bayar)',
            ])
            ->where('tanggal', $date)
            ->oneArray();

        return $record['sum'];
    }

    public function getPendaftaran()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            // Statistik berdasarkan rentang tanggal
            $stats['poliChart'] = $this->poliChartBatalByDateRange($start_date, $end_date);
            $stats['poliChartBaru'] = $this->poliChartBaruByDateRange($start_date, $end_date);
            $stats['getVisities'] = number_format($this->countVisiteByDateRange($start_date, $end_date),0,'','.');
            $stats['getCurrentVisities'] = number_format($this->countCurrentVisiteByDateRange($start_date, $end_date),0,'','.');
            $stats['getCurrentVisitiesBatal'] = number_format($this->countCurrentVisiteBatalByDateRange('Batal', $start_date, $end_date),0,'','.');
            $stats['getCurrentVisitiesBaru'] = number_format($this->countCurrentVisiteBaruByDateRange($start_date, $end_date),0,'','.');
            // Persentase dinonaktifkan untuk rentang kustom
            $stats['percentTotal'] = 0;
            $stats['percentDays'] = 0;
            $stats['percentDaysBatal'] = 0;
            $stats['percentDaysBaru'] = 0;
        } else {
            // Statistik default (harian/bawaan)
            $stats['poliChart'] = $this->poliChartBatal();
            $stats['poliChartBaru'] = $this->poliChartBaru();
            $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
            $stats['getCurrentVisities'] = number_format($this->countCurrentVisite(),0,'','.');
            $stats['getCurrentVisitiesBatal'] = number_format($this->countCurrentVisiteBatal('Batal'),0,'','.');
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
            if($this->countCurrentVisiteBatal('Batal') != 0) {
                $stats['percentDaysBatal'] = number_format((($this->countCurrentVisiteBatal('Batal')-$this->countLastCurrentVisiteBatal('Batal'))/$this->countCurrentVisiteBatal('Batal'))*100,0,'','.');
            }
            $stats['percentDaysBaru'] = 0;
            if($this->countCurrentVisiteBaru() != 0) {
                $stats['percentDaysBaru'] = number_format((($this->countCurrentVisiteBaru()-$this->countLastCurrentVisiteBaru())/$this->countCurrentVisiteBaru())*100,0,'','.');
            }
        }

      return $this->draw('pendaftaran.html',[
        'settings' => $settings,
        'stats' => $stats,
        'start_date' => $start_date,
        'end_date' => $end_date,
      ]);
    }

    public function getRawatJalan()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            $stats['poliChartBaru'] = $this->countDxByDateRange($start_date, $end_date);
            $stats['getVisities'] = number_format($this->countVisiteByDateRange($start_date, $end_date),0,'','.');
            $stats['getRujuk'] = number_format($this->countCurrentVisiteBatalByDateRange('Dirujuk', $start_date, $end_date),0,'','.');
            $stats['getRawat'] = number_format($this->countCurrentVisiteBatalByDateRange('Dirawat', $start_date, $end_date),0,'','.');
            $stats['getSudah'] = number_format($this->countCurrentVisiteBatalByDateRange('Sudah', $start_date, $end_date),0,'','.');
            $stats['percentTotal'] = 0;
            $stats['percentDays'] = 0;
            $stats['percentDaysBatal'] = 0;
            $stats['percentDaysBaru'] = 0;
        } else {
            $stats['poliChartBaru'] = $this->countDx();
            $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
            $stats['getRujuk'] = number_format($this->countCurrentVisiteBatal('Dirujuk'),0,'','.');
            $stats['getRawat'] = number_format($this->countCurrentVisiteBatal('Dirawat'),0,'','.');
            $stats['getSudah'] = number_format($this->countCurrentVisiteBatal('Sudah'),0,'','.');
            $stats['percentTotal'] = 0;
            if($this->countVisite() != 0) {
                $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
            }
            $stats['percentDays'] = 0;
            if($this->countCurrentVisiteBatal('Dirujuk') != 0) {
                $stats['percentDays'] = number_format((($this->countCurrentVisiteBatal('Dirujuk')-$this->countLastCurrentVisiteBatal('Dirujuk'))/$this->countCurrentVisiteBatal('Dirujuk'))*100,0,'','.');
            }
            $stats['percentDaysBatal'] = 0;
            if($this->countCurrentVisiteBatal('Batal') != 0) {
                $stats['percentDaysBatal'] = number_format((($this->countCurrentVisiteBatal('Batal')-$this->countLastCurrentVisiteBatal('Batal'))/$this->countCurrentVisiteBatal('Batal'))*100,0,'','.');
            }
            $stats['percentDaysBaru'] = 0;
            if($this->countCurrentVisiteBatal('Sudah') != 0) {
                $stats['percentDaysBaru'] = number_format((($this->countCurrentVisiteBatal('Sudah')-$this->countLastCurrentVisiteBatal('Sudah'))/$this->countCurrentVisiteBatal('Sudah'))*100,0,'','.');
            }
        }

      return $this->draw('rawatjalan.html',[
        'settings' => $settings,
        'stats' => $stats,
        'start_date' => $start_date,
        'end_date' => $end_date,
      ]);
    }

    public function getRawatInap()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            $stats['poliChart'] = $this->countKamarInapByDateRange($start_date, $end_date);
            $stats['getVisities'] = number_format($this->countVisiteByDateRange($start_date, $end_date),0,'','.');
            $stats['getRanapIn'] = number_format($this->countRanapByDateRange('tgl_masuk','-', $start_date, $end_date),0,'','.');
            $stats['getRanapOut'] = number_format($this->countRanapByDateRange('tgl_keluar',array('APS','Membaik'), $start_date, $end_date),0,'','.');
            $stats['getRanapDead'] = number_format($this->countRanapByDateRange('tgl_keluar','Meninggal', $start_date, $end_date),0,'','.');
            $stats['percentTotal'] = 0;
            $stats['percentIn'] = 0;
            $stats['percentOut'] = 0;
            $stats['percentDead'] = 0;
            $stats['percentYear'] = 0;
            $stats['percentMonth'] = 0;
            $stats['percentDays'] = 0;
        } else {
            $stats['poliChart'] = $this->countKamarInap();
            $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
            $stats['getRanapIn'] = number_format($this->countRanap('tgl_masuk','-'),0,'','.');
            $stats['getRanapOut'] = number_format($this->countRanap('tgl_keluar',array('APS','Membaik')),0,'','.');
            $stats['getRanapDead'] = number_format($this->countRanap('tgl_keluar','Meninggal'),0,'','.');

            $stats['percentTotal'] = 0;
            if($this->countVisite() != 0) {
                $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
            }

            $stats['percentIn'] = 0;
            if($this->countRanap('tgl_masuk','-') != 0) {
                $stats['percentIn'] = number_format((($this->countRanap('tgl_masuk','-')-$this->countLastRanap('tgl_masuk','-'))/$this->countRanap('tgl_masuk','-'))*100,0,'','.');
            }

            $stats['percentOut'] = 0;
            if($this->countRanap('tgl_keluar',array('APS','Membaik')) != 0) {
                $stats['percentOut'] = number_format((($this->countRanap('tgl_keluar',array('APS','Membaik'))-$this->countLastRanap('tgl_keluar',array('APS','Membaik')))/$this->countRanap('tgl_keluar',array('APS','Membaik')))*100,0,'','.');
            }

            $stats['percentDead'] = 0;
            if($this->countRanap('tgl_keluar','Meninggal') != 0) {
                $stats['percentDead'] = number_format((($this->countRanap('tgl_keluar','Meninggal')-$this->countLastRanap('tgl_keluar','Meninggal'))/$this->countRanap('tgl_keluar','Meninggal'))*100,0,'','.');
            }
            $stats['percentYear'] = 0;
            $stats['percentMonth'] = 0;
            $stats['percentDays'] = 0;
        }

      return $this->draw('rawatinap.html',[
        'settings' => $settings,
        'stats' => $stats,
        'start_date' => $start_date,
        'end_date' => $end_date,
      ]);
    }

    public function getDokter()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            $stats['poliChart'] = $this->countPxDrRjByDateRange($start_date, $end_date);
            $stats['ranapChart'] = $this->countPxDrRiByDateRange($start_date, $end_date);
        } else {
            $stats['poliChart'] = $this->countPxDrRj();
            $stats['ranapChart'] = $this->countPxDrRi();
        }

        return $this->draw('dokter.html',[
            'settings' => $settings,
            'stats' => $stats,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    public function getLaboratorium()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            $stats['getVisities'] = number_format($this->countVisiteByDateRange($start_date, $end_date),0,'','.');
            $stats['getLab'] = number_format($this->countCheckByDateRange('periksa_lab','Lab1', $start_date, $end_date),0,'','.');
            $stats['getLabMonthly'] = 0;
            $stats['getLabYearly'] = 0;
            $stats['getDrRujuk'] = $this->countDrPerujukLabByDateRange($start_date, $end_date);
            $stats['percentTotal'] = 0;
            $stats['percentDays'] = 0;
            $stats['percentMonths'] = 0;
            $stats['percentYears'] = 0;
            $stats['percentDaysBaru'] = 0;
            $stats['percentDaysBatal'] = 0;
        } else {
            $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
            $stats['getLab'] = number_format($this->countCheck('periksa_lab','Lab1'),0,'','.');
            $stats['getLabMonthly'] = number_format($this->countMonth('periksa_lab','Lab1'),0,'','.');
            $stats['getLabYearly'] = number_format($this->countYear('periksa_lab','Lab1'),0,'','.');
            $stats['getDrRujuk'] = $this->countDrPerujukLab();
            $stats['percentTotal'] = 0;
            if($this->countVisite() != 0) {
                $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
            }
            $stats['percentDays'] = 0;
            if($this->countCheck('periksa_lab','Lab1') != 0) {
                $stats['percentDays'] = number_format((($this->countCheck('periksa_lab','Lab1')-$this->countLastCheck('periksa_lab','Lab1'))/$this->countCheck('periksa_lab','Lab1'))*100,0,'','.');
            }
            $stats['percentMonths'] = 0;
            if($this->countMonth('periksa_lab','Lab1') != 0) {
                $stats['percentMonths'] = number_format((($this->countMonth('periksa_lab','Lab1')-$this->countLastMonth('periksa_lab','Lab1'))/$this->countMonth('periksa_lab','Lab1'))*100,0,'','.');
            }
            $stats['percentYears'] = 0;
            if($this->countYear('periksa_lab','Lab1') != 0) {
                $stats['percentYears'] = number_format((($this->countYear('periksa_lab','Lab1')-$this->countLastYear('periksa_lab','Lab1'))/$this->countYear('periksa_lab','Lab1'))*100,0,'','.');
            }
            $stats['percentDaysBaru'] = 0;
            $stats['percentDaysBatal'] = 0;
        }

      return $this->draw('laboratorium.html',[
        'settings' => $settings,
        'stats' => $stats,
        'start_date' => $start_date,
        'end_date' => $end_date,
      ]);
    }

    public function getRadiologi()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            $stats['getVisities'] = number_format($this->countVisiteByDateRange($start_date, $end_date),0,'','.');
            $stats['getLab'] = number_format($this->countCheckByDateRange('periksa_radiologi','rad1', $start_date, $end_date),0,'','.');
            $stats['getLabMonthly'] = 0;
            $stats['getLabYearly'] = 0;
            $stats['getDrRujuk'] = $this->countDrPerujukRadByDateRange($start_date, $end_date);
            $stats['percentTotal'] = 0;
            $stats['percentDays'] = 0;
            $stats['percentMonths'] = 0;
            $stats['percentYears'] = 0;
            $stats['percentDaysBaru'] = 0;
            $stats['percentDaysBatal'] = 0;
        } else {
            $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
            $stats['getLab'] = number_format($this->countCheck('periksa_radiologi','rad1'),0,'','.');
            $stats['getLabMonthly'] = number_format($this->countMonth('periksa_radiologi','rad1'),0,'','.');
            $stats['getLabYearly'] = number_format($this->countYear('periksa_radiologi','rad1'),0,'','.');
            $stats['getDrRujuk'] = $this->countDrPerujukRad();
            $stats['percentTotal'] = 0;
            if($this->countVisite() != 0) {
                $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
            }
            $stats['percentDays'] = 0;
            if($this->countCheck('periksa_radiologi','rad1') != 0) {
                $stats['percentDays'] = number_format((($this->countCheck('periksa_radiologi','rad1')-$this->countLastCheck('periksa_radiologi','rad1'))/$this->countCheck('periksa_radiologi','rad1'))*100,0,'','.');
            }
            $stats['percentMonths'] = 0;
            if($this->countMonth('periksa_radiologi','rad1') != 0) {
                $stats['percentMonths'] = number_format((($this->countMonth('periksa_radiologi','rad1')-$this->countLastMonth('periksa_radiologi','rad1'))/$this->countMonth('periksa_radiologi','rad1'))*100,0,'','.');
            }
            $stats['percentYears'] = 0;
            if($this->countYear('periksa_radiologi','rad1') != 0) {
                $stats['percentYears'] = number_format((($this->countYear('periksa_radiologi','rad1')-$this->countLastYear('periksa_radiologi','rad1'))/$this->countYear('periksa_radiologi','rad1'))*100,0,'','.');
            }
            $stats['percentDaysBaru'] = 0;
            $stats['percentDaysBatal'] = 0;
        }

      return $this->draw('radiologi.html',[
        'settings' => $settings,
        'stats' => $stats,
        'start_date' => $start_date,
        'end_date' => $end_date,
      ]);
    }

    public function getApotek()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            $stats['poliChart'] = $this->countResepDrByDateRange($start_date, $end_date);
        } else {
            $stats['poliChart'] = $this->countResepDr();
        }
        return $this->draw('apotek.html',[
            'settings' => $settings,
            'stats' => $stats,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    public function getFarmasi()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
        
        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            $stats['getTotalStok'] = number_format($this->countTotalStokObat(),0,'','.');
            $stats['getObatHampirHabis'] = number_format($this->countObatHampirHabis(),0,'','.');
            $stats['getTransaksiHariIni'] = number_format($this->countTransaksiFarmasiByDateRange($start_date, $end_date),0,'','.');
            $stats['getNilaiStok'] = number_format($this->sumNilaiStokObat(),0,'','.');
            $stats['farmasiChart'] = $this->farmasiStokChart();
            $stats['percentTotal'] = 0;
            $stats['percentHampirHabis'] = 0;
            $stats['percentTransaksi'] = 0;
            $stats['percentNilai'] = 0;
        } else {
            $stats['getTotalStok'] = number_format($this->countTotalStokObat(),0,'','.');
            $stats['getObatHampirHabis'] = number_format($this->countObatHampirHabis(),0,'','.');
            $stats['getTransaksiHariIni'] = number_format($this->countTransaksiFarmasi(),0,'','.');
            $stats['getNilaiStok'] = number_format($this->sumNilaiStokObat(),0,'','.');
            $stats['farmasiChart'] = $this->farmasiStokChart();
            
            $stats['percentTotal'] = 0;
            if($this->countTotalStokObat() != 0) {
                $stats['percentTotal'] = number_format((($this->countTotalStokObat()-$this->countLastTotalStokObat())/$this->countTotalStokObat())*100,0,'','.');
            }
            $stats['percentHampirHabis'] = 0;
            if($this->countObatHampirHabis() != 0) {
                $stats['percentHampirHabis'] = number_format((($this->countObatHampirHabis()-$this->countLastObatHampirHabis())/$this->countObatHampirHabis())*100,0,'','.');
            }
            $stats['percentTransaksi'] = 0;
            if($this->countTransaksiFarmasi() != 0) {
                $stats['percentTransaksi'] = number_format((($this->countTransaksiFarmasi()-$this->countLastTransaksiFarmasi())/$this->countTransaksiFarmasi())*100,0,'','.');
            }
            $stats['percentNilai'] = 0;
        }
        
        return $this->draw('farmasi.html',[
            'settings' => $settings,
            'stats' => $stats,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    public function getKasir()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $settings = htmlspecialchars_array($this->settings('manajemen'));

        // Baca dan validasi parameter tanggal
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $isValidDate = function($d) {
            return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        };
        $useFilter = false;
        if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
            $useFilter = true;
        } else {
            $start_date = null;
            $end_date = null;
        }

        if ($useFilter) {
            // Pendapatan berdasarkan rentang tanggal
            $pendapatanRajal = $this->sumPendapatanRajalByDateRange($start_date, $end_date) ?? 0;
            $pendapatanLain  = $this->sumPendapatanLainByDateRange($start_date, $end_date) ?? 0;
            $stats['getDapat'] = number_format($this->sumPdptLainByDateRange($start_date, $end_date) ?? 0, 0, '', '.');
            $stats['getPendapatanRajal'] = number_format($pendapatanRajal, 0, '', '.');
            $stats['getPendapatanLain']  = number_format($pendapatanLain, 0, '', '.');
            $stats['getVisities']        = number_format(($pendapatanRajal + $pendapatanLain), 0, '', '.');
            // Persentase dinonaktifkan pada rentang kustom
            $stats['percentTotal'] = 0;
            $stats['percentRajal'] = 0;
            $stats['percentLain']  = 0;
        } else {
            // Pendapatan default (harian)
            $stats['getDapat'] = number_format($this->sumPdptLain() ?? 0,0,'','.');        
            $stats['getPendapatanRajal'] = number_format($this->sumPendapatanRajal() ?? 0, 0, '', '.');
            $stats['getPendapatanLain']  = number_format($this->sumPendapatanLain() ?? 0, 0, '', '.');
            $stats['getVisities']        = number_format((($this->sumPendapatanRajal() ?? 0) + ($this->sumPendapatanLain() ?? 0)),0,'','.');
            
            $stats['percentTotal'] = 0;
            if($this->countVisite() != 0) {
                $stats['percentTotal'] = number_format((($this->countVisite()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
            }
            
            // Hitung persentase pendapatan rajal
            $stats['percentRajal'] = 0;
            if($this->sumPendapatanRajal() != 0) {
                $lastRajal = $this->sumLastPendapatanRajal();
                if($lastRajal != 0) {
                    $stats['percentRajal'] = number_format((($this->sumPendapatanRajal()-$lastRajal)/$lastRajal)*100,0,'','.');
                }
            }
            
            // Hitung persentase pendapatan lain-lain
            $stats['percentLain'] = 0;
            if($this->sumPendapatanLain() != 0) {
                $lastLain = $this->sumLastPendapatanLain();
                if($lastLain != 0) {
                    $stats['percentLain'] = number_format((($this->sumPendapatanLain()-$lastLain)/$lastLain)*100,0,'','.');
                }
            }
        }
        
        $stats['percentYear'] = 0;
        $stats['percentMonth'] = 0;
        $stats['percentOut'] = 0;
        $stats['percentDays'] = 0;
        return $this->draw('kasir.html',[
            'settings' => $settings,
            'stats' => $stats,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    // Helper pendapatan berdasarkan rentang tanggal untuk Kasir
    public function sumPendapatanRajalByDateRange($start_date, $end_date)
    {
        $record = $this->db('mlite_billing')
            ->where('tgl_billing', '>=', $start_date)
            ->where('tgl_billing', '<=', $end_date)
            ->select(['total' => 'SUM(jumlah_bayar)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }

    public function sumPendapatanLainByDateRange($start_date, $end_date)
    {
        $record = $this->db('mlite_penjualan_billing')
            ->where('tanggal', '>=', $start_date)
            ->where('tanggal', '<=', $end_date)
            ->select(['total' => 'SUM(jumlah_bayar)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }

    public function sumPdptLainByDateRange($start_date, $end_date)
    {
        $record = $this->db('mlite_penjualan_billing')
            ->where('tanggal', '>=', $start_date)
            ->where('tanggal', '<=', $end_date)
            ->select(['sum' => 'SUM(jumlah_bayar)'])
            ->oneArray();
        return $record['sum'] ?? 0;
    }

    public function getPresensi()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      $this->core->addJS(url('assets/jscripts/Chart.bundle.min.js'));
      $settings = htmlspecialchars_array($this->settings('manajemen'));

      // Baca dan validasi parameter tanggal
      $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
      $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
      $isValidDate = function($d) {
          return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
      };
      $useFilter = false;
      if ($start_date && $end_date && $isValidDate($start_date) && $isValidDate($end_date) && $start_date <= $end_date) {
          $useFilter = true;
      } else {
          $start_date = null;
          $end_date = null;
      }

      if ($useFilter) {
        $stats['getVisities'] = number_format($this->getTotalAbsenByDateRange($start_date, $end_date),0,'','.');
        $stats['getBelumAbsen'] = number_format($this->getBelumAbsenByDateRange($start_date, $end_date),0,'','.');
        $stats['getHarusAbsen'] = number_format($this->getJadwalJagaByDateRange($start_date, $end_date),0,'','.');
        $stats['presensiChart'] = $this->presensiChartByDateRange($start_date, $end_date);
        $stats['getIjin'] = number_format($this->getIjinByDateRange($start_date, $end_date),0,'','.');
        $stats['percentTotal'] = 0;
        $stats['percentDays'] = 0;
        $stats['percentDaysBaru'] = 0;
        $stats['percentDaysBatal'] = 0;
      } else {
        $stats['getVisities'] = number_format($this->getTotalAbsen(),0,'','.');
        $stats['getBelumAbsen'] = number_format($this->getBelumAbsen(),0,'','.');
        $stats['getHarusAbsen'] = number_format($this->getJadwalJaga(),0,'','.');
        $stats['presensiChart'] = $this->presensiChart(15);
        $stats['getIjin'] = number_format($this->getIjin(),0,'','.');
        $stats['percentTotal'] = 0;
        if($this->getTotalAbsen() != 0) {
            $stats['percentTotal'] = number_format((($this->getTotalAbsen()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
        }
      }

      return $this->draw('presensi.html',[
        'settings' => $settings,
        'stats' => $stats,
        'start_date' => $start_date,
        'end_date' => $end_date,
        ]);
    }

  	public function presensiChart($days = 14, $offset = 0)
    {
        $time = strtotime(date("Y-m-d", strtotime("-".($days + $offset)." days")));
        $date = date("Y-m-d", strtotime("-".($days + $offset)." days"));

        $query = $this->db()->pdo()->prepare("SELECT COUNT(photo) as count,COUNT(IF(keterangan != '-', 1, NULL)) as count2, date(jam_datang) as jam FROM `rekap_presensi` WHERE jam_datang >= '$date 00:00:00' GROUP BY date(jam_datang)");
        $query->execute();

        $data = $query->fetchAll(\PDO::FETCH_ASSOC);

            $return = [
                'labels'  => [],
                'visits'  => [],
                'visits2'  => [],
            ];

            while ($time < (time() - ($offset * 86400))) {
                $return['labels'][] = '"'.date("Y-m-d", $time).'"';
                $return['readable'][] = '"'.date("d M Y", $time).'"';
                $return['visits'][] = 0;
                $return['visits2'][] = 0;

                $time = strtotime('+1 day', $time);
            }

            foreach ($data as $day) {
                $index = array_search('"'.$day['jam'].'"', $return['labels']);
                if ($index === false) {
                    continue;
                }

                $return['visits'][$index] = $day['count'];
                $return['visits2'][$index] = $day['count2'];
            }

        return $return;
    }

    public function getCoba($days = 14, $offset = 0)
    {
      $date = date("Y-m-d", strtotime("-".($days + $offset)." days"));

      $query = $this->db('rekap_presensi')
          ->select([
            'count' => 'COUNT(photo)',
            'count2' => "COUNT(IF(keterangan = '', 1, NULL))",
          ])
          ->where('jam_datang', '>=', $date.' 00:00:00');


      $data = $query->toArray();
      print_r($data);
      exit();
    }

    // Method helper untuk statistik farmasi
    public function countTotalStokObat()
    {
        $record = $this->db('gudangbarang')
            ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
            ->where('databarang.status', '1')
            ->select(['total' => 'SUM(gudangbarang.stok)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }
    
    public function countObatHampirHabis()
    {
        $record = $this->db('gudangbarang')
            ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
            ->where('databarang.status', '1')
            ->where('gudangbarang.stok', '<', 10)
            ->count();
        return $record ?? 0;
    }
    
    public function countTransaksiFarmasi()
    {
        $date = date('Y-m-d');
        $record = $this->db('detail_pemberian_obat')
            ->where('tgl_perawatan', $date)
            ->count();
        return $record ?? 0;
    }
    
    public function sumNilaiStokObat()
    {
        $record = $this->db('gudangbarang')
            ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
            ->where('databarang.status', '1')
            ->select(['total' => 'SUM(gudangbarang.stok * databarang.dasar)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }
    
    public function countLastTotalStokObat()
    {
        // Implementasi untuk perbandingan periode sebelumnya
        return 0;
    }
    
    public function countLastObatHampirHabis()
    {
        // Implementasi untuk perbandingan periode sebelumnya
        return 0;
    }
    
    public function countLastTransaksiFarmasi()
    {
        $date = date('Y-m-d', strtotime('-1 day'));
        $record = $this->db('detail_pemberian_obat')
            ->where('tgl_perawatan', $date)
            ->count();
        return $record ?? 0;
    }
    
    public function farmasiStokChart()
    {
        $data = $this->db('gudangbarang')
            ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
            ->join('jenis', 'jenis.kdjns=databarang.kdjns')
            ->where('databarang.status', '1')
            ->select([
                'jenis' => 'jenis.nama',
                'total_stok' => 'SUM(gudangbarang.stok)'
            ])
            ->group('jenis.kdjns')
            ->limit(10)
            ->toArray();
            
        $return = [
            'labels' => [],
            'visits' => []
        ];
        
        foreach ($data as $row) {
            $return['labels'][] = $row['jenis'];
            $return['visits'][] = $row['total_stok'];
        }
        
        return $return;
    }

    // Method helper untuk statistik kasir
    public function sumPendapatanRajal()
    {
        $date = date('Y-m-d');
        $record = $this->db('mlite_billing')
            ->where('tgl_billing', $date)
            ->select(['total' => 'SUM(jumlah_bayar)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }

    public function sumPendapatanLain()
    {
        $date = date('Y-m-d');
        $record = $this->db('mlite_penjualan_billing')
            ->where('tanggal', $date)
            ->select(['total' => 'SUM(jumlah_bayar)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }

    public function sumLastPendapatanRajal()
    {
        $date = date('Y-m-d', strtotime('-1 day'));
        $record = $this->db('mlite_billing')
            ->where('tgl_billing', $date)
            ->select(['total' => 'SUM(jumlah_bayar)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }

    public function sumLastPendapatanLain()
    {
        $date = date('Y-m-d', strtotime('-1 day'));
        $record = $this->db('mlite_penjualan_billing')
            ->where('tanggal', $date)
            ->select(['total' => 'SUM(jumlah_bayar)'])
            ->oneArray();
        return $record['total'] ?? 0;
    }

    public function getSettings()
    {
        $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
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
