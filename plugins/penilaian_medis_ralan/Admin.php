<?php
namespace Plugins\Penilaian_Medis_Ralan;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        $disabled_menu = $this->core->loadDisabledMenu('penilaian_medis_ralan'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_rawat');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_penilaian_medis_ralan= isset_or($_POST['search_field_penilaian_medis_ralan']);
        $search_text_penilaian_medis_ralan = isset_or($_POST['search_text_penilaian_medis_ralan']);

        if ($search_text_penilaian_medis_ralan != '') {
          $where[$search_field_penilaian_medis_ralan.'[~]'] = $search_text_penilaian_medis_ralan;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('penilaian_medis_ralan', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('penilaian_medis_ralan', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('penilaian_medis_ralan', '*', $where);

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
'gigi'=>$row['gigi'],
'tht'=>$row['tht'],
'thoraks'=>$row['thoraks'],
'abdomen'=>$row['abdomen'],
'genital'=>$row['genital'],
'ekstremitas'=>$row['ekstremitas'],
'kulit'=>$row['kulit'],
'ket_fisik'=>$row['ket_fisik'],
'ket_lokalis'=>$row['ket_lokalis'],
'penunjang'=>$row['penunjang'],
'diagnosis'=>$row['diagnosis'],
'tata'=>$row['tata'],
'konsulrujuk'=>$row['konsulrujuk']

            );
        }

        ## Response
        http_response_code(200);
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('penilaian_medis_ralan => postData');
        }

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

            if($this->core->loadDisabledMenu('penilaian_medis_ralan')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

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
$gigi = $_POST['gigi'];
$tht = $_POST['tht'];
$thoraks = $_POST['thoraks'];
$abdomen = $_POST['abdomen'];
$genital = $_POST['genital'];
$ekstremitas = $_POST['ekstremitas'];
$kulit = $_POST['kulit'];
$ket_fisik = $_POST['ket_fisik'];
$ket_lokalis = $_POST['ket_lokalis'];
$penunjang = $_POST['penunjang'];
$diagnosis = $_POST['diagnosis'];
$tata = $_POST['tata'];
$konsulrujuk = $_POST['konsulrujuk'];

            
            $result = $this->core->db->insert('penilaian_medis_ralan', [
'no_rawat'=>$no_rawat, 'tanggal'=>$tanggal, 'kd_dokter'=>$kd_dokter, 'anamnesis'=>$anamnesis, 'hubungan'=>$hubungan, 'keluhan_utama'=>$keluhan_utama, 'rps'=>$rps, 'rpd'=>$rpd, 'rpk'=>$rpk, 'rpo'=>$rpo, 'alergi'=>$alergi, 'keadaan'=>$keadaan, 'gcs'=>$gcs, 'kesadaran'=>$kesadaran, 'td'=>$td, 'nadi'=>$nadi, 'rr'=>$rr, 'suhu'=>$suhu, 'spo'=>$spo, 'bb'=>$bb, 'tb'=>$tb, 'kepala'=>$kepala, 'gigi'=>$gigi, 'tht'=>$tht, 'thoraks'=>$thoraks, 'abdomen'=>$abdomen, 'genital'=>$genital, 'ekstremitas'=>$ekstremitas, 'kulit'=>$kulit, 'ket_fisik'=>$ket_fisik, 'ket_lokalis'=>$ket_lokalis, 'penunjang'=>$penunjang, 'diagnosis'=>$diagnosis, 'tata'=>$tata, 'konsulrujuk'=>$konsulrujuk
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah ditambah'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('penilaian_medis_ralan => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('penilaian_medis_ralan')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

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
$gigi = $_POST['gigi'];
$tht = $_POST['tht'];
$thoraks = $_POST['thoraks'];
$abdomen = $_POST['abdomen'];
$genital = $_POST['genital'];
$ekstremitas = $_POST['ekstremitas'];
$kulit = $_POST['kulit'];
$ket_fisik = $_POST['ket_fisik'];
$ket_lokalis = $_POST['ket_lokalis'];
$penunjang = $_POST['penunjang'];
$diagnosis = $_POST['diagnosis'];
$tata = $_POST['tata'];
$konsulrujuk = $_POST['konsulrujuk'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('penilaian_medis_ralan', [
'no_rawat'=>$no_rawat, 'tanggal'=>$tanggal, 'kd_dokter'=>$kd_dokter, 'anamnesis'=>$anamnesis, 'hubungan'=>$hubungan, 'keluhan_utama'=>$keluhan_utama, 'rps'=>$rps, 'rpd'=>$rpd, 'rpk'=>$rpk, 'rpo'=>$rpo, 'alergi'=>$alergi, 'keadaan'=>$keadaan, 'gcs'=>$gcs, 'kesadaran'=>$kesadaran, 'td'=>$td, 'nadi'=>$nadi, 'rr'=>$rr, 'suhu'=>$suhu, 'spo'=>$spo, 'bb'=>$bb, 'tb'=>$tb, 'kepala'=>$kepala, 'gigi'=>$gigi, 'tht'=>$tht, 'thoraks'=>$thoraks, 'abdomen'=>$abdomen, 'genital'=>$genital, 'ekstremitas'=>$ekstremitas, 'kulit'=>$kulit, 'ket_fisik'=>$ket_fisik, 'ket_lokalis'=>$ket_lokalis, 'penunjang'=>$penunjang, 'diagnosis'=>$diagnosis, 'tata'=>$tata, 'konsulrujuk'=>$konsulrujuk
            ], [
              'no_rawat'=>$no_rawat
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah diubah'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('penilaian_medis_ralan => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('penilaian_medis_ralan')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rawat= $_POST['no_rawat'];
            $result = $this->core->db->delete('penilaian_medis_ralan', [
              'AND' => [
                'no_rawat'=>$no_rawat
              ]
            ]);

            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah dihapus'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('penilaian_medis_ralan => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('penilaian_medis_ralan')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_penilaian_medis_ralan= $_POST['search_field_penilaian_medis_ralan'];
            $search_text_penilaian_medis_ralan = $_POST['search_text_penilaian_medis_ralan'];

            if ($search_text_penilaian_medis_ralan != '') {
              $where[$search_field_penilaian_medis_ralan.'[~]'] = $search_text_penilaian_medis_ralan;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('penilaian_medis_ralan', '*', $where);

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
'gigi'=>$row['gigi'],
'tht'=>$row['tht'],
'thoraks'=>$row['thoraks'],
'abdomen'=>$row['abdomen'],
'genital'=>$row['genital'],
'ekstremitas'=>$row['ekstremitas'],
'kulit'=>$row['kulit'],
'ket_fisik'=>$row['ket_fisik'],
'ket_lokalis'=>$row['ket_lokalis'],
'penunjang'=>$row['penunjang'],
'diagnosis'=>$row['diagnosis'],
'tata'=>$row['tata'],
'konsulrujuk'=>$row['konsulrujuk']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('penilaian_medis_ralan => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('penilaian_medis_ralan')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('penilaian_medis_ralan', '*', ['no_rawat' => $no_rawat]);

        if (!empty($result)){
          http_response_code(200);
          $data = array(
            'code' => '200', 
            'status' => 'success', 
            'msg' => $result
          );
        } else {
          http_response_code(201);
          $data = array(
            'code' => '201', 
            'status' => 'error', 
            'msg' => 'Data tidak ditemukan'
          );
        }

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('penilaian_medis_ralan => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('penilaian_medis_ralan')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $settings =  $this->settings('settings');

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('penilaian_medis_ralan => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('penilaian_medis_ralan', 'kd_dokter', ['GROUP' => 'kd_dokter']);
      $datasets = $this->core->db->select('penilaian_medis_ralan', ['count' => \Medoo\Medoo::raw('COUNT(<kd_dokter>)')], ['GROUP' => 'kd_dokter']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('penilaian_medis_ralan', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('penilaian_medis_ralan', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'penilaian_medis_ralan';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('penilaian_medis_ralan => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/penilaian_medis_ralan/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penilaian_medis_ralan/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('penilaian_medis_ralan')]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.css'));
        $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/xlsx.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.plugin.autotable.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');
        $this->core->addJS(url('assets/js/jquery.contextMenu.js'), 'footer');

        $this->core->addCSS(url([ 'penilaian_medis_ralan', 'css']));
        $this->core->addJS(url([ 'penilaian_medis_ralan', 'javascript']), 'footer');
    }

}
