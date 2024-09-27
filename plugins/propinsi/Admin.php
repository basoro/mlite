<?php
namespace Plugins\Propinsi;

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
        $search_field_propinsi= $_POST['search_field_propinsi'];
        $search_text_propinsi = $_POST['search_text_propinsi'];

        $searchQuery = " ";
        if($search_text_propinsi != ''){
            $searchQuery .= " and (".$search_field_propinsi." like '%".$search_text_propinsi."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from propinsi");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from propinsi WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from propinsi WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_prop'=>$row['kd_prop'],
'nm_prop'=>$row['nm_prop']

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

        $kd_prop = $_POST['kd_prop'];
$nm_prop = $_POST['nm_prop'];

            
            $propinsi_add = $this->db()->pdo()->prepare('INSERT INTO propinsi VALUES (?, ?)');
            $propinsi_add->execute([$kd_prop, $nm_prop]);

        }
        if ($act=="edit") {

        $kd_prop = $_POST['kd_prop'];
$nm_prop = $_POST['nm_prop'];


        // BUANG FIELD PERTAMA

            $propinsi_edit = $this->db()->pdo()->prepare("UPDATE propinsi SET kd_prop=?, nm_prop=? WHERE kd_prop=?");
            $propinsi_edit->execute([$kd_prop, $nm_prop,$kd_prop]);
        
        }

        if ($act=="del") {
            $kd_prop= $_POST['kd_prop'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM propinsi WHERE kd_prop='$kd_prop'");
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

            $search_field_propinsi= $_POST['search_field_propinsi'];
            $search_text_propinsi = $_POST['search_text_propinsi'];

            $searchQuery = " ";
            if($search_text_propinsi != ''){
                $searchQuery .= " and (".$search_field_propinsi." like '%".$search_text_propinsi."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from propinsi WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'kd_prop'=>$row['kd_prop'],
'nm_prop'=>$row['nm_prop']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($kd_prop)
    {
        $detail = $this->db('propinsi')->where('kd_prop', $kd_prop)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getImport()
    {
      $fileName = 'https://basoro.id/downloads/provinces.csv';
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
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO propinsi (kd_prop, nm_prop) VALUES $str ON DUPLICATE KEY UPDATE kd_prop=VALUES(kd_prop)");
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
        echo $this->draw(MODULES.'/propinsi/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/propinsi/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'propinsi', 'css']));
        $this->core->addJS(url([ADMIN, 'propinsi', 'javascript']), 'footer');
    }

}
