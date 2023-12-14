<?php
namespace Plugins\Laboratorium;

use Systems\AdminModule;
use Plugins\Icd\DB_ICD;
use Systems\Lib\QRCode;
use Systems\Lib\Fpdf\PDF_MC_Table;
use Systems\Lib\PHPMailer\PHPMailer;
use Systems\Lib\PHPMailer\SMTP;
use Systems\Lib\PHPMailer\Exception;

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

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='', $status_pulang='', $status_bayar ='', $type ='')
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

        $poliklinik = $this->db('poliklinik')->where('kd_poli', $this->settings('settings', 'laboratorium'))->oneArray();

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
        $_POST['kd_poli'] = $this->settings('settings', 'laboratorium');

        $query = $this->db('reg_periksa')->save($_POST);
      } else {
        $query = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->valu([
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
      $pj_lab = $this->db('dokter')->where('kd_dokter', $this->settings->get('settings.pj_laboratorium'))->oneArray();
      $qr = QRCode::getMinimumQRCode($pj_lab['nm_dokter'], QR_ERROR_CORRECT_LEVEL_L);
      $im = $qr->createImage(4, 4);
      imagepng($im, BASE_DIR .'/'.ADMIN.'/tmp/qrcode.png');
      imagedestroy($im);
      $qrCode = "../../".ADMIN."/tmp/qrcode.png";

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      $dokter_perujuk = $this->db('periksa_lab')
        ->join('pegawai', 'pegawai.nik=periksa_lab.dokter_perujuk')
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('tgl_periksa', $_GET['tgl_periksa'])
        ->where('jam', $_GET['jam'])
        ->oneArray();

      $rows_periksa_lab = $this->db('periksa_lab')
      ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
      ->where('no_rawat', $_GET['no_rawat'])
      ->where('tgl_periksa', $_GET['tgl_periksa'])
      ->where('jam', $_GET['jam'])
      ->where('periksa_lab.status', $_GET['status'])
      ->toArray();

      $periksa_lab = [];
      $jumlah_total_lab = 0;
      $no_lab = 1;
      foreach ($rows_periksa_lab as $row) {
        $jumlah_total_lab += $row['biaya'];
        $row['nomor'] = $no_lab++;
        $row['detail_periksa_lab'] = $this->db('detail_periksa_lab')
          ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
          ->where('detail_periksa_lab.no_rawat', $_GET['no_rawat'])
          ->where('detail_periksa_lab.tgl_periksa', $_GET['tgl_periksa'])
          ->where('detail_periksa_lab.jam', $_GET['jam'])
          ->where('detail_periksa_lab.kd_jenis_prw', $row['kd_jenis_prw'])
          ->toArray();
        $periksa_lab[] = $row;
      }

      $pdf = new PDF_MC_Table('P','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image('../'.$this->settings->get('settings.logo'), 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->settings->get('settings.nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->settings->get('settings.alamat').' - '.$this->settings->get('settings.kota'));
      $pdf->Text(30, 25, $this->settings->get('settings.nomor_telepon').' - '.$this->settings->get('settings.email'));
      $pdf->Line(10, 30, 205, 30);
      $pdf->Line(10, 31, 205, 31);

      //make a dummy empty cell as a vertical spacer
      $pdf->Cell(189 ,30,'',0,1);//end of line

      //billing address
      $pdf->SetFont('Arial','',12);
      $pdf->Cell(45 ,6,'Nama',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['nm_pasien'],0,1);
      $pdf->Cell(45 ,6,'Umur',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['umur'],0,1);
      $pdf->Cell(45 ,6,'Poli/Ruangan',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['nm_poli'],0,1);
      $pdf->Cell(45 ,6,'Dokter PJ',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pj_lab['nm_dokter'],0,1);
      $pdf->Cell(45 ,6,'Dokter Pengirim',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$dokter_perujuk['nama'],0,1);
      $pdf->Cell(45 ,6,'Tanggal',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['tgl_registrasi'],0,1);

      $pdf->SetXY(10, 85);
      $pdf->SetFont('Arial','B',14);
      $pdf->Cell(0, 4, 'Hasil Pemeriksaan Laboratorium', 0, 1, 'C', false);

      //make a dummy empty cell as a vertical spacer
      $pdf->Cell(205 ,2,'',0,1);//end of line
      //invoice contents
      $pdf->SetFont('Arial', 'B', 11);
      $pdf->SetWidths(array(10,85,25,25,25,25));
      $pdf->Row(array('No','Pemeriksaan','Hasil','Rujukan','Satuan','Keterangan'));
      $pdf->SetFont('Arial', '', 10);
      $no_lab_pdf = 1;
      foreach ($rows_periksa_lab as $row) {
        $row['nomor'] = $no_lab_pdf++;
        $rows2 = $this->db('detail_periksa_lab')
          ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
          ->where('detail_periksa_lab.no_rawat', $_GET['no_rawat'])
          ->where('detail_periksa_lab.kd_jenis_prw', $row['kd_jenis_prw'])
          ->toArray();
        $pdf->SetWidths(array(10,185));
        $pdf->Row(array($row['nomor'],$row['nm_perawatan']));
        foreach ($rows2 as $row2) {
          $pdf->SetWidths(array(10,85,25,25,25,25));
          $pdf->Row(array('',$row2['Pemeriksaan'],$row2['nilai'],$row2['nilai_rujukan'],$row2['satuan'],$row2['keterangan']));
        }
      }

      $pdf->Cell(189 ,10,'',0,1);//end of line

      $pdf->SetFont('Arial','',11);

      $pdf->Cell(120 ,5,'',0,0);
      $pdf->Cell(69 ,10,$settings['kota'].', '.date('Y-m-d'),0,1);//end of line

      $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
      //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
      $im=$qr->createImage(4,4);
      imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
      imagedestroy($im);

      $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";

      $pdf->Cell(120 ,5,'',0,0);
      $pdf->Cell(64, 5, $pdf->Image($image, $pdf->GetX(), $pdf->GetY(),30,30,'png'), 0, 0, 'C', false );
      $pdf->Cell(189 ,32,'',0,1);//end of line
      $pdf->Cell(120 ,5,'',0,0);
      $pdf->Cell(69 ,5,$this->core->getUserInfo('fullname', null, true),0,1);//end of line

      $filename = convertNorawat($dokter_perujuk['no_rawat']).'_'.$dokter_perujuk['kd_jenis_prw'].'_'.$dokter_perujuk['tgl_periksa'];
      if (file_exists(UPLOADS.'/laboratorium/'.$filename.'.pdf')) {
        unlink(UPLOADS.'/laboratorium/'.$filename.'.pdf');
      }

      $pdf->Output('F', UPLOADS.'/laboratorium/'.$filename.'.pdf', true);
      //$pdf->Output('cetak'.date('Y-m-d').'.pdf','I');

      echo $this->draw('cetakhasil.html', [
        'periksa_lab' => $periksa_lab,
        'jumlah_total_lab' => $jumlah_total_lab,
        'qrCode' => $qrCode,
        'pj_lab' => $pj_lab['nm_dokter'],
        'dokter_perujuk' => $dokter_perujuk['nama'],
        'pasien' => $pasien,
        'filename' => $filename,
        'no_rawat' => $_GET['no_rawat'],
        'wagateway' => $this->settings->get('wagateway')
      ]);
      exit();
    }

    public function getCetakPermintaan()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $pj_lab = $this->db('dokter')->where('kd_dokter', $this->settings->get('settings.pj_laboratorium'))->oneArray();

      $qr = QRCode::getMinimumQRCode($pj_lab['nm_dokter'], QR_ERROR_CORRECT_LEVEL_L);
      $im = $qr->createImage(4, 4);
      imagepng($im, BASE_DIR .'/'.ADMIN.'/tmp/qrcode.png');
      imagedestroy($im);
      $qrCode = "../../".ADMIN."/tmp/qrcode.png";

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      $dokter_perujuk = $this->db('permintaan_lab')
        ->join('pegawai', 'pegawai.nik=permintaan_lab.dokter_perujuk')
        ->where('noorder', $_GET['noorder'])
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('permintaan_lab.status', strtolower($_GET['status']))
        ->group('no_rawat')
        ->oneArray();

      $rows_permintaan_lab = $this->db('permintaan_lab')
        ->join('dokter', 'dokter.kd_dokter=permintaan_lab.dokter_perujuk')
        ->where('noorder', $_GET['noorder'])
        ->where('no_rawat', $_GET['no_rawat'])
        ->where('permintaan_lab.status', strtolower($_GET['status']))
        ->toArray();
      $permintaan_laboratorium = [];
      foreach ($rows_permintaan_lab as $row) {
        $rows2 = $this->db('permintaan_pemeriksaan_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=permintaan_pemeriksaan_lab.kd_jenis_prw')
          ->where('permintaan_pemeriksaan_lab.noorder', $row['noorder'])
          ->toArray();
          foreach ($rows2 as $row2) {
            $row2['noorder'] = $row2['noorder'];
            $row2['kd_jenis_prw'] = $row2['kd_jenis_prw'];
            $row2['stts_bayar'] = $row2['stts_bayar'];
            $row2['nm_perawatan'] = $row2['nm_perawatan'];
            $row2['kd_pj'] = $row2['kd_pj'];
            $row2['status'] = $row2['status'];
            $row2['kelas'] = $row2['kelas'];
            $row2['kategori'] = $row2['kategori'];
            $row2['template_laboratorium'] = $this->db('template_laboratorium')->where('kd_jenis_prw', $row2['kd_jenis_prw'])->toArray();
            $row['permintaan_pemeriksaan_lab'][] = $row2;
          }
        $permintaan_laboratorium[] = $row;
      }

      echo $this->draw('cetakpermintaan.html', [
        'permintaan_laboratorium' => $permintaan_laboratorium,
        'qrCode' => $qrCode,
        'pj_lab' => $pj_lab['nm_dokter'],
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

    public function postSaveDetail($type = "ralan")
    {
      if($_POST['kat'] == 'laboratorium') {
        $jns_perawatan = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        $periksa_lab = $this->db('periksa_lab')
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
            'kd_dokter' => $this->settings->get('settings.pj_laboratorium'),
            'status' => $_POST['status']
          ]);
        if($periksa_lab) {
          $template_laboratorium = $this->db('template_laboratorium')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->toArray();
          foreach ($template_laboratorium as $row) {
            $this->db('detail_periksa_lab')
              ->save([
                'no_rawat' => $_POST['no_rawat'],
                'kd_jenis_prw' => $_POST['kd_jenis_prw'],
                'tgl_periksa' => $_POST['tgl_perawatan'],
                'jam' => $_POST['jam_rawat'],
                'id_template' => $row['id_template'],
                'nilai' => '',
                'nilai_rujukan' => $row['nilai_rujukan_ld'],
                'keterangan' => '',
                'bagian_rs' => $row['bagian_rs'],
                'bhp' => $row['bhp'],
                'bagian_perujuk' => $row['bagian_perujuk'],
                'bagian_dokter' => $row['bagian_dokter'],
                'bagian_laborat' => $row['bagian_laborat'],
                'kso' => $row['kso'],
                'menejemen' => $row['menejemen'],
                'biaya_item' => $row['biaya_item']
              ]);
          }
        }
      }

      exit();
    }

    public function anySaveNilai()
    {
      $this->db('detail_periksa_lab')
        ->where('no_rawat', $_REQUEST['no_rawat'])
        ->where('tgl_periksa', $_REQUEST['tgl_periksa'])
        ->where('jam', $_REQUEST['jam'])
        ->where('id_template', $_REQUEST['id_template'])
        ->save([
          'nilai' => $_REQUEST['value']
        ]);

      exit();
    }

    public function anySaveKeterangan()
    {
      $this->db('detail_periksa_lab')
        ->where('no_rawat', $_REQUEST['no_rawat'])
        ->where('tgl_periksa', $_REQUEST['tgl_periksa'])
        ->where('jam', $_REQUEST['jam'])
        ->where('id_template', $_REQUEST['id_template'])
        ->save([
          'keterangan' => $_REQUEST['value']
        ]);
      exit();
    }

    public function postHapusLaboratorium()
    {
      $periksa_lab = $this->db('periksa_lab')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->where('status', 'Ralan')
      ->delete();
      if($periksa_lab) {
        $this->db('detail_periksa_lab')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_periksa', $_POST['tgl_perawatan'])
        ->where('jam', $_POST['jam_rawat'])
        ->delete();
      }
      exit();
    }

    public function anyRincian()
    {

      $rows = $this->db('permintaan_lab')
        ->join('dokter', 'dokter.kd_dokter=permintaan_lab.dokter_perujuk')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('permintaan_lab.status', strtolower($_POST['status']))
        ->toArray();
      $laboratorium = [];
      foreach ($rows as $row) {
        $rows2 = $this->db('permintaan_pemeriksaan_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=permintaan_pemeriksaan_lab.kd_jenis_prw')
          ->where('permintaan_pemeriksaan_lab.noorder', $row['noorder'])
          ->toArray();
          $row['permintaan_pemeriksaan_lab'] = [];
          foreach ($rows2 as $row2) {
            $row2['noorder'] = $row2['noorder'];
            $row2['kd_jenis_prw'] = $row2['kd_jenis_prw'];
            $row2['stts_bayar'] = $row2['stts_bayar'];
            $row2['nm_perawatan'] = $row2['nm_perawatan'];
            $row2['kd_pj'] = $row2['kd_pj'];
            $row2['status'] = $row2['status'];
            $row2['kelas'] = $row2['kelas'];
            $row2['kategori'] = $row2['kategori'];
            $rows3 = $this->db('permintaan_detail_permintaan_lab')->where('noorder', $row2['noorder'])->where('kd_jenis_prw', $row2['kd_jenis_prw'])->toArray();
            $row2['permintaan_detail_permintaan_lab'] = [];
            foreach ($rows3 as $row3) {
              $row3['template_laboratorium'] = $this->db('template_laboratorium')->where('kd_jenis_prw', $row3['kd_jenis_prw'])->where('id_template', $row3['id_template'])->oneArray();
              $row2['permintaan_detail_permintaan_lab'][] = $row3;
            }
            $row['permintaan_pemeriksaan_lab'][] = $row2;
          }
        $laboratorium[] = $row;
      }

      $rows_periksa_lab = $this->db('periksa_lab')
      ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
      ->join('dokter', 'dokter.kd_dokter=periksa_lab.dokter_perujuk')
      ->where('periksa_lab.status', $_POST['status'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->toArray();

      $periksa_lab = [];
      $no_lab = 1;
      foreach ($rows_periksa_lab as $row) {
        $row['nomor'] = $no_lab++;
        $row['detail_periksa_lab'] = $this->db('detail_periksa_lab')
          ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
          ->where('detail_periksa_lab.no_rawat', $_POST['no_rawat'])
          ->where('detail_periksa_lab.tgl_periksa', $row['tgl_periksa'])
          ->where('detail_periksa_lab.jam', $row['jam'])
          ->where('detail_periksa_lab.kd_jenis_prw', $row['kd_jenis_prw'])
          ->toArray();
        $periksa_lab[] = $row;
      }

      echo $this->draw('rincian.html', ['periksa_lab' => $periksa_lab, 'no_rawat' => $_POST['no_rawat'], 'laboratorium' => $laboratorium]);
      exit();
    }

    public function postHapusPermintaanLaboratorium()
    {
      $this->db('permintaan_lab')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('noorder', $_POST['noorder'])
      ->where('tgl_permintaan', $_POST['tgl_permintaan'])
      ->where('jam_permintaan', $_POST['jam_permintaan'])
      ->where('status', 'Ralan')
      ->delete();
      exit();
    }

    public function getDetailPermintaan($noorder, $kd_jenis_prw)
    {
      $rows = $this->db('permintaan_detail_permintaan_lab')->where('noorder', $noorder)->where('kd_jenis_prw', $kd_jenis_prw)->toArray();
      $detail_permintaan_lab = [];
      foreach ($rows as $row) {
        $row['template_laboratorium'] = $this->db('template_laboratorium')->where('kd_jenis_prw', $row['kd_jenis_prw'])->where('id_template', $row['id_template'])->oneArray();
        $detail_permintaan_lab[] = $row;
      }
      $this->tpl->set('detail', $detail_permintaan_lab);
      echo $this->tpl->draw(MODULES.'/laboratorium/view/admin/details.html', true);
      exit();
    }

    public function postValidasiPermintaanLab()
    {
      $permintaan_lab = $this->db('permintaan_lab')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->oneArray();
      $validasi_permintaan = $this->db('permintaan_lab')->where('no_rawat', $_POST['no_rawat'])->where('noorder', $_POST['noorder'])->save(['tgl_sampel' => date('Y-m-d'), 'jam_sampel' => date('H:i:s')]);
      $permintaan_pemeriksaan_lab = $this->db('permintaan_pemeriksaan_lab')->where('noorder', $_POST['noorder'])->toArray();
      //var_dump($permintaan_lab);
      foreach ($permintaan_pemeriksaan_lab as $row) {
        $jns_perawatan = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
        $periksa_lab = $this->db('periksa_lab')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'nip' => $this->core->getUserInfo('username', null, true),
            'kd_jenis_prw' => $row['kd_jenis_prw'],
            'tgl_periksa' => $_POST['tgl_permintaan'],
            'jam' => $_POST['jam_permintaan'],
            'dokter_perujuk' => $permintaan_lab['dokter_perujuk'],
            'bagian_rs' => $jns_perawatan['bagian_rs'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
            'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
            'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya' => $jns_perawatan['total_byr'],
            'kd_dokter' => $this->settings->get('settings.pj_laboratorium'),
            'status' => $_POST['status'],
            'kategori' => 'PK'
          ]);
        //var_dump($permintaan_pemeriksaan_lab);
        $permintaan_detail_permintaan_lab = $this->db('permintaan_detail_permintaan_lab')->where('noorder', $_POST['noorder'])->where('kd_jenis_prw', $row['kd_jenis_prw'])->toArray();
        foreach ($permintaan_detail_permintaan_lab as $row) {
          $template_laboratorium = $this->db('template_laboratorium')->where('kd_jenis_prw', $row['kd_jenis_prw'])->where('id_template', $row['id_template'])->oneArray();
          $this->db('detail_periksa_lab')
            ->save([
              'no_rawat' => $_POST['no_rawat'],
              'kd_jenis_prw' => $row['kd_jenis_prw'],
              'tgl_periksa' => $_POST['tgl_permintaan'],
              'jam' => $_POST['jam_permintaan'],
              'id_template' => $row['id_template'],
              'nilai' => '',
              'nilai_rujukan' => $template_laboratorium['nilai_rujukan_ld'],
              'keterangan' => '',
              'bagian_rs' => $template_laboratorium['bagian_rs'],
              'bhp' => $template_laboratorium['bhp'],
              'bagian_perujuk' => $template_laboratorium['bagian_perujuk'],
              'bagian_dokter' => $template_laboratorium['bagian_dokter'],
              'bagian_laborat' => $template_laboratorium['bagian_laborat'],
              'kso' => $template_laboratorium['kso'],
              'menejemen' => $template_laboratorium['menejemen'],
              'biaya_item' => $template_laboratorium['biaya_item']
            ]);
          //var_dump($permintaan_detail_permintaan_lab);
        }
      }
      exit();
    }

    public function postHapusDetailPermintaan()
    {
      $this->db('permintaan_detail_permintaan_lab')
        ->where('noorder', $_POST['noorder'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('id_template', $_POST['id_template'])
        ->delete();
      exit();
    }

    public function anyLayananLab()
    {
      $layanan = $this->db('jns_perawatan_lab')
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
      $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $this->settings('settings', 'laboratorium'))->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
      if(empty($max_id['no_reg'])) {
        $max_id['no_reg'] = '000';
      }
      $_next_no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));
      echo $_next_no_reg;
      exit();
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }

    public function getHasilPdf()
    {

      $tmp = $this->db('mlite_temporary')->toArray();
      $settings = $this->settings('settings');
      $logo = $this->settings->get('settings.logo');
      $pj_lab = $this->db('dokter')->where('kd_dokter', $this->settings->get('settings.pj_laboratorium'))->oneArray();

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      $dokter_perujuk = $this->db('periksa_lab')
        ->join('pegawai', 'pegawai.nik=periksa_lab.dokter_perujuk')
        ->where('no_rawat', $_GET['no_rawat'])
        ->group('no_rawat')
        ->oneArray();

      $rows_periksa_lab = $this->db('periksa_lab')
      ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
      ->where('no_rawat', $_GET['no_rawat'])
      ->toArray();

      $periksa_lab = [];
      $jumlah_total_lab = 0;
      $no_lab = 1;
      foreach ($rows_periksa_lab as $row) {
        $jumlah_total_lab += $row['biaya'];
        $row['nomor'] = $no_lab++;
        $row['detail_periksa_lab'] = $this->db('detail_periksa_lab')
          ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
          ->where('detail_periksa_lab.no_rawat', $_GET['no_rawat'])
          ->where('detail_periksa_lab.kd_jenis_prw', $row['kd_jenis_prw'])
          ->toArray();
        $periksa_lab[] = $row;
      }

      $pdf = new PDF_MC_Table('P','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image('../'.$logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->settings->get('settings.nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->settings->get('settings.alamat').' - '.$this->settings->get('settings.kota'));
      $pdf->Text(30, 25, $this->settings->get('settings.nomor_telepon').' - '.$this->settings->get('settings.email'));
      $pdf->Line(10, 30, 205, 30);
      $pdf->Line(10, 31, 205, 31);

      //make a dummy empty cell as a vertical spacer
      $pdf->Cell(189 ,30,'',0,1);//end of line

      //billing address
      $pdf->SetFont('Arial','',12);
      $pdf->Cell(45 ,6,'Nama',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['nm_pasien'],0,1);
      $pdf->Cell(45 ,6,'Umur',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['umur'],0,1);
      $pdf->Cell(45 ,6,'Poli/Ruangan',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['nm_poli'],0,1);
      $pdf->Cell(45 ,6,'Dokter PJ',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pj_lab['nm_dokter'],0,1);
      $pdf->Cell(45 ,6,'Dokter Pengirim',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$dokter_perujuk['nama'],0,1);
      $pdf->Cell(45 ,6,'Tanggal',0,0);//end of line
      $pdf->Cell(5 ,6,':',0,0);//end of line
      $pdf->Cell(150 ,6,$pasien['tgl_registrasi'],0,1);

      $pdf->SetXY(10, 85);
      $pdf->SetFont('Arial','B',14);
      $pdf->Cell(0, 4, 'Hasil Pemeriksaan Laboratorium', 0, 1, 'C', false);

      //make a dummy empty cell as a vertical spacer
      $pdf->Cell(205 ,2,'',0,1);//end of line
      //invoice contents
      /*
      $pdf->SetFont('Arial','B',12);

      $pdf->Cell(10 ,7,'No',1,0);
      $pdf->Cell(85 ,7,'Pemeriksaan',1,0);
      $pdf->Cell(25 ,7,'Hasil',1,0);
      $pdf->Cell(25 ,7,'Rujukan',1,0);
      $pdf->Cell(25 ,7,'Satuan',1,0);
      $pdf->Cell(25 ,7,'Keterangan',1,1);//end of line
      */

      $pdf->SetFont('Arial', 'B', 11);
      $pdf->SetWidths(array(10,85,25,25,25,25));
      $pdf->Row(array('No','Pemeriksaan','Hasil','Rujukan','Satuan','Keterangan'));
      $pdf->SetFont('Arial', '', 10);
      $no_lab_pdf = 1;
      foreach ($rows_periksa_lab as $row) {
        $row['nomor'] = $no_lab_pdf++;
        $rows2 = $this->db('detail_periksa_lab')
          ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
          ->where('detail_periksa_lab.no_rawat', $_GET['no_rawat'])
          ->where('detail_periksa_lab.kd_jenis_prw', $row['kd_jenis_prw'])
          ->toArray();
        $pdf->SetWidths(array(10,185));
        $pdf->Row(array($row['nomor'],$row['nm_perawatan']));
        foreach ($rows2 as $row2) {
          $pdf->SetWidths(array(10,85,25,25,25,25));
          $pdf->Row(array('','',$row2['nilai'],$row2['nilai_rujukan'],$row2['satuan'],$row2['keterangan']));
        }
      }

      $pdf->Cell(189 ,10,'',0,1);//end of line

      $pdf->SetFont('Arial','',11);

      $pdf->Cell(120 ,5,'',0,0);
      $pdf->Cell(69 ,10,$settings['kota'].', '.date('Y-m-d'),0,1);//end of line

      $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
      //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
      $im=$qr->createImage(4,4);
      imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
      imagedestroy($im);

      $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";

      $pdf->Cell(120 ,5,'',0,0);
      $pdf->Cell(64, 5, $pdf->Image($image, $pdf->GetX(), $pdf->GetY(),30,30,'png'), 0, 0, 'C', false );
      $pdf->Cell(189 ,32,'',0,1);//end of line
      $pdf->Cell(120 ,5,'',0,0);
      $pdf->Cell(69 ,5,$this->core->getUserInfo('fullname', null, true),0,1);//end of line

      $filename = convertNorawat($dokter_perujuk['no_rawat']).'_'.$dokter_perujuk['kd_jenis_prw'].'_'.$dokter_perujuk['tgl_periksa'];
      if (file_exists(UPLOADS.'/laboratorium/'.$filename.'.pdf')) {
        unlink(UPLOADS.'/laboratorium/'.$filename.'.pdf');
      }

      //$pdf->Output('F', UPLOADS.'/laboratorium/'.$filename.'.pdf', true);

      $pdf->Output('cetak'.date('Y-m-d').'.pdf','I');

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
      $temp  = @file_get_contents(MODULES."/laboratorium/email/email.send.html");

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
      $mail->AddStringAttachment($binary_content, "hasil_laboratorium.pdf", $encoding = 'base64', $type = 'application/pdf');

      // Setting the email content
      $mail->IsHTML(true);
      $mail->Subject = "Hasil pemeriksaan laboratorium anda di ".$this->core->settings->get('settings.nama_instansi');
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
        echo $this->draw(MODULES.'/laboratorium/js/admin/laboratorium.js');
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
        $this->core->addJS(url([ADMIN, 'laboratorium', 'javascript']), 'footer');
    }

    protected function data_icd($table)
    {
        return new DB_ICD($table);
    }

}
