<?php
namespace Plugins\Jam_Masuk;

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
        $search_field_jam_masuk= $_POST['search_field_jam_masuk'];
        $search_text_jam_masuk = $_POST['search_text_jam_masuk'];

        $searchQuery = " ";
        if($search_text_jam_masuk != ''){
            $searchQuery .= " and (".$search_field_jam_masuk." like '%".$search_text_jam_masuk."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from jam_masuk");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from jam_masuk WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from jam_masuk WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'shift'=>$row['shift'],
'jam_masuk'=>$row['jam_masuk'],
'jam_pulang'=>$row['jam_pulang']

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

        $shift = $_POST['shift'];
$jam_masuk = $_POST['jam_masuk'];
$jam_pulang = $_POST['jam_pulang'];

            
            $jam_masuk_add = $this->db()->pdo()->prepare('INSERT INTO jam_masuk VALUES (?, ?, ?)');
            $jam_masuk_add->execute([$shift, $jam_masuk, $jam_pulang]);

        }
        if ($act=="edit") {

        $shift = $_POST['shift'];
$jam_masuk = $_POST['jam_masuk'];
$jam_pulang = $_POST['jam_pulang'];


        // BUANG FIELD PERTAMA

            $jam_masuk_edit = $this->db()->pdo()->prepare("UPDATE jam_masuk SET shift=?, jam_masuk=?, jam_pulang=? WHERE shift=?");
            $jam_masuk_edit->execute([$shift, $jam_masuk, $jam_pulang,$shift]);
        
        }

        if ($act=="del") {
            $shift= $_POST['shift'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM jam_masuk WHERE shift='$shift'");
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

            $search_field_jam_masuk= $_POST['search_field_jam_masuk'];
            $search_text_jam_masuk = $_POST['search_text_jam_masuk'];

            $searchQuery = " ";
            if($search_text_jam_masuk != ''){
                $searchQuery .= " and (".$search_field_jam_masuk." like '%".$search_text_jam_masuk."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from jam_masuk WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'shift'=>$row['shift'],
'jam_masuk'=>$row['jam_masuk'],
'jam_pulang'=>$row['jam_pulang']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($shift)
    {
        $detail = $this->db('jam_masuk')->where('shift', $shift)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/jam_masuk/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/jam_masuk/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'jam_masuk', 'css']));
        $this->core->addJS(url([ADMIN, 'jam_masuk', 'javascript']), 'footer');
    }

}
