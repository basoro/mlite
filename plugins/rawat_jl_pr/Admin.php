<?php
namespace Plugins\Rawat_Jl_Pr;

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
        $disabled_menu = $this->core->loadDisabledMenu('rawat_jl_pr'); 
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
        $search_field_rawat_jl_pr= isset_or($_POST['search_field_rawat_jl_pr']);
        $search_text_rawat_jl_pr = isset_or($_POST['search_text_rawat_jl_pr']);

        if ($search_text_rawat_jl_pr != '') {
          $where[$search_field_rawat_jl_pr.'[~]'] = $search_text_rawat_jl_pr;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('rawat_jl_pr', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('rawat_jl_pr', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('rawat_jl_pr', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'kd_jenis_prw'=>$row['kd_jenis_prw'],
'nip'=>$row['nip'],
'tgl_perawatan'=>$row['tgl_perawatan'],
'jam_rawat'=>$row['jam_rawat'],
'material'=>$row['material'],
'bhp'=>$row['bhp'],
'tarif_tindakanpr'=>$row['tarif_tindakanpr'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'biaya_rawat'=>$row['biaya_rawat'],
'stts_bayar'=>$row['stts_bayar']

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
          $this->core->LogQuery('rawat_jl_pr => postData');
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

            if($this->core->loadDisabledMenu('rawat_jl_pr')['create'] == 'true') {
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
$kd_jenis_prw = $_POST['kd_jenis_prw'];
$nip = $_POST['nip'];
$tgl_perawatan = $_POST['tgl_perawatan'];
$jam_rawat = $_POST['jam_rawat'];
$material = $_POST['material'];
$bhp = $_POST['bhp'];
$tarif_tindakanpr = $_POST['tarif_tindakanpr'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$biaya_rawat = $_POST['biaya_rawat'];
$stts_bayar = $_POST['stts_bayar'];

            
            $result = $this->core->db->insert('rawat_jl_pr', [
'no_rawat'=>$no_rawat, 'kd_jenis_prw'=>$kd_jenis_prw, 'nip'=>$nip, 'tgl_perawatan'=>$tgl_perawatan, 'jam_rawat'=>$jam_rawat, 'material'=>$material, 'bhp'=>$bhp, 'tarif_tindakanpr'=>$tarif_tindakanpr, 'kso'=>$kso, 'menejemen'=>$menejemen, 'biaya_rawat'=>$biaya_rawat, 'stts_bayar'=>$stts_bayar
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
              $this->core->LogQuery('rawat_jl_pr => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('rawat_jl_pr')['update'] == 'true') {
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
$kd_jenis_prw = $_POST['kd_jenis_prw'];
$nip = $_POST['nip'];
$tgl_perawatan = $_POST['tgl_perawatan'];
$jam_rawat = $_POST['jam_rawat'];
$material = $_POST['material'];
$bhp = $_POST['bhp'];
$tarif_tindakanpr = $_POST['tarif_tindakanpr'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$biaya_rawat = $_POST['biaya_rawat'];
$stts_bayar = $_POST['stts_bayar'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('rawat_jl_pr', [
'no_rawat'=>$no_rawat, 'kd_jenis_prw'=>$kd_jenis_prw, 'nip'=>$nip, 'tgl_perawatan'=>$tgl_perawatan, 'jam_rawat'=>$jam_rawat, 'material'=>$material, 'bhp'=>$bhp, 'tarif_tindakanpr'=>$tarif_tindakanpr, 'kso'=>$kso, 'menejemen'=>$menejemen, 'biaya_rawat'=>$biaya_rawat, 'stts_bayar'=>$stts_bayar
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
              $this->core->LogQuery('rawat_jl_pr => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('rawat_jl_pr')['delete'] == 'true') {
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
            $result = $this->core->db->delete('rawat_jl_pr', [
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
              $this->core->LogQuery('rawat_jl_pr => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('rawat_jl_pr')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_rawat_jl_pr= $_POST['search_field_rawat_jl_pr'];
            $search_text_rawat_jl_pr = $_POST['search_text_rawat_jl_pr'];

            if ($search_text_rawat_jl_pr != '') {
              $where[$search_field_rawat_jl_pr.'[~]'] = $search_text_rawat_jl_pr;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('rawat_jl_pr', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'kd_jenis_prw'=>$row['kd_jenis_prw'],
'nip'=>$row['nip'],
'tgl_perawatan'=>$row['tgl_perawatan'],
'jam_rawat'=>$row['jam_rawat'],
'material'=>$row['material'],
'bhp'=>$row['bhp'],
'tarif_tindakanpr'=>$row['tarif_tindakanpr'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'biaya_rawat'=>$row['biaya_rawat'],
'stts_bayar'=>$row['stts_bayar']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('rawat_jl_pr => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('rawat_jl_pr')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('rawat_jl_pr', '*', ['no_rawat' => $no_rawat]);

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
          $this->core->LogQuery('rawat_jl_pr => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('rawat_jl_pr')['read'] == 'true') {
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
          $this->core->LogQuery('rawat_jl_pr => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('rawat_jl_pr', 'stts_bayar', ['GROUP' => 'stts_bayar']);
      $datasets = $this->core->db->select('rawat_jl_pr', ['count' => \Medoo\Medoo::raw('COUNT(<stts_bayar>)')], ['GROUP' => 'stts_bayar']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('rawat_jl_pr', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('rawat_jl_pr', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'rawat_jl_pr';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('rawat_jl_pr => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/rawat_jl_pr/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/rawat_jl_pr/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('rawat_jl_pr')]);
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

        $this->core->addCSS(url([ 'rawat_jl_pr', 'css']));
        $this->core->addJS(url([ 'rawat_jl_pr', 'javascript']), 'footer');
    }

}
