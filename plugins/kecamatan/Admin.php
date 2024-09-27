<?php
namespace Plugins\Kecamatan;

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
        $search_field_kecamatan= $_POST['search_field_kecamatan'];
        $search_text_kecamatan = $_POST['search_text_kecamatan'];

        $searchQuery = " ";
        if($search_text_kecamatan != ''){
            $searchQuery .= " and (".$search_field_kecamatan." like '%".$search_text_kecamatan."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from kecamatan");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from kecamatan WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from kecamatan WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_kec'=>$row['kd_kec'],
'nm_kec'=>$row['nm_kec']

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

        $kd_kec = $_POST['kd_kec'];
$nm_kec = $_POST['nm_kec'];

            
            $kecamatan_add = $this->db()->pdo()->prepare('INSERT INTO kecamatan VALUES (?, ?)');
            $kecamatan_add->execute([$kd_kec, $nm_kec]);

        }
        if ($act=="edit") {

        $kd_kec = $_POST['kd_kec'];
$nm_kec = $_POST['nm_kec'];


        // BUANG FIELD PERTAMA

            $kecamatan_edit = $this->db()->pdo()->prepare("UPDATE kecamatan SET kd_kec=?, nm_kec=? WHERE kd_kec=?");
            $kecamatan_edit->execute([$kd_kec, $nm_kec,$kd_kec]);
        
        }

        if ($act=="del") {
            $kd_kec= $_POST['kd_kec'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM kecamatan WHERE kd_kec='$kd_kec'");
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

            $search_field_kecamatan= $_POST['search_field_kecamatan'];
            $search_text_kecamatan = $_POST['search_text_kecamatan'];

            $searchQuery = " ";
            if($search_text_kecamatan != ''){
                $searchQuery .= " and (".$search_field_kecamatan." like '%".$search_text_kecamatan."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from kecamatan WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'kd_kec'=>$row['kd_kec'],
'nm_kec'=>$row['nm_kec']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($kd_kec)
    {
        $detail = $this->db('kecamatan')->where('kd_kec', $kd_kec)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getImport()
    {

      $fileName = 'https://basoro.id/downloads/districts.csv';
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
        $nama = $data[2];
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO kecamatan (kd_kec, nm_kec) VALUES $str ON DUPLICATE KEY UPDATE kd_kec=VALUES(kd_kec)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
      }

      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/kecamatan/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/kecamatan/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'kecamatan', 'css']));
        $this->core->addJS(url([ADMIN, 'kecamatan', 'javascript']), 'footer');
    }

}
