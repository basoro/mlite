<?php
namespace Plugins\Peta_Mukosa_Rongga_Mulut;

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
        $search_field_mlite_peta_mukosa_rongga_mulut= $_POST['search_field_mlite_peta_mukosa_rongga_mulut'];
        $search_text_mlite_peta_mukosa_rongga_mulut = $_POST['search_text_mlite_peta_mukosa_rongga_mulut'];

        $searchQuery = " ";
        if($search_text_mlite_peta_mukosa_rongga_mulut != ''){
            $searchQuery .= " and (".$search_field_mlite_peta_mukosa_rongga_mulut." like '%".$search_text_mlite_peta_mukosa_rongga_mulut."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_peta_mukosa_rongga_mulut");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_peta_mukosa_rongga_mulut WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_peta_mukosa_rongga_mulut WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'kelainan'=>$row['kelainan'],
'gambar'=>$row['gambar'],
'nip'=>$row['nip']

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

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$kelainan = $_POST['kelainan'];
$gambar = $_POST['gambar'];
$nip = $_POST['nip'];

            
            $mlite_peta_mukosa_rongga_mulut_add = $this->db()->pdo()->prepare('INSERT INTO mlite_peta_mukosa_rongga_mulut VALUES (?, ?, ?, ?, ?)');
            $mlite_peta_mukosa_rongga_mulut_add->execute([$no_rawat, $tanggal, $kelainan, $gambar, $nip]);

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$kelainan = $_POST['kelainan'];
$gambar = $_POST['gambar'];
$nip = $_POST['nip'];


        // BUANG FIELD PERTAMA

            $mlite_peta_mukosa_rongga_mulut_edit = $this->db()->pdo()->prepare("UPDATE mlite_peta_mukosa_rongga_mulut SET no_rawat=?, tanggal=?, kelainan=?, gambar=?, nip=? WHERE no_rawat=?");
            $mlite_peta_mukosa_rongga_mulut_edit->execute([$no_rawat, $tanggal, $kelainan, $gambar, $nip,$no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_peta_mukosa_rongga_mulut WHERE no_rawat='$no_rawat'");
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

            $search_field_mlite_peta_mukosa_rongga_mulut= $_POST['search_field_mlite_peta_mukosa_rongga_mulut'];
            $search_text_mlite_peta_mukosa_rongga_mulut = $_POST['search_text_mlite_peta_mukosa_rongga_mulut'];

            $searchQuery = " ";
            if($search_text_mlite_peta_mukosa_rongga_mulut != ''){
                $searchQuery .= " and (".$search_field_mlite_peta_mukosa_rongga_mulut." like '%".$search_text_mlite_peta_mukosa_rongga_mulut."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_peta_mukosa_rongga_mulut WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'kelainan'=>$row['kelainan'],
'gambar'=>$row['gambar'],
'nip'=>$row['nip']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($no_rawat)
    {
        $detail = $this->db('mlite_peta_mukosa_rongga_mulut')->where('no_rawat', $no_rawat)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/peta_mukosa_rongga_mulut/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/peta_mukosa_rongga_mulut/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'peta_mukosa_rongga_mulut', 'css']));
        $this->core->addJS(url([ADMIN, 'peta_mukosa_rongga_mulut', 'javascript']), 'footer');
    }

}
