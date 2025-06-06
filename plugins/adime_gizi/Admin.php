<?php
namespace Plugins\Adime_Gizi;

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
        $search_field_catatan_adime_gizi= $_POST['search_field_catatan_adime_gizi'];
        $search_text_catatan_adime_gizi = $_POST['search_text_catatan_adime_gizi'];

        $searchQuery = " ";
        if($search_text_catatan_adime_gizi != ''){
            $searchQuery .= " and (".$search_field_catatan_adime_gizi." like '%".$search_text_catatan_adime_gizi."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from catatan_adime_gizi");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from catatan_adime_gizi WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from catatan_adime_gizi WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'asesmen'=>$row['asesmen'],
'diagnosis'=>$row['diagnosis'],
'intervensi'=>$row['intervensi'],
'monitoring'=>$row['monitoring'],
'evaluasi'=>$row['evaluasi'],
'instruksi'=>$row['instruksi'],
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
$asesmen = $_POST['asesmen'];
$diagnosis = $_POST['diagnosis'];
$intervensi = $_POST['intervensi'];
$monitoring = $_POST['monitoring'];
$evaluasi = $_POST['evaluasi'];
$instruksi = $_POST['instruksi'];
$nip = $this->core->getUserInfo('username', $_SESSION['mlite_user']);

            
            $catatan_adime_gizi_add = $this->db()->pdo()->prepare('INSERT INTO catatan_adime_gizi VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $catatan_adime_gizi_add->execute([$no_rawat, $tanggal, $asesmen, $diagnosis, $intervensi, $monitoring, $evaluasi, $instruksi, $nip]);

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$asesmen = $_POST['asesmen'];
$diagnosis = $_POST['diagnosis'];
$intervensi = $_POST['intervensi'];
$monitoring = $_POST['monitoring'];
$evaluasi = $_POST['evaluasi'];
$instruksi = $_POST['instruksi'];
$nip = $this->core->getUserInfo('username', $_SESSION['mlite_user']);


        // BUANG FIELD PERTAMA

            $catatan_adime_gizi_edit = $this->db()->pdo()->prepare("UPDATE catatan_adime_gizi SET tanggal=?, asesmen=?, diagnosis=?, intervensi=?, monitoring=?, evaluasi=?, instruksi=?, nip=? WHERE no_rawat=?");
            $catatan_adime_gizi_edit->execute([$tanggal, $asesmen, $diagnosis, $intervensi, $monitoring, $evaluasi, $instruksi, $nip, $no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM catatan_adime_gizi WHERE no_rawat='$no_rawat'");
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

            $search_field_catatan_adime_gizi= $_POST['search_field_catatan_adime_gizi'];
            $search_text_catatan_adime_gizi = $_POST['search_text_catatan_adime_gizi'];

            $searchQuery = " ";
            if($search_text_catatan_adime_gizi != ''){
                $searchQuery .= " and (".$search_field_catatan_adime_gizi." like '%".$search_text_catatan_adime_gizi."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from catatan_adime_gizi WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'asesmen'=>$row['asesmen'],
'diagnosis'=>$row['diagnosis'],
'intervensi'=>$row['intervensi'],
'monitoring'=>$row['monitoring'],
'evaluasi'=>$row['evaluasi'],
'instruksi'=>$row['instruksi'],
'nip'=>$row['nip']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($no_rawat)
    {
        $detail = $this->db('catatan_adime_gizi')->where('no_rawat', revertNorawat($no_rawat))->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/adime_gizi/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/adime_gizi/js/admin/scripts.js', ['settings' => $settings]);
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
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        $this->core->addCSS(url([ADMIN, 'adime_gizi', 'css']));
        $this->core->addJS(url([ADMIN, 'adime_gizi', 'javascript']), 'footer');
    }

}
