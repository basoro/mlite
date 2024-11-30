<?php
namespace Plugins\Grafik_Fenton;

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
        $search_field_mlite_fenton= $_POST['search_field_mlite_fenton'];
        $search_text_mlite_fenton = $_POST['search_text_mlite_fenton'];

        $searchQuery = " ";
        if($search_text_mlite_fenton != ''){
            $searchQuery .= " and (".$search_field_mlite_fenton." like '%".$search_text_mlite_fenton."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_fenton");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_fenton WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_fenton WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id'=>$row['id'],
'no_rawat'=>$row['no_rawat'],
'nm_pasien'=>$this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])), 
'jk'=>$this->core->getPasienInfo('jk', $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])), 
'tanggal'=>$row['tanggal'],
'usia_kehamilan'=>$row['usia_kehamilan'],
'tgl_lahir'=>$row['tgl_lahir'],
'berat_badan'=>$row['berat_badan'],
'lingkar_kepala'=>$row['lingkar_kepala'],
'panjang_badan'=>$row['panjang_badan'],
'petugas'=>$row['petugas'],
'created_at'=>$row['created_at'],
'deleted_at'=>$row['deleted_at']

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

        $id = $_POST['id'];
$no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$usia_kehamilan = $_POST['usia_kehamilan'];
$tgl_lahir = $_POST['tgl_lahir'];
$berat_badan = $_POST['berat_badan'];
$lingkar_kepala = $_POST['lingkar_kepala'];
$panjang_badan = $_POST['panjang_badan'];
$petugas = $_POST['petugas'];
$created_at = $_POST['created_at'];
$deleted_at = $_POST['deleted_at'];

            
            $mlite_fenton_add = $this->db()->pdo()->prepare('INSERT INTO mlite_fenton VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_fenton_add->execute([$id, $no_rawat, $tanggal, $usia_kehamilan, $tgl_lahir, $berat_badan, $lingkar_kepala, $panjang_badan, $petugas, $created_at, $deleted_at]);

        }
        if ($act=="edit") {

        $id = $_POST['id'];
$no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$usia_kehamilan = $_POST['usia_kehamilan'];
$tgl_lahir = $_POST['tgl_lahir'];
$berat_badan = $_POST['berat_badan'];
$lingkar_kepala = $_POST['lingkar_kepala'];
$panjang_badan = $_POST['panjang_badan'];
$petugas = $_POST['petugas'];
$created_at = $_POST['created_at'];
$deleted_at = $_POST['deleted_at'];


        // BUANG FIELD PERTAMA

            $mlite_fenton_edit = $this->db()->pdo()->prepare("UPDATE mlite_fenton SET id=?, no_rawat=?, tanggal=?, usia_kehamilan=?, tgl_lahir=?, berat_badan=?, lingkar_kepala=?, panjang_badan=?, petugas=?, created_at=?, deleted_at=? WHERE id=?");
            $mlite_fenton_edit->execute([$id, $no_rawat, $tanggal, $usia_kehamilan, $tgl_lahir, $berat_badan, $lingkar_kepala, $panjang_badan, $petugas, $created_at, $deleted_at,$id]);
        
        }

        if ($act=="del") {
            $id= $_POST['id'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_fenton WHERE id='$id'");
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

            $search_field_mlite_fenton= $_POST['search_field_mlite_fenton'];
            $search_text_mlite_fenton = $_POST['search_text_mlite_fenton'];

            $searchQuery = " ";
            if($search_text_mlite_fenton != ''){
                $searchQuery .= " and (".$search_field_mlite_fenton." like '%".$search_text_mlite_fenton."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_fenton WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'id'=>$row['id'],
'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'usia_kehamilan'=>$row['usia_kehamilan'],
'tgl_lahir'=>$row['tgl_lahir'],
'berat_badan'=>$row['berat_badan'],
'lingkar_kepala'=>$row['lingkar_kepala'],
'panjang_badan'=>$row['panjang_badan'],
'petugas'=>$row['petugas'],
'created_at'=>$row['created_at'],
'deleted_at'=>$row['deleted_at']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($id)
    {
        $detail = $this->db('mlite_fenton')->where('id', $id)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getGrafik($no_rawat)
    {
        $no_rawat = revertNoRawat($no_rawat);
        $detail = $this->db('mlite_fenton')->where('no_rawat', $no_rawat)->toArray();
        return $this->draw('grafik.html', ['fenton' => $detail]);
    }

    public function getFentonJson($no_rawat)
    {
        $no_rawat = revertNoRawat($no_rawat);
        $details = $this->db('mlite_fenton')->where('no_rawat', $no_rawat)->toArray();
        $measurements = [];
        $idx = 0;
        foreach($details as $row) {
            $result['idx'] = $idx++;
            $result['date'] = date('Y-m-d', strtotime($row['tanggal']));
            $result['weight'] = $row['berat_badan'];
            $result['length'] = $row['panjang_badan'];
            $result['head'] = $row['lingkar_kepala'];
            $result['dateOfBirth'] = $row['tgl_lahir'];
            $measurements[] = $result;
        }
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
        $sex = 'male';
        if($this->core->getPasienInfo('jk', $no_rkm_medis) == 'P') {
            $sex = 'female';
        }
        $array = array(
            'idx' => 0,
            'open' => true, 
            'name' => $this->core->getPasienInfo('nm_pasien', $no_rkm_medis), 
            'dateOfBirth' => $details[0]['tgl_lahir'], 
            'sex' => $sex, 
            'colourHex' => '#0544d3', 
            'measurements' => $measurements
        );
        echo json_encode(array($array));
        // echo parseUrl()[2];
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/grafik_fenton/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/grafik_fenton/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'grafik_fenton', 'css']));
        $this->core->addJS(url([ADMIN, 'grafik_fenton', 'javascript']), 'footer');
    }

}
