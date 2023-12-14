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
        $query = $this->db()->pdo()->prepare("SELECT COUNT(diagnosa_pasien.kd_penyakit) as count ,penyakit.nm_penyakit FROM diagnosa_pasien JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat JOIN penyakit ON diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit WHERE diagnosa_pasien.status ='Ralan' and reg_periksa.tgl_registrasi like '%$date%' GROUP BY diagnosa_pasien.kd_penyakit ORDER BY `count`  DESC Limit 10");
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
        $record = $this->db('pemasukan_lain')
            ->select([
                'sum' => 'SUM(besar)',
            ])
            ->where('tanggal', $date)
            ->oneArray();

        return $record['sum'];
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

      return $this->draw('pendaftaran.html',[
        'settings' => $settings,
        'stats' => $stats,
      ]);
    }

    public function getRawatJalan()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));
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

      return $this->draw('rawatjalan.html',[
        'settings' => $settings,
        'stats' => $stats,
      ]);
    }

    public function getRawatInap()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));
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

      return $this->draw('rawatinap.html',[
        'settings' => $settings,
        'stats' => $stats,
      ]);
    }

    public function getDokter()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

            $settings = htmlspecialchars_array($this->settings('manajemen'));
            $stats['poliChart'] = $this->countPxDrRj();
            $stats['ranapChart'] = $this->countPxDrRi();

        return $this->draw('dokter.html',[
            'settings' => $settings,
            'stats' => $stats,
        ]);
    }

    public function getLaboratorium()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));

        $settings = htmlspecialchars_array($this->settings('manajemen'));
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

      return $this->draw('radiologi.html',[
        'settings' => $settings,
        'stats' => $stats,
      ]);
    }

    public function getApotek()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
        $settings = htmlspecialchars_array($this->settings('manajemen'));
        $stats['poliChart'] = $this->countResepDr();
        return $this->draw('apotek.html',[
            'settings' => $settings,
            'stats' => $stats,
        ]);
    }

    public function getFarmasi()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      return $this->draw('farmasi.html');
    }

    public function getKasir()
    {
        $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
        $settings = htmlspecialchars_array($this->settings('manajemen'));
        $stats['getDapat'] = number_format($this->sumPdptLain(),0,'','.');
        return $this->draw('kasir.html',[
            'settings' => $settings,
            'stats' => $stats,
        ]);
    }

    public function getPresensi()
    {
      $this->core->addCSS(url(MODULES.'/manajemen/css/admin/style.css'));
      $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
      $settings = htmlspecialchars_array($this->settings('manajemen'));
      $stats['getVisities'] = number_format($this->getTotalAbsen(),0,'','.');
      $stats['getBelumAbsen'] = number_format($this->getBelumAbsen(),0,'','.');
      $stats['getHarusAbsen'] = number_format($this->getJadwalJaga(),0,'','.');
      $stats['presensiChart'] = $this->presensiChart(15);

      $stats['getIjin'] = number_format($this->getIjin(),0,'','.');

      $stats['percentTotal'] = 0;
        if($this->getTotalAbsen() != 0) {
            $stats['percentTotal'] = number_format((($this->getTotalAbsen()-$this->countVisiteNoRM())/$this->countVisite())*100,0,'','.');
        }

      return $this->draw('presensi.html',[
        'settings' => $settings,
        'stats' => $stats,
        ]);
    }

  	public function presensiChart($days = 14, $offset = 0)
    {
        $time = strtotime(date("Y-m-d", strtotime("-".($days + $offset)." days")));
        $date = date("Y-m-d", strtotime("-".($days + $offset)." days"));

        $query = $this->db()->pdo()->prepare("SELECT COUNT(photo) as count,COUNT(IF(keterangan != '-', 1, NULL)) as count2, date(jam_datang) as jam FROM `rekap_presensi` WHERE jam_datang >= '$date 00:00:00' GROUP BY jam");
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
