<?php
namespace Plugins\Radiologi;

use Systems\AdminModule;
use Systems\Lib\QRCode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function anyManage($type = "ralan")
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';
        $status_pulang = '';

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
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_pulang, $status_bayar, $type);
        return $this->draw('manage.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'type' => $type]);
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';
        $status_pulang = '';
        $type = isset_or($_POST['status']);

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
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_pulang, $status_bayar, $type);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'type' => $type]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='', $status_pulang='', $status_bayar='', $type='')
    {
        $this->_addHeaderFiles();

        $this->assign['poliklinik']     = $this->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
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

        if($type == 'ranap') {
          $sql = "SELECT
              kamar_inap.*,
              reg_periksa.*,
              pasien.*,
              kamar.*,
              bangsal.*,
              penjab.*
            FROM
              kamar_inap,
              reg_periksa,
              pasien,
              kamar,
              bangsal,
              penjab
            WHERE
              kamar_inap.no_rawat=reg_periksa.no_rawat
            AND
              reg_periksa.no_rkm_medis=pasien.no_rkm_medis
            AND
              kamar_inap.kd_kamar=kamar.kd_kamar
            AND
              bangsal.kd_bangsal=kamar.kd_bangsal
            AND
              reg_periksa.kd_pj=penjab.kd_pj";

          if($status_periksa == '') {
            $sql .= " AND kamar_inap.stts_pulang = '-'";
          }
          if($status_periksa == 'belum') {
            $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'";
          }
          if($status_periksa == 'selesai') {
            $sql .= " AND kamar_inap.stts_pulang != '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'";
          }
          if($status_periksa == 'lunas') {
            $sql .= " AND kamar_inap.stts_pulang != '-' AND kamar_inap.tgl_keluar BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'";
          }

        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
          $row['dokter'] = $dpjp_ranap;
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
          if($type == 'ranap') {
            $this->assign['kamar_inap'] = $this->db('kamar_inap')
              ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
              ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
              ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
              ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
              ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
              ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
              ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
              ->oneArray();
          }
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
          if($type == 'ranap') {
            $this->assign['reg_periksa'] = [
              'tgl_masuk' => date('Y-m-d'),
              'jam_masuk' => date('H:i:s'),
              'tgl_keluar' => date('Y-m-d'),
              'jam_keluar' => date('H:i:s'),
              'no_rkm_medis' => '',
              'nm_pasien' => '',
              'tgl_lahir' => '',
              'jk' => '',
              'alamat' => '',
              'no_tlp' => '',
              'no_rawat' => '',
              'no_reg' => '',
              'kd_dokter' => '',
              'kd_kamar' => '',
              'kd_pj' => '',
              'diagnosa_awal' => '',
              'diagnosa_akhir' => '',
              'stts_pulang' => '',
              'lama' => ''
            ];
          }
        }
    }

    public function anyForm()
    {

      $this->assign['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
      $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
      $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
      $this->assign['no_rawat'] = '';
      $this->assign['no_reg']     = '';
      $this->assign['tgl_registrasi']= date('Y-m-d');
      $this->assign['jam_reg']= date('H:i:s');
      if (isset($_POST['no_rawat'])){
        $this->assign['reg_periksa'] = $this->db('reg_periksa')
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
            $stts_daftar ="Transaki tanggal ".date('Y-m-d', strtotime($rawat['tgl_registrasi']))." belum diselesaikan" ;
            $stts_daftar_hidden = $stts_daftar;
            if($this->settings->get('settings.cekstatusbayar') == 'false'){
              $stts_daftar_hidden = 'Lama';
            }
            $bg_status = 'text-danger';
          } else {
            $result = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
            if($result >= 1) {
              $stts_daftar = 'Lama';
              $bg_status = 'text-info';
              $stts_daftar_hidden = $stts_daftar;
            } else {
              $stts_daftar = 'Baru';
              $bg_status = 'text-success';
              $stts_daftar_hidden = $stts_daftar;
            }
          }
        echo $this->draw('stts.daftar.html', ['stts_daftar' => $stts_daftar, 'stts_daftar_hidden' => $stts_daftar_hidden, 'bg_status' =>$bg_status]);
      } else {
        $rawat = $this->db('reg_periksa')
          ->where('no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('stts.daftar.html', ['stts_daftar' => $rawat['stts_daftar']]);
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

        $poliklinik = $this->db('poliklinik')->where('kd_poli', $this->settings('settings', 'radiologi'))->oneArray();

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
        $_POST['kd_poli'] = $this->settings('settings', 'radiologi');

        $query = $this->db('reg_periksa')->save($_POST);
      } else {
        $query = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
          'kd_dokter' => $_POST['kd_dokter'],
          'kd_pj' => $_POST['kd_pj']
        ]);
      }
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

    public function getCetakHasil()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $pj_radiologi = $this->db('dokter')->where('kd_dokter', $this->settings->get('settings.pj_radiologi'))->oneArray();
      $qr = QRCode::getMinimumQRCode($pj_radiologi['nm_dokter'], QR_ERROR_CORRECT_LEVEL_L);
      $im = $qr->createImage(4, 4);
      imagepng($im, BASE_DIR .'/'.ADMIN.'/tmp/qrcode.png');
      imagedestroy($im);
      $qrCode = url()."/".ADMIN."/tmp/qrcode.png";

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      $dokter_perujuk = $this->db('periksa_radiologi')
        ->join('pegawai', 'pegawai.nik=periksa_radiologi.dokter_perujuk')
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('tgl_periksa', $_GET['tgl_periksa'])
        ->where('jam', $_GET['jam'])
        ->oneArray();

      // $rows_periksa_radiologi = $this->db('periksa_radiologi')
      // ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
      // ->join('hasil_radiologi', 'hasil_radiologi.no_rawat=periksa_radiologi.no_rawat')
      // ->where('periksa_radiologi.no_rawat', $_GET['no_rawat'])
      // ->where('periksa_radiologi.status', $_GET['status'])
      // ->group('periksa_radiologi.no_rawat')
      // ->group('periksa_radiologi.tgl_periksa')
      // ->group('periksa_radiologi.jam')
      // ->toArray();
      //
      // $periksa_radiologi = [];
      // $jumlah_total_radiologi = 0;
      // $no_radiologi = 1;
      // foreach ($rows_periksa_radiologi as $row) {
      //   $jumlah_total_radiologi += $row['biaya'];
      //   $row['nomor'] = $no_radiologi++;
      //   $row['gambar_radiologi'] = $this->db('gambar_radiologi')
      //     ->where('no_rawat', $_GET['no_rawat'])
      //     ->toArray();
      //   $periksa_radiologi[] = $row;
      // }

      $rows_periksa_radiologi = $this->db('periksa_radiologi')
      ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
      ->join('reg_periksa', 'reg_periksa.no_rawat=periksa_radiologi.no_rawat')
      ->join('dokter', 'dokter.kd_dokter=periksa_radiologi.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->join('petugas', 'petugas.nip=periksa_radiologi.nip')
      ->where('periksa_radiologi.status', $_GET['status'])
      ->where('periksa_radiologi.no_rawat', $_GET['no_rawat'])
      ->where('periksa_radiologi.tgl_periksa', $_GET['tgl_periksa'])
      ->where('periksa_radiologi.jam', $_GET['jam'])
      ->toArray();

      $periksa_radiologi = [];
      $jumlah_total_radiologi = 0;
      $no_radiologi = 1;
      foreach ($rows_periksa_radiologi as $row) {
        $jumlah_total_radiologi += $row['biaya'];
        $row['nomor'] = $no_radiologi++;
        $row['status_periksa'] = $_GET['status'];
        $periksa_radiologi[] = $row;
      }

      $hasil_radiologi = $this->db('hasil_radiologi')
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('tgl_periksa', $_GET['tgl_periksa'])
        ->where('jam', $_GET['jam'])
        ->toArray();

      $gambar_radiologi = $this->db('gambar_radiologi')
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('tgl_periksa', $_GET['tgl_periksa'])
        ->where('jam', $_GET['jam'])
        ->toArray();

      $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
      $im=$qr->createImage(4,4);
      imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
      imagedestroy($im);

      $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";

      $filename = convertNorawat($dokter_perujuk['no_rawat']).'_'.$dokter_perujuk['kd_jenis_prw'].'_'.$dokter_perujuk['tgl_periksa'];
      if (file_exists(UPLOADS.'/radiologi/'.$filename.'.pdf')) {
        unlink(UPLOADS.'/radiologi/'.$filename.'.pdf');
      }

      echo $this->draw('cetakhasil.html', [
        'periksa_radiologi' => $periksa_radiologi,
        'hasil_radiologi' => $hasil_radiologi,
        'gambar_radiologi' => $gambar_radiologi,
        'jumlah_total_radiologi' => $jumlah_total_radiologi,
        'qrCode' => $qrCode,
        'pj_radiologi' => $pj_radiologi['nm_dokter'],
        'dokter_perujuk' => $dokter_perujuk['nama'],
        'pasien' => $pasien,
        'filename' => $filename,
        'no_rawat' => $_GET['no_rawat'],
        'wagateway' => $this->settings->get('wagateway')
      ]);

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4', 
        'orientation' => 'P'
      ]);

      $css = '
      <style>
        del { 
          display: none;
        }
        table {
          padding-top: 1cm;
          padding-bottom: 1cm;
        }
        td, th {
          border-bottom: 1px solid #dddddd;
          padding: 5px;
        }        
        tr:nth-child(even) {
          background-color: #ffffff;
        }
      </style>
      ';
      
      $url = url(ADMIN.'/tmp/cetakhasil.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
      $mpdf->WriteHTML($css);
      $mpdf->WriteHTML($html);
  
      // Output a PDF file save to server
      $mpdf->Output(UPLOADS.'/radiologi/'.$filename.'.pdf','F');

      exit();
    }

    public function getCetakPermintaan()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $pj_radiologi = $this->db('dokter')->where('kd_dokter', $this->settings->get('settings.pj_radiologi'))->oneArray();

      $qr = QRCode::getMinimumQRCode($pj_radiologi['nm_dokter'], QR_ERROR_CORRECT_LEVEL_L);
      $im = $qr->createImage(4, 4);
      imagepng($im, BASE_DIR .'/'.ADMIN.'/tmp/qrcode.png');
      imagedestroy($im);
      $qrCode = url()."/".ADMIN."/tmp/qrcode.png";

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      $dokter_perujuk = $this->db('permintaan_radiologi')
        ->join('pegawai', 'pegawai.nik=permintaan_radiologi.dokter_perujuk')
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('permintaan_radiologi.status', strtolower($_GET['status']))
        ->group('no_rawat')
        ->oneArray();

      $rows_permintaan_radiologi = $this->db('permintaan_radiologi')
        ->join('dokter', 'dokter.kd_dokter=permintaan_radiologi.dokter_perujuk')
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('permintaan_radiologi.status', strtolower($_GET['status']))
        ->toArray();
      $permintaan_radiologi = [];
      foreach ($rows_permintaan_radiologi as $row) {
        $rows2 = $this->db('permintaan_pemeriksaan_radiologi')
          ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=permintaan_pemeriksaan_radiologi.kd_jenis_prw')
          ->where('permintaan_pemeriksaan_radiologi.noorder', $row['noorder'])
          ->toArray();
          foreach ($rows2 as $row2) {
            $row2['noorder'] = $row2['noorder'];
            $row2['kd_jenis_prw'] = $row2['kd_jenis_prw'];
            $row2['stts_bayar'] = $row2['stts_bayar'];
            $row2['nm_perawatan'] = $row2['nm_perawatan'];
            $row2['kd_pj'] = $row2['kd_pj'];
            $row2['status'] = $row2['status'];
            $row2['kelas'] = $row2['kelas'];
            $row['permintaan_pemeriksaan_radiologi'][] = $row2;
          }
        $permintaan_radiologi[] = $row;
      }

      echo $this->draw('cetakpermintaan.html', [
        'permintaan_radiologi' => $permintaan_radiologi,
        'qrCode' => $qrCode,
        'pj_radiologi' => $pj_radiologi['nm_dokter'],
        'dokter_perujuk' => $dokter_perujuk['nama'],
        'pasien' => $pasien,
        'no_rawat' => $_GET['no_rawat']
      ]);
      exit();
    }

    public function postHapus()
    {
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function postSaveDetail()
    {

      if($_POST['kat'] == 'radiologi') {
        $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        $this->db('periksa_radiologi')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'nip' => $this->core->getUserInfo('username', null, true),
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'tgl_periksa' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'dokter_perujuk' => $_POST['kode_provider'],
            'bagian_rs' => $jns_perawatan['bagian_rs'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
            'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
            'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya' => $jns_perawatan['total_byr'],
            'kd_dokter' => $this->settings->get('settings.pj_radiologi'),
            'status' => $_POST['status'],
            'proyeksi' => '',
            'kV' => '',
            'mAS' => '',
            'FFD' => '',
            'BSF' => '',
            'inak' => '',
            'jml_penyinaran' => '',
            'dosis' => ''
          ]);
      }

      exit();
    }

    public function postHapusRadiologi()
    {
      $this->db('periksa_radiologi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->where('status', 'Ralan')
      ->delete();
      exit();
    }

    public function postHapusHasilRadiologi()
    {
      $this->db('hasil_radiologi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->delete();
      $this->db('gambar_radiologi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->delete();
      exit();
    }

    public function anyRincian()
    {

      $rows = $this->db('permintaan_radiologi')
        ->join('dokter', 'dokter.kd_dokter=permintaan_radiologi.dokter_perujuk')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('permintaan_radiologi.status', strtolower($_POST['status']))
        ->toArray();
      $radiologi = [];
      foreach ($rows as $row) {
        $rows2 = $this->db('permintaan_pemeriksaan_radiologi')
          ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=permintaan_pemeriksaan_radiologi.kd_jenis_prw')
          ->where('permintaan_pemeriksaan_radiologi.noorder', $row['noorder'])
          ->toArray();
          $row['permintaan_pemeriksaan_radiologi'] = [];
          foreach ($rows2 as $row2) {
            $row2['noorder'] = $row2['noorder'];
            $row2['kd_jenis_prw'] = $row2['kd_jenis_prw'];
            $row2['stts_bayar'] = $row2['stts_bayar'];
            $row2['nm_perawatan'] = $row2['nm_perawatan'];
            $row2['kd_pj'] = $row2['kd_pj'];
            $row2['status'] = $row2['status'];
            $row2['kelas'] = $row2['kelas'];
            $row['permintaan_pemeriksaan_radiologi'][] = $row2;
          }
        $radiologi[] = $row;
      }

      $rows_periksa_radiologi = $this->db('periksa_radiologi')
      ->join('reg_periksa', 'reg_periksa.no_rawat=periksa_radiologi.no_rawat')
      ->join('dokter', 'dokter.kd_dokter=periksa_radiologi.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->join('petugas', 'petugas.nip=periksa_radiologi.nip')
      ->where('periksa_radiologi.status', $_POST['status'])
      ->where('periksa_radiologi.no_rawat', $_POST['no_rawat'])
      ->group('periksa_radiologi.no_rawat')
      ->group('tgl_periksa')
      ->group('jam')
      ->toArray();

      $periksa_radiologi = [];
      $jumlah_total_radiologi = 0;
      $no_radiologi = 1;
      foreach ($rows_periksa_radiologi as $row) {
        $jumlah_total_radiologi += $row['biaya'];
        $row['nomor'] = $no_radiologi++;
        $row['status_periksa'] = $_POST['status'];
        $row['periksa_radiologi'] = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
          ->where('periksa_radiologi.status', $_POST['status'])
          ->where('no_rawat', $_POST['no_rawat'])
          ->where('tgl_periksa', $row['tgl_periksa'])
          ->where('jam', $row['jam'])
          ->toArray();
        $row['hasil_radiologi'] = $this->db('hasil_radiologi')
          ->where('no_rawat', $_POST['no_rawat'])
          ->where('tgl_periksa', $row['tgl_periksa'])
          ->where('jam', $row['jam'])
          ->toArray();
        $row['gambar_radiologi'] = $this->db('gambar_radiologi')
          ->where('no_rawat', $_POST['no_rawat'])
          ->where('tgl_periksa', $row['tgl_periksa'])
          ->where('jam', $row['jam'])
          ->toArray();
        $periksa_radiologi[] = $row;
      }

      echo $this->draw('rincian.html', ['periksa_radiologi' => $periksa_radiologi, 'jumlah_total_radiologi' => $jumlah_total_radiologi, 'no_rawat' => $_POST['no_rawat'], 'radiologi' => $radiologi]);
      exit();
    }

    public function postValidasiPermintaanRadiologi()
    {
      $permintaan_radiologi = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->oneArray();
      $validasi_permintaan = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->save(['tgl_sampel' => date('Y-m-d'), 'jam_sampel' => date('H:i:s')]);
      $permintaan_pemeriksaan_radiologi = $this->db('permintaan_pemeriksaan_radiologi')->where('noorder', $_POST['noorder'])->toArray();
      foreach ($permintaan_pemeriksaan_radiologi as $row) {
        $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
        $periksa_radiologi = $this->db('periksa_radiologi')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'nip' => $this->core->getUserInfo('username', null, true),
            'kd_jenis_prw' => $row['kd_jenis_prw'],
            'tgl_periksa' => $_POST['tgl_permintaan'],
            'jam' => $_POST['jam_permintaan'],
            'dokter_perujuk' => $permintaan_radiologi['dokter_perujuk'],
            'bagian_rs' => $jns_perawatan['bagian_rs'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
            'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
            'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya' => $jns_perawatan['total_byr'],
            'kd_dokter' => $this->settings->get('settings.pj_radiologi'),
            'status' => $_POST['status'],
            'proyeksi' => '',
            'kV' => '',
            'mAS' => '',
            'FFD' => '',
            'BSF' => '',
            'inak' => '',
            'jml_penyinaran' => '',
            'dosis' => ''
          ]);
      }
      exit();
    }

    public function anyLayananRadiologi()
    {
      $layanan = $this->db('jns_perawatan_radiologi')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['layanan'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('layanan.html', ['layanan' => $layanan]);
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

    public function postMaxid()
    {
      $max_id = $this->db('reg_periksa')->select(['no_rawat' => 'ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0)'])->where('tgl_registrasi', date('Y-m-d'))->oneArray();
      if(empty($max_id['no_rawat'])) {
        $max_id['no_rawat'] = '000000';
      }
      $_next_no_rawat = sprintf('%06s', ($max_id['no_rawat'] + 1));
      $next_no_rawat = date('Y/m/d').'/'.$_next_no_rawat;
      echo $next_no_rawat;
      exit();
    }

    public function postMaxAntrian()
    {
      $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $this->settings('settings', 'radiologi'))->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
      if(empty($max_id['no_reg'])) {
        $max_id['no_reg'] = '000';
      }
      $_next_no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));
      echo $_next_no_reg;
      exit();
    }

    public function postSaveHasil()
    {
      $result = $this->db('hasil_radiologi')
        ->save([
          'no_rawat' => $_POST['no_rawat'],
          'tgl_periksa' => $_POST['tgl_periksa'],
          'jam' => $_POST['jam_periksa'],
          'hasil' => $_POST['hasil']
        ]);
      exit();
    }
    public function postUploadHasil()
    {
        header('Content-type: application/json');
        $dir    = WEBAPPS_PATH.'/radiologi/pages/upload';
        $error    = null;

        if (!file_exists($dir)) {
            mkdir(WEBAPPS_PATH."/radiologi", 0777);
            mkdir(WEBAPPS_PATH."/radiologi/pages", 0777);
            mkdir(WEBAPPS_PATH."/radiologi/pages/upload", 0777);
            mkdir($dir, 0777, true);
        }

        if (isset($_FILES['file']['tmp_name'])) {
            $img = new \Systems\Lib\Image;

            if ($img->load($_FILES['file']['tmp_name'])) {
                $imgPath = $dir.'/'.time().'.'.$img->getInfos('type');
                $img->save($imgPath);
                $result = $this->db('gambar_radiologi')
                  ->save([
                    'no_rawat' => $_POST['no_rawat'],
                    'tgl_periksa' => $_POST['tgl_periksa'],
                    'jam' => $_POST['jam_periksa'],
                    'lokasi_gambar' => 'pages/upload/'.time().'.'.$img->getInfos('type')
                  ]);
                echo json_encode(['status' => 'success', 'result' => url($imgPath)]);
            } else {
                $error = "Upload gagal";
            }

            if ($error) {
                echo json_encode(['status' => 'failure', 'result' => $error]);
            }
        }
        exit();
    }

    public function postKirimEmail() {
      $email = $_POST['email'];
      $nama_lengkap = $_POST['receiver'];
      $file = $_POST['file'];
      $this->sendEmail($email, $nama_lengkap, $file);
      exit();
    }

    private function sendEmail($email, $receiver, $file)
    {
      $binary_content = file_get_contents($file);

      if ($binary_content === false) {
         throw new Exception("Could not fetch remote content from: '$file'");
      }

	    $mail = new PHPMailer(true);
      $temp  = @file_get_contents(MODULES."/radiologi/email/email.send.html");

      $temp  = str_replace("{SITENAME}", $this->core->settings->get('settings.nama_instansi'), $temp);
      $temp  = str_replace("{ADDRESS}", $this->core->settings->get('settings.alamat')." - ".$this->core->settings->get('settings.kota'), $temp);
      $temp  = str_replace("{TELP}", $this->core->settings->get('settings.nomor_telepon'), $temp);
      //$temp  = str_replace("{NUMBER}", $number, $temp);

	    //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // for detailed debug output
      $mail->isSMTP();
      $mail->Host = $this->settings->get('api.apam_smtp_host');
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = $this->settings->get('api.apam_smtp_port');

      $mail->Username = $this->settings->get('api.apam_smtp_username');
      $mail->Password = $this->settings->get('api.apam_smtp_password');

      // Sender and recipient settings
      $mail->setFrom($this->core->settings->get('settings.email'), $this->core->settings->get('settings.nama_instansi'));
      $mail->addAddress($email, $receiver);
      $mail->AddStringAttachment($binary_content, "hasil_radiologi.pdf", $encoding = 'base64', $type = 'application/pdf');

      // Setting the email content
      $mail->IsHTML(true);
      $mail->Subject = "Hasil pemeriksaan radiologi anda di ".$this->core->settings->get('settings.nama_instansi');
      $mail->Body = $temp;

      $mail->send();

      if (!$mail->send()) {
        echo 'Error: ' . $mail->ErrorInfo;
      } else {
        echo 'Pesan email telah dikirim.';
      }

    }

    public function postCekWaktu()
    {
      echo date('H:i:s');
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $this->assign['websocket'] = $this->settings->get('settings.websocket');
        $this->assign['websocket_proxy'] = $this->settings->get('settings.websocket_proxy');
        echo $this->draw(MODULES.'/radiologi/js/admin/radiologi.js', ['mlite' => $this->assign]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'radiologi', 'javascript']), 'footer');
    }

}
