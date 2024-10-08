<?php
namespace Plugins\Kabupaten;

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
        $search_field_kabupaten= $_POST['search_field_kabupaten'];
        $search_text_kabupaten = $_POST['search_text_kabupaten'];

        $searchQuery = " ";
        if($search_text_kabupaten != ''){
            $searchQuery .= " and (".$search_field_kabupaten." like '%".$search_text_kabupaten."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from kabupaten");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from kabupaten WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from kabupaten WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_kab'=>$row['kd_kab'],
'nm_kab'=>$row['nm_kab']

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

        $kd_kab = $_POST['kd_kab'];
$nm_kab = $_POST['nm_kab'];

            
            $kabupaten_add = $this->db()->pdo()->prepare('INSERT INTO kabupaten VALUES (?, ?)');
            $kabupaten_add->execute([$kd_kab, $nm_kab]);

        }
        if ($act=="edit") {

        $kd_kab = $_POST['kd_kab'];
$nm_kab = $_POST['nm_kab'];


        // BUANG FIELD PERTAMA

            $kabupaten_edit = $this->db()->pdo()->prepare("UPDATE kabupaten SET kd_kab=?, nm_kab=? WHERE kd_kab=?");
            $kabupaten_edit->execute([$kd_kab, $nm_kab,$kd_kab]);
        
        }

        if ($act=="del") {
            $kd_kab= $_POST['kd_kab'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM kabupaten WHERE kd_kab='$kd_kab'");
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

            $search_field_kabupaten= $_POST['search_field_kabupaten'];
            $search_text_kabupaten = $_POST['search_text_kabupaten'];

            $searchQuery = " ";
            if($search_text_kabupaten != ''){
                $searchQuery .= " and (".$search_field_kabupaten." like '%".$search_text_kabupaten."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from kabupaten WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'kd_kab'=>$row['kd_kab'],
'nm_kab'=>$row['nm_kab']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($kd_kab)
    {
        $detail = $this->db('kabupaten')->where('kd_kab', $kd_kab)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getImport()
    {

      $fileName = 'https://basoro.id/downloads/regencies.csv';
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
      $result = $this->core->db()->pdo()->exec("INSERT INTO kabupaten (kd_kab, nm_kab) VALUES $str ON DUPLICATE KEY UPDATE kd_kab=VALUES(kd_kab)");
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
        echo $this->draw(MODULES.'/kabupaten/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/kabupaten/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'kabupaten', 'css']));
        $this->core->addJS(url([ADMIN, 'kabupaten', 'javascript']), 'footer');
    }

}
