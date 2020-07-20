<?php

namespace Plugins\Pendaftaran;

use Systems\AdminModule;
use Systems\Lib\BpjsRequest;
use Systems\Lib\Fpdf\FPDF;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{
    private $assign = [];

    public function navigation()
    {
        return [
            'Kelola'    => 'manage',
            'Booking'          => 'booking',
            'Tambah Baru'                => 'add',
            'Jadwal Dokter'          => 'jadwal',
            'Pengaturan'          => 'settings'
        ];
    }

    public function getManage($page = 1)
    {
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
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'pendaftaran', 'manage', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.nm_pasien, pasien.alamat, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab, pasien.no_peserta FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);
              $row['editURL'] = url([ADMIN, 'pendaftaran', 'edit', convertNorawat($row['no_rawat'])]);
              $row['delURL']  = url([ADMIN, 'pendaftaran', 'delete', convertNorawat($row['no_rawat'])]);
              $row['viewURL'] = url([ADMIN, 'pendaftaran', 'view', convertNorawat($row['no_rawat'])]);
              $row['bridgingBPJS'] = url([ADMIN, 'pendaftaran', 'bridgingbpjs', convertNorawat($row['no_peserta'])]);
              $row['dataSEP'] = url([ADMIN, 'pendaftaran', 'datasep', convertNorawat($row['no_peserta'])]);
              $row['print_buktidaftar'] = url([ADMIN, 'pendaftaran', 'print_buktidaftar', convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['options'] = htmlspecialchars_array($this->options('pendaftaran'));
      $this->assign['searchUrl'] =  url([ADMIN, 'pendaftaran', 'manage', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage.html', ['pendaftaran' => $this->assign]);

    }

    /**
    * add new pasien
    */
    public function getAdd()
    {
        $this->_addHeaderFiles();

        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
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
              'status_poli' => ''
            ];
        }

        $this->assign['form']['tgl_registrasi'] = date('Y-m-d');
        $this->assign['form']['jam_reg'] = date('H:i:s');
        $this->assign['poliklinik'] = $this->core->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter'] = $this->core->db('dokter')->where('status', '1')->toArray();
        $this->assign['status_lanjut'] = $this->core->getEnum('reg_periksa', 'status_lanjut');
        $this->assign['status_bayar'] = $this->core->getEnum('reg_periksa', 'status_bayar');
        $this->assign['penjab'] = $this->core->db('penjab')->toArray();

        $this->assign['manageURL'] = url([ADMIN, 'pendaftaran', 'manage']);
        $this->assign['form']['no_rawat'] = $this->core->setNoRawat();
        $this->assign['pasien']['nm_pasien'] = '';

        return $this->draw('form.html', ['pendaftaran' => $this->assign]);
    }

    public function getEdit($id)
    {
        $id = revertNorawat($id);
        $this->_addHeaderFiles();
        $pasien = $this->db('reg_periksa')->where('no_rawat', $id)->oneArray();
        $this->assign['poliklinik'] = $this->core->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter'] = $this->core->db('dokter')->toArray();
        $this->assign['status_lanjut'] = $this->core->getEnum('reg_periksa', 'status_lanjut');
        $this->assign['status_bayar'] = $this->core->getEnum('reg_periksa', 'status_bayar');
        $this->assign['penjab'] = $this->core->db('penjab')->toArray();

        if (!empty($pasien)) {
            $this->assign['form'] = $pasien;
            $this->assign['form']['norawat'] = convertNorawat($pasien['no_rawat']);
            $this->assign['title'] = 'Edit Pendaftaran Pasien';
            $this->assign['manageURL'] = url([ADMIN, 'pendaftaran', 'manage']);
            $this->assign['pasien'] = $this->db('pasien')->where('no_rkm_medis', $pasien['no_rkm_medis'])->oneArray();

            return $this->draw('form.html', ['pendaftaran' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pendaftaran', 'manage']));
        }
    }

    /**
    * save pasien data
    */
    public function postSave($id = null)
    {
        $errors = 0;

        $date = date('Y-m-d');

        $cek_no_rawat = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->count();

        $_POST['hubunganpj'] = $this->core->getPasienInfo('keluarga', $_POST['no_rkm_medis']);

        $_POST['stts'] = 'Belum';

        $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->count();
        $_POST['stts_daftar'] = 'Baru';
        if($cek_stts_daftar > 0) {
          $_POST['stts_daftar'] = 'Lama';
        }

        $biaya_reg = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
        $_POST['biaya_reg'] = $biaya_reg['registrasi'];
        if($_POST['stts_daftar'] == 'Lama') {
          $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
        }

        $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('kd_poli', $_POST['kd_poli'])->count();
        $_POST['status_poli'] = 'Baru';
        if($cek_status_poli > 0) {
          $_POST['status_poli'] = 'Lama';
        }

        // set umur
        $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $_POST['no_rkm_medis']));
        $today = new \DateTime($date);
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
        $_POST['umurdaftar'] = $umur;
        $_POST['sttsumur'] = $sttsumur;

        // location to redirect
        if ($cek_no_rawat == 0) {
            $_POST['no_reg'] = $this->core->setNoReg($_POST['kd_dokter']);
            $location = url([ADMIN, 'pendaftaran', 'manage']);
        } else {
            $location = url([ADMIN, 'pendaftaran', 'edit', $id]);
        }

        // check if pasien already exists
        if ($this->_pasienAlreadyExists($id)) {
            $errors++;
            $this->notify('failure', 'Pasien sudah terdaftar ditanggal yang sama.');
        }

        if($this->options->get('pendaftaran.cekstatusbayar') == 1) {
          if ($this->_cekStatusBayar($id)) {
              $errors++;
              $this->notify('failure', 'Ada tagihan belum dibayar. Silahkan hubungi kasir.');
          }
        }

        if($this->options->get('pendaftaran.ceklimit') == 1) {
          if ($this->_cekLimitKuota($_POST['kd_dokter'], $_POST['tgl_registrasi'])) {
              $errors++;
              $this->notify('failure', 'Kuota pendaftaran sudah terpenuhi. Silahkan hubungi petugas.');
          }
        }

        // CREATE / EDIT
        if (!$errors) {
            unset($_POST['save']);

            if($_POST['booking']) {
              $query = $this->db('booking_registrasi')
                ->save([
                  'tanggal_booking' => date('Y-m-d'),
                  'jam_booking' => date('H:i:s'),
                  'no_rkm_medis' => $_POST['no_rkm_medis'],
                  'tanggal_periksa' => $_POST['tgl_registrasi'],
                  'kd_dokter' => $_POST['kd_dokter'],
                  'kd_poli' => $_POST['kd_poli'],
                  'no_reg' => $this->core->setNoBooking($_POST['kd_dokter'], $_POST['tgl_registrasi']),
                  'kd_pj' => $_POST['kd_pj'],
                  'limit_reg' => 0,
                  'waktu_kunjungan' => $_POST['tgl_registrasi'].' '.$_POST['jam_reg'],
                  'status' => 'Belum'
                ]);
            } else {
              if ($cek_no_rawat == 0) {    // new
                  $_POST['no_rawat'] = $this->core->setNoRawat();
                  $query = $this->db('reg_periksa')->save($_POST);
              } else {        // edit
                  $dokter = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tgl_registrasi', $_POST['tgl_registrasi'])->where('kd_dokter', '<>', $_POST['kd_dokter'])->count();
                  if($dokter) {
                    $_POST['no_reg'] = $this->core->setNoReg($_POST['kd_dokter']);
                  }
                  $query = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save($_POST);
              }
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

    public function anyBooking($page = 1)
    {

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
                  $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $item));
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
                        'no_rawat' => $this->core->setNoRawat(),
                        'tgl_registrasi' => date('Y-m-d'),
                        'jam_reg' => date('H:i:s'),
                        'kd_dokter' => $row['kd_dokter'],
                        'no_rkm_medis' => $item,
                        'kd_poli' => $row['kd_poli'],
                        'p_jawab' => $this->core->getPasienInfo('namakeluarga', $item),
                        'almt_pj' => $this->core->getPasienInfo('alamatpj', $item),
                        'hubunganpj' => $this->core->getPasienInfo('keluarga', $item),
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

              redirect(url([ADMIN, 'pendaftaran', 'booking']));
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

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'pendaftaran', 'booking', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT booking_registrasi.*, pasien.nm_pasien, pasien.alamat, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab, pasien.no_peserta FROM booking_registrasi, pasien, dokter, poliklinik, penjab WHERE booking_registrasi.no_rkm_medis = pasien.no_rkm_medis AND booking_registrasi.kd_dokter = dokter.kd_dokter AND booking_registrasi.kd_poli = poliklinik.kd_poli AND booking_registrasi.kd_pj = penjab.kd_pj AND (booking_registrasi.no_rkm_medis LIKE ? OR pasien.nm_pasien LIKE ?) AND booking_registrasi.tanggal_periksa BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url([ADMIN, 'pendaftaran', 'booking', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('booking.html', ['booking' => $this->assign]);

    }

    public function getSettings()
    {
        $this->assign['penjab'] = $this->db('penjab')->toArray();
        $this->assign['pendaftaran'] = htmlspecialchars_array($this->options('pendaftaran'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['pendaftaran'] as $key => $val) {
            $this->options('pendaftaran', $key, $val);
        }
        $this->notify('success', 'Pengaturan pendaftaran telah disimpan');
        redirect(url([ADMIN, 'pendaftaran', 'settings']));
    }

    public function getDataSEP($id)
    {
      $rows = $this->db('bridging_sep')->where('no_kartu', $id)->toArray();
      $sep['detail'] = [];
      foreach ($rows as $row) {
          $row = htmlspecialchars_array($row);
          $row['NoRujukanURL'] = url([ADMIN, 'pendaftaran', 'norujukan', $row['no_rujukan']]);
          $sep['detail'][] = $row;
      }
      $sep['title'] = 'Data SEP BPJS';
      $this->tpl->set('bridging', $sep);
      echo $this->tpl->draw(MODULES.'/pendaftaran/view/admin/data.sep.html', true);
      exit();
    }

    public function getBridgingBPJS($id)
    {
      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');

      $url = $this->options->get('settings.BpjsApiUrl').'Rujukan/List/Peserta/'.$id;
      $output = BpjsRequest::get($url, NULL, NULL, $consid, $secretkey);
      $json = json_decode($output, true);
      //print("<pre>".print_r($json,true)."</pre>");

      $sep['detail'] = [];
      foreach ($json['response']['rujukan'] as $key=>$value) {
          $value['NoRujukanURL'] = url([ADMIN, 'pendaftaran', 'norujukan', $value['noKunjungan']]);
          $sep['detail'][] = $value;
      }

      $url2 = $this->options->get('settings.BpjsApiUrl').'Rujukan/RS/List/Peserta/'.$id;
      $output2 = BpjsRequest::get($url2, NULL, NULL, $consid, $secretkey);
      $json2 = json_decode($output2, true);
      //print("<pre>".print_r($json,true)."</pre>");

      $sep2['detail'] = [];
      foreach ($json2['response']['rujukan'] as $key=>$value) {
          $value['NoRujukanURL'] = url([ADMIN, 'pendaftaran', 'norujukan2', $value['noKunjungan']]);
          $sep2['detail'][] = $value;
      }


      $sep['title'] = 'Ajukan SEP BPJS';
      $this->tpl->set('bridging', $sep);
      $this->tpl->set('bridging2', $sep2);
      echo $this->tpl->draw(MODULES.'/pendaftaran/view/admin/manage.rujukan.html', true);
      exit();
    }

    public function getNoRujukan($id)
    {
      $this->_addProfileHeaderFiles();
      $date = date('Y-m-d');
      $bridging_sep = $this->db('bridging_sep')->where('no_rujukan', $id)->oneArray();
      $this->assign['bridging_sep'] = $bridging_sep;

      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');

      $url = $this->options->get('settings.BpjsApiUrl').'Rujukan/'.$id;
      $rujukan = BpjsRequest::get($url, NULL, NULL, $consid, $secretkey);
      $json = json_decode($rujukan, true);
      $this->assign['rujukan'] = $json['response']['rujukan'];
      $no_kartu = $json['response']['rujukan']['peserta']['noKartu'];
      $sex = $json['response']['rujukan']['peserta']['sex'];

      $pasien = $this->db('pasien')->where('no_peserta', $json['response']['rujukan']['peserta']['noKartu'])->oneArray();
      $this->assign['pasien'] = $pasien;
      $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $pasien['no_rkm_medis'])->oneArray();

      $this->assign['reg_periksa'] = $this->db('reg_periksa')->where('no_rkm_medis', $pasien['no_rkm_medis'])->desc('no_rawat')->toArray();

      $this->assign['kode_ppk'] = $this->core->getSettings('kode_ppk');

      $url_referensi = $this->options->get('settings.BpjsApiUrl').'referensi/dokter/pelayanan/'.$json['response']['rujukan']['pelayanan']['kode'].'/tglPelayanan/'.$date.'/Spesialis/'.$json['response']['rujukan']['poliRujukan']['kode'];
      $dpjp = BpjsRequest::get($url_referensi, NULL, NULL, $consid, $secretkey);
      $json = json_decode($dpjp, true);
      $this->assign['dpjp'] = [];
      foreach ($json['response']['list'] as $key=>$value) {
          $this->assign['dpjp'][] = $value;
      }

      $url_rujukan = $this->options->get('settings.BpjsApiUrl').'Rujukan/List/Peserta/'.$no_kartu;
      $url_rujukan = BpjsRequest::get($url_rujukan, NULL, NULL, $consid, $secretkey);
      $json_rujukan = json_decode($url_rujukan, true);
      $this->assign['sepdetail'] = [];
      foreach ($json_rujukan['response']['rujukan'] as $key=>$value) {
          //$get_sepdetail = $this->db('bridging_sep')->where('no_rujukan', $value['noKunjungan'])->oneArray();
          $value['NoRujukanURL'] = url([ADMIN, 'pendaftaran', 'sepdetail', $value['noKunjungan']]);
          $this->assign['sepdetail'][] = $value;
      }

      $this->assign['get_noskdp'] = $this->db('skdp_bpjs')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_datang', date('Y-m-d'))->oneArray();
      $this->assign['get_noskdp_alt'] = $this->core->setNoSKDP();
      $this->assign['fotoURL'] = url('/plugins/pasien/img/'.$sex.'.png');
      $this->assign['printSEP'] = url([ADMIN, 'pendaftaran', 'printsep', $id]);
      if(!empty($personal_pasien['gambar'])) {
        $this->assign['fotoURL'] = url(WEBAPPS_PATH.'/photopasien/'.$personal_pasien['gambar']);
      }

      $this->assign['nama_instansi'] = $this->core->getSettings('nama_instansi');

      return $this->draw('bridgingbpjs.form.html', ['bridging' => $this->assign]);
    }

    public function getNoRujukan2($id)
    {
      $this->_addProfileHeaderFiles();
      $date = date('Y-m-d');
      $bridging_sep = $this->db('bridging_sep')->where('no_rujukan', $id)->oneArray();
      $this->assign['bridging_sep'] = $bridging_sep;

      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');

      $url = $this->options->get('settings.BpjsApiUrl').'Rujukan/RS/'.$id;
      $rujukan = BpjsRequest::get($url, NULL, NULL, $consid, $secretkey);
      $json = json_decode($rujukan, true);
      $this->assign['rujukan'] = $json['response']['rujukan'];
      $no_kartu = $json['response']['rujukan']['peserta']['noKartu'];
      $sex = $json['response']['rujukan']['peserta']['sex'];

      $pasien = $this->db('pasien')->where('no_peserta', $json['response']['rujukan']['peserta']['noKartu'])->oneArray();
      $this->assign['pasien'] = $pasien;
      $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $pasien['no_rkm_medis'])->oneArray();

      $this->assign['reg_periksa'] = $this->db('reg_periksa')->where('no_rkm_medis', $pasien['no_rkm_medis'])->desc('no_rawat')->toArray();

      $this->assign['kode_ppk'] = $this->core->getSettings('kode_ppk');

      $url_referensi = $this->options->get('settings.BpjsApiUrl').'referensi/dokter/pelayanan/'.$json['response']['rujukan']['pelayanan']['kode'].'/tglPelayanan/'.$date.'/Spesialis/'.$json['response']['rujukan']['poliRujukan']['kode'];
      $dpjp = BpjsRequest::get($url_referensi, NULL, NULL, $consid, $secretkey);
      $json = json_decode($dpjp, true);
      $this->assign['dpjp'] = [];
      foreach ($json['response']['list'] as $key=>$value) {
          $this->assign['dpjp'][] = $value;
      }

      $url_rujukan = $this->options->get('settings.BpjsApiUrl').'Rujukan/RS/List/Peserta/'.$no_kartu;
      $url_rujukan = BpjsRequest::get($url_rujukan, NULL, NULL, $consid, $secretkey);
      $json_rujukan = json_decode($url_rujukan, true);
      $this->assign['sepdetail'] = [];
      foreach ($json_rujukan['response']['rujukan'] as $key=>$value) {
          //$get_sepdetail = $this->db('bridging_sep')->where('no_rujukan', $value['noKunjungan'])->oneArray();
          $value['NoRujukanURL'] = url([ADMIN, 'pendaftaran', 'sepdetail', $value['noKunjungan']]);
          $this->assign['sepdetail'][] = $value;
      }

      $this->assign['get_noskdp'] = $this->db('skdp_bpjs')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_datang', date('Y-m-d'))->oneArray();
      $this->assign['get_noskdp_alt'] = $this->core->setNoSKDP();
      $this->assign['fotoURL'] = url('/plugins/pasien/img/'.$sex.'.png');
      $this->assign['printSEP'] = url([ADMIN, 'pendaftaran', 'printsep', $id]);
      if(!empty($personal_pasien['gambar'])) {
        $this->assign['fotoURL'] = url(WEBAPPS_PATH.'/photopasien/'.$personal_pasien['gambar']);
      }

      $this->assign['nama_instansi'] = $this->core->getSettings('nama_instansi');

      return $this->draw('bridgingbpjs.form.html', ['bridging' => $this->assign]);
    }

    public function getSepDetail($id)
    {
      $this->_addProfileHeaderFiles();
      $date = date('Y-m-d');
      $bridging_sep = $this->db('bridging_sep')->where('no_sep', $id)->oneArray();
      $this->assign['bridging_sep'] = $bridging_sep;

      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');

      $url = $this->options->get('settings.BpjsApiUrl').'Rujukan/'.$bridging_sep['no_rujukan'];
      $rujukan = BpjsRequest::get($url, NULL, NULL, $consid, $secretkey);
      $json = json_decode($rujukan, true);
      $this->assign['rujukan'] = $json['response']['rujukan'];
      $no_kartu = $json['response']['rujukan']['peserta']['noKartu'];
      $sex = $json['response']['rujukan']['peserta']['sex'];

      $pasien = $this->db('pasien')->where('no_peserta', $json['response']['rujukan']['peserta']['noKartu'])->oneArray();
      $this->assign['pasien'] = $pasien;
      $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $pasien['no_rkm_medis'])->oneArray();

      $this->assign['reg_periksa'] = $this->db('reg_periksa')->where('no_rkm_medis', $pasien['no_rkm_medis'])->desc('no_rawat')->toArray();

      $this->assign['kode_ppk'] = $this->core->getSettings('kode_ppk');

      $url_referensi = $this->options->get('settings.BpjsApiUrl').'referensi/dokter/pelayanan/'.$json['response']['rujukan']['pelayanan']['kode'].'/tglPelayanan/'.$date.'/Spesialis/'.$json['response']['rujukan']['poliRujukan']['kode'];
      $dpjp = BpjsRequest::get($url_referensi, NULL, NULL, $consid, $secretkey);
      $json = json_decode($dpjp, true);
      $this->assign['dpjp'] = [];
      foreach ($json['response']['list'] as $key=>$value) {
          $this->assign['dpjp'][] = $value;
      }

      $url_rujukan = $this->options->get('settings.BpjsApiUrl').'Rujukan/List/Peserta/'.$no_kartu;
      $url_rujukan = BpjsRequest::get($url_rujukan, NULL, NULL, $consid, $secretkey);
      $json_rujukan = json_decode($url_rujukan, true);
      $this->assign['sepdetail'] = [];
      foreach ($json_rujukan['response']['rujukan'] as $key=>$value) {
          $value['NoRujukanURL'] = url([ADMIN, 'pendaftaran', 'sepdetail', $value['noKunjungan']]);
          $this->assign['sepdetail'][] = $value;
      }

      $this->assign['fotoURL'] = url('/plugins/pasien/img/'.$sex.'.png');
      $this->assign['printSEP'] = url([ADMIN, 'pendaftaran', 'printsep', $id]);
      if(!empty($personal_pasien['gambar'])) {
        $this->assign['fotoURL'] = url(WEBAPPS_PATH.'/photopasien/'.$personal_pasien['gambar']);
      }

      $this->assign['nama_instansi'] = $this->core->getSettings('nama_instansi');

      return $this->draw('bridgingbpjs.form.html', ['bridging' => $this->assign]);
    }

    public function postSaveSEP() {
      $date = date('Y-m-d');
      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');
      $bpjsapiurl = $this->options->get('settings.BpjsApiUrl');

      $_POST['kdppkpelayanan'] = $this->core->getSettings('kode_ppk');
      $_POST['user'] = $this->core->getUserInfo('username', null, true);

      $sup = new \StdClass();
      $sup->noKartu = $_POST['no_kartu']; #pass
      $sup->tglSep = $_POST['tglsep']; #pass
      $sup->ppkPelayanan = $_POST['kdppkpelayanan']; #pass
      $sup->jnsPelayanan = $_POST['jnspelayanan']; #pass
      $sup->klsRawat = $_POST['klsrawat']; #pass
      $sup->noMR = $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']); #pass
      $sup->rujukan = new \StdClass();
      $sup->rujukan->asalRujukan = $_POST['asal_rujukan']; #pass
      $sup->rujukan->tglRujukan = $_POST['tglrujukan']; #pass
      $sup->rujukan->noRujukan = $_POST['no_rujukan']; #pass
      $sup->rujukan->ppkRujukan = $_POST['kdppkrujukan']; #pass
      $sup->catatan = $_POST['catatan']; #pass
      $sup->diagAwal = $_POST['diagawal']; #pass
      $sup->poli = new \StdClass();
      $sup->poli->tujuan = $_POST['kdpolitujuan']; #pass
      $sup->poli->eksekutif = $_POST['eksekutif']; #pass
      $sup->cob = new \StdClass();
      $sup->cob->cob = $_POST['cob']; #pass
      $sup->katarak = new \StdClass();
      $sup->katarak->katarak = $_POST['katarak']; #pass
      $sup->jaminan = new \StdClass();
      $sup->jaminan->lakaLantas = $_POST['lakaLantas']; #pass
      $sup->jaminan->penjamin = new \StdClass();
      $sup->jaminan->penjamin->penjamin = $_POST['penjamin']; #pass
      $sup->jaminan->penjamin->tglKejadian = $_POST['tglkkl']; #pass
      $sup->jaminan->penjamin->keterangan = $_POST['keterangankkl']; #pass
      $sup->jaminan->penjamin->suplesi = new \StdClass();
      $sup->jaminan->penjamin->suplesi->suplesi = $_POST['suplesi']; #pass
      $sup->jaminan->penjamin->suplesi->noSepSuplesi = $_POST['no_sep_suplesi']; #pass
      $sup->jaminan->penjamin->suplesi->lokasiLaka = new \StdClass();
      $sup->jaminan->penjamin->suplesi->lokasiLaka->kdPropinsi = $_POST['kdprop']; #pass
      $sup->jaminan->penjamin->suplesi->lokasiLaka->kdKabupaten = $_POST['kdkab']; #pass
      $sup->jaminan->penjamin->suplesi->lokasiLaka->kdKecamatan = $_POST['kdkec']; #pass
      $sup->skdp = new \StdClass();
      $sup->skdp->noSurat = $_POST['noskdp']; #pass
      $sup->skdp->kodeDPJP = $_POST['kddpjp']; #pass
      $sup->noTelp = $_POST['notelep']; #pass
      $sup->user = $_POST['user'];

      $data = new \StdClass();
      $data->request = new \StdClass();
      $data->request->t_sep = $sup;

      $sep = json_encode($data);

      date_default_timezone_set('UTC');
      $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
      $signature = hash_hmac('sha256', $consid."&".$tStamp, $secretkey, true);
      $encodedSignature = base64_encode($signature);
      $ch = curl_init();
      $headers = array(
        'X-cons-id: '.$consid.'',
        'X-timestamp: '.$tStamp.'' ,
        'X-signature: '.$encodedSignature.'',
        'Content-Type:Application/x-www-form-urlencoded',
      );
      curl_setopt($ch, CURLOPT_URL, $bpjsapiurl."SEP/1.1/insert");
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $sep);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $content = curl_exec($ch);
      $err = curl_error($ch);
      //print_r($content);
      //print_r($err);

      curl_close($ch);
      $result = json_decode($content,true);
      $meta = $result['metaData']['code'];
      $mets = $result['metaData']['message'];
      $sep = $result['response']['sep']['noSep'];

      $location = url([ADMIN, 'pendaftaran', 'norujukan', $_POST['no_rujukan']]);

      if($meta == '200') {

        unset($_POST['save']);

        if ($_POST['suplesi'] == '0') {
          $_POST['suplesi'] = '0. Tidak';
        }else {
          $_POST['suplesi'] = '1. Ya';
        };
        if ($_POST['eksekutif'] == '0') {
          $_POST['eksekutif'] = '0. Tidak';
        }else {
          $_POST['eksekutif'] = '1. Ya';
        };
        if ($_POST['cob'] == '0') {
          $_POST['cob'] = '0. Tidak';
        }else {
          $_POST['cob'] = '1. Ya';
        };
        if ($_POST['katarak'] == '0') {
          $_POST['katarak'] = '0. Tidak';
        }else {
          $_POST['katarak'] = '1. Ya';
        };
        if ($_POST['asal_rujukan'] == '1') {
          $_POST['asal_rujukan'] = '1. Faskes 1';
        }else {
          $_POST['asal_rujukan'] = '2. Faskes 2(RS)';
        };

        $_POST['no_sep'] = $sep; #ambil dari ws bpjs
        $_POST['nmppkpelayanan'] = $this->core->getSettings('nama_instansi');
        $_POST['tglpulang'] = '1970-01-01 00:00:00';
        $_POST['nmpolitujuan'] = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $_POST['kdpolitujuan'])->oneArray()['nm_poli_bpjs']; #ambil dari ws bpjs
        $_POST['nmdiagnosaawal'] = $this->db('penyakit')->where('kd_penyakit', $_POST['diagawal'])->oneArray()['nm_penyakit']; #ambil dari ws bpjs
		    /*
		    $_POST['nmprop'] = $this->db('propinsi')->where('kd_prop', $_POST['kdprop'])->oneArray()['nm_prop']; #ambil dari ws bpjs
        $_POST['nmkab'] = $this->db('kabupaten')->where('kd_kab', $_POST['kdkab'])->oneArray()['nm_kab']; #ambil dari ws bpjs
        $_POST['nmkec'] = $this->db('kecamatan')->where('kd_kec', $_POST['kdkec'])->oneArray()['nm_kec']; #ambil dari ws bpjs
        */
        $_POST['nmdpdjp'] = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $_POST['kddpjp'])->oneArray()['nm_dokter_bpjs']; #ambil dari ws bpjs
        $_POST['nmprop'] = 'PROP'; #ambil dari ws bpjs
        $_POST['nmkab'] = 'KAB'; #ambil dari ws bpjs
        $_POST['nmkec'] = 'KEC'; #ambil dari ws bpjs



        $query = $this->db('bridging_sep')->save($_POST);

        if ($query) {
            $this->notify('success', 'Simpan sukes');
            if(empty($this->db('skdp_bpjs')->where('no_rkm_medis', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))->where('tanggal_datang', date('Y-m-d'))->oneArray()['no_antrian'])){
              $this->db('skdp_bpjs')
                ->save([
                  'tahun' => date('Y'),
                  'no_rkm_medis' => $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']),
                  'diagnosa' => $_POST['nmdiagnosaawal'],
                  'terapi' => '',
                  'alasan1' => '',
                  'alasan2' => '',
                  'rtl1' => '',
                  'rtl2' => '',
                  'tanggal_datang' => date('Y-m-d'),
                  'tanggal_rujukan' => $_POST['tglrujukan'],
                  'no_antrian' => $_POST['noskdp'],
                  'kd_dokter' => $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $_POST['kddpjp'])->oneArray()['kd_dokter'],
                  'status' => 'Menunggu'
                ]);
            }
        } else {
            $this->notify('failure', 'Simpan gagal');
        }

        redirect($location);

      } else {

        $this->notify('failure', 'Simpan gagal!! '.$mets);

        redirect($location);

      }

    }

    public function getJadwalAdd()
    {
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
        $this->assign['postUrl'] = url([ADMIN, 'pendaftaran', 'jadwalsave', $this->assign['form']['kd_dokter'], $this->assign['form']['hari_kerja']]);
        return $this->draw('jadwal.form.html', ['pendaftaran' => $this->assign]);
    }

    public function getJadwalEdit($id, $hari_kerja)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jadwal')->where('kd_dokter', $id)->where('hari_kerja', $hari_kerja)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Jadwal';
            $this->assign['hari_kerja'] = $this->core->getEnum('jadwal', 'hari_kerja');
            $this->assign['dokter'] = $this->db('dokter')->toArray();
            $this->assign['poliklinik'] = $this->db('poliklinik')->toArray();

            $this->assign['postUrl'] = url([ADMIN, 'pendaftaran', 'jadwalsave', $this->assign['form']['kd_dokter'], $this->assign['form']['hari_kerja']]);
            return $this->draw('jadwal.form.html', ['pendaftaran' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pendaftaran', 'jadwal']));
        }
    }

    public function postJadwalSave($id = null, $hari_kerja = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'pendaftaran', 'jadwal']);
        } else {
            $location = url([ADMIN, 'pendaftaran', 'jadwaledit', $_POST['kd_dokter'], $_POST['hari_kerja']]);
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


    /**
    * remove pasien
    */
    public function getDelete($id)
    {
        if ($pendaftaran = $this->db('reg_periksa')->where('no_rawat', revertNorawat($id))->oneArray()) {
            if ($this->db('reg_periksa')->where('no_rawat', revertNorawat($id))->delete()) {
                $this->notify('success', 'Hapus sukses');
            } else {
                $this->notify('failure', 'Hapus gagal');
            }
        }
        redirect(url([ADMIN, 'pendaftaran', 'manage']));
    }

    public function getAjax()
    {
      $show = isset($_GET['show']) ? $_GET['show'] : "";
      switch($show){
       default:
         $s_keyword="";
         if (isset($_GET['keyword'])) {
             $s_keyword = $_GET['keyword'];
         }
         $search_keyword = '%'. $s_keyword .'%';

         $query = $this->db()->pdo()->prepare("SELECT * FROM pasien WHERE (no_rkm_medis LIKE ? OR nm_pasien LIKE ? OR no_ktp LIKE ? OR no_peserta LIKE ?) ORDER BY no_rkm_medis DESC LIMIT 50");
         $query->execute([$search_keyword, $search_keyword, $search_keyword, $search_keyword]);
          $rows = $query->fetchAll();
         foreach($rows as $row){
           echo '<tr class="pilihpasien" data-norkmmedis="'.$row['no_rkm_medis'].'" data-nmpasien="'.$row['nm_pasien'].'" data-namakeluarga="'.$row['namakeluarga'].'" data-alamatkeluarga="'.$row['alamatpj'].'">';
           echo '<td>'.$row['no_rkm_medis'].'</td>';
           echo '<td>'.$row['nm_pasien'].'</td>';
           echo '<td>'.$row['no_ktp'].'</td>';
           echo '<td>'.$row['namakeluarga'].'</td>';
           echo '<td>'.$row['alamatpj'].'</td>';
           echo '<td>'.$row['pekerjaan'].'</td>';
           echo '<td>'.$row['no_peserta'].'</td>';
           echo '<td>'.$row['no_tlp'].'</td>';
           echo '</tr>';
         }
        break;

        case "poliklinik":
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $url_referensi = $this->options->get('settings.BpjsApiUrl').'referensi/poli/'.$phrase;
        $consid = $this->options->get('settings.BpjsConsID');
        $secretkey = $this->options->get('settings.BpjsSecretKey');
        $poliklinik = BpjsRequest::get($url_referensi, NULL, NULL, $consid, $secretkey);
        $json_poliklinik = json_decode($poliklinik, true);
        echo json_encode($json_poliklinik, true);
        break;

        case "dokter":
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $url_referensi = $this->options->get('settings.BpjsApiUrl').'referensi/dokter/pelayanan/1/tglPelayanan/'.date('Y-m-d').'/Spesialis/'.$phrase;
        $consid = $this->options->get('settings.BpjsConsID');
        $secretkey = $this->options->get('settings.BpjsSecretKey');
        $dokter = BpjsRequest::get($url_referensi, NULL, NULL, $consid, $secretkey);
        $json_dokter = json_decode($dokter, true);
        echo json_encode($json_dokter, true);
        break;

        case "diagnosa":
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $url_diagnosa = $this->options->get('settings.BpjsApiUrl').'referensi/diagnosa/'.$phrase;
        $consid = $this->options->get('settings.BpjsConsID');
        $secretkey = $this->options->get('settings.BpjsSecretKey');
        $diagnosa = BpjsRequest::get($url_diagnosa, NULL, NULL, $consid, $secretkey);
        $json_diagnosa = json_decode($diagnosa, true);
        echo json_encode($json_diagnosa, true);
        break;

        case "propinsi":
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $url_referensi = $this->options->get('settings.BpjsApiUrl').'referensi/propinsi';
        $consid = $this->options->get('settings.BpjsConsID');
        $secretkey = $this->options->get('settings.BpjsSecretKey');
        $array = BpjsRequest::get($url_referensi, NULL, NULL, $consid, $secretkey);
        $json = json_decode($array, true);
        echo json_encode($json, true);
        break;

      }
      exit();
    }

    public function getJadwal()
    {
        $this->_addHeaderFiles();
        $rows = $this->db('jadwal')->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')->toArray();
        $this->assign['jadwal'] = [];
        foreach ($rows as $row) {
            $row['delURL'] = url([ADMIN, 'pendaftaran', 'jadwaldel', $row['kd_dokter'], $row['hari_kerja']]);
            $row['editURL'] = url([ADMIN, 'pendaftaran', 'jadwaledit', $row['kd_dokter'], $row['hari_kerja']]);
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
        redirect(url([ADMIN, 'pendaftaran', 'jadwal']));
    }

    public function getPrint_BuktiDaftar($id)
    {
        $pendaftaran = $this->db('reg_periksa')->where('no_rawat', revertNorawat($id))->oneArray();
        $pasien = $this->db('pasien')->where('no_rkm_medis', $pendaftaran['no_rkm_medis'])->oneArray();
        $dokter = $this->db('dokter')->where('kd_dokter', $pendaftaran['kd_dokter'])->oneArray();
        $poliklinik = $this->db('poliklinik')->where('kd_poli', $pendaftaran['kd_poli'])->oneArray();
        $penjab = $this->db('penjab')->where('kd_pj', $pendaftaran['kd_pj'])->oneArray();
        $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

        $pdf = new FPDF('P', 'mm', array(59,98));
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetTopMargin(5);
        $pdf->SetLeftMargin(5);
        $pdf->SetRightMargin(5);

        $pdf->Image($logo, 2, 2, '11', '11', 'png');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Text(15, 6, $this->core->getSettings('nama_instansi'));
        $pdf->SetFont('Arial', '', 6);
        $pdf->Text(15, 8, $this->core->getSettings('alamat_instansi'));
        $pdf->Text(15, 10, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
        $pdf->Text(15, 12, $this->core->getSettings('kabupaten').' - '.$this->core->getSettings('propinsi'));

      	$pdf->SetFont('Arial', '', 11);
        $pdf->Text(9, 20, 'BUKTI PENDAFTARAN');
      	$pdf->Text(5, 21, '_______________________');
      	$pdf->SetFont('Arial', '', 10);
      	$pdf->Text(15, 26, $pendaftaran['no_rawat']);
      	$pdf->SetFont('Arial', '', 9);
      	$pdf->Text(3, 35, 'Tanggal');
      	$pdf->Text(16, 35, ': '.$pendaftaran['tgl_registrasi']);
      	$pdf->Text(3, 40, 'Antrian');
        $pdf->Text(16, 40, ': '.$pendaftaran['no_reg']);
      	$pdf->Text(3, 45, 'Nama');
        $pdf->Text(16, 45, ': '.substr($pasien['nm_pasien'],0,20));
      	$pdf->Text(3, 50, 'No. RM');
        $pdf->Text(16, 50, ': '.$pendaftaran['no_rkm_medis']);
      	$pdf->Text(3, 55, 'Alamat');
      	$pdf->Text(16, 55, ': '.substr($pasien['alamat'],0,23));
      	$pdf->Text(18, 60, substr($pasien['alamat'],23,42));
      	$pdf->Text(3, 65, 'Ruang');
      	$pdf->Text(16, 65, ': '.substr($poliklinik['nm_poli'],0,23));
      	$pdf->Text(3, 70, 'Dokter');
      	$pdf->Text(16, 70, ': '.substr($dokter['nm_dokter'],0,23));
      	$pdf->Text(3, 75, 'Bayar');
      	$pdf->Text(16, 75, ': '.$penjab['png_jawab']);
      	$pdf->SetFont('Arial', '', 7);
      	$pdf->Text(9, 89, 'Terima Kasih Atas kepercayaan Anda');
      	$pdf->Text(18, 92, 'Bawalah kartu Berobat');
      	$pdf->Text(11, 95, 'setiap berkunjung ke Rumah Sakit');

        $pdf->Output('bukti_register_'.convertNorawat($pendaftaran['no_rawat']).'.pdf','I');

    }

    public function getPrintSEP($id) {
      $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_rujukan', $id)->oneArray();
      $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_rujukan', $id)->oneArray();
      $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
      $print_sep['nama_instansi'] = $this->core->getSettings('nama_instansi');
      $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
      $this->tpl->set('print_sep', $print_sep);
      echo $this->tpl->draw(MODULES.'/pendaftaran/view/admin/cetak.sep.html', true);
      exit();
    }
    /**
    * check if pasien already exists
    * @return array
    */
    private function _pasienAlreadyExists($id = null)
    {
        $date = date('Y-m-d');
        $cek_no_rawat = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->where('tgl_registrasi', $date)->count();

        if (!$cek_no_rawat) {    // new
            $count = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tgl_registrasi', $_POST['tgl_registrasi'])->count();
        } else {        // edit
            $count = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tgl_registrasi', $_POST['tgl_registrasi'])->where('no_rawat', '<>', $_POST['no_rawat'])->count();
        }
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function _cekStatusBayar($id = null)
    {
        $date = date('Y-m-d');
        $cek_no_rawat = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->count();

        if (!$cek_no_rawat) {    // new
          $count = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('status_bayar', 'Belum Bayar')->count();
        }

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function _cekLimitKuota($id = null, $tgl_registrasi = null, $booking = null)
    {
        $tanggal=$_POST['tgl_registrasi'];
        $tentukan_hari=date('D',strtotime($tanggal));
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

        $cek_register = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_dokter', $_POST['kd_dokter'])->where('tgl_registrasi', $_POST['tgl_registrasi'])->oneArray();
        if($_POST['booking']) {
            $cek_register = $this->db('booking_registrasi')->select(['count' => 'COUNT(DISTINCT no_rkm_medis)'])->where('kd_dokter', $_POST['kd_dokter'])->where('tanggal_periksa', $_POST['tgl_registrasi'])->oneArray();
        }
        $cek_limit = $this->db('jadwal')->where('kd_dokter', $_POST['kd_dokter'])->where('hari_kerja', $hari)->oneArray();
        $limit = $cek_limit['kuota']-$cek_register['count'];

        if ($limit <= 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/pendaftaran/js/admin/pendaftaran.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/pendaftaran/css/admin/pendaftaran.css');
        exit();
    }

    public function getCssProfile()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/pendaftaran/css/admin/pendaftaran_profile.css');
        exit();
    }

    private function _addProfileHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'pendaftaran', 'cssprofile']));
        $this->core->addJS(url([ADMIN, 'pendaftaran', 'javascript']), 'footer');
    }
    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'pendaftaran', 'css']));
        $this->core->addJS(url([ADMIN, 'pendaftaran', 'javascript']), 'footer');
    }

    private function _setUmur($tanggal)
    {
        list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
        list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tanggal)));
        $umur = $cY - $Y;
        return $umur;
    }

}
