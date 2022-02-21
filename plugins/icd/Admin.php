<?php
namespace Plugins\Icd;

use Systems\AdminModule;
use Plugins\Icd\DB_ICD;

class Admin extends AdminModule
{

  public function navigation()
  {
      return [
          'Kelola'   => 'manage',
      ];
  }

  public function getManage()
  {
      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      // JS
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');
      return $this->draw('manage.html');
  }

  public function getICD10()
  {
    $rows_icd10 = $this->data_icd('icd10')->toArray();
    $return_array = array('data'=> $rows_icd10);
    echo json_encode($return_array);
    exit();
  }

  public function getICD9()
  {
    $rows_icd9 = $this->data_icd('icd9')->toArray();
    $return_array = array('data'=> $rows_icd9);
    echo json_encode($return_array);
    exit();
  }

  public function postICD9()
  {

    if(isset($_POST["query"])){
      $output = '';
      $key = "%".$_POST["query"]."%";
      $rows = $this->data_icd('icd9')->like('kode', $key)->orLike('nama', $key)->asc('nama')->limit(10)->toArray();
      $output = '';
      if(count($rows)){
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">'.$row["kode"].': '.$row["nama"].'</li>';
        }
      } else {
        $output .= '<li class="list-group-item link-class">Tidak ada yang cocok.</li>';
      }
      echo $output;
    }

    exit();

  }

  public function postICD10()
  {

    if(isset($_POST["query"])){
      $output = '';
      $key = "%".$_POST["query"]."%";
      $rows = $this->data_icd('icd10')->like('kode', $key)->orLike('nama', $key)->asc('nama')->limit(10)->toArray();
      $output = '';
      if(count($rows)){
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">'.$row["kode"].': '.$row["nama"].'</li>';
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
    if(!$this->db('icd9')->where('kode', $_POST['kode'])->oneArray()){
      $this->db('icd9')->save([
        'kode' => $_POST['kode'],
        'deskripsi_panjang' => $_POST['nama'],
        'deskripsi_pendek' => $_POST['nama']
      ]);
    }
    unset($_POST['nama']);
    $this->db('prosedur_pasien')->save($_POST);
    exit();
  }

  public function postSaveICD10()
  {
    if(!$this->db('penyakit')->where('kd_penyakit', $_POST['kd_penyakit'])->oneArray()){
      $this->db('penyakit')->save([
        'kd_penyakit' => $_POST['kd_penyakit'],
        'nm_penyakit' => $_POST['nama'],
        'ciri_ciri' => '-',
        'keterangan' => '-',
        'kd_ktg' => '-',
        'status' => 'Tidak Menular'
      ]);
    }
    $_POST['status_penyakit'] = 'Baru';
    //if($this->db('diagnosa_pasien')->where('kd_penyakit', $_POST['kd_penyakit'])->oneArray()){
    //  $_POST['status_penyakit'] = 'Lama';
    //}
    unset($_POST['nama']);
    $this->db('diagnosa_pasien')->save($_POST);
    exit();
  }

  public function getDisplay()
  {
    $no_rawat = $_GET['no_rawat'];
    $prosedurs = $this->core->db('prosedur_pasien')
      ->where('no_rawat', $no_rawat)
      ->asc('prioritas')
      ->toArray();
    $prosedur = [];
    foreach ($prosedurs as $row_prosedur) {
      $icd9 = $this->db('icd9')->where('kode', $row_prosedur['kode'])->oneArray();
      $row_prosedur['nama'] = $icd9['deskripsi_panjang'];
      $prosedur[] = $row_prosedur;
    }

    $diagnosas = $this->core->db('diagnosa_pasien')
      ->where('no_rawat', $no_rawat)
      ->asc('prioritas')
      ->toArray();
    $diagnosa = [];
    foreach ($diagnosas as $row_diagnosa) {
      $icd10 = $this->db('penyakit')->where('kd_penyakit', $row_diagnosa['kd_penyakit'])->oneArray();
      $row_diagnosa['nama'] = $icd10['nm_penyakit'];
      $diagnosa[] = $row_diagnosa;
    }

    echo $this->draw('display.html', ['diagnosa' => $diagnosa, 'prosedur' => $prosedur]);
    exit();
  }

  public function postHapusICD10()
  {
    $this->db('diagnosa_pasien')->where('no_rawat', $_POST['no_rawat'])->where('prioritas', $_POST['prioritas'])->delete();
    exit();
  }

  public function postHapusICD9()
  {
    $this->db('prosedur_pasien')->where('no_rawat', $_POST['no_rawat'])->where('prioritas', $_POST['prioritas'])->delete();
    exit();
  }

  protected function data_icd($table)
  {
      return new DB_ICD($table);
  }

}
