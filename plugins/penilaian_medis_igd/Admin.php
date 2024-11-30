<?php
namespace Plugins\Penilaian_Medis_IGD;

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
        $enum['anamnesis'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'anamnesis');
        $enum['keadaan'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'keadaan');
        $enum['kesadaran'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'kesadaran');
        $enum['kepala'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'kepala');
        $enum['mata'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'mata');
        $enum['gigi'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'gigi');
        $enum['leher'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'leher');
        $enum['thoraks'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'thoraks');
        $enum['abdomen'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'abdomen');
        $enum['genital'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'genital');
        $enum['ekstremitas'] = $this->core->getEnum('mlite_penilaian_medis_igd', 'ekstremitas');
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
        $search_field_mlite_penilaian_medis_igd= $_POST['search_field_mlite_penilaian_medis_igd'];
        $search_text_mlite_penilaian_medis_igd = $_POST['search_text_mlite_penilaian_medis_igd'];

        $searchQuery = " ";
        if($search_text_mlite_penilaian_medis_igd != ''){
            $searchQuery .= " and (".$search_field_mlite_penilaian_medis_igd." like '%".$search_text_mlite_penilaian_medis_igd."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_medis_igd");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_medis_igd WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_penilaian_medis_igd WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'kd_dokter'=>$row['kd_dokter'],
'anamnesis'=>$row['anamnesis'],
'hubungan'=>$row['hubungan'],
'keluhan_utama'=>$row['keluhan_utama'],
'rps'=>$row['rps'],
'rpd'=>$row['rpd'],
'rpk'=>$row['rpk'],
'rpo'=>$row['rpo'],
'alergi'=>$row['alergi'],
'keadaan'=>$row['keadaan'],
'gcs'=>$row['gcs'],
'kesadaran'=>$row['kesadaran'],
'td'=>$row['td'],
'nadi'=>$row['nadi'],
'rr'=>$row['rr'],
'suhu'=>$row['suhu'],
'spo'=>$row['spo'],
'bb'=>$row['bb'],
'tb'=>$row['tb'],
'kepala'=>$row['kepala'],
'mata'=>$row['mata'],
'gigi'=>$row['gigi'],
'leher'=>$row['leher'],
'thoraks'=>$row['thoraks'],
'abdomen'=>$row['abdomen'],
'genital'=>$row['genital'],
'ekstremitas'=>$row['ekstremitas'],
'ket_fisik'=>$row['ket_fisik'],
'ket_lokalis'=>$row['ket_lokalis'],
'ekg'=>$row['ekg'],
'rad'=>$row['rad'],
'lab'=>$row['lab'],
'diagnosis'=>$row['diagnosis'],
'tata'=>$row['tata']

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
$kd_dokter = $_POST['kd_dokter'];
$anamnesis = $_POST['anamnesis'];
$hubungan = $_POST['hubungan'];
$keluhan_utama = $_POST['keluhan_utama'];
$rps = $_POST['rps'];
$rpd = $_POST['rpd'];
$rpk = $_POST['rpk'];
$rpo = $_POST['rpo'];
$alergi = $_POST['alergi'];
$keadaan = $_POST['keadaan'];
$gcs = $_POST['gcs'];
$kesadaran = $_POST['kesadaran'];
$td = $_POST['td'];
$nadi = $_POST['nadi'];
$rr = $_POST['rr'];
$suhu = $_POST['suhu'];
$spo = $_POST['spo'];
$bb = $_POST['bb'];
$tb = $_POST['tb'];
$kepala = $_POST['kepala'];
$mata = $_POST['mata'];
$gigi = $_POST['gigi'];
$leher = $_POST['leher'];
$thoraks = $_POST['thoraks'];
$abdomen = $_POST['abdomen'];
$genital = $_POST['genital'];
$ekstremitas = $_POST['ekstremitas'];
$ket_fisik = $_POST['ket_fisik'];
$ket_lokalis = $_POST['ket_lokalis'];
$ekg = $_POST['ekg'];
$rad = $_POST['rad'];
$lab = $_POST['lab'];
$diagnosis = $_POST['diagnosis'];
$tata = $_POST['tata'];

            
            $mlite_penilaian_medis_igd_add = $this->db()->pdo()->prepare('INSERT INTO mlite_penilaian_medis_igd VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_penilaian_medis_igd_add->execute([$no_rawat, $tanggal, $kd_dokter, $anamnesis, $hubungan, $keluhan_utama, $rps, $rpd, $rpk, $rpo, $alergi, $keadaan, $gcs, $kesadaran, $td, $nadi, $rr, $suhu, $spo, $bb, $tb, $kepala, $mata, $gigi, $leher, $thoraks, $abdomen, $genital, $ekstremitas, $ket_fisik, $ket_lokalis, $ekg, $rad, $lab, $diagnosis, $tata]);

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$kd_dokter = $_POST['kd_dokter'];
$anamnesis = $_POST['anamnesis'];
$hubungan = $_POST['hubungan'];
$keluhan_utama = $_POST['keluhan_utama'];
$rps = $_POST['rps'];
$rpd = $_POST['rpd'];
$rpk = $_POST['rpk'];
$rpo = $_POST['rpo'];
$alergi = $_POST['alergi'];
$keadaan = $_POST['keadaan'];
$gcs = $_POST['gcs'];
$kesadaran = $_POST['kesadaran'];
$td = $_POST['td'];
$nadi = $_POST['nadi'];
$rr = $_POST['rr'];
$suhu = $_POST['suhu'];
$spo = $_POST['spo'];
$bb = $_POST['bb'];
$tb = $_POST['tb'];
$kepala = $_POST['kepala'];
$mata = $_POST['mata'];
$gigi = $_POST['gigi'];
$leher = $_POST['leher'];
$thoraks = $_POST['thoraks'];
$abdomen = $_POST['abdomen'];
$genital = $_POST['genital'];
$ekstremitas = $_POST['ekstremitas'];
$ket_fisik = $_POST['ket_fisik'];
$ket_lokalis = $_POST['ket_lokalis'];
$ekg = $_POST['ekg'];
$rad = $_POST['rad'];
$lab = $_POST['lab'];
$diagnosis = $_POST['diagnosis'];
$tata = $_POST['tata'];


        // BUANG FIELD PERTAMA

            $mlite_penilaian_medis_igd_edit = $this->db()->pdo()->prepare("UPDATE mlite_penilaian_medis_igd SET no_rawat=?, tanggal=?, kd_dokter=?, anamnesis=?, hubungan=?, keluhan_utama=?, rps=?, rpd=?, rpk=?, rpo=?, alergi=?, keadaan=?, gcs=?, kesadaran=?, td=?, nadi=?, rr=?, suhu=?, spo=?, bb=?, tb=?, kepala=?, mata=?, gigi=?, leher=?, thoraks=?, abdomen=?, genital=?, ekstremitas=?, ket_fisik=?, ket_lokalis=?, ekg=?, rad=?, lab=?, diagnosis=?, tata=? WHERE no_rawat=?");
            $mlite_penilaian_medis_igd_edit->execute([$no_rawat, $tanggal, $kd_dokter, $anamnesis, $hubungan, $keluhan_utama, $rps, $rpd, $rpk, $rpo, $alergi, $keadaan, $gcs, $kesadaran, $td, $nadi, $rr, $suhu, $spo, $bb, $tb, $kepala, $mata, $gigi, $leher, $thoraks, $abdomen, $genital, $ekstremitas, $ket_fisik, $ket_lokalis, $ekg, $rad, $lab, $diagnosis, $tata,$no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_penilaian_medis_igd WHERE no_rawat='$no_rawat'");
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

            $search_field_mlite_penilaian_medis_igd= $_POST['search_field_mlite_penilaian_medis_igd'];
            $search_text_mlite_penilaian_medis_igd = $_POST['search_text_mlite_penilaian_medis_igd'];

            $searchQuery = " ";
            if($search_text_mlite_penilaian_medis_igd != ''){
                $searchQuery .= " and (".$search_field_mlite_penilaian_medis_igd." like '%".$search_text_mlite_penilaian_medis_igd."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_penilaian_medis_igd WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'kd_dokter'=>$row['kd_dokter'],
'anamnesis'=>$row['anamnesis'],
'hubungan'=>$row['hubungan'],
'keluhan_utama'=>$row['keluhan_utama'],
'rps'=>$row['rps'],
'rpd'=>$row['rpd'],
'rpk'=>$row['rpk'],
'rpo'=>$row['rpo'],
'alergi'=>$row['alergi'],
'keadaan'=>$row['keadaan'],
'gcs'=>$row['gcs'],
'kesadaran'=>$row['kesadaran'],
'td'=>$row['td'],
'nadi'=>$row['nadi'],
'rr'=>$row['rr'],
'suhu'=>$row['suhu'],
'spo'=>$row['spo'],
'bb'=>$row['bb'],
'tb'=>$row['tb'],
'kepala'=>$row['kepala'],
'mata'=>$row['mata'],
'gigi'=>$row['gigi'],
'leher'=>$row['leher'],
'thoraks'=>$row['thoraks'],
'abdomen'=>$row['abdomen'],
'genital'=>$row['genital'],
'ekstremitas'=>$row['ekstremitas'],
'ket_fisik'=>$row['ket_fisik'],
'ket_lokalis'=>$row['ket_lokalis'],
'ekg'=>$row['ekg'],
'rad'=>$row['rad'],
'lab'=>$row['lab'],
'diagnosis'=>$row['diagnosis'],
'tata'=>$row['tata']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($no_rawat)
    {
        $detail = $this->db('mlite_penilaian_medis_igd')->where('no_rawat', $no_rawat)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/penilaian_medis_igd/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penilaian_medis_igd/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'penilaian_medis_igd', 'css']));
        $this->core->addJS(url([ADMIN, 'penilaian_medis_igd', 'javascript']), 'footer');
    }

}
