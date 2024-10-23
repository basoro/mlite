<?php
namespace Plugins\Detail_Pemberian_Obat;

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
        $search_field_detail_pemberian_obat= $_POST['search_field_detail_pemberian_obat'];
        $search_text_detail_pemberian_obat = $_POST['search_text_detail_pemberian_obat'];

        $searchQuery = " ";
        if($search_text_detail_pemberian_obat != ''){
            $searchQuery .= " and (".$search_field_detail_pemberian_obat." like '%".$search_text_detail_pemberian_obat."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from detail_pemberian_obat");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from detail_pemberian_obat WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from detail_pemberian_obat WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tgl_perawatan'=>$row['tgl_perawatan'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'kode_brng'=>$row['kode_brng'],
'h_beli'=>$row['h_beli'],
'biaya_obat'=>$row['biaya_obat'],
'jml'=>$row['jml'],
'embalase'=>$row['embalase'],
'tuslah'=>$row['tuslah'],
'total'=>$row['total'],
'status'=>$row['status'],
'kd_bangsal'=>$row['kd_bangsal'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur']

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

        $tgl_perawatan = $_POST['tgl_perawatan'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kode_brng = $_POST['kode_brng'];
$h_beli = $_POST['h_beli'];
$biaya_obat = $_POST['biaya_obat'];
$jml = $_POST['jml'];
$embalase = $_POST['embalase'];
$tuslah = $_POST['tuslah'];
$total = $_POST['total'];
$status = $_POST['status'];
$kd_bangsal = $_POST['kd_bangsal'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];

            
            $detail_pemberian_obat_add = $this->db()->pdo()->prepare('INSERT INTO detail_pemberian_obat VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $detail_pemberian_obat_add->execute([$tgl_perawatan, $jam, $no_rawat, $kode_brng, $h_beli, $biaya_obat, $jml, $embalase, $tuslah, $total, $status, $kd_bangsal, $no_batch, $no_faktur]);

        }
        if ($act=="edit") {

        $tgl_perawatan = $_POST['tgl_perawatan'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kode_brng = $_POST['kode_brng'];
$h_beli = $_POST['h_beli'];
$biaya_obat = $_POST['biaya_obat'];
$jml = $_POST['jml'];
$embalase = $_POST['embalase'];
$tuslah = $_POST['tuslah'];
$total = $_POST['total'];
$status = $_POST['status'];
$kd_bangsal = $_POST['kd_bangsal'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];


        // BUANG FIELD PERTAMA

            $detail_pemberian_obat_edit = $this->db()->pdo()->prepare("UPDATE detail_pemberian_obat SET tgl_perawatan=?, jam=?, no_rawat=?, kode_brng=?, h_beli=?, biaya_obat=?, jml=?, embalase=?, tuslah=?, total=?, status=?, kd_bangsal=?, no_batch=?, no_faktur=? WHERE tgl_perawatan=?");
            $detail_pemberian_obat_edit->execute([$tgl_perawatan, $jam, $no_rawat, $kode_brng, $h_beli, $biaya_obat, $jml, $embalase, $tuslah, $total, $status, $kd_bangsal, $no_batch, $no_faktur,$tgl_perawatan]);
        
        }

        if ($act=="del") {
            $tgl_perawatan= $_POST['tgl_perawatan'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM detail_pemberian_obat WHERE tgl_perawatan='$tgl_perawatan'");
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

            $search_field_detail_pemberian_obat= $_POST['search_field_detail_pemberian_obat'];
            $search_text_detail_pemberian_obat = $_POST['search_text_detail_pemberian_obat'];

            $searchQuery = " ";
            if($search_text_detail_pemberian_obat != ''){
                $searchQuery .= " and (".$search_field_detail_pemberian_obat." like '%".$search_text_detail_pemberian_obat."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from detail_pemberian_obat WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'tgl_perawatan'=>$row['tgl_perawatan'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'kode_brng'=>$row['kode_brng'],
'h_beli'=>$row['h_beli'],
'biaya_obat'=>$row['biaya_obat'],
'jml'=>$row['jml'],
'embalase'=>$row['embalase'],
'tuslah'=>$row['tuslah'],
'total'=>$row['total'],
'status'=>$row['status'],
'kd_bangsal'=>$row['kd_bangsal'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($tgl_perawatan)
    {
        $detail = $this->db('detail_pemberian_obat')->where('tgl_perawatan', $tgl_perawatan)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/detail_pemberian_obat/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/detail_pemberian_obat/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'detail_pemberian_obat', 'css']));
        $this->core->addJS(url([ADMIN, 'detail_pemberian_obat', 'javascript']), 'footer');
    }

}
