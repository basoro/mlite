<?php
namespace Plugins\ICD_9;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage(){
        $this->_addHeaderFiles();
        return $this->draw('manage.html');
    }

    public function postData(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_icd9= $_POST['search_field_icd9'];
        $search_text_icd9 = $_POST['search_text_icd9'];

        $searchQuery = " ";
        if($search_text_icd9 != ''){
            $searchQuery .= " and (".$search_field_icd9." like '%".$search_text_icd9."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from icd9");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from icd9 WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from icd9 WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode'=>$row['kode'],
'deskripsi_panjang'=>$row['deskripsi_panjang'],
'deskripsi_pendek'=>$row['deskripsi_pendek']

            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode($response);
        exit();
    }

    public function postAksi()
    {
        if(isset($_POST['typeact'])){ 
            $act = $_POST['typeact']; 
        }else{ 
            $act = ''; 
        }

        if ($act=='add') {

        $kode = $_POST['kode'];
$deskripsi_panjang = $_POST['deskripsi_panjang'];
$deskripsi_pendek = $_POST['deskripsi_pendek'];

            
            $icd9_add = $this->db()->pdo()->prepare('INSERT INTO icd9 VALUES (?, ?, ?)');
            $icd9_add->execute([$kode, $deskripsi_panjang, $deskripsi_pendek]);

        }
        if ($act=="edit") {

        $kode = $_POST['kode'];
$deskripsi_panjang = $_POST['deskripsi_panjang'];
$deskripsi_pendek = $_POST['deskripsi_pendek'];


        // BUANG FIELD PERTAMA

            $icd9_edit = $this->db()->pdo()->prepare("UPDATE icd9 SET kode=?, deskripsi_panjang=?, deskripsi_pendek=? WHERE kode=?");
            $icd9_edit->execute([$kode, $deskripsi_panjang, $deskripsi_pendek,$kode]);
        
        }

        if ($act=="del") {
            $kode= $_POST['kode'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM icd9 WHERE kode='$kode'");
            $result = $check_db->execute();
            $error = $check_db->errorInfo();
            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => $no_rkm_medis
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $error['2']
              );
            }
            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            $search_field_icd9= $_POST['search_field_icd9'];
            $search_text_icd9 = $_POST['search_text_icd9'];

            $searchQuery = " ";
            if($search_text_icd9 != ''){
                $searchQuery .= " and (".$search_field_icd9." like '%".$search_text_icd9."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from icd9 WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'kode'=>$row['kode'],
'deskripsi_panjang'=>$row['deskripsi_panjang'],
'deskripsi_pendek'=>$row['deskripsi_pendek']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($kode)
    {
        $detail = $this->db('icd9')->where('kode', $kode)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getImport()
    {
      $fileName = 'https://basoro.id/downloads/icd9cm.csv';
      echo '['.date('d-m-Y H:i:s').'][info] --- Mengimpor file csv'."<br>";

      $csvData = file_get_contents($fileName);
      if($csvData) {
        echo '['.date('d-m-Y H:i:s').'][info] Berkas ditemukan'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] File '.$filename.' tidak ditemukan'."<br>";
        exit();
      }

      $lines = explode(PHP_EOL, $csvData);
      $array = array();
      foreach ($lines as $line) {
          $array[] = str_getcsv($line);
      }

      foreach ($array as $data){   
        $kode = $data[0];
        $nama = isset_or($data[1], '');
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."','')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO icd9 (kode, deskripsi_panjang, deskripsi_pendek) VALUES $str ON DUPLICATE KEY UPDATE kode=VALUES(kode)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
      }
      
      exit();
    }
        
    public function postSaveICD9()
    {
      unset($_POST['nama']);
      $this->db('prosedur_pasien')->save($_POST);
      exit();
    }

    public function getDisplay()
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
  
      echo $this->draw('display.html', ['diagnosa' => $diagnosa, 'prosedur' => $prosedur]);
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
        
    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/icd_9/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/icd_9/js/admin/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/datatables.min.css'));
        $this->core->addJS(url('assets/jscripts/jqueryvalidation.js'));
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('assets/jscripts/datatables.min.js'));

        $this->core->addCSS(url([ADMIN, 'icd_9', 'css']));
        $this->core->addJS(url([ADMIN, 'icd_9', 'javascript']), 'footer');
    }

}
