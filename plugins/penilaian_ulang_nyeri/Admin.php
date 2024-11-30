<?php
namespace Plugins\Penilaian_Ulang_Nyeri;

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
        $enum['nyeri'] = $this->core->getEnum('mlite_penilaian_ulang_nyeri', 'nyeri');
        $enum['provokes'] = $this->core->getEnum('mlite_penilaian_ulang_nyeri', 'provokes');
        $enum['quality'] = $this->core->getEnum('mlite_penilaian_ulang_nyeri', 'quality');
        $enum['menyebar'] = $this->core->getEnum('mlite_penilaian_ulang_nyeri', 'menyebar');
        $enum['skala_nyeri'] = $this->core->getEnum('mlite_penilaian_ulang_nyeri', 'skala_nyeri');
        $enum['nyeri_hilang'] = $this->core->getEnum('mlite_penilaian_ulang_nyeri', 'nyeri_hilang');
        return $this->draw('manage.html', ['enum' => $enum]);
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
        $search_field_mlite_penilaian_ulang_nyeri= $_POST['search_field_mlite_penilaian_ulang_nyeri'];
        $search_text_mlite_penilaian_ulang_nyeri = $_POST['search_text_mlite_penilaian_ulang_nyeri'];

        $searchQuery = " ";
        if($search_text_mlite_penilaian_ulang_nyeri != ''){
            $searchQuery .= " and (".$search_field_mlite_penilaian_ulang_nyeri." like '%".$search_text_mlite_penilaian_ulang_nyeri."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_ulang_nyeri");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_ulang_nyeri WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_penilaian_ulang_nyeri WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'nyeri'=>$row['nyeri'],
'provokes'=>$row['provokes'],
'ket_provokes'=>$row['ket_provokes'],
'quality'=>$row['quality'],
'ket_quality'=>$row['ket_quality'],
'lokasi'=>$row['lokasi'],
'menyebar'=>$row['menyebar'],
'skala_nyeri'=>$row['skala_nyeri'],
'durasi'=>$row['durasi'],
'nyeri_hilang'=>$row['nyeri_hilang'],
'ket_nyeri'=>$row['ket_nyeri'],
'manajemen_nyeri'=>$row['manajemen_nyeri'],
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
$nyeri = $_POST['nyeri'];
$provokes = $_POST['provokes'];
$ket_provokes = $_POST['ket_provokes'];
$quality = $_POST['quality'];
$ket_quality = $_POST['ket_quality'];
$lokasi = $_POST['lokasi'];
$menyebar = $_POST['menyebar'];
$skala_nyeri = $_POST['skala_nyeri'];
$durasi = $_POST['durasi'];
$nyeri_hilang = $_POST['nyeri_hilang'];
$ket_nyeri = $_POST['ket_nyeri'];
$manajemen_nyeri = $_POST['manajemen_nyeri'];
$nip = $_POST['nip'];

            
            $mlite_penilaian_ulang_nyeri_add = $this->db()->pdo()->prepare('INSERT INTO mlite_penilaian_ulang_nyeri VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_penilaian_ulang_nyeri_add->execute([$no_rawat, $tanggal, $nyeri, $provokes, $ket_provokes, $quality, $ket_quality, $lokasi, $menyebar, $skala_nyeri, $durasi, $nyeri_hilang, $ket_nyeri, $manajemen_nyeri, $nip]);

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$nyeri = $_POST['nyeri'];
$provokes = $_POST['provokes'];
$ket_provokes = $_POST['ket_provokes'];
$quality = $_POST['quality'];
$ket_quality = $_POST['ket_quality'];
$lokasi = $_POST['lokasi'];
$menyebar = $_POST['menyebar'];
$skala_nyeri = $_POST['skala_nyeri'];
$durasi = $_POST['durasi'];
$nyeri_hilang = $_POST['nyeri_hilang'];
$ket_nyeri = $_POST['ket_nyeri'];
$manajemen_nyeri = $_POST['manajemen_nyeri'];
$nip = $_POST['nip'];


        // BUANG FIELD PERTAMA

            $mlite_penilaian_ulang_nyeri_edit = $this->db()->pdo()->prepare("UPDATE mlite_penilaian_ulang_nyeri SET no_rawat=?, tanggal=?, nyeri=?, provokes=?, ket_provokes=?, quality=?, ket_quality=?, lokasi=?, menyebar=?, skala_nyeri=?, durasi=?, nyeri_hilang=?, ket_nyeri=?, manajemen_nyeri=?, nip=? WHERE no_rawat=?");
            $mlite_penilaian_ulang_nyeri_edit->execute([$no_rawat, $tanggal, $nyeri, $provokes, $ket_provokes, $quality, $ket_quality, $lokasi, $menyebar, $skala_nyeri, $durasi, $nyeri_hilang, $ket_nyeri, $manajemen_nyeri, $nip,$no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_penilaian_ulang_nyeri WHERE no_rawat='$no_rawat'");
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

            $search_field_mlite_penilaian_ulang_nyeri= $_POST['search_field_mlite_penilaian_ulang_nyeri'];
            $search_text_mlite_penilaian_ulang_nyeri = $_POST['search_text_mlite_penilaian_ulang_nyeri'];

            $searchQuery = " ";
            if($search_text_mlite_penilaian_ulang_nyeri != ''){
                $searchQuery .= " and (".$search_field_mlite_penilaian_ulang_nyeri." like '%".$search_text_mlite_penilaian_ulang_nyeri."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_penilaian_ulang_nyeri WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'nyeri'=>$row['nyeri'],
'provokes'=>$row['provokes'],
'ket_provokes'=>$row['ket_provokes'],
'quality'=>$row['quality'],
'ket_quality'=>$row['ket_quality'],
'lokasi'=>$row['lokasi'],
'menyebar'=>$row['menyebar'],
'skala_nyeri'=>$row['skala_nyeri'],
'durasi'=>$row['durasi'],
'nyeri_hilang'=>$row['nyeri_hilang'],
'ket_nyeri'=>$row['ket_nyeri'],
'manajemen_nyeri'=>$row['manajemen_nyeri'],
'nip'=>$row['nip']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($no_rawat)
    {
        $detail = $this->db('mlite_penilaian_ulang_nyeri')->where('no_rawat', $no_rawat)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/penilaian_ulang_nyeri/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penilaian_ulang_nyeri/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'penilaian_ulang_nyeri', 'css']));
        $this->core->addJS(url([ADMIN, 'penilaian_ulang_nyeri', 'javascript']), 'footer');
    }

}
