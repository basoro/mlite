<?php
namespace Plugins\Igd;

use Systems\AdminModule;

class Admin extends AdminModule
{

    private $_uploads = WEBAPPS_PATH.'/berkasrawat/pages/upload';
    private $assign = '';

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function anyManage()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        if(isset($_POST['status_bayar'])) {
          $status_bayar = $_POST['status_bayar'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_bayar);
        return $this->draw('manage.html', [
          'rawat_jalan' => $this->assign, 
          'cek_vclaim' => $cek_vclaim, 
          'master_berkas_digital' => $master_berkas_digital, 
          'admin_mode' => $this->settings->get('settings.admin_mode'), 
          'username_fp' => $this->settings->get('settings.username_fp'), 
          'password_fp' => $this->settings->get('settings.password_fp'), 
          'username_frista' => $this->settings->get('settings.username_frista'), 
          'password_frista' => $this->settings->get('settings.password_frista')
        ]);
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='')
    {
        $this->_addHeaderFiles();

        if (!is_array($this->assign)) {
            $this->assign = []; // atau bisa langsung array dengan default
        }

        $this->assign['poliklinik']     = $this->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

        $igd = $this->settings('settings', 'igd');
        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND reg_periksa.kd_poli = '$igd'
          AND reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'
          AND reg_periksa.kd_dokter = dokter.kd_dokter
          AND reg_periksa.kd_poli = poliklinik.kd_poli
          AND reg_periksa.kd_pj = penjab.kd_pj";

        if($status_periksa == 'belum') {
          $sql .= " AND reg_periksa.stts = 'Belum'";
        }
        if($status_periksa == 'selesai') {
          $sql .= " AND reg_periksa.stts = 'Sudah'";
        }
        if($status_periksa == 'lunas') {
          $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $row['no_rawat'])->oneArray();
          $row['no_sep'] = isset_or($bridging_sep['no_sep']);
          $this->assign['list'][] = $row;
        }

        if (isset($_POST['no_rawat'])){
          $this->assign['reg_periksa'] = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
            ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
            ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
            ->where('no_rawat', $_POST['no_rawat'])
            ->oneArray();
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
        }
    }

    public function anyForm()
    {
      // Initialize assign as array to prevent "Cannot access offset of type string on string" error
      if (!is_array($this->assign)) {
          $this->assign = [];
      }

      $this->assign['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
      $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
      $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
      $this->assign['no_rawat'] = '';
      $this->assign['no_reg']     = '';
      $this->assign['tgl_registrasi']= date('Y-m-d');
      $this->assign['jam_reg']= date('H:i:s');
      if (isset($_POST['no_rawat'])){
        $this->assign['reg_periksa'] = $this->db('reg_periksa')
          ->select('pasien.no_rkm_medis')
          ->select('pasien.nm_pasien')
          ->select('pasien.tgl_lahir')
          ->select('pasien.jk')
          ->select('pasien.no_tlp')
          ->select('reg_periksa.tgl_registrasi')
          ->select('reg_periksa.jam_reg')
          ->select('reg_periksa.no_rawat')
          ->select('reg_periksa.no_reg')
          ->select('poliklinik.kd_poli')
          ->select('dokter.kd_dokter')
          ->select('penjab.kd_pj')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
          ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
          ->where('no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('form.html', [
          'rawat_jalan' => $this->assign
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
          'rawat_jalan' => $this->assign
        ]);
      }
      exit();
    }

    public function anyStatusDaftar()
    {
      if(isset($_POST['no_rkm_medis'])) {
        $rawat = $this->db('reg_periksa')
          ->where('no_rkm_medis', $_POST['no_rkm_medis'])
          ->where('status_bayar', 'Belum Bayar')
          ->limit(1)
          ->oneArray();
          if($rawat) {
            $stts_daftar = "Transaki tanggal ".date('Y-m-d', strtotime($rawat['tgl_registrasi']))." belum diselesaikan" ;
            $stts_daftar_hidden = $stts_daftar;
            if($this->settings->get('settings.cekstatusbayar') == 'false'){
              $stts_daftar_hidden = 'Lama';
            }
            $bg_status = 'has-error';
          } else {
            $result = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
            if(!empty($result['no_rawat'])) {
              $stts_daftar = 'Lama';
              $bg_status = 'has-info';
              $stts_daftar_hidden = $stts_daftar;
            } else {
              $stts_daftar = 'Baru';
              $bg_status = 'has-success';
              $stts_daftar_hidden = $stts_daftar;
            }
          }
        echo $this->draw('stts.daftar.html', ['stts_daftar' => $stts_daftar, 'stts_daftar_hidden' => $stts_daftar_hidden, 'bg_status' =>$bg_status]);
      } else {
        $rawat = $this->db('reg_periksa')
          ->where('no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('stts.daftar.html', [
          'stts_daftar' => $rawat['stts_daftar'],
          'stts_daftar_hidden' => $rawat['stts_daftar'],
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

        $poliklinik = $this->db('poliklinik')->where('kd_poli', $this->settings('settings', 'igd'))->oneArray();

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
          if($y !='0'){
            $umur_daftar = $y;
            $status_umur = "Th";
          }
          if($y =='0' && $m !='0'){
            $umur_daftar = $m;
            $status_umur = "Bl";
          }
        }

        $_POST['umurdaftar'] = $umur_daftar;
        $_POST['sttsumur'] = $status_umur;
        $_POST['status_poli'] = 'Lama';
        $_POST['kd_poli'] = $this->settings('settings', 'igd');

        $query = $this->db('reg_periksa')->save($_POST);
      } else {
        $query = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
          'kd_dokter' => $_POST['kd_dokter'],
          'kd_pj' => $_POST['kd_pj']
        ]);
      }

      if($query) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }
      exit();
    }

    public function postStatusRawat()
    {
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save($_POST);
      exit();
    }

    public function postStatusLanjut()
    {
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
        'status_lanjut' => $_POST['status_lanjut']
      ]);
      exit();
    }

    public function anyPasien()
    {
      if(isset($_POST['cari'])) {
        $pasien = $this->db('pasien')
          ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
          ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
          ->asc('no_rkm_medis')
          ->limit(5)
          ->toArray();
      }
      echo $this->draw('pasien.html', ['pasien' => $pasien]);
      exit();
    }

    public function getAntrian()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $rawat_jalan = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      echo $this->draw('antrian.html', ['rawat_jalan' => $rawat_jalan]);
      exit();
    }

    public function postHapus()
    {
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function postSaveDetail()
    {
      if($_POST['kat'] == 'tindakan') {
        $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        if($_POST['provider'] == 'rawat_jl_dr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_jl_dr')->save([
              'no_rawat' => $_POST['no_rawat'],
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'kd_dokter' => $_POST['kode_provider'],
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam_rawat' => date('H:i:s', strtotime($_POST['jam_rawat']. ' +'.$i.'0 seconds')),
              'material' => $jns_perawatan['material'],
              'bhp' => $jns_perawatan['bhp'],
              'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
              'kso' => $jns_perawatan['kso'],
              'menejemen' => $jns_perawatan['menejemen'],
              'biaya_rawat' => $jns_perawatan['total_byrdr'],
              'stts_bayar' => 'Belum'
            ]);
          }
        }
        if($_POST['provider'] == 'rawat_jl_pr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_jl_pr')->save([
              'no_rawat' => $_POST['no_rawat'],
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'nip' => $_POST['kode_provider2'],
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam_rawat' => $_POST['jam_rawat'], 
              'material' => $jns_perawatan['material'],
              'bhp' => $jns_perawatan['bhp'],
              'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
              'kso' => $jns_perawatan['kso'],
              'menejemen' => $jns_perawatan['menejemen'],
              'biaya_rawat' => $jns_perawatan['total_byrpr'],
              'stts_bayar' => 'Belum'
            ]);
          }
        }
        if($_POST['provider'] == 'rawat_jl_drpr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_jl_drpr')->save([
              'no_rawat' => $_POST['no_rawat'],
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'kd_dokter' => $_POST['kode_provider'],
              'nip' => $_POST['kode_provider2'],
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam_rawat' => date('H:i:s', strtotime($_POST['jam_rawat']. ' +'.$i.'0 seconds')),
              'material' => $jns_perawatan['material'],
              'bhp' => $jns_perawatan['bhp'],
              'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
              'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
              'kso' => $jns_perawatan['kso'],
              'menejemen' => $jns_perawatan['menejemen'],
              'biaya_rawat' => $jns_perawatan['total_byrdrpr'],
               'stts_bayar' => 'Belum'
            ]);
          }
        }
      }
      if($_POST['kat'] == 'obat') {

        $no_resep = $this->core->setNoResep($_POST['tgl_perawatan']);
        $cek_resep = $this->db('resep_obat')->join('resep_dokter', 'resep_obat.no_resep = resep_dokter.no_resep')->where('no_rawat', $_POST['no_rawat'])->where('tgl_peresepan', $_POST['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ralan')->oneArray();

        if(empty($cek_resep)) {

          $resep_obat = $this->db('resep_obat')
            ->save([
              'no_resep' => $no_resep,
              'tgl_perawatan' => '0000-00-00',
              'jam' => '00:00:00',
              'no_rawat' => $_POST['no_rawat'],
              'kd_dokter' => $_POST['kode_provider'],
              'tgl_peresepan' => $_POST['tgl_perawatan'],
              'jam_peresepan' => $_POST['jam_rawat'],
              'status' => 'ralan',
              'tgl_penyerahan' => '0000-00-00',
              'jam_penyerahan' => '00:00:00'
            ]);

          if ($this->db('resep_obat')->where('no_resep', $no_resep)->where('kd_dokter', $_POST['kode_provider'])->oneArray()) {
            $this->db('resep_dokter')
              ->save([
                'no_resep' => $no_resep,
                'kode_brng' => $_POST['kd_jenis_prw'],
                'jml' => $_POST['jml'],
                'aturan_pakai' => $_POST['aturan_pakai']
              ]);
          }

        } else {

          $no_resep = $cek_resep['no_resep'];

          $this->db('resep_dokter')
            ->save([
              'no_resep' => $no_resep,
              'kode_brng' => $_POST['kd_jenis_prw'],
              'jml' => $_POST['jml'],
              'aturan_pakai' => $_POST['aturan_pakai']
            ]);

        }

      }
      exit();
    }

    public function postHapusDetail()
    {
      if($_POST['provider'] == 'rawat_jl_dr') {
        $this->db('rawat_jl_dr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_jl_pr') {
        $this->db('rawat_jl_pr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_jl_drpr') {
        $this->db('rawat_jl_drpr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      exit();
    }

    public function postHapusResep()
    {
      if(isset($_POST['kd_jenis_prw'])) {
        $this->db('resep_dokter')
        ->where('no_resep', $_POST['no_resep'])
        ->where('kode_brng', $_POST['kd_jenis_prw'])
        ->delete();
      } else {
        $this->db('resep_obat')
        ->where('no_resep', $_POST['no_resep'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_peresepan', $_POST['tgl_peresepan'])
        ->where('jam_peresepan', $_POST['jam_peresepan'])
        ->delete();
      }

      exit();
    }

    public function anyRincian()
    {
      $rows_rawat_jl_dr = $this->db('rawat_jl_dr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_pr = $this->db('rawat_jl_pr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_drpr = $this->db('rawat_jl_drpr')->where('no_rawat', $_POST['no_rawat'])->toArray();

      $jumlah_total = 0;
      $rawat_jl_dr = [];
      $rawat_jl_pr = [];
      $rawat_jl_drpr = [];
      $i = 1;

      if($rows_rawat_jl_dr) {
        foreach ($rows_rawat_jl_dr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_dr';
          $rawat_jl_dr[] = $row;
        }
      }

      if($rows_rawat_jl_pr) {
        foreach ($rows_rawat_jl_pr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_pr';
          $rawat_jl_pr[] = $row;
        }
      }

      if($rows_rawat_jl_drpr) {
        foreach ($rows_rawat_jl_drpr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_drpr';
          $rawat_jl_drpr[] = $row;
        }
      }

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ralan')
        ->toArray();
      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep += floatval($value['ralan']);
        }
        $resep[] = $row;
      }
      echo $this->draw('rincian.html', ['rawat_jl_dr' => $rawat_jl_dr, 'rawat_jl_pr' => $rawat_jl_pr, 'rawat_jl_drpr' => $rawat_jl_drpr, 'jumlah_total' => $jumlah_total, 'jumlah_total_resep' => $jumlah_total_resep, 'resep' =>$resep, 'no_rawat' => $_POST['no_rawat']]);
      exit();
    }

    public function anySoap()
    {

      $prosedurs = $this->db('prosedur_pasien')
         ->where('no_rawat', $_POST['no_rawat'])
         ->asc('prioritas')
         ->toArray();
       $prosedur = [];
       foreach ($prosedurs as $row) {
         $icd9 = $this->db('icd9')->where('kode', $row['kode'])->oneArray();
         $row['nama'] = $icd9['deskripsi_panjang'];
         $prosedur[] = $row;
       }
       $diagnosas = $this->db('diagnosa_pasien')
         ->where('no_rawat', $_POST['no_rawat'])
         ->asc('prioritas')
         ->toArray();
       $diagnosa = [];
       foreach ($diagnosas as $row) {
         $icd10 = $this->db('penyakit')->where('kd_penyakit', $row['kd_penyakit'])->oneArray();
         $row['nama'] = $icd10['nm_penyakit'];
         $diagnosa[] = $row;
       }

      $rows = $this->db('pemeriksaan_ralan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();
      $i = 1;
      $row['nama_petugas'] = '';
      $row['departemen_petugas'] = '';
      $result = [];
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
        $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
        $result[] = $row;
      }

      $rows_ranap = $this->db('pemeriksaan_ranap')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();
      $result_ranap = [];
      foreach ($rows_ranap as $row) {
        $row['nomor'] = $i++;
        $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
        $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
        $result_ranap[] = $row;
      }

      echo $this->draw('soap.html', ['pemeriksaan' => $result, 'pemeriksaan_ranap' => $result_ranap, 'diagnosa' => $diagnosa, 'prosedur' => $prosedur, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
      exit();
    }

    public function postSaveSOAP()
    {
      $_POST['nip'] = $this->core->getUserInfo('username', null, true);

      if(!$this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->where('nip', $_POST['nip'])->oneArray()) {
        $this->db('pemeriksaan_ralan')->save($_POST);
      } else {
        $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->where('nip', $_POST['nip'])->save($_POST);
      }
      exit();
    }

    public function postHapusSOAP()
    {
      $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->delete();
      exit();
    }

    public function anyLayanan()
    {
      $poliklinik = $this->db('poliklinik')->select('kd_poli')->where('status', '1')->toArray();
      $poliklinik = implode(",", array_column($poliklinik, 'kd_poli'));
      $poliklinik = explode(',', $poliklinik);
      if($this->core->getUserInfo('role', null, true) != 'admin') {
        $poliklinik = explode(',', $this->core->getUserInfo('cap', null, true));
      }
      $layanan = $this->db('jns_perawatan')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['layanan'].'%')
        ->in('kd_poli', $poliklinik)
        ->limit(10)
        ->toArray();
      echo $this->draw('layanan.html', ['layanan' => $layanan]);
      exit();
    }

    public function postGetLayanan()
    {
      $layanan = $this->db('jns_perawatan')
        ->where('status', '1')
        ->where('kd_jenis_prw', $_POST['layanan'])
        ->oneArray();
      echo json_encode($layanan);
      exit();
    }    

    public function anyObat()
    {
      $obat = $this->db('databarang')
        ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
        ->where('status', '1')
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.igd'))
        ->like('databarang.nama_brng', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function postAturanPakai()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('master_aturan_pakai')->like('aturan', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["aturan"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function anyBerkasDigital()
    {
      $berkas_digital = $this->db('berkas_digital_perawatan')->where('no_rawat', $_POST['no_rawat'])->toArray();
      echo $this->draw('berkasdigital.html', ['berkas_digital' => $berkas_digital]);
      exit();
    }

    public function postSaveBerkasDigital()
    {

      if(MULTI_APP) {

        $curl = curl_init();
        $filePath = $_FILES['file']['tmp_name'];

        curl_setopt_array($curl, array(
          CURLOPT_URL => substr(rtrim(WEBAPPS_URL, '/'), 0, strrpos(rtrim(WEBAPPS_URL, '/'), '/')).'/api/berkasdigital',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('file'=> new \CURLFILE($filePath),'token' => $this->settings->get('api.berkasdigital_key'), 'no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode']),
          CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $json = json_decode($response, true);
        if($json['status'] == 'Success') {
          echo '<br><img src="'.WEBAPPS_URL.'/berkasrawat/'.$json['msg'].'" width="150" />';
        } else {
          echo 'Gagal menambahkan gambar';
        }

      } else {

        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['file']['tmp_name'];
        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
            $imgName = time().$cntr++;
            $imgPath = $dir.'/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $img->save($imgPath);
            $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
            if($query) {
              echo '<br><img src="'.WEBAPPS_URL.'/berkasrawat/'.$lokasi_file.'" width="150" />';
            }
        }
      }
      exit();

    }

    public function postProviderList()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_dokter"].': '.$row["nm_dokter"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postProviderList2()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["nip"].': '.$row["nama"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postCekWaktu()
    {
      echo date('H:i:s');
      exit();
    }

    public function postMaxid()
    {
      if(isset($_POST['tgl_registrasi'])) {
        $tgl_registrasi = $_POST['tgl_registrasi'];
      } else {
        $tgl_registrasi = date('Y-m-d');
      }
      $urut = $this->db('reg_periksa')
          ->where('tgl_registrasi', $tgl_registrasi)
          ->nextRightNumber('no_rawat', 6);

      $next_no_rawat = date('Y/m/d', strtotime($tgl_registrasi))
          . '/' . sprintf('%06d', $urut);

      echo $next_no_rawat;
      exit();
    }

    public function postMaxAntrian()
    {
      if(isset($_POST['tgl_registrasi'])) {
        $tgl_registrasi = $_POST['tgl_registrasi'];
      } else {
        $tgl_registrasi = date('Y-m-d');
      }

      $urut = $this->db('reg_periksa')
          ->where('kd_poli', $this->settings('settings', 'igd'))
          ->where('tgl_registrasi', $tgl_registrasi)
          ->nextRightNumber('no_reg', 3);

      echo sprintf('%03d', $urut);
      exit();
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }

    public function getOdontogram($no_rkm_medis)
    {

      $odt['18'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_18')->desc('tgl_input')->limit(1)->oneArray();
      $odt['17'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_17')->desc('tgl_input')->limit(1)->oneArray();
      $odt['16'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_16')->desc('tgl_input')->limit(1)->oneArray();
      $odt['15'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_15')->desc('tgl_input')->limit(1)->oneArray();
      $odt['14'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_14')->desc('tgl_input')->limit(1)->oneArray();
      $odt['13'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_13')->desc('tgl_input')->limit(1)->oneArray();
      $odt['12'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_12')->desc('tgl_input')->limit(1)->oneArray();
      $odt['11'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_11')->desc('tgl_input')->limit(1)->oneArray();
      $odt['21'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_21')->desc('tgl_input')->limit(1)->oneArray();
      $odt['22'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_22')->desc('tgl_input')->limit(1)->oneArray();
      $odt['23'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_23')->desc('tgl_input')->limit(1)->oneArray();
      $odt['24'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_24')->desc('tgl_input')->limit(1)->oneArray();
      $odt['25'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_25')->desc('tgl_input')->limit(1)->oneArray();
      $odt['26'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_26')->desc('tgl_input')->limit(1)->oneArray();
      $odt['27'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_27')->desc('tgl_input')->limit(1)->oneArray();
      $odt['28'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_28')->desc('tgl_input')->limit(1)->oneArray();
      $odt['38'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_38')->desc('tgl_input')->limit(1)->oneArray();
      $odt['37'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_37')->desc('tgl_input')->limit(1)->oneArray();
      $odt['36'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_36')->desc('tgl_input')->limit(1)->oneArray();
      $odt['35'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_35')->desc('tgl_input')->limit(1)->oneArray();
      $odt['34'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_34')->desc('tgl_input')->limit(1)->oneArray();
      $odt['33'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_33')->desc('tgl_input')->limit(1)->oneArray();
      $odt['32'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_32')->desc('tgl_input')->limit(1)->oneArray();
      $odt['31'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_31')->desc('tgl_input')->limit(1)->oneArray();
      $odt['41'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_41')->desc('tgl_input')->limit(1)->oneArray();
      $odt['42'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_42')->desc('tgl_input')->limit(1)->oneArray();
      $odt['43'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_43')->desc('tgl_input')->limit(1)->oneArray();
      $odt['44'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_44')->desc('tgl_input')->limit(1)->oneArray();
      $odt['45'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_45')->desc('tgl_input')->limit(1)->oneArray();
      $odt['46'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_46')->desc('tgl_input')->limit(1)->oneArray();
      $odt['47'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_47')->desc('tgl_input')->limit(1)->oneArray();
      $odt['48'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_48')->desc('tgl_input')->limit(1)->oneArray();
      $odt['55'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_55')->desc('tgl_input')->limit(1)->oneArray();
      $odt['54'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_54')->desc('tgl_input')->limit(1)->oneArray();
      $odt['53'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_53')->desc('tgl_input')->limit(1)->oneArray();
      $odt['52'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_52')->desc('tgl_input')->limit(1)->oneArray();
      $odt['51'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_51')->desc('tgl_input')->limit(1)->oneArray();
      $odt['61'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_61')->desc('tgl_input')->limit(1)->oneArray();
      $odt['62'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_62')->desc('tgl_input')->limit(1)->oneArray();
      $odt['63'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_63')->desc('tgl_input')->limit(1)->oneArray();
      $odt['64'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_64')->desc('tgl_input')->limit(1)->oneArray();
      $odt['65'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_65')->desc('tgl_input')->limit(1)->oneArray();
      $odt['75'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_75')->desc('tgl_input')->limit(1)->oneArray();
      $odt['74'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_74')->desc('tgl_input')->limit(1)->oneArray();
      $odt['73'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_73')->desc('tgl_input')->limit(1)->oneArray();
      $odt['72'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_72')->desc('tgl_input')->limit(1)->oneArray();
      $odt['71'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_71')->desc('tgl_input')->limit(1)->oneArray();
      $odt['81'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_81')->desc('tgl_input')->limit(1)->oneArray();
      $odt['82'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_82')->desc('tgl_input')->limit(1)->oneArray();
      $odt['83'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_83')->desc('tgl_input')->limit(1)->oneArray();
      $odt['84'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_84')->desc('tgl_input')->limit(1)->oneArray();
      $odt['85'] = $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->where('pemeriksaan', 'gg_85')->desc('tgl_input')->limit(1)->oneArray();

      echo $this->draw('odontogram.html', [
        'odontogram' => $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->toArray(), 
        'ohis' => $this->db('mlite_ohis')->where('no_rkm_medis', $no_rkm_medis)->toArray(), 
        'odt' => $odt
      ]);
      exit();
    }

    public function getOdontogramTampil($no_rkm_medis)
    {
      echo $this->draw('odontogram.tampil.html', ['odontogram' => $this->db('mlite_odontogram')->where('no_rkm_medis', $no_rkm_medis)->toArray()]);
      exit();
    }

    public function postOdontogramSave()
    {
      $_POST['id_user']	= $this->core->getUserInfo('id');
      $_POST['tgl_input'] = date('Y-m-d');
      $query = $this->db('mlite_odontogram')->save($_POST);
      exit();
    }

    public function postOdontogramDelete()
    {
      $_POST['id_user']	= $this->core->getUserInfo('id');
      $query = $this->db('mlite_odontogram')
      ->where('no_rkm_medis', $_POST['no_rkm_medis'])
      ->where('pemeriksaan', $_POST['pemeriksaan'])
      ->where('kondisi', $_POST['kondisi'])
      ->where('catatan', $_POST['catatan'])
      ->where('tgl_input', $_POST['tgl_input'])
      ->where('id_user', $_POST['id_user'])
      ->delete();
      exit();
    }

    public function getOhisTampil($no_rkm_medis)
    {
      echo $this->draw('ohis.tampil.html', ['ohis' => $this->db('mlite_ohis')->where('no_rkm_medis', $no_rkm_medis)->toArray()]);
      exit();
    }

    public function postOhisSave()
    {
      $_POST['id_user']	= $this->core->getUserInfo('id');
      $_POST['tgl_input'] = date('Y-m-d');
      $_POST['debris'] = ($_POST['d_16']+$_POST['d_11']+$_POST['d_26']+$_POST['d_36']+$_POST['d_31']+$_POST['d_46'])/6;
      $_POST['debris'] = ceil($_POST['debris']*100)/100;
      $_POST['calculus'] = ($_POST['c_16']+$_POST['c_11']+$_POST['c_26']+$_POST['c_36']+$_POST['c_31']+$_POST['c_46'])/6;
      $_POST['calculus'] = ceil($_POST['calculus']*100)/100;
      $_POST['nilai'] = $_POST['debris']+$_POST['calculus'];
      if($_POST['nilai'] >= '0,0' && $_POST['nilai'] <= '1,2') {
        $_POST['kriteria'] = 'Baik';
      } elseif($_POST['nilai'] >= '1,3' && $_POST['nilai'] <= '3,0') {
        $_POST['kriteria'] = 'Sedang';
      } elseif($_POST['nilai'] >= '1,3' && $_POST['nilai'] <= '3,0') {
        $_POST['kriteria'] = 'Buruk';
      } else {
        $_POST['kriteria'] = '';
      }
      $query = $this->db('mlite_ohis')->save($_POST);
      exit();
    }

    public function postOhisDelete()
    {
      $_POST['id_user']	= $this->core->getUserInfo('id');
      $query = $this->db('mlite_ohis')
      ->where('no_rkm_medis', $_POST['no_rkm_medis'])
      ->where('tgl_input', $_POST['tgl_input'])
      ->where('id_user', $_POST['id_user'])
      ->delete();
      exit();
    }    

    public function getSepDetail($no_sep){
      $sep = $this->db('bridging_sep')->where('no_sep', $no_sep)->oneArray();
      $this->tpl->set('sep', $this->tpl->noParse_array(htmlspecialchars_array($sep)));

      $potensi_prb = $this->db('bpjs_prb')->where('no_sep', $no_sep)->oneArray();
      $data_sep['potensi_prb'] = $potensi_prb['prb'];
      echo $this->draw('sep.detail.html', ['data_sep' => $data_sep]);
      exit();
    }

    public function postSaveICD10()
    {
      $_POST['status_penyakit'] = 'Baru';
      unset($_POST['nama']);
      $this->db('diagnosa_pasien')->save($_POST);
      exit();
    }  

    public function postHapusICD10()
    {
      $this->db('diagnosa_pasien')->where('no_rawat', $_POST['no_rawat'])->where('prioritas', $_POST['prioritas'])->delete();
      exit();
    }
  
    public function postICD10()
    {
  
      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('penyakit')->like('kd_penyakit', $key)->orLike('nm_penyakit', $key)->asc('kd_penyakit')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_penyakit"].': '.$row["nm_penyakit"].'</li>';
          }
        } else {
          $output .= '<li class="list-group-item link-class">Tidak ada yang cocok.</li>';
        }
        echo $output;
      }
  
      exit();
  
    }

    public function postSaveICD9()
    {
      unset($_POST['nama']);
      $this->db('prosedur_pasien')->save($_POST);
      exit();
    }

    public function postHapusICD9()
    {
      $this->db('prosedur_pasien')->where('no_rawat', $_POST['no_rawat'])->where('prioritas', $_POST['prioritas'])->delete();
      exit();
    }

    public function postICD9()
    {
  
      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('icd9')->like('kode', $key)->orLike('deskripsi_panjang', $key)->asc('kode')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kode"].': '.$row["deskripsi_panjang"].'</li>';
          }
        } else {
          $output .= '<li class="list-group-item link-class">Tidak ada yang cocok.</li>';
        }
        echo $output;
      }
  
      exit();
  
    }

    public function getDisplayICD()
    {
      $no_rawat = $_GET['no_rawat'];
      $prosedurs = $this->db('prosedur_pasien')
        ->where('no_rawat', $no_rawat)
        ->asc('prioritas')
        ->toArray();
      $prosedur = [];
      foreach ($prosedurs as $row_prosedur) {
        $icd9 = $this->db('icd9')->where('kode', $row_prosedur['kode'])->oneArray();
        $row_prosedur['nama'] = $icd9['deskripsi_panjang'];
        $prosedur[] = $row_prosedur;
      }
  
      $diagnosas = $this->db('diagnosa_pasien')
        ->where('no_rawat', $no_rawat)
        ->asc('prioritas')
        ->toArray();
      $diagnosa = [];
      foreach ($diagnosas as $row_diagnosa) {
        $icd10 = $this->db('penyakit')->where('kd_penyakit', $row_diagnosa['kd_penyakit'])->oneArray();
        $row_diagnosa['nama'] = $icd10['nm_penyakit'];
        $diagnosa[] = $row_diagnosa;
      }
  
      echo $this->draw('display.icd.html', ['diagnosa' => $diagnosa, 'prosedur' => $prosedur]);
      exit();
    }

    public function getSuratRujukan($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_rujukan')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        $this->tpl->set('nomor_surat', $this->settings->get('settings.set_nomor_surat').'/'.$this->settings->get('settings.prefix_surat').'/'.getRomawi(date('m')).'/'.date('Y'));
        echo $this->tpl->draw(MODULES.'/igd/view/admin/surat.rujukan.html', true);
        exit();
    }

    public function getSuratSehat($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_sehat')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        $this->tpl->set('nomor_surat', $this->settings->get('settings.set_nomor_surat').'/'.$this->settings->get('settings.prefix_surat').'/'.getRomawi(date('m')).'/'.date('Y'));
        echo $this->tpl->draw(MODULES.'/igd/view/admin/surat.sehat.html', true);
        exit();
    }

    public function getSuratSakit($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_sakit')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        $this->tpl->set('nomor_surat', $this->settings->get('settings.set_nomor_surat').'/'.$this->settings->get('settings.prefix_surat').'/'.getRomawi(date('m')).'/'.date('Y'));
        echo $this->tpl->draw(MODULES.'/igd/view/admin/surat.sakit.html', true);
        exit();
    }

    public function postSimpanSuratSakit()
    {
      $query = $this->db('mlite_surat_sakit')->save([
        'nomor_surat' => $_POST['nomor_surat'], 
        'no_rawat' => $_POST['no_rawat'], 
        'no_rkm_medis' => $_POST['no_rkm_medis'], 
        'nm_pasien' => $_POST['nm_pasien'], 
        'tgl_lahir' => $_POST['tgl_lahir'], 
        'umur' => $_POST['umur'], 
        'jk' => $_POST['jk'], 
        'alamat' => $_POST['alamat'], 
        'keadaan' => $_POST['keadaan'], 
        'diagnosa' => $_POST['diagnosa'], 
        'lama_angka' => $_POST['lama_angka'], 
        'lama_huruf' => $_POST['lama_huruf'], 
        'tanggal_mulai' => $_POST['tanggal_mulai'], 
        'tanggal_selesai' => $_POST['tanggal_selesai'], 
        'dokter' => $_POST['dokter'], 
        'petugas' => $_POST['petugas']
      ]);

      if($query) {
        $nomor_surat = ltrim($this->settings->get('settings.set_nomor_surat'));
        $nomor_surat = sprintf('%03s', ($nomor_surat + 1));
        $this->db('mlite_settings')->where('module', 'settings')->where('field', 'set_nomor_surat')->set('value', $nomor_surat)->update();
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }

      exit();
    }

    public function postSimpanSuratSehat()
    {
      $query = $this->db('mlite_surat_sehat')->save([
        'nomor_surat' => $_POST['nomor_surat'], 
        'no_rawat' => $_POST['no_rawat'], 
        'no_rkm_medis' => $_POST['no_rkm_medis'], 
        'nm_pasien' => $_POST['nm_pasien'], 
        'tgl_lahir' => $_POST['tgl_lahir'], 
        'umur' => $_POST['umur'], 
        'jk' => $_POST['jk'], 
        'alamat' => $_POST['alamat'], 
        'tanggal' => $_POST['tanggal'], 
        'berat_badan' => $_POST['berat_badan'], 
        'tinggi_badan' => $_POST['tinggi_badan'], 
        'tensi' => $_POST['tensi'], 
        'gol_darah' => $_POST['gol_darah'], 
        'riwayat_penyakit' => $_POST['riwayat_penyakit'], 
        'keperluan' => $_POST['keperluan'], 
        'dokter' => $_POST['dokter'], 
        'petugas' => $_POST['petugas']
      ]);

      if($query) {
        $nomor_surat = ltrim($this->settings->get('settings.set_nomor_surat'));
        $nomor_surat = sprintf('%03s', ($nomor_surat + 1));
        $this->db('mlite_settings')->where('module', 'settings')->where('field', 'set_nomor_surat')->set('value', $nomor_surat)->update();
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }

      exit();
    }

    public function postSimpanSuratRujukan()
    {
      $query = $this->db('mlite_surat_rujukan')->save([
        'nomor_surat' => $_POST['nomor_surat'], 
        'no_rawat' => $_POST['no_rawat'], 
        'no_rkm_medis' => $_POST['no_rkm_medis'], 
        'nm_pasien' => $_POST['nm_pasien'], 
        'tgl_lahir' => $_POST['tgl_lahir'], 
        'umur' => $_POST['umur'], 
        'jk' => $_POST['jk'], 
        'alamat' => $_POST['alamat'], 
        'kepada' => $_POST['kepada'], 
        'di' => $_POST['di'], 
        'anamnesa' => $_POST['anamnesa'], 
        'pemeriksaan_fisik' => $_POST['pemeriksaan_fisik'], 
        'pemeriksaan_penunjang' => $_POST['pemeriksaan_penunjang'], 
        'diagnosa' => $_POST['diagnosa'], 
        'terapi' => $_POST['terapi'], 
        'alasan_dirujuk' => $_POST['alasan_dirujuk'], 
        'dokter' => $_POST['dokter'], 
        'petugas' => $_POST['petugas']
      ]);

      if($query) {
        $nomor_surat = ltrim($this->settings->get('settings.set_nomor_surat'));
        $nomor_surat = sprintf('%03s', ($nomor_surat + 1));
        $this->db('mlite_settings')->where('module', 'settings')->where('field', 'set_nomor_surat')->set('value', $nomor_surat)->update();
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }

      exit();
    }

    public function postCetak()
    {
      $this->db()->pdo()->exec("DELETE FROM mlite_temporary");

      $tgl_awal  = $_POST['tgl_awal'];
      $tgl_akhir = $_POST['tgl_akhir'];
      $igd       = $this->settings->get('settings.igd');

      $stmt = $this->db()->pdo()->prepare("
        INSERT INTO mlite_temporary (
          temp1,temp2,temp3,temp4,temp5,temp6,temp7,temp8,temp9,temp10,
          temp11,temp12,temp13,temp14,temp15,temp16,temp17,temp18,temp19
        )
        SELECT *
        FROM reg_periksa
        WHERE kd_poli = ?
        AND tgl_registrasi BETWEEN ? AND ?
      ");
      $stmt->execute([$igd, $tgl_awal, $tgl_akhir]);

      exit;
    }

    public function getCetakPdf()
    {
      $cetak = $this->db('mlite_temporary')->toArray();

      $html = $this->draw('cetak.igd.html', [
        'cetak' => $cetak
      ]);

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'orientation' => 'L'
      ]);

      $mpdf->SetHTMLHeader($this->core->setPrintHeader());
      $mpdf->SetHTMLFooter($this->core->setPrintFooter());

      $mpdf->WriteHTML(
        $this->core->setPrintCss(),
        \Mpdf\HTMLParserMode::HEADER_CSS
      );
      $mpdf->WriteHTML(
        $html,
        \Mpdf\HTMLParserMode::HTML_BODY
      );

      $mpdf->Output();
      exit;
    }

    public function getLokalis($no_rawat)
    {
      $filename = 'lokalis_' . $no_rawat . '.png';
      $lokalis = UPLOADS . '/lokalis/' . $filename;
      if(!file_exists($lokalis)) {
        $filename = '';
      }
      echo $this->draw('lokalis.html', [
        'no_rawat' => revertNorawat($no_rawat),
        'lokalis' => $filename
      ]);
      exit();
    }

    public function getPersetujuanUmum($no_rkm_medis)
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
      echo $this->draw('persetujuan.umum.html', ['pasien' => $pasien]);
      exit();
    }
    
    public function getAssessment($no_rawat)
    {
      $no_rawat = revertNoRawat($no_rawat);
      
      // Cek apakah sudah ada data assessment
      $penilaian_igd = $this->db('penilaian_awal_keperawatan_igd')
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      
      // Jika belum ada, ambil data fallback dari pemeriksaan_ralan
      if(!$penilaian_igd) {
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
          ->where('no_rawat', $no_rawat)
          ->desc('tgl_perawatan')
          ->desc('jam_rawat')
          ->oneArray();
        
        $penilaian_igd = [
          'no_rawat' => $no_rawat,
          'tanggal' => date('Y-m-d H:i:s'),
          'informasi' => 'Autoanamnesis',
          'keluhan_utama' => $pemeriksaan_ralan['keluhan'] ?? '',
          'rpd' => '',
          'rpo' => '',
          'status_kehamilan' => 'Tidak Hamil',
          'gravida' => '',
          'para' => '',
          'abortus' => '',
          'hpht' => '',
          'tekanan' => 'TAK',
          'pupil' => 'Normal',
          'neurosensorik' => 'TAK',
          'integumen' => 'TAK',
          'turgor' => 'Baik',
          'edema' => 'Tidak Ada',
          'mukosa' => 'Lembab',
          'perdarahan' => 'Tidak Ada',
          'jumlah_perdarahan' => '',
          'warna_perdarahan' => '',
          'intoksikasi' => 'Tidak Ada',
          'bab' => '',
          'xbab' => '',
          'kbab' => '',
          'wbab' => '',
          'bak' => '',
          'xbak' => '',
          'wbak' => '',
          'lbak' => '',
          'psikologis' => 'Tidak Ada Masalah',
          'jiwa' => 'Tidak',
          'perilaku' => '-',
          'dilaporkan' => '',
          'sebutkan' => '',
          'hubungan' => 'Harmonis',
          'tinggal_dengan' => 'Orang Tua',
          'ket_tinggal' => '',
          'budaya' => 'Tidak Ada',
          'ket_budaya' => '',
          'pendidikan_pj' => '-',
          'ket_pendidikan_pj' => '',
          'edukasi' => 'Pasien',
          'ket_edukasi' => '',
          'kemampuan' => 'Mandiri',
          'aktifitas' => 'Berjalan',
          'alat_bantu' => 'Tidak',
          'ket_bantu' => '',
          'nyeri' => 'Tidak Ada Nyeri',
          'provokes' => 'Proses Penyakit',
          'ket_provokes' => '',
          'quality' => 'Seperti Tertusuk',
          'ket_quality' => '',
          'lokasi' => '',
          'menyebar' => 'Tidak',
          'skala_nyeri' => '0',
          'durasi' => '',
          'nyeri_hilang' => 'Istirahat',
          'ket_nyeri' => '',
          'pada_dokter' => 'Tidak',
          'ket_dokter' => '',
          'berjalan_a' => 'Tidak',
          'berjalan_b' => 'Tidak',
          'berjalan_c' => 'Tidak',
          'hasil' => 'Tidak beresiko (tidak ditemukan a dan b)',
          'lapor' => 'Tidak',
          'ket_lapor' => '',
          'rencana' => '',
          'nip' => $this->core->getUserInfo('username', null, true)
        ];
      }
      
      echo $this->draw('assesment.html', ['penilaian_igd' => $penilaian_igd]);
      exit();
    }

    public function postAssessmentsave()
    {
      $_POST['nip'] = $this->core->getUserInfo('username', null, true);
      
      // Cek apakah sudah ada data
      $existing = $this->db('penilaian_awal_keperawatan_igd')
        ->where('no_rawat', $_POST['no_rawat'])
        ->oneArray();
      
      if($existing) {
        // Update data yang sudah ada
        $query = $this->db('penilaian_awal_keperawatan_igd')
          ->where('no_rawat', $_POST['no_rawat'])
          ->save($_POST);
      } else {
        // Insert data baru
        $query = $this->db('penilaian_awal_keperawatan_igd')->save($_POST);
      }
      
      if($query) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = 'Gagal menyimpan data assessment';
        echo json_encode($data);
      }
      exit();
    }

    public function getAssessmenttampil($no_rawat)
    {
      $no_rawat = revertNoRawat($no_rawat);
      
      $penilaian_igd = $this->db('penilaian_awal_keperawatan_igd')
        ->join('petugas', 'petugas.nip=penilaian_awal_keperawatan_igd.nip')
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      
      if($penilaian_igd) {
        $penilaian_igd['nama_petugas'] = $penilaian_igd['nama'];
      }
      
      echo $this->draw('assesment.tampil.html', ['penilaian_igd' => $penilaian_igd]);
      exit();
    }

    public function postAssessmentdelete()
    {
      $query = $this->db('penilaian_awal_keperawatan_igd')
        ->where('no_rawat', $_POST['no_rawat'])
        ->delete();
      
      if($query) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = 'Gagal menghapus data assessment';
        echo json_encode($data);
      }
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        if (!is_array($this->assign)) {
            $this->assign = []; // atau bisa langsung array dengan default
        }

        $cek_pegawai = $this->db('pegawai')->where('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']))->oneArray();
        $cek_role = '';
        if($cek_pegawai) {
          $cek_role = $this->core->getPegawaiInfo('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']));
        }
        $this->assign['websocket'] = $this->settings->get('settings.websocket');
        $this->assign['websocket_proxy'] = $this->settings->get('settings.websocket_proxy');
        echo $this->draw(MODULES.'/igd/js/admin/igd.js', ['cek_role' => $cek_role, 'mlite' => $this->assign]);
        exit();
    }

    // ===== TRIASE IGD METHODS =====
    
    public function anyTriaseIgd()
    {
        if(isset($_POST['no_rawat'])) {
            $no_rawat = $_POST['no_rawat'];
            $no_rkm_medis = $_POST['no_rkm_medis'];
            $nm_pasien = $_POST['nm_pasien'];
            $tgl_registrasi = $_POST['tgl_registrasi'];
            
            // Cek apakah sudah ada data triase
            $triase_igd = $this->db('mlite_triase_igd')
                ->where('no_rawat', $no_rawat)
                ->oneArray();
            
            // Jika belum ada, buat data default
            if(!$triase_igd) {
                $triase_igd = [
                    'id_triase' => '',
                    'no_rawat' => $no_rawat,
                    'no_rkm_medis' => $no_rkm_medis,
                    'nm_pasien' => $nm_pasien,
                    'tgl_registrasi' => $tgl_registrasi,
                    'tgl_triase' => date('Y-m-d\TH:i'),
                    'petugas_id' => $this->core->getUserInfo('username', null, true),
                    'kesadaran' => '',
                    'airway' => '',
                    'breathing' => '',
                    'circulation' => '',
                    'tekanan_darah' => '',
                    'nadi' => '',
                    'respirasi' => '',
                    'suhu' => '',
                    'spo2' => '',
                    'gcs_e' => '',
                    'gcs_v' => '',
                    'gcs_m' => '',
                    'kategori' => '',
                    'skala_triase' => '',
                    'keluhan_utama' => '',
                    'diagnosa_awal' => ''
                ];
            } else {
                $triase_igd['nm_pasien'] = $nm_pasien;
                $triase_igd['tgl_registrasi'] = $tgl_registrasi;
                // Format datetime untuk input datetime-local
                if($triase_igd['tgl_triase']) {
                    $triase_igd['tgl_triase'] = date('Y-m-d\TH:i', strtotime($triase_igd['tgl_triase']));
                }
            }
            
            // Ambil data petugas untuk dropdown
            $petugas = $this->db('petugas')->where('status', '1')->toArray();
            
            echo $this->draw('triase_igd.html', [
                'triase_igd' => $triase_igd,
                'petugas' => $petugas
            ]);
        }
        exit();
    }
    
    public function postTriaseIgdSave()
    {
        try {
            // Debug: Log raw POST data
            error_log('DEBUG: Raw POST data: ' . print_r($_POST, true));
            
            // Validasi input required
            $required_fields = ['no_rawat', 'no_rkm_medis', 'tgl_triase', 'petugas_id', 'kesadaran_triase', 'airway', 'breathing', 'circulation', 'kategori'];
            foreach($required_fields as $field) {
                if(empty($_POST[$field])) {
                    throw new \Exception("Field {$field} harus diisi");
                }
            }
            
            // Prepare clean data array
            $data_to_save = [];
            
            // Copy only allowed fields
            $allowed_fields = ['no_rawat', 'no_rkm_medis', 'tgl_triase', 'petugas_id', 'kesadaran', 'airway', 'breathing', 'circulation', 'tekanan_darah', 'nadi', 'respirasi', 'suhu', 'spo2', 'gcs_e', 'gcs_v', 'gcs_m', 'kategori', 'skala_triase', 'keluhan_utama', 'diagnosa_awal'];
            
            foreach($allowed_fields as $field) {
                if(isset($_POST[$field]) && $_POST[$field] !== '') {
                    $data_to_save[$field] = $_POST[$field];
                }
            }
            
            // Handle kesadaran_triase mapping
            if(isset($_POST['kesadaran_triase'])) {
                $data_to_save['kesadaran'] = $_POST['kesadaran_triase'];
            }
            
            // Format datetime
            if(isset($data_to_save['tgl_triase'])) {
                $data_to_save['tgl_triase'] = date('Y-m-d H:i:s', strtotime($data_to_save['tgl_triase']));
            }
            
            // Debug: Log cleaned data
            error_log('DEBUG: Cleaned data to save: ' . print_r($data_to_save, true));
            
            // Completely prevent framework from auto-adding timestamp fields
            // MLite QueryWrapper auto-adds created_at and updated_at with time() if columns exist
            // We need to bypass this behavior entirely
            
            // Explicitly set timestamp fields to NULL to prevent framework auto-addition
            $data_to_save['created_at'] = null;
            $data_to_save['updated_at'] = null;
            
            // Remove id_triase for insert
            unset($data_to_save['id_triase']);
            
            // Debug: Log final data
            error_log('DEBUG: Final data after cleanup: ' . print_r($data_to_save, true));
            
            // Use direct SQL approach to bypass framework timestamp behavior
            if(!empty($_POST['id_triase'])) {
                // Update data yang sudah ada - use direct SQL to avoid framework timestamp issues
                error_log('DEBUG: Performing UPDATE with direct SQL for id_triase: ' . $_POST['id_triase']);
                
                // Remove null timestamp fields and let MySQL handle them
                unset($data_to_save['created_at']);
                // unset($data_to_save['updated_at']);
                $data_to_save['updated_at'] = date('Y-m-d H:i:s');
                
                // Build UPDATE query manually to avoid framework interference
                $fields = array_keys($data_to_save);
                $set_clause = [];
                foreach($fields as $field) {
                    $set_clause[] = "{$field} = :{$field}";
                }
                $sql = "UPDATE mlite_triase_igd SET " . implode(', ', $set_clause) . " WHERE id_triase = :id_triase";
                
                // Add id_triase to data for WHERE clause
                $data_to_save['id_triase'] = $_POST['id_triase'];
                
                error_log('DEBUG: Direct UPDATE SQL: ' . $sql);
                error_log('DEBUG: UPDATE Data: ' . print_r($data_to_save, true));
                
                $stmt = $this->db()->pdo()->prepare($sql);
                $query = $stmt->execute($data_to_save);
                $message = 'Data triase berhasil diupdate';
            } else {
                // Insert data baru - use direct SQL to avoid framework timestamp issues
                error_log('DEBUG: Performing INSERT with direct SQL');
                
                // Remove null timestamp fields and let MySQL handle them
                unset($data_to_save['created_at']);
                unset($data_to_save['updated_at']);
                
                // Build INSERT query manually to avoid framework interference
                $fields = array_keys($data_to_save);
                $placeholders = ':' . implode(', :', $fields);
                $sql = "INSERT INTO mlite_triase_igd (" . implode(', ', $fields) . ") VALUES (" . $placeholders . ")";
                
                error_log('DEBUG: Direct SQL: ' . $sql);
                error_log('DEBUG: SQL Data: ' . print_r($data_to_save, true));
                
                $stmt = $this->db()->pdo()->prepare($sql);
                $query = $stmt->execute($data_to_save);
                $message = 'Data triase berhasil disimpan';
            }
            
            if($query) {
                $data['status'] = 'success';
                $data['msg'] = $message;
                error_log('DEBUG: Database operation successful');
            } else {
                $data['status'] = 'error';
                $data['msg'] = 'Gagal menyimpan data triase';
                error_log('DEBUG: Database operation failed');
            }
            
        } catch(\Exception $e) {
            $data['status'] = 'error';
            $data['msg'] = $e->getMessage();
            error_log('DEBUG: Exception caught: ' . $e->getMessage());
            error_log('DEBUG: Exception trace: ' . $e->getTraceAsString());
        }
        
        echo json_encode($data);
        exit();
    }
    
    public function postTriaseIgdDelete()
    {
        try {
            if(empty($_POST['no_rawat'])) {
                throw new \Exception('No rawat tidak boleh kosong');
            }
            
            $query = $this->db('mlite_triase_igd')
                ->where('no_rawat', $_POST['no_rawat'])
                ->delete();
            
            if($query) {
                $data['status'] = 'success';
                $data['msg'] = 'Data triase berhasil dihapus';
            } else {
                $data['status'] = 'error';
                $data['msg'] = 'Gagal menghapus data triase';
            }
            
        } catch(\Exception $e) {
            $data['status'] = 'error';
            $data['msg'] = $e->getMessage();
        }
        
        echo json_encode($data);
        exit();
    }
    
    public function postTriaseIgdView()
    {
        if(isset($_POST['no_rawat'])) {
            $triase_igd = $this->db('mlite_triase_igd')
                ->join('petugas', 'petugas.nip=mlite_triase_igd.petugas_id')
                ->join('pasien', 'pasien.no_rkm_medis=mlite_triase_igd.no_rkm_medis')
                ->where('mlite_triase_igd.no_rawat', $_POST['no_rawat'])
                ->oneArray();
            
            if($triase_igd) {
                $triase_igd['nama_petugas'] = $triase_igd['nama'];
                // Pastikan nm_pasien tersedia, jika tidak ada berikan fallback
                if(empty($triase_igd['nm_pasien'])) {
                    $triase_igd['nm_pasien'] = 'Nama Pasien Tidak Ditemukan';
                }
                // Hitung total GCS
                $total_gcs = ($triase_igd['gcs_e'] ?? 0) + ($triase_igd['gcs_v'] ?? 0) + ($triase_igd['gcs_m'] ?? 0);
                $triase_igd['total_gcs'] = $total_gcs > 0 ? $total_gcs : '-';
                
                echo $this->draw('triase_igd_view.html', ['triase_igd' => $triase_igd]);
            } else {
                echo '<div class="alert alert-warning">Data triase tidak ditemukan</div>';
            }
        }
        exit();
    }

    public function postSimpanLokalis()
    {
        $img = $_POST['image'] ?? '';
        $no_rawat = $_POST['no_rawat'] ?? '';
        $no_rawat = convertNoRawat($no_rawat);

        if (!$img) {
            http_response_code(400);
            exit('Data kosong');
        }

        $img = str_replace('data:image/png;base64,', '', $img);
        $img = base64_decode($img);

        if(!is_dir(UPLOADS . '/lokalis/')) {
            mkdir(UPLOADS . '/lokalis/', 0755, true);
        }

        $filename = 'lokalis_' . $no_rawat . '.png';
        file_put_contents(UPLOADS . '/lokalis/' . $filename, $img);

        echo json_encode([
            'status' => 'ok',
            'file' => $filename
        ]);
        exit;
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addCSS(url('plugins/igd/css/triase_igd.css'));
        $this->core->addJS(url([ADMIN, 'igd', 'javascript']), 'footer');
    }



    public function apiList()
    {

        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $draw = $_GET['draw'] ?? 0;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $columnIndex = $_GET['order'][0]['column'] ?? 0;
        $columnName = $_GET['columns'][$columnIndex]['data'] ?? 'no_reg';
        $columnSortOrder = $_GET['order'][0]['dir'] ?? 'asc';
        $searchValue = is_array($_GET['search'] ?? null) ? ($_GET['search']['value'] ?? '') : ($_GET['search'] ?? '');

        $tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d');
        $tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
        $status_periksa = $_GET['status_periksa'] ?? '';
        $status_bayar = $_GET['status_bayar'] ?? '';

        $user_id = $this->db('mlite_users')->where('username', $username)->oneArray()['id'];
        $poliklinik = str_replace(",","','", (string)$this->core->getUserInfo('cap', $user_id, true));
        $igd = $this->settings('settings', 'igd');

        // Base Query
        if ($tgl_awal > date('Y-m-d')) {
            $sql = "SELECT booking_registrasi.no_reg, booking_registrasi.jam_booking as jam_reg, 
                booking_registrasi.no_rkm_medis, booking_registrasi.kd_poli, booking_registrasi.kd_dokter,
                booking_registrasi.status as stts, 'Belum Bayar' as status_bayar, booking_registrasi.kd_pj,
                pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab,
                concat(date_format(booking_registrasi.tanggal_periksa, '%Y/%m/%d'), '/', lpad(booking_registrasi.no_reg, 6, '0')) as no_rawat 
                FROM booking_registrasi 
                JOIN pasien ON booking_registrasi.no_rkm_medis = pasien.no_rkm_medis 
                JOIN dokter ON booking_registrasi.kd_dokter = dokter.kd_dokter 
                JOIN poliklinik ON booking_registrasi.kd_poli = poliklinik.kd_poli 
                JOIN penjab ON booking_registrasi.kd_pj = penjab.kd_pj 
                WHERE booking_registrasi.kd_poli = '$igd' 
                AND booking_registrasi.tanggal_periksa BETWEEN '$tgl_awal' AND '$tgl_akhir'";

            if ($this->core->getUserInfo('role', $user_id, true) != 'admin') {
                $sql .= " AND booking_registrasi.kd_poli IN ('$poliklinik')";
            }
            if($status_periksa == 'belum') {
                $sql .= " AND booking_registrasi.status = 'Belum'";
            }
            if($status_periksa == 'selesai') {
                $sql .= " AND booking_registrasi.status = 'Sudah'";
            }

            // Search
            if (!empty($searchValue)) {
                $sql .= " AND (booking_registrasi.no_rkm_medis LIKE '%$searchValue%' OR pasien.nm_pasien LIKE '%$searchValue%' OR dokter.nm_dokter LIKE '%$searchValue%' OR poliklinik.nm_poli LIKE '%$searchValue%')";
            }
        } else {
            $sql = "SELECT reg_periksa.*, pasien.nm_pasien, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab 
                    FROM reg_periksa 
                    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
                    JOIN dokter ON reg_periksa.kd_dokter = dokter.kd_dokter 
                    JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli 
                    JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj 
                    WHERE reg_periksa.kd_poli = '$igd' 
                    AND reg_periksa.tgl_registrasi BETWEEN '$tgl_awal' AND '$tgl_akhir'";

            if ($this->core->getUserInfo('role', $user_id, true) != 'admin') {
                $sql .= " AND reg_periksa.kd_poli IN ('$poliklinik')";
            }
            if($status_periksa == 'belum') {
                $sql .= " AND reg_periksa.stts = 'Belum'";
            }
            if($status_periksa == 'selesai') {
                $sql .= " AND reg_periksa.stts = 'Sudah'";
            }
            if($status_periksa == 'lunas') {
                $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
            }

            // Search
            if (!empty($searchValue)) {
                $sql .= " AND (reg_periksa.no_rawat LIKE '%$searchValue%' OR reg_periksa.no_rkm_medis LIKE '%$searchValue%' OR pasien.nm_pasien LIKE '%$searchValue%' OR dokter.nm_dokter LIKE '%$searchValue%' OR poliklinik.nm_poli LIKE '%$searchValue%')";
            }
        }

        // Count Total (filtered)
        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $totalRecords = $stmt->rowCount();

        // Order and Limit
        $sql .= " ORDER BY $columnName $columnSortOrder LIMIT $start, $length";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $row['no_rawat'])->oneArray();
            $row['no_sep'] = isset_or($bridging_sep['no_sep']);
            $data[] = $row;
        }

        return [
            "status" => "success",
            "data" => $data,
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
        if (!$this->core->checkPermission($username, 'can_read', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if(!$no_rawat) {
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
            
        if($row) {
            return ['status' => 'success', 'data' => $row];
        } else {
            return ['status' => 'error', 'message' => 'Not found'];
        }
    }

    public function apiCreate()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        if (empty($input['no_rkm_medis']) || empty($input['kd_poli']) || empty($input['kd_dokter'])) {
            return ['status' => 'error', 'message' => 'Data incomplete'];
        }

        $input['tgl_registrasi'] = $input['tgl_registrasi'] ?? date('Y-m-d');
        $input['jam_reg'] = $input['jam_reg'] ?? date('H:i:s');

        if ($input['tgl_registrasi'] > date('Y-m-d')) {
            $booking = [
                'tanggal_booking' => date('Y-m-d'),
                'jam_booking' => date('H:i:s'),
                'no_rkm_medis' => $input['no_rkm_medis'],
                'tanggal_periksa' => $input['tgl_registrasi'],
                'kd_dokter' => $input['kd_dokter'],
                'kd_poli' => $input['kd_poli'],
                'no_reg' => $this->core->setNoBooking($input['kd_dokter'], $input['tgl_registrasi'], $input['kd_poli']),
                'kd_pj' => $input['kd_pj'] ?? '-',
                'limit_reg' => '0',
                'waktu_kunjungan' => $input['tgl_registrasi'] . ' ' . $input['jam_reg'],
                'status' => 'Belum'
            ];
            try {
                $this->db('booking_registrasi')->save($booking);
                return ['status' => 'created', 'data' => $booking];
            } catch (\PDOException $e) {
                $message = $e->getMessage();
                $message = preg_replace('/`[^`]+`\./', '', $message);
                return ['status' => 'error', 'message' => $message];
            }
        }

        $input['no_rawat'] = $this->setNoRawat($input['tgl_registrasi']);
        
        // Calculate No Reg (Queue Number)
        $tgl_registrasi = $input['tgl_registrasi'];
        $q = $this->db('reg_periksa')
            ->where('kd_poli', $input['kd_poli'])
            ->where('tgl_registrasi', $tgl_registrasi);

        if ($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
            $q->where('kd_dokter', $input['kd_dokter']);
        }

        $urut = $q->nextRightNumber('no_reg', 3);

        $input['no_reg'] = sprintf('%03d', $urut);

        $input['status_lanjut'] = 'Ralan';
        $input['stts'] = 'Belum';
        $input['status_bayar'] = 'Belum Bayar';
        $input['p_jawab'] = $input['p_jawab'] ?? '-';
        $input['almt_pj'] = $input['almt_pj'] ?? '-';
        $input['hubunganpj'] = $input['hubunganpj'] ?? '-';

        $poliklinik = $this->db('poliklinik')->where('kd_poli', $input['kd_poli'])->oneArray();
        $input['biaya_reg'] = $poliklinik['registrasi'];

        $pasien = $this->db('pasien')->where('no_rkm_medis', $input['no_rkm_medis'])->oneArray();
        
        // Calculate Age
        $birthDate = new \DateTime($pasien['tgl_lahir']);
        $today = new \DateTime("today");
        $y = $today->diff($birthDate)->y;
        $m = $today->diff($birthDate)->m;
        $d = $today->diff($birthDate)->d;
        $input['umurdaftar'] = $d;
        $input['sttsumur'] = "Hr";
        if($y !='0'){
            $input['umurdaftar'] = $y;
            $input['sttsumur'] = "Th";
        }
        if($y =='0' && $m !='0'){
            $input['umurdaftar'] = $m;
            $input['sttsumur'] = "Bl";
        }
        $input['status_poli'] = 'Lama';

        try {
            $this->db('reg_periksa')->save($input);
            return ['status' => 'created', 'data' => $input];
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function apiUpdate($no_rawat = null)
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_update', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if(!$no_rawat) {
             return ['status' => 'error', 'message' => 'No rawat missing'];
        }
        
        $no_rawat = revertNoRawat($no_rawat);
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $parts = explode('/', $no_rawat);
        if (count($parts) == 4) {
            $date = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
            if ($date > date('Y-m-d')) {
                 $no_reg = substr($parts[3], -3);
                 $booking = $this->db('booking_registrasi')
                    ->where('tanggal_periksa', $date)
                    ->where('no_reg', $no_reg)
                    ->oneArray();
                 
                 if ($booking) {
                     if(isset($input['stts'])) {
                        $input['status'] = $input['stts'];
                        unset($input['stts']);
                     }

                     if ($input['status'] == 'Terdaftar') {
                        $pasien = $this->db('pasien')->where('no_rkm_medis', $booking['no_rkm_medis'])->oneArray();
                        $poliklinik = $this->db('poliklinik')->where('kd_poli', $booking['kd_poli'])->oneArray();
                        
                        $birthDate = new \DateTime($pasien['tgl_lahir']);
                        $today = new \DateTime($booking['tanggal_periksa']);
                        $y = $today->diff($birthDate)->y;
                        $m = $today->diff($birthDate)->m;
                        $d = $today->diff($birthDate)->d;
                        $umurdaftar = $d;
                        $sttsumur = "Hr";
                        if($y !='0'){
                            $umurdaftar = $y;
                            $sttsumur = "Th";
                        }
                        if($y =='0' && $m !='0'){
                            $umurdaftar = $m;
                            $sttsumur = "Bl";
                        }

                        $reg_data = [
                            'no_reg' => $booking['no_reg'],
                            'no_rawat' => $this->setNoRawat($booking['tanggal_periksa']),
                            'tgl_registrasi' => $booking['tanggal_periksa'],
                            'jam_reg' => date('H:i:s'),
                            'kd_dokter' => $booking['kd_dokter'],
                            'no_rkm_medis' => $booking['no_rkm_medis'],
                            'kd_poli' => $booking['kd_poli'],
                            'p_jawab' => $pasien['namakeluarga'] ?? '-',
                            'almt_pj' => $pasien['alamatpj'] ?? '-',
                            'hubunganpj' => $pasien['keluarga'] ?? '-',
                            'biaya_reg' => $poliklinik['registrasi'],
                            'stts' => 'Belum',
                            'status_lanjut' => 'Ralan',
                            'kd_pj' => $booking['kd_pj'],
                            'umurdaftar' => $umurdaftar,
                            'sttsumur' => $sttsumur,
                            'status_bayar' => 'Belum Bayar',
                            'status_poli' => 'Lama'
                        ];
                        
                        if(!$this->db('reg_periksa')->where('no_rkm_medis', $booking['no_rkm_medis'])->where('tgl_registrasi', $booking['tanggal_periksa'])->oneArray()) {
                            $this->db('reg_periksa')->save($reg_data);
                        }
                     }

                     try {
                        $this->db('booking_registrasi')
                            ->where('tanggal_periksa', $date)
                            ->where('no_reg', $no_reg)
                            ->save($input);
                        return ['status' => 'updated', 'data' => $input];
                     } catch (\PDOException $e) {
                        $message = $e->getMessage();
                        $message = preg_replace('/`[^`]+`\./', '', $message);
                        return ['status' => 'error', 'message' => $message];
                     }
                 }
            }
        }

        try {
            $this->db('reg_periksa')->where('no_rawat', $no_rawat)->save($input);
            return ['status' => 'updated', 'data' => $input];
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function apiDelete($no_rawat = null)
    {
        $username = $this->core->checkAuth('DELETE');
        if (!$this->core->checkPermission($username, 'can_delete', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if(!$no_rawat) {
             return ['status' => 'error', 'message' => 'No rawat missing'];
        }
        $no_rawat = revertNoRawat($no_rawat);

        $parts = explode('/', $no_rawat);
        if (count($parts) == 4) {
            $date = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
            if ($date > date('Y-m-d')) {
                 $no_reg = substr($parts[3], -3);
                 $booking = $this->db('booking_registrasi')
                    ->where('tanggal_periksa', $date)
                    ->where('no_reg', $no_reg)
                    ->oneArray();
                 
                 if ($booking) {
                     $this->db('booking_registrasi')
                        ->where('tanggal_periksa', $date)
                        ->where('no_reg', $no_reg)
                        ->delete();
                     return ['status' => 'deleted', 'no_rawat' => $no_rawat];
                 }
            }
        }

        if(!$this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray()) {
            return ['status' => 'error', 'message' => 'No rawat not found'];
        }

        try {
            $this->db('reg_periksa')->where('no_rawat', $no_rawat)->delete();
            return ['status' => 'deleted', 'no_rawat' => $no_rawat];
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function apiSaveDetail()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        
        $kategori = trim($input['kat']);

        if (empty($kategori) || empty($input['no_rawat']) || empty($input['kd_jenis_prw'])) {
            return ['status' => 'error', 'message' => 'Data incomplete'];
        }

        try {
            if($kategori == 'tindakan') {
                if (empty($input['provider'])) {
                    return ['status' => 'error', 'message' => 'Provider missing for tindakan'];
                }
                $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $input['kd_jenis_prw'])->oneArray();
                if(!$jns_perawatan) {
                    return ['status' => 'error', 'message' => 'Jenis perawatan not found'];
                }

                if($input['provider'] == 'rawat_jl_dr') {
                  for ($i = 0; $i < $input['jml_tindakan']; $i++) { 
                    $this->db('rawat_jl_dr')->save([
                        'no_rawat' => $input['no_rawat'],
                        'kd_jenis_prw' => $input['kd_jenis_prw'],
                        'kd_dokter' => $input['kode_provider'],
                        'tgl_perawatan' => $input['tgl_perawatan'],
                        'jam_rawat' => date('H:i:s', strtotime($input['jam_rawat']. ' +'.$i.'0 seconds')),
                        'material' => $jns_perawatan['material'],
                        'bhp' => $jns_perawatan['bhp'],
                        'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
                        'kso' => $jns_perawatan['kso'],
                        'menejemen' => $jns_perawatan['menejemen'],
                        'biaya_rawat' => $jns_perawatan['total_byrdr'],
                        'stts_bayar' => 'Belum'
                    ]);
                  }
                }
                if($input['provider'] == 'rawat_jl_pr') {
                  for ($i = 0; $i < $input['jml_tindakan']; $i++) { 
                    $this->db('rawat_jl_pr')->save([
                        'no_rawat' => $input['no_rawat'],
                        'kd_jenis_prw' => $input['kd_jenis_prw'],
                        'nip' => $input['kode_provider2'],
                        'tgl_perawatan' => $input['tgl_perawatan'],
                        'jam_rawat' => date('H:i:s', strtotime($input['jam_rawat']. ' +'.$i.'0 seconds')),
                        'material' => $jns_perawatan['material'],
                        'bhp' => $jns_perawatan['bhp'],
                        'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
                        'kso' => $jns_perawatan['kso'],
                        'menejemen' => $jns_perawatan['menejemen'],
                        'biaya_rawat' => $jns_perawatan['total_byrpr'],
                        'stts_bayar' => 'Belum'
                    ]);
                  }
                }
                if($input['provider'] == 'rawat_jl_drpr') {
                  for ($i = 0; $i < $input['jml_tindakan']; $i++) { 
                    $this->db('rawat_jl_drpr')->save([
                        'no_rawat' => $input['no_rawat'],
                        'kd_jenis_prw' => $input['kd_jenis_prw'],
                        'kd_dokter' => $input['kode_provider'],
                        'nip' => $input['kode_provider2'],
                        'tgl_perawatan' => $input['tgl_perawatan'],
                        'jam_rawat' => date('H:i:s', strtotime($input['jam_rawat']. ' +'.$i.'0 seconds')),
                        'material' => $jns_perawatan['material'],
                        'bhp' => $jns_perawatan['bhp'],
                        'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
                        'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
                        'kso' => $jns_perawatan['kso'],
                        'menejemen' => $jns_perawatan['menejemen'],
                        'biaya_rawat' => $jns_perawatan['total_byrdrpr'],
                        'stts_bayar' => 'Belum'
                    ]);
                  }
                }
                
                return ['status' => 'success', 'message' => 'Detail saved'];
            } elseif ($kategori == 'obat') {
                $no_resep = $this->core->setNoResep($input['tgl_perawatan']);
                $cek_resep = $this->db('resep_obat')->where('no_rawat', $input['no_rawat'])->where('tgl_peresepan', $input['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ralan')->oneArray();

                if (empty($cek_resep)) {
                    $this->db('resep_obat')
                        ->save([
                            'no_resep' => $no_resep,
                            'tgl_perawatan' => '0000-00-00',
                            'jam' => '00:00:00',
                            'no_rawat' => $input['no_rawat'],
                            'kd_dokter' => $input['kode_provider'] ?? $username,
                            'tgl_peresepan' => $input['tgl_perawatan'],
                            'jam_peresepan' => $input['jam_rawat'],
                            'status' => 'ralan',
                            'tgl_penyerahan' => '0000-00-00',
                            'jam_penyerahan' => '00:00:00'
                        ]);

                    if ($this->db('resep_obat')->where('no_resep', $no_resep)->oneArray()) {
                        $this->db('resep_dokter')
                            ->save([
                                'no_resep' => $no_resep,
                                'kode_brng' => $input['kd_jenis_prw'],
                                'jml' => $input['jml'],
                                'aturan_pakai' => $input['aturan_pakai']
                            ]);
                    }
                } else {
                    $no_resep = $cek_resep['no_resep'];
                    $this->db('resep_dokter')
                        ->save([
                            'no_resep' => $no_resep,
                            'kode_brng' => $input['kd_jenis_prw'],
                            'jml' => $input['jml'],
                            'aturan_pakai' => $input['aturan_pakai']
                        ]);
                }
                return ['status' => 'success', 'message' => 'Obat saved'];
            } elseif ($kategori == 'racikan') {
                $no_resep = $this->core->setNoResep($input['tgl_perawatan']);
                $cek_resep = $this->db('resep_obat')->where('no_rawat', $input['no_rawat'])->where('tgl_peresepan', $input['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ralan')->oneArray();
                
                $jam_rawat = $input['jam_rawat'] ?? date('H:i:s');

                if (empty($cek_resep)) {
                    $this->db('resep_obat')
                        ->save([
                            'no_resep' => $no_resep,
                            'tgl_perawatan' => '0000-00-00',
                            'jam' => '00:00:00',
                            'no_rawat' => $input['no_rawat'],
                            'kd_dokter' => $input['kode_provider'] ?? $username,
                            'tgl_peresepan' => $input['tgl_perawatan'],
                            'jam_peresepan' => $jam_rawat,
                            'status' => 'ralan',
                            'tgl_penyerahan' => '0000-00-00',
                            'jam_penyerahan' => '00:00:00'
                        ]);

                    if ($this->db('resep_obat')->where('no_resep', $no_resep)->oneArray()) {
                        $no_racik = $this->db('resep_dokter_racikan')->where('no_resep', $no_resep)->count();
                        $no_racik = $no_racik + 1;
                        $this->db('resep_dokter_racikan')
                            ->save([
                                'no_resep' => $no_resep,
                                'no_racik' => $no_racik,
                                'nama_racik' => $input['nama_racik'],
                                'kd_racik' => $input['kd_jenis_prw'],
                                'jml_dr' => $input['jml'],
                                'aturan_pakai' => $input['aturan_pakai'],
                                'keterangan' => $input['keterangan']
                            ]);
                        
                        $kode_brng_list = is_string($input['kode_brng']) ? json_decode($input['kode_brng'], true) : $input['kode_brng'];
                        $kandungan_list = is_string($input['kandungan']) ? json_decode($input['kandungan'], true) : $input['kandungan'];
                        
                        $kode_brng_count = count($kode_brng_list);
                        for ($i = 0; $i < $kode_brng_count; $i++) {
                            $kapasitas = $this->db('databarang')->where('kode_brng', $kode_brng_list[$i]['value'])->oneArray();
                            $jml = $input['jml'] * $kandungan_list[$i]['value'];
                            if ($kapasitas['kapasitas'] > 0) {
                                $jml = round(($jml / $kapasitas['kapasitas']), 1);
                            }
                            $this->db('resep_dokter_racikan_detail')
                                ->save([
                                    'no_resep' => $no_resep,
                                    'no_racik' => $no_racik,
                                    'kode_brng' => $kode_brng_list[$i]['value'],
                                    'p1' => '1',
                                    'p2' => '1',
                                    'kandungan' => $kandungan_list[$i]['value'],
                                    'jml' => $jml
                                ]);
                        }
                    }
                } else {
                    $no_resep = $cek_resep['no_resep'];
                    $no_racik = $this->db('resep_dokter_racikan')->where('no_resep', $no_resep)->count();
                    $no_racik = $no_racik + 1;
                    $this->db('resep_dokter_racikan')
                        ->save([
                            'no_resep' => $no_resep,
                            'no_racik' => $no_racik,
                            'nama_racik' => $input['nama_racik'],
                            'kd_racik' => $input['kd_jenis_prw'],
                            'jml_dr' => $input['jml'],
                            'aturan_pakai' => $input['aturan_pakai'],
                            'keterangan' => $input['keterangan']
                        ]);

                    $kode_brng_list = is_string($input['kode_brng']) ? json_decode($input['kode_brng'], true) : $input['kode_brng'];
                    $kandungan_list = is_string($input['kandungan']) ? json_decode($input['kandungan'], true) : $input['kandungan'];

                    $kode_brng_count = count($kode_brng_list);
                    for ($i = 0; $i < $kode_brng_count; $i++) {
                        $kapasitas = $this->db('databarang')->where('kode_brng', $kode_brng_list[$i]['value'])->oneArray();
                        $jml = $input['jml'] * $kandungan_list[$i]['value'];
                        if ($kapasitas['kapasitas'] > 0) {
                             $jml = round(($jml / $kapasitas['kapasitas']), 1);
                        }
                        $this->db('resep_dokter_racikan_detail')
                            ->save([
                                'no_resep' => $no_resep,
                                'no_racik' => $no_racik,
                                'kode_brng' => $kode_brng_list[$i]['value'],
                                'p1' => '1',
                                'p2' => '1',
                                'kandungan' => $kandungan_list[$i]['value'],
                                'jml' => $jml
                            ]);
                    }
                }
                return ['status' => 'success', 'message' => 'Racikan saved'];
            } else {
                return ['status' => 'error', 'message' => 'Category not supported'];
            }
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function apiDeleteDetail()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        
        $kategori = trim($input['kat']);

        if (empty($kategori) || empty($input['no_rawat']) || empty($input['kd_jenis_prw']) || empty($input['provider']) || empty($input['tgl_perawatan']) || empty($input['jam_rawat'])) {
            return ['status' => 'error', 'message' => 'Data incomplete'];
        }

        try {
            if($kategori == 'tindakan') {
                if($input['provider'] == 'rawat_jl_dr') {
                    $this->db('rawat_jl_dr')
                        ->where('no_rawat', $input['no_rawat'])
                        ->where('kd_jenis_prw', $input['kd_jenis_prw'])
                        ->where('tgl_perawatan', $input['tgl_perawatan'])
                        ->where('jam_rawat', $input['jam_rawat'])
                        ->delete();
                }
                if($input['provider'] == 'rawat_jl_pr') {
                    $this->db('rawat_jl_pr')
                        ->where('no_rawat', $input['no_rawat'])
                        ->where('kd_jenis_prw', $input['kd_jenis_prw'])
                        ->where('tgl_perawatan', $input['tgl_perawatan'])
                        ->where('jam_rawat', $input['jam_rawat'])
                        ->delete();
                }
                if($input['provider'] == 'rawat_jl_drpr') {
                    $this->db('rawat_jl_drpr')
                        ->where('no_rawat', $input['no_rawat'])
                        ->where('kd_jenis_prw', $input['kd_jenis_prw'])
                        ->where('tgl_perawatan', $input['tgl_perawatan'])
                        ->where('jam_rawat', $input['jam_rawat'])
                        ->delete();
                }
                
                return ['status' => 'success', 'message' => 'Detail deleted'];
            } elseif ($kategori == 'obat') {
                if(isset($input['kd_jenis_prw'])) {
                    $this->db('resep_dokter')
                        ->where('no_resep', $input['no_resep'])
                        ->where('kode_brng', $input['kd_jenis_prw'])
                        ->delete();
                } else {
                    $this->db('resep_obat')
                        ->where('no_resep', $input['no_resep'])
                        ->where('no_rawat', $input['no_rawat'])
                        ->where('tgl_peresepan', $input['tgl_peresepan'])
                        ->where('jam_peresepan', $input['jam_peresepan'])
                        ->delete();
                }
                return ['status' => 'success', 'message' => 'Resep deleted'];
            } elseif ($kategori == 'racikan') {
                if(isset($input['kd_jenis_prw']) && isset($input['no_racik'])) {
                    $this->db('resep_dokter_racikan_detail')
                        ->where('no_resep', $input['no_resep'])
                        ->where('no_racik', $input['no_racik'])
                        ->where('kode_brng', $input['kd_jenis_prw'])
                        ->delete();
                } elseif (isset($input['no_racik'])) {
                    $this->db('resep_dokter_racikan')
                        ->where('no_resep', $input['no_resep'])
                        ->where('no_racik', $input['no_racik'])
                        ->delete();
                    $this->db('resep_dokter_racikan_detail')
                        ->where('no_resep', $input['no_resep'])
                        ->where('no_racik', $input['no_racik'])
                        ->delete();
                }
                return ['status' => 'success', 'message' => 'Racikan deleted'];
            } else {
                return ['status' => 'error', 'message' => 'Category not supported'];
            }
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function apiShowDetail($kategori, $no_rawat)
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_read', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $no_rawat = revertNorawat($no_rawat);
        $kategori = trim($kategori);
        $no_resep = isset($_GET['no_resep']) ? $_GET['no_resep'] : null;

        $pasien = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('no_rawat', $no_rawat)
            ->oneArray();

        $patient_info = [
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'no_rkm_medis' => $pasien['no_rkm_medis'] ?? ''
        ];

        try {
            if ($kategori == 'tindakan') {
                $rawat_jl_dr = $this->db('rawat_jl_dr')
                    ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')
                    ->join('dokter', 'dokter.kd_dokter = rawat_jl_dr.kd_dokter')
                    ->where('no_rawat', $no_rawat)
                    ->toArray();

                $rawat_jl_pr = $this->db('rawat_jl_pr')
                    ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')
                    ->join('petugas', 'petugas.nip = rawat_jl_pr.nip')
                    ->where('no_rawat', $no_rawat)
                    ->toArray();

                $rawat_jl_drpr = $this->db('rawat_jl_drpr')
                    ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')
                    ->join('dokter', 'dokter.kd_dokter = rawat_jl_drpr.kd_dokter')
                    ->join('petugas', 'petugas.nip = rawat_jl_drpr.nip')
                    ->where('no_rawat', $no_rawat)
                    ->toArray();

                return [
                    'status' => 'success',
                    'patient' => $patient_info,
                    'data' => [
                        'rawat_jl_dr' => $rawat_jl_dr,
                        'rawat_jl_pr' => $rawat_jl_pr,
                        'rawat_jl_drpr' => $rawat_jl_drpr
                    ]
                ];
            } elseif ($kategori == 'obat') {
                $query = $this->db('resep_dokter')
                    ->join('resep_obat', 'resep_obat.no_resep = resep_dokter.no_resep')
                    ->join('databarang', 'databarang.kode_brng = resep_dokter.kode_brng')
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where('resep_obat.status', 'ralan');

                if ($no_resep) {
                    $query->where('resep_obat.no_resep', $no_resep);
                }

                $resep_dokter = $query->toArray();

                return [
                    'status' => 'success',
                    'patient' => $patient_info,
                    'data' => $resep_dokter
                ];
            } elseif ($kategori == 'racikan') {
                $query = $this->db('resep_dokter_racikan')
                    ->join('resep_obat', 'resep_obat.no_resep = resep_dokter_racikan.no_resep')
                    ->join('metode_racik', 'metode_racik.kd_racik = resep_dokter_racikan.kd_racik')
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where('resep_obat.status', 'ralan');

                if ($no_resep) {
                    $query->where('resep_obat.no_resep', $no_resep);
                }

                $resep_racikan = $query->toArray();

                foreach ($resep_racikan as &$racikan) {
                    $racikan['detail'] = $this->db('resep_dokter_racikan_detail')
                        ->join('databarang', 'databarang.kode_brng = resep_dokter_racikan_detail.kode_brng')
                        ->where('no_resep', $racikan['no_resep'])
                        ->where('no_racik', $racikan['no_racik'])
                        ->toArray();
                }

                return [
                    'status' => 'success',
                    'patient' => $patient_info,
                    'data' => $resep_racikan
                ];
            } else {
                return ['status' => 'error', 'message' => 'Category not supported: ' . $kategori];
            }
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiShowSoap($no_rawat)
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_read', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $no_rawat = revertNorawat($no_rawat);

        try {
            $pemeriksaan = $this->db('pemeriksaan_ralan')
                ->where('no_rawat', $no_rawat)
                ->toArray();

            return ['status' => 'success', 'data' => $pemeriksaan];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveSOAP()
    {
        $username = $this->core->checkAuth('POST');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $required = ['no_rawat', 'tgl_perawatan', 'jam_rawat', 'suhu_tubuh', 'tensi', 'nadi', 'respirasi', 'tinggi', 'berat', 'gcs', 'keluhan', 'pemeriksaan', 'alergi', 'lingkar_perut', 'rtl', 'penilaian', 'instruksi', 'evaluasi', 'nip'];
        
        foreach($required as $field) {
            if(!isset($input[$field])) {
                return ['status' => 'error', 'message' => 'Field '.$field.' missing'];
            }
        }

        try {
            if(!$this->db('pemeriksaan_ralan')
                ->where('no_rawat', $input['no_rawat'])
                ->where('tgl_perawatan', $input['tgl_perawatan'])
                ->where('jam_rawat', $input['jam_rawat'])
                ->where('nip', $input['nip'])
                ->oneArray()) {

                if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
                    return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
                }

                $this->db('pemeriksaan_ralan')->save($input);
            } else {

                if (!$this->core->checkPermission($username, 'can_update', 'igd')) {
                    return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
                }

                $this->db('pemeriksaan_ralan')
                    ->where('no_rawat', $input['no_rawat'])
                    ->where('tgl_perawatan', $input['tgl_perawatan'])
                    ->where('jam_rawat', $input['jam_rawat'])
                    ->where('nip', $input['nip'])
                    ->save($input);
            }
            return ['status' => 'success', 'message' => 'SOAP saved'];
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function apiDeleteSOAP()
    {
        $username = $this->core->checkAuth('DELETE');
        if (!$this->core->checkPermission($username, 'can_delete', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_REQUEST;

        if(empty($input['no_rawat']) || empty($input['tgl_perawatan']) || empty($input['jam_rawat'])) {
            return ['status' => 'error', 'message' => 'Parameters incomplete'];
        }

        try {
            $this->db('pemeriksaan_ralan')
                ->where('no_rawat', $input['no_rawat'])
                ->where('tgl_perawatan', $input['tgl_perawatan'])
                ->where('jam_rawat', $input['jam_rawat'])
                ->delete();
            return ['status' => 'success', 'message' => 'SOAP deleted'];
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function setNoRawat($date = null)
    {
        $date = $date ?? date('Y-m-d');
        $urut = $this->db('reg_periksa')
            ->where('tgl_registrasi', $date)
            ->nextRightNumber('no_rawat', 6);

        $next_no_rawat = str_replace('-', '/', $date)
            . '/' . sprintf('%06d', $urut);

        return $next_no_rawat;
    }

    public function apiSaveDiagnosa()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kd_penyakit' => $input['kd_penyakit'],
            'status' => $input['status'] ?? 'Ralan',
            'prioritas' => $input['prioritas'],
            'status_penyakit' => 'Baru'
        ];

        try {
            $this->db('diagnosa_pasien')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteDiagnosa()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('diagnosa_pasien')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kd_penyakit', $input['kd_penyakit'])
                ->where('prioritas', $input['prioritas'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveProsedur()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kode' => $input['kode'],
            'status' => $input['status'] ?? 'Ralan',
            'prioritas' => $input['prioritas']
        ];

        try {
            $this->db('prosedur_pasien')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteProsedur()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('prosedur_pasien')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kode', $input['kode'])
                ->where('prioritas', $input['prioritas'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveCatatan()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $input['no_rawat']);

        $data = [
            'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'),
            'no_rawat' => $input['no_rawat'],
            'kd_dokter' => $kd_dokter,
            'catatan' => $input['catatan']
        ];

        try {
            $this->db('catatan_perawatan')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteCatatan()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('catatan_perawatan')
                ->where('no_rawat', $input['no_rawat'])
                ->where('tanggal', $input['tanggal'])
                ->where('jam', $input['jam'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveBerkas()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kode' => $input['judul'] ?? '',
            'lokasi_file' => $input['deskripsi'] ?? ''
        ];

        try {
            $this->db('berkas_digital_perawatan')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteBerkas()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('berkas_digital_perawatan')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kode', $input['judul'] ?? $input['kode'])
                ->where('lokasi_file', $input['deskripsi'] ?? $input['lokasi_file'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveResume()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $input['no_rawat']);

        // Check if exists
        $exists = $this->db('resume_pasien')->where('no_rawat', $input['no_rawat'])->oneArray();

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kd_dokter' => $kd_dokter,
            'diagnosa_utama' => $input['diagnosa_utama'] ?? '',
            'diagnosa_sekunder' => $input['diagnosa_sekunder'] ?? '',
            'jalannya_penyakit' => $input['jalannya_penyakit'] ?? '',
            'obat_pulang' => $input['terapi'] ?? '',
            'kondisi_pulang' => $input['kondisi_pulang'] ?? 'Hidup',
            // Default required fields
            'keluhan_utama' => '',
            'pemeriksaan_penunjang' => '',
            'hasil_laborat' => '',
            'kd_diagnosa_utama' => '',
            'kd_diagnosa_sekunder' => '',
            'diagnosa_sekunder2' => '',
            'kd_diagnosa_sekunder2' => '',
            'diagnosa_sekunder3' => '',
            'kd_diagnosa_sekunder3' => '',
            'diagnosa_sekunder4' => '',
            'kd_diagnosa_sekunder4' => '',
            'prosedur_utama' => '',
            'kd_prosedur_utama' => '',
            'prosedur_sekunder' => '',
            'kd_prosedur_sekunder' => '',
            'prosedur_sekunder2' => '',
            'kd_prosedur_sekunder2' => '',
            'prosedur_sekunder3' => '',
            'kd_prosedur_sekunder3' => ''
        ];

        try {
            if ($exists) {
                $this->db('resume_pasien')->where('no_rawat', $input['no_rawat'])->save($data);
            } else {
                $this->db('resume_pasien')->save($data);
            }
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveRujukanInternal()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kd_poli' => $input['kd_poli'],
            'kd_dokter' => $input['kd_dokter'],
            'isi_rujukan' => $input['catatan'] ?? ''
        ];

        try {
            $this->db('mlite_rujukan_internal_poli')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteRujukanInternal()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('mlite_rujukan_internal_poli')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kd_poli', $input['kd_poli'])
                ->where('kd_dokter', $input['kd_dokter'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveLaporanOperasi()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'igd')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'tanggal' => $input['tanggal'] . ' ' . $input['jam'],
            'operator' => $input['operator'],
            'laporan_operasi' => $input['laporan'] ?? '',
            // Defaults
            'operator2' => '-', 'operator3' => '-', 'asisten_operator1' => '-', 'asisten_operator2' => '-', 'asisten_operator3' => '-',
            'instrumen' => '-', 'dokter_anak' => '-', 'perawat_resusitas' => '-', 'dokter_anestesi' => '-', 'asisten_anestesi' => '-',
            'bidan' => '-', 'perawat_luar' => '-', 'omloop' => '-', 'omloop2' => '-', 'omloop3' => '-', 'omloop4' => '-', 'omloop5' => '-'
        ];

        try {
            $this->db('laporan_operasi')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteLaporanOperasi()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $tanggal = $input['tanggal'];
            if(isset($input['jam'])) {
                $tanggal .= ' ' . $input['jam'];
            }

            $this->db('laporan_operasi')
                ->where('no_rawat', $input['no_rawat'])
                ->where('tanggal', $tanggal)
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }    

}
