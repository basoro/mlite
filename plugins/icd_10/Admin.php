<?php
namespace Plugins\ICD_10;

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
        $search_field_penyakit= $_POST['search_field_penyakit'];
        $search_text_penyakit = $_POST['search_text_penyakit'];

        $searchQuery = " ";
        if($search_text_penyakit != ''){
            $searchQuery .= " and (".$search_field_penyakit." like '%".$search_text_penyakit."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from penyakit");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from penyakit WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from penyakit WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_penyakit'=>$row['kd_penyakit'],
'nm_penyakit'=>$row['nm_penyakit'],
'ciri_ciri'=>$row['ciri_ciri'],
'keterangan'=>$row['keterangan'],
'kd_ktg'=>$row['kd_ktg'],
'status'=>$row['status']

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

        $kd_penyakit = $_POST['kd_penyakit'];
$nm_penyakit = $_POST['nm_penyakit'];
$ciri_ciri = $_POST['ciri_ciri'];
$keterangan = $_POST['keterangan'];
$kd_ktg = $_POST['kd_ktg'];
$status = $_POST['status'];

            
            $penyakit_add = $this->db()->pdo()->prepare('INSERT INTO penyakit VALUES (?, ?, ?, ?, ?, ?)');
            $penyakit_add->execute([$kd_penyakit, $nm_penyakit, $ciri_ciri, $keterangan, $kd_ktg, $status]);

        }
        if ($act=="edit") {

        $kd_penyakit = $_POST['kd_penyakit'];
$nm_penyakit = $_POST['nm_penyakit'];
$ciri_ciri = $_POST['ciri_ciri'];
$keterangan = $_POST['keterangan'];
$kd_ktg = $_POST['kd_ktg'];
$status = $_POST['status'];


        // BUANG FIELD PERTAMA

            $penyakit_edit = $this->db()->pdo()->prepare("UPDATE penyakit SET kd_penyakit=?, nm_penyakit=?, ciri_ciri=?, keterangan=?, kd_ktg=?, status=? WHERE kd_penyakit=?");
            $penyakit_edit->execute([$kd_penyakit, $nm_penyakit, $ciri_ciri, $keterangan, $kd_ktg, $status,$kd_penyakit]);
        
        }

        if ($act=="del") {
            $kd_penyakit= $_POST['kd_penyakit'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM penyakit WHERE kd_penyakit='$kd_penyakit'");
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

            $search_field_penyakit= $_POST['search_field_penyakit'];
            $search_text_penyakit = $_POST['search_text_penyakit'];

            $searchQuery = " ";
            if($search_text_penyakit != ''){
                $searchQuery .= " and (".$search_field_penyakit." like '%".$search_text_penyakit."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from penyakit WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'kd_penyakit'=>$row['kd_penyakit'],
'nm_penyakit'=>$row['nm_penyakit'],
'ciri_ciri'=>$row['ciri_ciri'],
'keterangan'=>$row['keterangan'],
'kd_ktg'=>$row['kd_ktg'],
'status'=>$row['status']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($kd_penyakit)
    {
        $detail = $this->db('penyakit')->where('kd_penyakit', $kd_penyakit)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getImport()
    {
      $fileName = 'https://basoro.id/downloads/icd10.csv';
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
        $nama = str_replace('"','',$nama);
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."','','','-','Tidak Menular')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO penyakit (kd_penyakit, nm_penyakit, ciri_ciri, keterangan, kd_ktg, status) VALUES $str ON DUPLICATE KEY UPDATE kd_penyakit=VALUES(kd_penyakit)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
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
        
    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/icd_10/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/icd_10/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'icd_10', 'css']));
        $this->core->addJS(url([ADMIN, 'icd_10', 'javascript']), 'footer');
    }

}
