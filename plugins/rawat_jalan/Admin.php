<?php
namespace Plugins\Rawat_Jalan;

use Systems\AdminModule;
use Systems\Lib\BpjsService;
use LZCompressor\LZString;

class Admin extends AdminModule
{

    protected $consid;
    protected $secretkey;
    protected $user_key;
    protected $api_url;
    protected array $assign = [];
    
    public function init()
    {
      $this->consid = $this->settings->get('settings.BpjsConsID');
      $this->secretkey = $this->settings->get('settings.BpjsSecretKey');
      $this->user_key = $this->settings->get('settings.BpjsUserKey');
      $this->api_url = $this->settings->get('settings.BpjsApiUrl');
    }
    private $_uploads = WEBAPPS_PATH.'/berkasrawat/pages/upload';
    public function navigation()
    {
        return [
            'Kelola'              => 'index',
            'Rawat Jalan'         => 'manage',
            'Booking Registrasi'  => 'booking',
            'Booking Periksa'     => 'bookingperiksa',
            'Jadwal Dokter'       => 'jadwal'
        ];
    }

    public function getIndex()
    {
      $sub_modules = [
        ['name' => 'Rawat Jalan', 'url' => url([ADMIN, 'rawat_jalan', 'manage']), 'icon' => 'wheelchair', 'desc' => 'Pendaftaran pasien rawat jalan'],
        ['name' => 'Booking Registrasi', 'url' => url([ADMIN, 'rawat_jalan', 'booking']), 'icon' => 'file-o', 'desc' => 'Pendaftaran pasien booking rawat jalan'],
        ['name' => 'Booking Periksa', 'url' => url([ADMIN, 'rawat_jalan', 'bookingperiksa']), 'icon' => 'file-o', 'desc' => 'Booking periksa pasien rawat jalan via Online'],
        ['name' => 'Jadwal Dokter', 'url' => url([ADMIN, 'rawat_jalan', 'jadwal']), 'icon' => 'user-md', 'desc' => 'Jadwal dokter rawat jalan'],
      ];
      return $this->draw('index.html', ['sub_modules' => $sub_modules]);
    }

    public function anyManage()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';

        $waapitoken =  $this->settings->get('wagateway.token');
        $waapiphonenumber =  $this->settings->get('wagateway.phonenumber');
        $nama_instansi =  $this->settings->get('settings.nama_instansi');

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
        $cek_pcare = $this->db('mlite_modules')->where('dir', 'pcare')->oneArray();
        $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
        $responsivevoice =  $this->settings->get('settings.responsivevoice');
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_bayar);
        return $this->draw('manage.html',
          [
            'rawat_jalan' => $this->assign,
            'cek_vclaim' => $cek_vclaim,
            'cek_pcare' => $cek_pcare,
            'master_berkas_digital' => $master_berkas_digital,
            'responsivevoice' => $responsivevoice,
            'admin_mode' => $this->settings->get('settings.admin_mode'),
            'waapitoken' => $waapitoken,
            'waapiphonenumber' => $waapiphonenumber,
            'nama_instansi' => $nama_instansi, 
            'username_fp' => $this->settings->get('settings.username_fp'), 
            'password_fp' => $this->settings->get('settings.password_fp'), 
            'username_frista' => $this->settings->get('settings.username_frista'), 
            'password_frista' => $this->settings->get('settings.password_frista')
          ]
        );
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';

        $waapitoken =  $this->settings->get('wagateway.token');
        $waapiphonenumber =  $this->settings->get('wagateway.phonenumber');
        $nama_instansi =  $this->settings->get('settings.nama_instansi');

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
        $cek_pcare = $this->db('mlite_modules')->where('dir', 'pcare')->oneArray();
        $responsivevoice =  $this->settings->get('settings.responsivevoice');
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_bayar);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'cek_pcare' => $cek_pcare, 'responsivevoice' => $responsivevoice, 'admin_mode' => $this->settings->get('settings.admin_mode'), 'waapitoken' => $waapitoken, 'waapiphonenumber' => $waapiphonenumber, 'nama_instansi' => $nama_instansi]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='', $status_bayar='')
    {

        if($this->settings->get('settings.responsivevoice') == 'true') {
          $this->core->addJS(url('assets/jscripts/responsivevoice.js'));
        }
        $this->_addHeaderFiles();

        $this->assign['poliklinik']     = $this->db('poliklinik')->where('status', '1')->where('kd_poli', '<>', $this->settings->get('settings.igd'))->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

        $poliklinik = str_replace(",","','", $this->core->getUserInfo('cap', null, true));
        $igd = $this->settings('settings', 'igd');

        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND reg_periksa.kd_poli != '$igd'
          AND reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'
          AND reg_periksa.kd_dokter = dokter.kd_dokter
          AND reg_periksa.kd_poli = poliklinik.kd_poli
          AND reg_periksa.kd_pj = penjab.kd_pj";

        if ($this->core->getUserInfo('role') != 'admin') {
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

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $row['no_rawat'])->oneArray();
          $row['no_sep'] = isset_or($bridging_sep['no_sep']);
          $this->assign['list'][] = $row;
        }

        $sql_rujukan_internal = "SELECT 
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            reg_periksa.no_rawat,
            p1.nm_poli as poli_asal,
            reg_periksa.kd_poli as kd_poli_asal,
            p2.nm_poli as poli_tujuan,
            mlite_rujukan_internal_poli.kd_poli as kd_poli_tujuan, 
            d1.nm_dokter as dokter_perujuk,
            reg_periksa.kd_dokter as kd_dokter_perujuk, 
            d2.nm_dokter as dokter_tujuan,
            mlite_rujukan_internal_poli.kd_dokter as kd_dokter_tujuan, 
            reg_periksa.tgl_registrasi as tgl_rujukan,
            mlite_rujukan_internal_poli.isi_rujukan as keterangan,
            mlite_rujukan_internal_poli.jawab_rujukan as keterangan_jawab 
          FROM mlite_rujukan_internal_poli
          INNER JOIN reg_periksa ON mlite_rujukan_internal_poli.no_rawat = reg_periksa.no_rawat
          INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          INNER JOIN poliklinik p1 ON reg_periksa.kd_poli = p1.kd_poli
          INNER JOIN poliklinik p2 ON mlite_rujukan_internal_poli.kd_poli = p2.kd_poli
          INNER JOIN dokter d1 ON reg_periksa.kd_dokter = d1.kd_dokter
          INNER JOIN dokter d2 ON mlite_rujukan_internal_poli.kd_dokter = d2.kd_dokter
          INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj
          WHERE reg_periksa.kd_poli != '$igd'
          AND reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'";

        if ($this->core->getUserInfo('role') != 'admin') {
          $sql_rujukan_internal .= " AND reg_periksa.kd_poli IN ('$poliklinik')";
        }
        if($status_periksa == 'belum') {
          $sql_rujukan_internal .= " AND reg_periksa.stts = 'Belum'";
        }
        if($status_periksa == 'selesai') {
          $sql_rujukan_internal .= " AND reg_periksa.stts = 'Sudah'";
        }
        if($status_periksa == 'lunas') {
          $sql_rujukan_internal .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
        }

        $stmt = $this->db()->pdo()->prepare($sql_rujukan_internal);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list_rujukan_internal'] = [];
        foreach ($rows as $row) {
          $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $row['no_rawat'])->oneArray();
          $row['no_sep'] = isset_or($bridging_sep['no_sep']);
          $this->assign['list_rujukan_internal'][] = $row;  
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

      $this->assign['poliklinik'] = $this->db('poliklinik')->where('kd_poli', '<>', $this->settings->get('settings.igd'))->where('status', '1')->toArray();
      $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
      $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
      $date = date('Y-m-d');
      $jam = date('H:i:s');
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
      if ($_POST['tgl_registrasi'] > date('Y-m-d')) {
        $this->db('booking_registrasi')->save([
          'tanggal_booking' => date('Y-m-d'),
          'jam_booking' => date('H:i:s'),
          'no_rkm_medis' => $_POST['no_rkm_medis'],
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'kd_dokter' => $_POST['kd_dokter'],
          'kd_poli' => $_POST['kd_poli'],
          'no_reg' => $this->core->setNoBooking($_POST['kd_dokter'], $_POST['tgl_registrasi'], $_POST['kd_poli']),
          'kd_pj' => $_POST['kd_pj'],
          'limit_reg' => '0',
          'waktu_kunjungan' => $_POST['tgl_registrasi'] . ' ' . $_POST['jam_reg'],
          'status' => 'Belum'
        ]);
      } else if (!$this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {

        $_POST['status_lanjut'] = 'Ralan';
        $_POST['stts'] = 'Belum';
        $_POST['status_bayar'] = 'Belum Bayar';
        $_POST['p_jawab'] = '-';
        $_POST['almt_pj'] = '-';
        $_POST['hubunganpj'] = '-';

        $poliklinik = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();

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

        $query = $this->db('reg_periksa')->save($_POST);
      } else {
        $query = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
          'kd_poli' => $_POST['kd_poli'],
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

    public function anyBooking($page = 1)
    {

      $this->core->addCSS(url('assets/css/jquery-ui.css'));
      $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

      // JS
      $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
      $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');

      $waapitoken =  $this->settings->get('wagateway.token');
      $waapiphonenumber =  $this->settings->get('wagateway.phonenumber');
      $nama_instansi =  $this->settings->get('settings.nama_instansi');

      if (isset($_POST['valid'])) {
          if (isset($_POST['no_rkm_medis']) && !empty($_POST['no_rkm_medis'])) {
              foreach ($_POST['no_rkm_medis'] as $item) {

                  $row = $this->db('booking_registrasi')->where('no_rkm_medis', $item)->where('tanggal_periksa', date('Y-m-d'))->oneArray();

                  $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $item)->count();
                  $_POST['stts_daftar'] = 'Baru';
                  if($cek_stts_daftar > 0) {
                    $_POST['stts_daftar'] = 'Lama';
                  }

                  $biaya_reg = $this->db('poliklinik')->where('kd_poli', $row['kd_poli'])->oneArray();
                  $_POST['biaya_reg'] = $biaya_reg['registrasi'];
                  if($_POST['stts_daftar'] == 'Lama') {
                    $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
                  }

                  $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $item)->where('kd_poli', $row['kd_poli'])->count();
                  $_POST['status_poli'] = 'Baru';
                  if($cek_status_poli > 0) {
                    $_POST['status_poli'] = 'Lama';
                  }

                  // set umur
                  $tanggal = new \DateTime($this->getPasienInfo('tgl_lahir', $item));
                  $today = new \DateTime(date('Y-m-d'));
                  $y = $today->diff($tanggal)->y;
                  $m = $today->diff($tanggal)->m;
                  $d = $today->diff($tanggal)->d;

                  $umur="0";
                  $sttsumur="Th";
                  if($y>0){
                      $umur=$y;
                      $sttsumur="Th";
                  }else if($y==0){
                      if($m>0){
                          $umur=$m;
                          $sttsumur="Bl";
                      }else if($m==0){
                          $umur=$d;
                          $sttsumur="Hr";
                      }
                  }

                  if($row['status'] == 'Belum') {
                    $insert = $this->db('reg_periksa')
                      ->save([
                        'no_reg' => $row['no_reg'],
                        'no_rawat' => $this->setNoRawat(),
                        'tgl_registrasi' => date('Y-m-d'),
                        'jam_reg' => date('H:i:s'),
                        'kd_dokter' => $row['kd_dokter'],
                        'no_rkm_medis' => $item,
                        'kd_poli' => $row['kd_poli'],
                        'p_jawab' => $this->getPasienInfo('namakeluarga', $item),
                        'almt_pj' => $this->getPasienInfo('alamatpj', $item),
                        'hubunganpj' => $this->getPasienInfo('keluarga', $item),
                        'biaya_reg' => $_POST['biaya_reg'],
                        'stts' => 'Belum',
                        'stts_daftar' => $_POST['stts_daftar'],
                        'status_lanjut' => 'Ralan',
                        'kd_pj' => $row['kd_pj'],
                        'umurdaftar' => $umur,
                        'sttsumur' => $sttsumur,
                        'status_bayar' => 'Belum Bayar',
                        'status_poli' => $_POST['status_poli']
                      ]);

                      if ($insert) {
                          $this->db('booking_registrasi')->where('no_rkm_medis', $item)->where('tanggal_periksa', date('Y-m-d'))->update('status', 'Terdaftar');
                          $this->notify('success', 'Validasi sukses');
                      } else {
                          $this->notify('failure', 'Validasi gagal');
                      }
                  }
              }

              redirect(url([ADMIN, 'rawat_jalan', 'booking']));
          }
      }

      $this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT booking_registrasi.no_rkm_medis FROM booking_registrasi, pasien WHERE booking_registrasi.no_rkm_medis = pasien.no_rkm_medis AND (booking_registrasi.no_rkm_medis LIKE ? OR pasien.nm_pasien LIKE ?) AND booking_registrasi.tanggal_periksa BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'rawat_jalan', 'booking', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT booking_registrasi.*, pasien.nm_pasien, pasien.alamat, pasien.no_tlp, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab, pasien.no_peserta FROM booking_registrasi, pasien, dokter, poliklinik, penjab WHERE booking_registrasi.no_rkm_medis = pasien.no_rkm_medis AND booking_registrasi.kd_dokter = dokter.kd_dokter AND booking_registrasi.kd_poli = poliklinik.kd_poli AND booking_registrasi.kd_pj = penjab.kd_pj AND (booking_registrasi.no_rkm_medis LIKE ? OR pasien.nm_pasien LIKE ?) AND booking_registrasi.tanggal_periksa BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url([ADMIN, 'rawat_jalan', 'booking', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('booking.html', ['booking' => $this->assign, 'waapitoken' => $waapitoken, 'waapiphonenumber' => $waapiphonenumber, 'nama_instansi' => $nama_instansi]);

    }

    public function getBookingPeriksa()
    {
        $date = date('Y-m-d');
        $text = 'Booking Periksa';

        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        return $this->draw('booking.periksa.html',
          [
            'text' => $text,
            'waapitoken' => $this->settings->get('wagateway.token'),
            'waapiphonenumber' => $this->settings->get('wagateway.phonenumber'),
            'nama_instansi' => $this->settings->get('settings.nama_instansi'),
            'booking' => $this->db('booking_periksa')
              ->select([
                'no_booking' => 'booking_periksa.no_booking',
                'tanggal' => 'booking_periksa.tanggal',
                'nama' => 'booking_periksa.nama',
                'no_telp' => 'booking_periksa.no_telp',
                'alamat' => 'booking_periksa.alamat',
                'email' => 'booking_periksa.email',
                'nm_poli' => 'poliklinik.nm_poli',
                'status' => 'booking_periksa.status',
                'tanggal_booking' => 'booking_periksa.tanggal_booking'
              ])
              ->join('poliklinik', 'poliklinik.kd_poli = booking_periksa.kd_poli')
              //->where('tambahan_pesan', 'jkn_mobile')
              ->toArray()
          ]
        );
    }

    public function postSaveBookingPeriksa()
    {
      $this->db('booking_periksa')->where('no_booking', $_POST['no_booking'])->save(['status' => $_POST['status']]);
      $this->db('booking_periksa_balasan')
      ->save([
        'no_booking' => $_POST['no_booking'],
        'balasan' => $_POST['message']
      ]);
      exit();
    }

    public function anyKontrol()
    {
      $rows = $this->db('booking_registrasi')
        ->join('poliklinik', 'poliklinik.kd_poli=booking_registrasi.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=booking_registrasi.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=booking_registrasi.kd_pj')
        ->where('no_rkm_medis', $_POST['no_rkm_medis'])
        ->toArray();
      $i = 1;
      $result = [];
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $result[] = $row;
      }
      echo $this->draw('kontrol.html', ['booking_registrasi' => $result]);
      exit();
    }

    public function postSaveKontrol()
    {

      $query = $this->db('skdp_bpjs')->save([
        'tahun' => date('Y'),
        'no_rkm_medis' => $_POST['no_rkm_medis'],
        'diagnosa' => $_POST['diagnosa'],
        'terapi' => $_POST['terapi'],
        'alasan1' => $_POST['alasan1'],
        'alasan2' => '',
        'rtl1' => $_POST['rtl1'],
        'rtl2' => '',
        'tanggal_datang' => $_POST['tanggal_datang'],
        'tanggal_rujukan' => $_POST['tanggal_rujukan'],
        'no_antrian' => $this->core->setNoSKDP(),
        'kd_dokter' => $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']),
        'status' => 'Menunggu'
      ]);

      if ($query) {
        $this->db('booking_registrasi')
          ->save([
            'tanggal_booking' => date('Y-m-d'),
            'jam_booking' => date('H:i:s'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'tanggal_periksa' => $_POST['tanggal_datang'],
            'kd_dokter' => $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']),
            'kd_poli' => $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']),
            'no_reg' => $this->core->setNoBooking($this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']), $_POST['tanggal_datang'], $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat'])),
            'kd_pj' => $this->core->getRegPeriksaInfo('kd_pj', $_POST['no_rawat']),
            'limit_reg' => 0,
            'waktu_kunjungan' => $_POST['tanggal_datang'].' '.date('H:i:s'),
            'status' => 'Belum'
          ]);
      }

      exit();
    }

    public function postSaveKontrolBPJS()
    {

      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consid . $this->secretkey . $tStamp;
      $_POST['sep_user']  = $this->core->getUserInfo('fullname', null, true);

      $maping_dokter_dpjpvclaim = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']))->oneArray();
      $maping_poli_bpjs = $this->db('maping_poli_bpjs')->where('kd_poli_rs', $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']))->oneArray();
      $get_sep = $this->db('bridging_sep')->where('no_rawat', $_POST['no_rawat'])->oneArray();
      $_POST['no_sep'] = $get_sep['no_sep'];
      $get_sep_internal = $this->db('bridging_sep_internal')->where('no_rawat', $_POST['no_rawat'])->oneArray();

      if(empty($get_sep['no_sep'])) {
        $_POST['no_sep'] = $get_sep_internal['no_sep'];
      }

      $data = [
        'request' => [
          'noSEP' => $_POST['no_sep'],
          'kodeDokter' => $maping_dokter_dpjpvclaim['kd_dokter_bpjs'],
          'poliKontrol' => $maping_poli_bpjs['kd_poli_bpjs'],
          'tglRencanaKontrol' => $_POST['tanggal_datang'],
          'user' => $_POST['sep_user']
        ]
      ];
      $statusUrl = 'insert';
      $method = 'post';

      $data = json_encode($data);

      $url = $this->api_url . 'RencanaKontrol/' . $statusUrl;
      $output = BpjsService::$method($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $data = json_decode($output, true);
      //echo $data['metaData']['message'];
      if ($data == NULL) {
        echo 'Koneksi ke server BPJS terputus. Silahkan ulangi beberapa saat lagi!';
      } else if ($data['metaData']['code'] == 200) {
        $stringDecrypt = stringDecrypt($key, $data['response']);
        $decompress = '""';
        $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
        $spri = json_decode($decompress, true);
        //echo $spri['noSuratKontrol'];

        $bridging_surat_pri_bpjs = $this->db('bridging_surat_kontrol_bpjs')->save([
          'no_sep' => $_POST['no_sep'],
          'tgl_surat' => $_POST['tanggal_rujukan'],
          'no_surat' => $spri['noSuratKontrol'],
          'tgl_rencana' => $_POST['tanggal_datang'],
          'kd_dokter_bpjs' => $maping_dokter_dpjpvclaim['kd_dokter_bpjs'],
          'nm_dokter_bpjs' => $maping_dokter_dpjpvclaim['nm_dokter_bpjs'],
          'kd_poli_bpjs' => $maping_poli_bpjs['kd_poli_bpjs'],
          'nm_poli_bpjs' => $maping_poli_bpjs['nm_poli_bpjs']
        ]);

      }

      exit();
    }

    public function postHapusKontrol()
    {
      $this->db('booking_registrasi')->where('kd_dokter', $_POST['kd_dokter'])->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tanggal_periksa', $_POST['tanggal_periksa'])->where('status', 'Belum')->delete();
      $this->db('skdp_bpjs')->where('kd_dokter', $_POST['kd_dokter'])->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tanggal_datang', $_POST['tanggal_periksa'])->where('status', 'Menunggu')->delete();
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

    public function getJadwal()
    {
        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->_addHeaderFiles();
        $rows = $this->db('jadwal')->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')->toArray();
        $this->assign['jadwal'] = [];
        foreach ($rows as $row) {
            $row['delURL'] = url([ADMIN, 'rawat_jalan', 'jadwaldel', $row['kd_dokter'], $row['hari_kerja']]);
            $row['editURL'] = url([ADMIN, 'rawat_jalan', 'jadwaledit', $row['kd_dokter'], $row['hari_kerja']]);
            $this->assign['jadwal'][] = $row;
        }

        return $this->draw('jadwal.html', ['pendaftaran' => $this->assign]);
    }

    public function getJadwalDel($kd_dokter, $hari_kerja)
    {
        if ($pendaftaran = $this->db('jadwal')->where('kd_dokter', $kd_dokter)->where('hari_kerja', $hari_kerja)->oneArray()) {
            if ($this->db('jadwal')->where('kd_dokter', $kd_dokter)->where('hari_kerja', $hari_kerja)->delete()) {
                $this->notify('success', 'Hapus sukses');
            } else {
                $this->notify('failure', 'Hapus gagal');
            }
        }
        redirect(url([ADMIN, 'rawat_jalan', 'jadwal']));
    }

    public function getJadwalAdd()
    {
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_dokter' => '',
              'hari_kerja' => '',
              'jam_mulai' => '',
              'jam_selesai' => '',
              'kd_poli' => '',
              'kuota' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Jadwal Dokter';
        $this->assign['dokter'] = $this->db('dokter')->toArray();
        $this->assign['poliklinik'] = $this->db('poliklinik')->toArray();
        $this->assign['hari_kerja'] = $this->core->getEnum('jadwal', 'hari_kerja');
        $this->assign['postUrl'] = url([ADMIN, 'rawat_jalan', 'jadwalsave', $this->assign['form']['kd_dokter'], $this->assign['form']['hari_kerja']]);
        return $this->draw('jadwal.form.html', ['pendaftaran' => $this->assign]);
    }

    public function getJadwalEdit($id, $hari_kerja)
    {
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->_addHeaderFiles();
        $row = $this->db('jadwal')->where('kd_dokter', $id)->where('hari_kerja', $hari_kerja)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Jadwal';
            $this->assign['hari_kerja'] = $this->core->getEnum('jadwal', 'hari_kerja');
            $this->assign['dokter'] = $this->db('dokter')->toArray();
            $this->assign['poliklinik'] = $this->db('poliklinik')->toArray();

            $this->assign['postUrl'] = url([ADMIN, 'rawat_jalan', 'jadwalsave', $this->assign['form']['kd_dokter'], $this->assign['form']['hari_kerja']]);
            return $this->draw('jadwal.form.html', ['pendaftaran' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'rawat_jalan', 'jadwal']));
        }
    }

    public function postJadwalSave($id = null, $hari_kerja = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'rawat_jalan', 'jadwal']);
        } else {
            $location = url([ADMIN, 'rawat_jalan', 'jadwaledit', $_POST['kd_dokter'], $_POST['hari_kerja']]);
        }

        if (checkEmptyFields(['kd_dokter', 'hari_kerja', 'kd_poli'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $query = $this->db('jadwal')->save($_POST);
            } else {        // edit
                $query = $this->db('jadwal')->where('kd_dokter', $id)->where('hari_kerja', $hari_kerja)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function postStatusRawat()
    {
      $datetime = date('Y-m-d H:i:s');
      $cek = $this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->oneArray();
      if($_POST['stts'] == 'Berkas Dikirim') {
          if(!$this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {
            $this->db('mutasi_berkas')->save([
              'no_rawat' => $_POST['no_rawat'],
              'status' => 'Sudah Dikirim',
              'dikirim' => $datetime,
              'diterima' => '0000-00-00 00:00:00',
              'kembali' => '0000-00-00 00:00:00',
              'tidakada' => '0000-00-00 00:00:00',
              'ranap' => '0000-00-00 00:00:00'
            ]);
          }
      } else if ($_POST['stts'] == 'Berkas Diterima') {
          if(!$this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {
            $this->db('mutasi_berkas')->save([
              'no_rawat' => $_POST['no_rawat'],
              'status' => 'Sudah Diterima',
              'dikirim' => $datetime,
              'diterima' => $datetime,
              'kembali' => '0000-00-00 00:00:00',
              'tidakada' => '0000-00-00 00:00:00',
              'ranap' => '0000-00-00 00:00:00'
            ]);
          } else {
            $this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->save([
              'status' => 'Sudah Diterima',
              'diterima' => $datetime
            ]);
          }
          $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save($_POST);
      } else {
          $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save($_POST);
      }
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
          $this->db('rawat_jl_dr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'kd_dokter' => $_POST['kode_provider'],
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam_rawat' => $_POST['jam_rawat'],
            'material' => $jns_perawatan['material'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya_rawat' => $jns_perawatan['total_byrdr'],
            'stts_bayar' => 'Belum'
          ]);
        }
        if($_POST['provider'] == 'rawat_jl_pr') {
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
        if($_POST['provider'] == 'rawat_jl_drpr') {
          $this->db('rawat_jl_drpr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'kd_dokter' => $_POST['kode_provider'],
            'nip' => $_POST['kode_provider2'],
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam_rawat' => $_POST['jam_rawat'],
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

      echo $this->draw('rincian.html', ['rawat_jl_dr' => $rawat_jl_dr, 'rawat_jl_pr' => $rawat_jl_pr, 'rawat_jl_drpr' => $rawat_jl_drpr, 'jumlah_total' => $jumlah_total, 'no_rawat' => $_POST['no_rawat']]);
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

      $result_ranap = [];

      $check_table = $this->db()->pdo()->query("SHOW TABLES LIKE 'pemeriksaan_ranap'");
      $check_table->execute();
      $check_table = $check_table->fetch();
      if($check_table) {
        $rows_ranap = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $_POST['no_rawat'])
          ->toArray();
        foreach ($rows_ranap as $row) {
          $row['nomor'] = $i++;
          $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
          $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
          $result_ranap[] = $row;
        }
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
        $filePath = isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : '';
        if (empty($filePath) || !file_exists($filePath)) {
          echo 'Gagal menambahkan gambar: berkas tidak ditemukan';
          exit();
        }

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
        $json = is_string($response) ? json_decode($response, true) : null;
        if(is_array($json) && isset($json['status']) && $json['status'] == 'Success' && isset($json['msg'])) {
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
      $max_id = $this->db('reg_periksa')->select(['no_rawat' => 'ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0)'])->where('tgl_registrasi', $tgl_registrasi)->oneArray();
      if(empty($max_id['no_rawat'])) {
        $max_id['no_rawat'] = '000000';
      }
      $_next_no_rawat = sprintf('%06s', ($max_id['no_rawat'] + 1));
      $next_no_rawat = date('Y/m/d', strtotime($tgl_registrasi)).'/'.$_next_no_rawat;
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

      $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $_POST['kd_poli'])->where('tgl_registrasi', $tgl_registrasi)->desc('no_reg')->limit(1)->oneArray();
      if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
        $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $_POST['kd_poli'])->where('kd_dokter', $_POST['kd_dokter'])->where('tgl_registrasi', $tgl_registrasi)->desc('no_reg')->limit(1)->oneArray();
      }
      if(empty($max_id['no_reg'])) {
        $max_id['no_reg'] = '000';
      }
      $_next_no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));

      $date = date('Y-m-d');
      $tentukan_hari=date('D',strtotime(date('Y-m-d')));
      $day = array(
        'Sun' => 'AKHAD',
        'Mon' => 'SENIN',
        'Tue' => 'SELASA',
        'Wed' => 'RABU',
        'Thu' => 'KAMIS',
        'Fri' => 'JUMAT',
        'Sat' => 'SABTU'
      );
      $hari=$day[$tentukan_hari];

      $jadwal_dokter = $this->db('jadwal')->where('kd_poli', $_POST['kd_poli'])->where('kd_dokter', $_POST['kd_dokter'])->where('hari_kerja', $hari)->oneArray();
      $jadwal_poli = $this->db('jadwal')->where('kd_poli', $_POST['kd_poli'])->where('hari_kerja', $hari)->toArray();
      $kuota_poli = 0;
      foreach ($jadwal_poli as $row) {
        $kuota_poli += $row['kuota'];
      }
      if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true' && $this->settings->get('settings.ceklimit') == 'true' && $_next_no_reg > $jadwal_dokter['kuota']) {
        $_next_no_reg = '888888';
      }
      if($this->settings->get('settings.dokter_ralan_per_dokter') == 'false' && $this->settings->get('settings.ceklimit') == 'true' && $_next_no_reg > $kuota_poli) {
        $_next_no_reg = '999999';
      }
      echo $_next_no_reg;
      exit();
    }

    public function getPasienInfo($field, $no_rkm_medis)
    {
        $row = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        return $row[$field];
    }

    public function getRegPeriksaInfo($field, $no_rawat)
    {
        $row = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    public function setNoRawat()
    {
        $date = date('Y-m-d');
        $last_no_rawat = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0) FROM reg_periksa WHERE tgl_registrasi = '$date'");
        $last_no_rawat->execute();
        $last_no_rawat = $last_no_rawat->fetch();
        if(empty($last_no_rawat[0])) {
          $last_no_rawat[0] = '000000';
        }
        $next_no_rawat = sprintf('%06s', ($last_no_rawat[0] + 1));
        $next_no_rawat = date('Y/m/d').'/'.$next_no_rawat;

        return $next_no_rawat;
    }

    public function postCetak()
    {
      $this->db()->pdo()->exec("DELETE FROM `mlite_temporary`");
      $cari = $_POST['cari'];
      $tgl_awal = $_POST['tgl_awal'];
      $tgl_akhir = $_POST['tgl_akhir'];
      $igd = $this->settings->get('settings.igd');
      $this->db()->pdo()->exec("INSERT INTO `mlite_temporary` (
        `temp1`,`temp2`,`temp3`,`temp4`,`temp5`,`temp6`,`temp7`,`temp8`,`temp9`,`temp10`,`temp11`,`temp12`,`temp13`,`temp14`,`temp15`,`temp16`,`temp17`,`temp18`,`temp19`
      )
      SELECT *
      FROM `reg_periksa`
      WHERE `kd_poli` <> '$igd'
      AND `tgl_registrasi` BETWEEN '$tgl_awal' AND '$tgl_akhir'
      ");

      $cetak = $this->db('mlite_temporary')->toArray();
      echo $this->draw('cetak.rawat_jalan.html', ['cetak' => $cetak]);

      exit();
    }

    public function getCetakPdf()
    {
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'orientation' => 'L'
      ]);
  
      $mpdf->SetHTMLHeader($this->core->setPrintHeader());
      $mpdf->SetHTMLFooter($this->core->setPrintFooter());
            
      $url = url(ADMIN.'/tmp/cetak.rawat_jalan.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
      $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
  
      // Output a PDF file directly to the browser
      $mpdf->Output();
      exit();      
    }

    public function getExcel()
    {
      $file = "data.pasien.rawat.jalan.xls";
      $html = file_get_contents(url(ADMIN.'/tmp/cetak.rawat_jalan.html'));
      header("Content-type: application/vnd-ms-excel");
      header("Content-Disposition: attachment; filename=$file");
      echo "<!DOCTYPE html><html><head></head><body>";
      echo $html;
      echo "</body></html>";
      exit();
    }
    
    public function postObatKronis()
    {
      if (isset($_POST['no_rawat']) && $_POST['no_rawat'] !='') {
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->oneArray();
        $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $_POST['no_rawat'])->oneArray();
        if(!$bridging_sep) {
          $bridging_sep['no_sep'] = '';
        }
        $this->db('mlite_veronisa')->save([
          'id' => NULL,
          'tanggal' => date('Y-m-d'),
          'no_rkm_medis' => $reg_periksa['no_rkm_medis'],
          'no_rawat' => $_POST['no_rawat'],
          'tgl_registrasi' => $reg_periksa['tgl_registrasi'],
          'nosep' => $bridging_sep['no_sep'],
          'status' => 'Belum',
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }
      exit();
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

    public function getSepDetail($no_sep)
    {
      $sep = $this->db('bridging_sep')->where('no_sep', $no_sep)->oneArray();
      $this->tpl->set('sep', $this->tpl->noParse_array(htmlspecialchars_array($sep)));

      $potensi_prb = $this->db('bpjs_prb')->where('no_sep', $no_sep)->oneArray();
      $data_sep['potensi_prb'] = $potensi_prb['prb'];
      echo $this->draw('sep.detail.html', ['data_sep' => $data_sep]);
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
        echo $this->tpl->draw(MODULES.'/rawat_jalan/view/admin/surat.rujukan.html', true);
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
        echo $this->tpl->draw(MODULES.'/rawat_jalan/view/admin/surat.sehat.html', true);
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
        echo $this->tpl->draw(MODULES.'/rawat_jalan/view/admin/surat.sakit.html', true);
        exit();
    }

    public function postSimpanSuratSakit()
    {
      $query = $this->db('mlite_surat_sakit')->save([
        'id' => NULL, 
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
        'id' => NULL, 
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
        'id' => NULL, 
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

    public function getAssessment($no_rawat)
    {
        $no_rawat_reverted = revertNoRawat($no_rawat);
        
        // Cek apakah data assessment sudah ada
        $existing_assessment = $this->db('penilaian_awal_keperawatan_ralan')
            ->where('no_rawat', $no_rawat_reverted)
            ->oneArray();
        
        $penilaian_awal_keperawatan_ralan = null;
        
        if($existing_assessment) {
            // Jika data assessment sudah ada, ambil dengan join petugas
            $penilaian_awal_keperawatan_ralan = $this->db('penilaian_awal_keperawatan_ralan')
                ->where('no_rawat', $no_rawat_reverted)
                ->join('petugas', 'petugas.nip=penilaian_awal_keperawatan_ralan.nip')
                ->oneArray();
        } else {
            // Jika belum ada, ambil data dari pemeriksaan_ralan sebagai fallback
            $pemeriksaan_data = $this->db('pemeriksaan_ralan')
                ->where('no_rawat', $no_rawat_reverted)
                ->desc('tgl_perawatan')
                ->desc('jam_rawat')
                ->oneArray();
            
            if($pemeriksaan_data) {
                // Map data dari pemeriksaan_ralan ke format penilaian_awal_keperawatan_ralan
                $penilaian_awal_keperawatan_ralan = [
                    'no_rawat' => $pemeriksaan_data['no_rawat'],
                    'tanggal' => $pemeriksaan_data['tgl_perawatan'] . 'T' . substr($pemeriksaan_data['jam_rawat'], 0, 5),
                    'informasi' => 'Autoanamnesis', // default
                    'td' => $pemeriksaan_data['tensi'] ?? '',
                    'nadi' => $pemeriksaan_data['nadi'] ?? '',
                    'rr' => $pemeriksaan_data['respirasi'] ?? '',
                    'suhu' => $pemeriksaan_data['suhu_tubuh'] ?? '',
                    'gcs' => $pemeriksaan_data['gcs'] ?? '',
                    'bb' => $pemeriksaan_data['berat'] ?? '',
                    'tb' => $pemeriksaan_data['tinggi'] ?? '',
                    'bmi' => '', // akan dihitung otomatis jika bb dan tb ada
                    'keluhan_utama' => $pemeriksaan_data['keluhan'] ?? '',
                    'rpd' => '',
                    'rpk' => '',
                    'rpo' => '',
                    'alergi' => $pemeriksaan_data['alergi'] ?? '',
                    'alat_bantu' => 'Tidak',
                    'ket_bantu' => '',
                    'prothesa' => 'Tidak',
                    'ket_pro' => '',
                    'adl' => 'Mandiri',
                    'status_psiko' => 'Tenang',
                    'ket_psiko' => '',
                    'hub_keluarga' => 'Baik',
                    'tinggal_dengan' => 'Keluarga',
                    'ket_tinggal' => '',
                    'ekonomi' => 'Cukup',
                    'budaya' => 'Tidak Ada',
                    'ket_budaya' => '',
                    'edukasi' => 'Pasien',
                    'ket_edukasi' => '',
                    'berjalan_a' => 'Tidak',
                    'berjalan_b' => 'Tidak',
                    'berjalan_c' => 'Tidak',
                    'hasil' => 'Rendah',
                    'lapor' => 'Tidak',
                    'ket_lapor' => '',
                    'sg1' => 'Tidak',
                    'nilai1' => '0',
                    'sg2' => 'Tidak',
                    'nilai2' => '0',
                    'total_hasil' => 0,
                    'nyeri' => 'Tidak',
                    'provokes' => 'Aktivitas',
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
                    'rencana' => '',
                    'nip' => $this->core->getUserInfo('username') ?? ''
                ];
                
                // Hitung BMI jika bb dan tb tersedia
                if(!empty($penilaian_awal_keperawatan_ralan['bb']) && !empty($penilaian_awal_keperawatan_ralan['tb'])) {
                    $bb = floatval($penilaian_awal_keperawatan_ralan['bb']);
                    $tb = floatval($penilaian_awal_keperawatan_ralan['tb']) / 100; // convert cm to m
                    if($tb > 0) {
                        $bmi = $bb / ($tb * $tb);
                        $penilaian_awal_keperawatan_ralan['bmi'] = number_format($bmi, 2);
                    }
                }
            }
        }
        
        $data_assessment['penilaian_awal_keperawatan_ralan'] = $existing_assessment;
        
        echo $this->draw('assesment.html', [
            'reg_periksa' => $this->db('reg_periksa')->where('no_rawat', $no_rawat_reverted)->oneArray(),
            'penilaian_awal_keperawatan_ralan' => $penilaian_awal_keperawatan_ralan,
            'pasien' => $this->db('pasien')->where('no_rawat', $no_rawat_reverted)->join('reg_periksa','pasien.no_rkm_medis=reg_periksa.no_rkm_medis')->oneArray(),
            'petugas' => $existing_assessment ? $this->db('petugas')->where('no_rawat', $no_rawat_reverted)->join('penilaian_awal_keperawatan_ralan','petugas.nip=penilaian_awal_keperawatan_ralan.nip')->oneArray() : [],
            'data_assessment' => $data_assessment
        ]);
        exit();
    }

    public function postAssessmentsave()
    {
        // Trim semua input POST untuk menghilangkan whitespace
        foreach($_POST as $key => $value) {
            if(is_string($value)) {
                $_POST[$key] = trim($value);
            }
        }
        
        // Log untuk debugging
        error_log('POST data received: ' . print_r($_POST, true));
        
        // Debug khusus untuk field GCS
        error_log('GCS field debug - isset: ' . (isset($_POST['gcs']) ? 'true' : 'false') . ', value: "' . (isset($_POST['gcs']) ? $_POST['gcs'] : 'NOT_SET') . '"');
        
        // Validasi input required
        $required_fields = ['no_rawat', 'tanggal', 'informasi', 'rr', 'gcs', 'bmi', 'rpk', 'rpo', 'ket_pro', 'ket_psiko', 'ket_tinggal', 'ket_budaya', 'ket_edukasi', 'ket_lapor', 'ket_provokes', 'ket_quality', 'lokasi', 'durasi', 'ket_nyeri', 'ket_dokter', 'rencana', 'nip'];
        
        foreach($required_fields as $field) {
            $value = isset($_POST[$field]) ? $_POST[$field] : '';
            $trimmed_value = is_string($value) ? trim($value) : $value;
            
            // Log detail untuk setiap field
            error_log('Field validation - ' . $field . ': isset=' . (isset($_POST[$field]) ? 'true' : 'false') . ', original="' . $value . '", trimmed="' . $trimmed_value . '", empty=' . (empty($trimmed_value) ? 'true' : 'false'));
            
            if(empty($trimmed_value) || $trimmed_value === '') {
                error_log('Validation failed for field: ' . $field . ', value: "' . $value . '"');
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Field ' . $field . ' harus diisi',
                    'field' => $field,
                    'value' => $value,
                    'trimmed_value' => $trimmed_value,
                    'debug_info' => [
                        'isset' => isset($_POST[$field]),
                        'original_value' => $value,
                        'trimmed_value' => $trimmed_value,
                        'is_empty' => empty($trimmed_value)
                    ]
                ]);
                exit();
            }
        }

        // Cek apakah data sudah ada
        $existing = $this->db('penilaian_awal_keperawatan_ralan')
            ->where('no_rawat', $_POST['no_rawat'])
            ->oneArray();

        $data = [
            'no_rawat' => $_POST['no_rawat'],
            'tanggal' => $_POST['tanggal'],
            'informasi' => $_POST['informasi'],
            'td' => $_POST['td'] ?? '',
            'nadi' => $_POST['nadi'] ?? '',
            'rr' => $_POST['rr'],
            'suhu' => $_POST['suhu'] ?? '',
            'gcs' => $_POST['gcs'],
            'bb' => $_POST['bb'] ?? '',
            'tb' => $_POST['tb'] ?? '',
            'bmi' => $_POST['bmi'],
            'keluhan_utama' => $_POST['keluhan_utama'] ?? '',
            'rpd' => $_POST['rpd'] ?? '',
            'rpk' => $_POST['rpk'],
            'rpo' => $_POST['rpo'],
            'alergi' => $_POST['alergi'] ?? '',
            'alat_bantu' => $_POST['alat_bantu'],
            'ket_bantu' => $_POST['ket_bantu'] ?? '',
            'prothesa' => $_POST['prothesa'],
            'ket_pro' => $_POST['ket_pro'],
            'adl' => $_POST['adl'],
            'status_psiko' => $_POST['status_psiko'],
            'ket_psiko' => $_POST['ket_psiko'],
            'hub_keluarga' => $_POST['hub_keluarga'],
            'tinggal_dengan' => $_POST['tinggal_dengan'],
            'ket_tinggal' => $_POST['ket_tinggal'],
            'ekonomi' => $_POST['ekonomi'],
            'budaya' => $_POST['budaya'],
            'ket_budaya' => $_POST['ket_budaya'],
            'edukasi' => $_POST['edukasi'],
            'ket_edukasi' => $_POST['ket_edukasi'],
            'berjalan_a' => $_POST['berjalan_a'],
            'berjalan_b' => $_POST['berjalan_b'],
            'berjalan_c' => $_POST['berjalan_c'],
            'hasil' => $_POST['hasil'],
            'lapor' => $_POST['lapor'],
            'ket_lapor' => $_POST['ket_lapor'],
            'sg1' => $_POST['sg1'],
            'nilai1' => $_POST['nilai1'],
            'sg2' => $_POST['sg2'],
            'nilai2' => $_POST['nilai2'],
            'total_hasil' => (int)$_POST['total_hasil'],
            'nyeri' => $_POST['nyeri'],
            'provokes' => $_POST['provokes'],
            'ket_provokes' => $_POST['ket_provokes'],
            'quality' => $_POST['quality'],
            'ket_quality' => $_POST['ket_quality'],
            'lokasi' => $_POST['lokasi'],
            'menyebar' => $_POST['menyebar'],
            'skala_nyeri' => $_POST['skala_nyeri'],
            'durasi' => $_POST['durasi'],
            'nyeri_hilang' => $_POST['nyeri_hilang'],
            'ket_nyeri' => $_POST['ket_nyeri'],
            'pada_dokter' => $_POST['pada_dokter'],
            'ket_dokter' => $_POST['ket_dokter'],
            'rencana' => $_POST['rencana'],
            'nip' => $_POST['nip']
        ];

        try {
            if($existing) {
                // Update data yang sudah ada
                $query = $this->db('penilaian_awal_keperawatan_ralan')
                    ->where('no_rawat', $_POST['no_rawat'])
                    ->save($data);
            } else {
                // Insert data baru
                $query = $this->db('penilaian_awal_keperawatan_ralan')->save($data);
            }

            if($query) {
                echo json_encode(['status' => 'success', 'message' => 'Data assessment berhasil disimpan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data assessment']);
            }
        } catch(\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function getAssessmenttampil($no_rawat)
    {
        $no_rawat = revertNorawat($no_rawat);
        $penilaian_awal_keperawatan_ralan = $this->db('penilaian_awal_keperawatan_ralan')
            ->join('petugas', 'petugas.nip=penilaian_awal_keperawatan_ralan.nip')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        if($penilaian_awal_keperawatan_ralan) {
            $penilaian_awal_keperawatan_ralan['nm_petugas'] = $penilaian_awal_keperawatan_ralan['nama'];
        }
        
        echo $this->draw('assesment.tampil.html', ['penilaian_awal_keperawatan_ralan' => $penilaian_awal_keperawatan_ralan]);
        exit();
    }

    public function postAssessmentdelete()
    {
        try {
            $query = $this->db('penilaian_awal_keperawatan_ralan')
                ->where('no_rawat', $_POST['no_rawat'])
                ->delete();
            
            if($query) {
                echo json_encode(['status' => 'success', 'message' => 'Data assessment berhasil dihapus']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data assessment']);
            }
        } catch(\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function postRujukanInternal()
    {
        try {
            $query = $this->db('mlite_rujukan_internal_poli')
                ->save([
                    'no_rawat' => $_POST['no_rawat'],
                    'kd_poli' => $_POST['kd_poli'],
                    'kd_dokter' => $_POST['kd_dokter'], 
                    'isi_rujukan' => $_POST['isi_rujukan']
                ]);
            if($query) {
                echo json_encode(['status' => 'success', 'message' => 'Data rujukan internal berhasil disimpan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data rujukan internal']);
            }
        } catch(\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function postHapusrujukaninternal()
    {
        try {
            // Get POST data
            $no_rawat = isset($_POST['no_rawat']) ? $_POST['no_rawat'] : '';

            if (empty($no_rawat)) {
                echo json_encode(['status' => 'error', 'message' => 'No rawat tidak boleh kosong']);
                exit();
            }

            // Delete rujukan internal
            $result = $this->db('mlite_rujukan_internal_poli')->where('no_rawat', $no_rawat)->delete();

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Rujukan internal berhasil dihapus']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus rujukan internal atau data tidak ditemukan']);
            }
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function postEditrujukaninternal()
    {
        try {
            // Get POST data
            $no_rawat = isset($_POST['no_rawat']) ? $_POST['no_rawat'] : '';
            $jawab_rujukan = isset($_POST['jawab_rujukan']) ? $_POST['jawab_rujukan'] : '';

            if (empty($jawab_rujukan)) {
                echo json_encode(['status' => 'error', 'message' => 'Jawaban rujukan tidak boleh kosong']);
                exit();
            }

            // Update rujukan internal
            $data = [
                'jawab_rujukan' => $jawab_rujukan
            ];

            $result = $this->db('mlite_rujukan_internal_poli')
                ->where('no_rawat', $no_rawat)
                ->save($data);

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Rujukan internal berhasil diupdate']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate rujukan internal']);
            }
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $cek_pegawai = $this->db('pegawai')->where('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']))->oneArray();
        $cek_role = '';
        if($cek_pegawai) {
          $cek_role = $this->core->getPegawaiInfo('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']));
        }
        $poliklinik = $this->db('poliklinik')->where('status', '1')->toArray();
        $dokter = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['poliklinik'] = $poliklinik;
        $this->assign['dokter'] = $dokter;
        $this->assign['websocket'] = $this->settings->get('settings.websocket');
        $this->assign['websocket_proxy'] = $this->settings->get('settings.websocket_proxy');
        echo $this->draw(MODULES.'/rawat_jalan/js/admin/rawat_jalan.js', ['cek_role' => $cek_role, 'mlite' => $this->assign]);
        exit();
    }

    public function anyDataTb()
    {
        try {
            // Add debugging
            error_log('anyDataTb called with POST: ' . print_r($_POST, true));
            
            // Validate POST data
            if(empty($_POST['no_rawat'])) {
                throw new Exception('No rawat tidak ditemukan');
            }
            
            $no_rawat = $_POST['no_rawat'];
            $no_rkm_medis = $_POST['no_rkm_medis'] ?? '';
            $nm_pasien = $_POST['nm_pasien'] ?? '';
            
            // Get patient data from reg_periksa and pasien tables
            $patient_data = $this->db('reg_periksa')
                ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
                ->where('reg_periksa.no_rawat', $no_rawat)
                ->oneArray();
            
            $tgl_lahir = '';
            $jk = '';
            $alamat = '';
            $no_tlp = '';
            $umur = '';
            
            if($patient_data) {
                $tgl_lahir = $patient_data['tgl_lahir'] ?? '';
                $jk = $patient_data['jk'] ?? '';
                $alamat = $patient_data['alamat'] ?? '';
                $no_tlp = $patient_data['no_tlp'] ?? '';
                
                // Calculate age
                if(!empty($patient_data['tgl_lahir'])) {
                    $birth_date = new \DateTime($patient_data['tgl_lahir']);
                    $current_date = new \DateTime();
                    $age = $current_date->diff($birth_date)->y;
                    $umur = $age;
                }
            }
            
            // Check if data_tb table exists
            $tableExists = false;
            try {
                $tableExists = $this->db()->pdo()->query("SHOW TABLES LIKE 'data_tb'")->rowCount() > 0;
            } catch(Exception $e) {
                error_log('Error checking table existence: ' . $e->getMessage());
            }
            
            if(!$tableExists) {
                error_log('Table data_tb does not exist');
                // Create default data without database query
                $data_tb = $this->getDefaultDataTb($no_rawat);
            } else {
                // Try to get existing data
                try {
                    $data_tb = $this->db('data_tb')->where('no_rawat', $no_rawat)->oneArray();
                    if(!$data_tb) {
                        $data_tb = $this->getDefaultDataTb($no_rawat);
                    }
                } catch(Exception $e) {
                    error_log('Error querying data_tb: ' . $e->getMessage());
                    $data_tb = $this->getDefaultDataTb($no_rawat);
                }
            }
            
            // Add patient data to data_tb array for template access
            $data_tb['tgl_lahir'] = $tgl_lahir;
            $data_tb['jk'] = $jk;
            $data_tb['alamat'] = $alamat;
            $data_tb['no_tlp'] = $no_tlp;
            $data_tb['umur'] = $umur;
            
            $html = $this->draw('form.data_tb.html', [
                'data_tb' => $data_tb,
                'no_rawat' => $no_rawat,
                'no_rkm_medis' => $no_rkm_medis,
                'nm_pasien' => $nm_pasien
            ]);
            
            error_log('anyDataTb HTML length: ' . strlen($html));
            echo $html;
            
        } catch(Exception $e) {
            error_log('anyDataTb error: ' . $e->getMessage());
            echo '<div class="modal-header">';
            echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
            echo '<h4 class="modal-title">Error - Data TB</h4>';
            echo '</div>';
            echo '<div class="modal-body">';
            echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<p>Silakan hubungi administrator sistem.</p>';
            echo '</div>';
        }
        exit();
    }

    private function getDefaultDataTb($no_rawat) {
        return [
            'no_rawat' => $no_rawat,
            'id_tb_03' => '',
            'id_periode_laporan' => '',
            'tanggal_buat_laporan' => '',
            'tahun_buat_laporan' => date('Y'),
            'kd_wasor' => '',
            'noregkab' => '',
            'id_propinsi' => '',
            'kd_kabupaten' => '',
            'id_kecamatan' => '',
            'id_kelurahan' => '',
            'nama_rujukan' => '',
            'sebutkan1' => '',
            'tipe_diagnosis' => '',
            'klasifikasi_lokasi_anatomi' => '',
            'klasifikasi_riwayat_pengobatan' => '',
            'klasifikasi_status_hiv' => '',
            'total_skoring_anak' => '',
            'konfirmasiSkoring5' => '',
            'konfirmasiSkoring6' => '',
            'tanggal_mulai_pengobatan' => '',
            'paduan_oat' => '',
            'sumber_obat' => '',
            'sebutkan' => '',
            'sebelum_pengobatan_hasil_mikroskopis' => '',
            'sebelum_pengobatan_hasil_tes_cepat' => '',
            'sebelum_pengobatan_hasil_biakan' => '',
            'noreglab_bulan_2' => '',
            'hasil_mikroskopis_bulan_2' => '',
            'noreglab_bulan_3' => '',
            'hasil_mikroskopis_bulan_3' => '',
            'noreglab_bulan_5' => '',
            'hasil_mikroskopis_bulan_5' => '',
            'akhir_pengobatan_noreglab' => '',
            'akhir_pengobatan_hasil_mikroskopis' => '',
            'tanggal_hasil_akhir_pengobatan' => '',
            'hasil_akhir_pengobatan' => '',
            'tanggal_dianjurkan_tes' => '',
            'tanggal_tes_hiv' => '',
            'hasil_tes_hiv' => '',
            'ppk' => '',
            'art' => '',
            'tb_dm' => '',
            'terapi_dm' => '',
            'pindah_ro' => '',
            'status_pengobatan' => '',
            'foto_toraks' => '',
            'toraks_tdk_dilakukan' => '',
            'keterangan' => '',
            'kode_icd_x' => '',
            // Additional fields found in template
            'tanggal_daftar' => '',
            'tanggal_mulai_berobat' => '',
            'tipe_diagnosis_tb' => '',
            'lokasi_anatomi' => '',
            'klasifikasi_tb' => '',
            'klasifikasi_kasus' => '',
            'sebutkan2' => '',
            'skoring_kontak_tb' => '',
            'skoring_uji_tuberkulin' => '',
            'skoring_status_gizi' => '',
            'skoring_demam_tanpa_sebab' => '',
            'skoring_batuk_kronik' => '',
            'skoring_pembesaran_kelenjar_limfe' => '',
            'skoring_pembengkakan_tulang_sendi' => '',
            'skoring_foto_toraks' => '',
            'skoring_total_skor' => ''
        ];
    }

    public function postDataTbSave()
    {
        try {
            // Validasi input
            if(empty($_POST['no_rawat'])) {
                echo json_encode(['status' => 'error', 'message' => 'No rawat tidak boleh kosong']);
                exit();
            }

            // Cek apakah data sudah ada
            $existing = $this->db('data_tb')->where('no_rawat', $_POST['no_rawat'])->oneArray();

            // Siapkan data untuk disimpan
            $data = [
                'no_rawat' => $_POST['no_rawat'],
                'id_tb_03' => isset($_POST['id_tb_03']) ? $_POST['id_tb_03'] : null,
                'id_periode_laporan' => isset($_POST['id_periode_laporan']) ? $_POST['id_periode_laporan'] : null,
                'tanggal_buat_laporan' => isset($_POST['tanggal_buat_laporan']) && !empty($_POST['tanggal_buat_laporan']) ? $_POST['tanggal_buat_laporan'] : null,
                'tahun_buat_laporan' => isset($_POST['tahun_buat_laporan']) && !empty($_POST['tahun_buat_laporan']) ? (int)$_POST['tahun_buat_laporan'] : null,
                'kd_wasor' => isset($_POST['kd_wasor']) && !empty($_POST['kd_wasor']) ? (int)$_POST['kd_wasor'] : null,
                'noregkab' => isset($_POST['noregkab']) && !empty($_POST['noregkab']) ? (int)$_POST['noregkab'] : null,
                'id_propinsi' => isset($_POST['id_propinsi']) ? $_POST['id_propinsi'] : null,
                'kd_kabupaten' => isset($_POST['kd_kabupaten']) ? $_POST['kd_kabupaten'] : null,
                'id_kecamatan' => isset($_POST['id_kecamatan']) ? $_POST['id_kecamatan'] : null,
                'id_kelurahan' => isset($_POST['id_kelurahan']) ? $_POST['id_kelurahan'] : null,
                'nama_rujukan' => isset($_POST['nama_rujukan']) ? $_POST['nama_rujukan'] : null,
                'sebutkan1' => isset($_POST['sebutkan1']) ? $_POST['sebutkan1'] : null,
                'tipe_diagnosis' => isset($_POST['tipe_diagnosis']) ? $_POST['tipe_diagnosis'] : null,
                'klasifikasi_lokasi_anatomi' => isset($_POST['klasifikasi_lokasi_anatomi']) ? $_POST['klasifikasi_lokasi_anatomi'] : null,
                'klasifikasi_riwayat_pengobatan' => isset($_POST['klasifikasi_riwayat_pengobatan']) ? $_POST['klasifikasi_riwayat_pengobatan'] : null,
                'klasifikasi_status_hiv' => isset($_POST['klasifikasi_status_hiv']) ? $_POST['klasifikasi_status_hiv'] : null,
                'total_skoring_anak' => isset($_POST['total_skoring_anak']) ? $_POST['total_skoring_anak'] : null,
                'konfirmasiSkoring5' => isset($_POST['konfirmasiSkoring5']) ? $_POST['konfirmasiSkoring5'] : null,
                'konfirmasiSkoring6' => isset($_POST['konfirmasiSkoring6']) ? $_POST['konfirmasiSkoring6'] : null,
                'tanggal_mulai_pengobatan' => isset($_POST['tanggal_mulai_pengobatan']) && !empty($_POST['tanggal_mulai_pengobatan']) ? $_POST['tanggal_mulai_pengobatan'] : null,
                'paduan_oat' => isset($_POST['paduan_oat']) ? $_POST['paduan_oat'] : null,
                'sumber_obat' => isset($_POST['sumber_obat']) ? $_POST['sumber_obat'] : null,
                'sebutkan' => isset($_POST['sebutkan']) ? $_POST['sebutkan'] : null,
                'sebelum_pengobatan_hasil_mikroskopis' => isset($_POST['sebelum_pengobatan_hasil_mikroskopis']) ? $_POST['sebelum_pengobatan_hasil_mikroskopis'] : null,
                'sebelum_pengobatan_hasil_tes_cepat' => isset($_POST['sebelum_pengobatan_hasil_tes_cepat']) ? $_POST['sebelum_pengobatan_hasil_tes_cepat'] : null,
                'sebelum_pengobatan_hasil_biakan' => isset($_POST['sebelum_pengobatan_hasil_biakan']) ? $_POST['sebelum_pengobatan_hasil_biakan'] : null,
                'noreglab_bulan_2' => isset($_POST['noreglab_bulan_2']) ? $_POST['noreglab_bulan_2'] : null,
                'hasil_mikroskopis_bulan_2' => isset($_POST['hasil_mikroskopis_bulan_2']) ? $_POST['hasil_mikroskopis_bulan_2'] : null,
                'noreglab_bulan_3' => isset($_POST['noreglab_bulan_3']) ? $_POST['noreglab_bulan_3'] : null,
                'hasil_mikroskopis_bulan_3' => isset($_POST['hasil_mikroskopis_bulan_3']) ? $_POST['hasil_mikroskopis_bulan_3'] : null,
                'noreglab_bulan_5' => isset($_POST['noreglab_bulan_5']) ? $_POST['noreglab_bulan_5'] : null,
                'hasil_mikroskopis_bulan_5' => isset($_POST['hasil_mikroskopis_bulan_5']) ? $_POST['hasil_mikroskopis_bulan_5'] : null,
                'akhir_pengobatan_noreglab' => isset($_POST['akhir_pengobatan_noreglab']) ? $_POST['akhir_pengobatan_noreglab'] : null,
                'akhir_pengobatan_hasil_mikroskopis' => isset($_POST['akhir_pengobatan_hasil_mikroskopis']) ? $_POST['akhir_pengobatan_hasil_mikroskopis'] : null,
                'tanggal_hasil_akhir_pengobatan' => isset($_POST['tanggal_hasil_akhir_pengobatan']) && !empty($_POST['tanggal_hasil_akhir_pengobatan']) ? $_POST['tanggal_hasil_akhir_pengobatan'] : null,
                'hasil_akhir_pengobatan' => isset($_POST['hasil_akhir_pengobatan']) ? $_POST['hasil_akhir_pengobatan'] : null,
                'tanggal_dianjurkan_tes' => isset($_POST['tanggal_dianjurkan_tes']) && !empty($_POST['tanggal_dianjurkan_tes']) ? $_POST['tanggal_dianjurkan_tes'] : null,
                'tanggal_tes_hiv' => isset($_POST['tanggal_tes_hiv']) && !empty($_POST['tanggal_tes_hiv']) ? $_POST['tanggal_tes_hiv'] : null,
                'hasil_tes_hiv' => isset($_POST['hasil_tes_hiv']) ? $_POST['hasil_tes_hiv'] : null,
                'ppk' => isset($_POST['ppk']) ? $_POST['ppk'] : null,
                'art' => isset($_POST['art']) ? $_POST['art'] : null,
                'tb_dm' => isset($_POST['tb_dm']) ? $_POST['tb_dm'] : null,
                'terapi_dm' => isset($_POST['terapi_dm']) ? $_POST['terapi_dm'] : null,
                'pindah_ro' => isset($_POST['pindah_ro']) ? $_POST['pindah_ro'] : null,
                'status_pengobatan' => isset($_POST['status_pengobatan']) ? $_POST['status_pengobatan'] : null,
                'foto_toraks' => isset($_POST['foto_toraks']) ? $_POST['foto_toraks'] : null,
                'toraks_tdk_dilakukan' => isset($_POST['toraks_tdk_dilakukan']) ? $_POST['toraks_tdk_dilakukan'] : null,
                'keterangan' => isset($_POST['keterangan']) ? $_POST['keterangan'] : null,
                'kode_icd_x' => isset($_POST['kode_icd_x']) ? $_POST['kode_icd_x'] : null
            ];

            if($existing) {
                // Update data yang sudah ada
                $query = $this->db('data_tb')
                    ->where('no_rawat', $_POST['no_rawat'])
                    ->save($data);
            } else {
                // Insert data baru
                $query = $this->db('data_tb')->save($data);
            }

            if($query) {
                echo json_encode(['status' => 'success', 'message' => 'Data TB berhasil disimpan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data TB']);
            }
        } catch(\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function getDataTbView($no_rawat)
    {
        $no_rawat = revertNorawat($no_rawat);
        $data_tb = $this->db('data_tb')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        if(!$data_tb) {
            echo json_encode(['status' => 'error', 'message' => 'Data TB tidak ditemukan']);
            exit();
        }
        
        // Format tanggal untuk tampilan
        if($data_tb['tanggal_buat_laporan']) {
            $data_tb['tanggal_buat_laporan_formatted'] = date('d/m/Y H:i', strtotime($data_tb['tanggal_buat_laporan']));
        }
        if($data_tb['tanggal_mulai_pengobatan']) {
            $data_tb['tanggal_mulai_pengobatan_formatted'] = date('d/m/Y', strtotime($data_tb['tanggal_mulai_pengobatan']));
        }
        if($data_tb['tanggal_hasil_akhir_pengobatan']) {
            $data_tb['tanggal_hasil_akhir_pengobatan_formatted'] = date('d/m/Y', strtotime($data_tb['tanggal_hasil_akhir_pengobatan']));
        }
        if($data_tb['tanggal_dianjurkan_tes']) {
            $data_tb['tanggal_dianjurkan_tes_formatted'] = date('d/m/Y', strtotime($data_tb['tanggal_dianjurkan_tes']));
        }
        if($data_tb['tanggal_tes_hiv']) {
            $data_tb['tanggal_tes_hiv_formatted'] = date('d/m/Y', strtotime($data_tb['tanggal_tes_hiv']));
        }
        
        echo $this->draw('view.data_tb.html', ['data_tb' => $data_tb]);
        exit();
    }

    public function postDataTbHapus()
    {
        try {
            if(empty($_POST['no_rawat'])) {
                echo json_encode(['status' => 'error', 'message' => 'No rawat tidak boleh kosong']);
                exit();
            }

            $query = $this->db('data_tb')
                ->where('no_rawat', $_POST['no_rawat'])
                ->delete();
            
            if($query) {
                echo json_encode(['status' => 'success', 'message' => 'Data TB berhasil dihapus']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data TB atau data tidak ditemukan']);
            }
        } catch(\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
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
        $this->core->addJS(url([ADMIN, 'rawat_jalan', 'javascript']), 'footer');
    }

}
