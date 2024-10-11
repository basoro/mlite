<?php

namespace Plugins\Utd;

use Systems\AdminModule;

class Admin extends AdminModule
{

  public function navigation()
  {
      return [
          'Kelola'   => 'manage',
          'Data Pendonor' => 'pendonor',
          'Data Donor' => 'donor',
          'Stok Darah' => 'stokdarah',
          'Komponen Darah' => 'komponendarah'
      ];
  }

  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Data Pendonor', 'url' => url([ADMIN, 'utd', 'pendonor']), 'icon' => 'group', 'desc' => 'Data Pendonor'],
      ['name' => 'Data Donor', 'url' => url([ADMIN, 'utd', 'donor']), 'icon' => 'heart', 'desc' => 'Data Donor'],
      ['name' => 'Stok Darah', 'url' => url([ADMIN, 'utd', 'stokdarah']), 'icon' => 'database', 'desc' => 'Data Donor'],
      ['name' => 'Komponen Darah', 'url' => url([ADMIN, 'utd', 'komponendarah']), 'icon' => 'clipboard', 'desc' => 'Komponen Donor'],
    ];
    return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
  }

  public function getPendonor()
  {
    $this->_addHeaderFiles();
    $pendonor = $this->db('utd_pendonor')
      ->join('propinsi', 'propinsi.kd_prop=utd_pendonor.kd_prop')
      ->join('kabupaten', 'kabupaten.kd_kab=utd_pendonor.kd_kab')
      ->join('kecamatan', 'kecamatan.kd_kec=utd_pendonor.kd_kec')
      ->join('kelurahan', 'kelurahan.kd_kel=utd_pendonor.kd_kel')
      ->toArray();
    return $this->draw('data.pendonor.html', [
      'pendonor' => $pendonor,
      'nomor' => $this->setNoPendonor(),
      'waapitoken' => $this->settings->get('wagateway.token'),
      'waapiphonenumber' => $this->settings->get('wagateway.phonenumber')
    ]);
  }

  public function postSavePendonor()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      if(!$this->db('propinsi')->where('kd_prop', $_POST['kd_prop'])->oneArray()){
        $this->db('propinsi')->save(['kd_prop' => $_POST['kd_prop'], 'nm_prop' => $_POST['nm_prop']]);
      }
      if(!$this->db('kabupaten')->where('kd_kab', $_POST['kd_kab'])->oneArray()){
        $this->db('kabupaten')->save(['kd_kab' => $_POST['kd_kab'], 'nm_kab' => $_POST['nm_kab']]);
      }
      if(!$this->db('kecamatan')->where('kd_kec', $_POST['kd_kec'])->oneArray()){
        $this->db('kecamatan')->save(['kd_kec' => $_POST['kd_kec'], 'nm_kec' => $_POST['nm_kec']]);
      }
      if(!$this->db('kelurahan')->where('nm_kel', $_POST['nm_kel'])->oneArray()){
        $result = $this->db('kelurahan')->select('kd_kel')->desc('kd_kel')->limit(1)->oneArray();
        $_POST['kd_kel'] = $result['kd_kel'] + 1;
        $this->db('kelurahan')->save(['kd_kel' => $_POST['kd_kel'], 'nm_kel' => $_POST['nm_kel']]);
      }
      unset($_POST['nm_prop']);
      unset($_POST['nm_kab']);
      unset($_POST['nm_kec']);
      unset($_POST['nm_kel']);
      $this->db('utd_pendonor')->save($_POST);
      $this->notify('success', 'Data pendonor telah disimpan');
    } else if ($_POST['update']) {
      $no_pendonor = $_POST['no_pendonor'];
      unset($_POST['update']);
      unset($_POST['no_pendonor']);
      if(!$this->db('propinsi')->where('kd_prop', $_POST['kd_prop'])->oneArray()){
        $this->db('propinsi')->save(['kd_prop' => $_POST['kd_prop'], 'nm_prop' => $_POST['nm_prop']]);
      }
      if(!$this->db('kabupaten')->where('kd_kab', $_POST['kd_kab'])->oneArray()){
        $this->db('kabupaten')->save(['kd_kab' => $_POST['kd_kab'], 'nm_kab' => $_POST['nm_kab']]);
      }
      if(!$this->db('kecamatan')->where('kd_kec', $_POST['kd_kec'])->oneArray()){
        $this->db('kecamatan')->save(['kd_kec' => $_POST['kd_kec'], 'nm_kec' => $_POST['nm_kec']]);
      }
      if(!$this->db('kelurahan')->where('nm_kel', $_POST['nm_kel'])->oneArray()){
        $result = $this->db('kelurahan')->select('kd_kel')->desc('kd_kel')->limit(1)->oneArray();
        $_POST['kd_kel'] = $result['kd_kel'] + 1;
        $this->db('kelurahan')->save(['kd_kel' => $_POST['kd_kel'], 'nm_kel' => $_POST['nm_kel']]);
      }
      unset($_POST['nm_prop']);
      unset($_POST['nm_kab']);
      unset($_POST['nm_kec']);
      unset($_POST['nm_kel']);
      $this->db('utd_pendonor')
        ->where('no_pendonor', $no_pendonor)
        ->save($_POST);
      $this->notify('failure', 'Data pendonor telah diubah');
    }
    redirect(url([ADMIN, 'utd', 'pendonor']));
  }

  public function getHapusPendonor($no_pendonor)
  {
    $this->db('utd_pendonor')
      ->where('no_pendonor', $no_pendonor)
      ->delete();
    redirect(url([ADMIN, 'utd', 'pendonor']));
  }

  public function postCetak()
  {
    $this->db()->pdo()->exec("DELETE FROM `mlite_temporary`");
    $cari = $_POST['cari'];
    $this->db()->pdo()->exec("INSERT INTO `mlite_temporary` (
      `temp1`,
      `temp2`,
      `temp3`,
      `temp4`,
      `temp5`,
      `temp6`,
      `temp7`,
      `temp8`,
      `temp9`,
      `temp10`,
      `temp11`,
      `temp12`,
      `temp13`,
      `temp14`
    )
    SELECT *
    FROM `utd_pendonor`
    WHERE (`no_pendonor` LIKE '%$cari%' OR `nama` LIKE '%$cari%' OR `alamat` LIKE '%$cari%')
    ");

    $cetak = $this->db('mlite_temporary')->toArray();
    return $this->draw('cetak.utd.html', ['cetak' => $cetak]);
    exit();
  }

  public function getCetakPendonor()
  {
    $mpdf = new \Mpdf\Mpdf([
      'mode' => 'utf-8',
      'orientation' => 'L'
    ]);

    $mpdf->SetHTMLHeader($this->core->setPrintHeader());
    $mpdf->SetHTMLFooter($this->core->setPrintFooter());
          
    $url = url(ADMIN.'/tmp/cetak.utd.html');
    $html = file_get_contents($url);
    $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);

    // Output a PDF file directly to the browser
    $mpdf->Output();
    exit();      

  }

  public function getDonor()
  {
    $this->_addHeaderFiles();
    $donor = $this->db('utd_donor')
      ->join('utd_pendonor', 'utd_pendonor.no_pendonor=utd_donor.no_pendonor')
      ->join('kelurahan', 'kelurahan.kd_kel=utd_pendonor.kd_kel')
      ->join('kecamatan', 'kecamatan.kd_kec=utd_pendonor.kd_kec')
      ->join('kabupaten', 'kabupaten.kd_kab=utd_pendonor.kd_kab')
      ->join('propinsi', 'propinsi.kd_prop=utd_pendonor.kd_prop')
      ->toArray();
    $pendonor = $this->db('utd_pendonor')->toArray();
    $petugas = $this->db('petugas')->toArray();
    return $this->draw('data.donor.html', ['donor' => $donor, 'pendonor' => $pendonor, 'petugas' => $petugas]);
  }

  public function postSaveDonor()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('utd_donor')->save($_POST);
      $this->notify('success', 'Data donor telah disimpan');
    } else if ($_POST['update']) {
      $no_donor = $_POST['no_donor'];
      unset($_POST['update']);
      unset($_POST['no_donor']);
      $this->db('utd_donor')
        ->where('no_donor', $no_donor)
        ->save($_POST);
      $this->notify('failure', 'Data donor telah diubah');
    }
    redirect(url([ADMIN, 'utd', 'donor']));
  }

  public function getHapusDonor($no_donor)
  {
    $this->db('utd_donor')
      ->where('no_donor', $no_donor)
      ->delete();
    redirect(url([ADMIN, 'utd', 'donor']));
  }

  public function getStokDarah()
  {
    $this->_addHeaderFiles();
    $stokdarah = $this->db('utd_stok_darah')
      ->join('utd_komponen_darah', 'utd_komponen_darah.kode=utd_stok_darah.kode_komponen')
      ->toArray();
    $komponendarah = $this->db('utd_komponen_darah')->toArray();
    return $this->draw('stok.darah.html', ['stokdarah' => $stokdarah, 'komponendarah' => $komponendarah]);
  }

  public function postSaveStokDarah()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('utd_stok_darah')->save($_POST);
      $this->notify('success', 'Data stok darah telah disimpan');
    } else if ($_POST['update']) {
      $no_kantong = $_POST['no_kantong'];
      unset($_POST['update']);
      unset($_POST['no_kantong']);
      $this->db('utd_stok_darah')
        ->where('no_kantong', $no_kantong)
        ->save($_POST);
      $this->notify('failure', 'Data stok darah telah diubah');
    }
    redirect(url([ADMIN, 'utd', 'stokdarah']));
  }

  public function getHapusStokDarah($no_kantong)
  {
    $this->db('utd_stok_darah')
      ->where('no_kantong', $no_kantong)
      ->delete();
    redirect(url([ADMIN, 'utd', 'stokdarah']));
  }

  public function getKomponenDarah()
  {
    $this->_addHeaderFiles();
    $komponendarah = $this->db('utd_komponen_darah')
      ->toArray();
    return $this->draw('komponen.darah.html', ['komponendarah' => $komponendarah]);
  }

  public function postSaveKomponenDarah()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('utd_komponen_darah')->save($_POST);
      $this->notify('success', 'Data komponen darah telah disimpan');
    } else if ($_POST['update']) {
      $kode = $_POST['kode'];
      unset($_POST['update']);
      unset($_POST['kode']);
      $this->db('utd_komponen_darah')
        ->where('kode', $kode)
        ->save($_POST);
      $this->notify('failure', 'Data komponen darah telah diubah');
    }
    redirect(url([ADMIN, 'utd', 'komponendarah']));
  }

  public function getHapusKomponenDarah($kode)
  {
    $this->db('utd_komponen_darah')
      ->where('kode', $kode)
      ->delete();
    redirect(url([ADMIN, 'utd', 'komponendarah']));
  }

  public function anyWilayah()
  {
    $show = isset($_GET['show']) ? $_GET['show'] : "";
    switch($show){
      default:
      break;
      case "caripropinsi":
        if(isset($_POST["query"])){
          $output = '';
          $key = "%".$_POST["query"]."%";
          $rows = $this->db('propinsi')->like('nm_prop', $key)->asc('kd_prop')->limit(10)->toArray();
          $output = '';
          if(count($rows)){
            foreach ($rows as $row) {
              $output .= '<li class="list-group-item link-class">'.$row["kd_prop"].': '.$row["nm_prop"].'</li>';
            }
          }
          echo $output;
        }
      break;
      case "carikabupaten":
        if(isset($_POST["query"])){
          $output = '';
          $key = "%".$_POST["query"]."%";
          $rows = $this->db('kabupaten')->like('nm_kab', $key)->asc('kd_kab')->limit(10)->toArray();
          $output = '';
          if(count($rows)){
            foreach ($rows as $row) {
              $output .= '<li class="list-group-item link-class">'.$row["kd_kab"].': '.$row["nm_kab"].'</li>';
            }
          }
          echo $output;
        }
      break;
      case "carikecamatan":
        if(isset($_POST["query"])){
          $output = '';
          $key = "%".$_POST["query"]."%";
          $rows = $this->db('kecamatan')->like('nm_kec', $key)->asc('kd_kec')->limit(10)->toArray();
          $output = '';
          if(count($rows)){
            foreach ($rows as $row) {
              $output .= '<li class="list-group-item link-class">'.$row["kd_kec"].': '.$row["nm_kec"].'</li>';
            }
          }
          echo $output;
        }
      break;
      case "carikelurahan":
        if(isset($_POST["query"])){
          $output = '';
          $key = "%".$_POST["query"]."%";
          $rows = $this->db('kelurahan')->like('nm_kel', $key)->asc('kd_kel')->limit(10)->toArray();
          $output = '';
          if(count($rows)){
            foreach ($rows as $row) {
              $output .= '<li class="list-group-item link-class">'.$row["kd_kel"].': '.$row["nm_kel"].'</li>';
            }
          }
          echo $output;
        }
      break;
    }
    exit();
  }

  public function getKartuDonor($no_pendonor)
  {
      $pdf=new PDF_Code128('L', 'mm', array(59,98));
      $pdf->AddPage();
      $pdf->SetFont('Arial','',10);
      $pdf->Code128(9,35,$no_pendonor,80,20);
      $pdf->SetFont('Arial','B',16);
      $pdf->SetXY(8,0);
      $pdf->Cell(0,35,$no_pendonor);
      $pdf->Output('kartudonor_'.$no_pendonor.'.pdf','I');
  }

  public function setNoPendonor()
  {
      $date = date('Y-m-d');
      $last_no = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_pendonor,6),signed)),0) FROM utd_pendonor");
      $last_no->execute();
      $last_no = $last_no->fetch();
      if(empty($last_no[0])) {
        $last_no[0] = '000000';
      }
      $next_no = sprintf('%06s', ($last_no[0] + 1));
      $next_no = 'UTD'.$next_no;

      return $next_no;
  }

  public function getCss()
  {
      header('Content-type: text/css');
      echo $this->draw(MODULES.'/utd/css/admin/utd.css');
      exit();
  }

  public function getJavascript()
  {
      header('Content-type: text/javascript');
      echo $this->draw(MODULES.'/utd/js/admin/utd.js');
      exit();
  }

  private function _addHeaderFiles()
  {
      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addCSS(url([ADMIN, 'utd', 'css']));
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
      $this->core->addJS(url('assets/jscripts/jquery.confirm.js'));
      $this->core->addJS(url([ADMIN, 'utd', 'javascript']), 'footer');
  }

}
