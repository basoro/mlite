<?php
namespace Plugins\Template_Laboratorium;

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
        $disabled_menu = $this->core->loadDisabledMenu('template_laboratorium'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_jenis_prw');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_template_laboratorium= isset_or($_POST['search_field_template_laboratorium']);
        $search_text_template_laboratorium = isset_or($_POST['search_text_template_laboratorium']);

        if ($search_text_template_laboratorium != '') {
          $where[$search_field_template_laboratorium.'[~]'] = $search_text_template_laboratorium;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('template_laboratorium', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('template_laboratorium', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('template_laboratorium', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_jenis_prw'=>$row['kd_jenis_prw'],
'id_template'=>$row['id_template'],
'Pemeriksaan'=>$row['Pemeriksaan'],
'satuan'=>$row['satuan'],
'nilai_rujukan_ld'=>$row['nilai_rujukan_ld'],
'nilai_rujukan_la'=>$row['nilai_rujukan_la'],
'nilai_rujukan_pd'=>$row['nilai_rujukan_pd'],
'nilai_rujukan_pa'=>$row['nilai_rujukan_pa'],
'bagian_rs'=>$row['bagian_rs'],
'bhp'=>$row['bhp'],
'bagian_perujuk'=>$row['bagian_perujuk'],
'bagian_dokter'=>$row['bagian_dokter'],
'bagian_laborat'=>$row['bagian_laborat'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'biaya_item'=>$row['biaya_item'],
'urut'=>$row['urut']

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
          $this->core->LogQuery('template_laboratorium => postData');
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

            if($this->core->loadDisabledMenu('template_laboratorium')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_jenis_prw = $_POST['kd_jenis_prw'];
$id_template = $_POST['id_template'];
$Pemeriksaan = $_POST['Pemeriksaan'];
$satuan = $_POST['satuan'];
$nilai_rujukan_ld = $_POST['nilai_rujukan_ld'];
$nilai_rujukan_la = $_POST['nilai_rujukan_la'];
$nilai_rujukan_pd = $_POST['nilai_rujukan_pd'];
$nilai_rujukan_pa = $_POST['nilai_rujukan_pa'];
$bagian_rs = $_POST['bagian_rs'];
$bhp = $_POST['bhp'];
$bagian_perujuk = $_POST['bagian_perujuk'];
$bagian_dokter = $_POST['bagian_dokter'];
$bagian_laborat = $_POST['bagian_laborat'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$biaya_item = $_POST['biaya_item'];
$urut = $_POST['urut'];

            
            $result = $this->core->db->insert('template_laboratorium', [
'kd_jenis_prw'=>$kd_jenis_prw, 'id_template'=>$id_template, 'Pemeriksaan'=>$Pemeriksaan, 'satuan'=>$satuan, 'nilai_rujukan_ld'=>$nilai_rujukan_ld, 'nilai_rujukan_la'=>$nilai_rujukan_la, 'nilai_rujukan_pd'=>$nilai_rujukan_pd, 'nilai_rujukan_pa'=>$nilai_rujukan_pa, 'bagian_rs'=>$bagian_rs, 'bhp'=>$bhp, 'bagian_perujuk'=>$bagian_perujuk, 'bagian_dokter'=>$bagian_dokter, 'bagian_laborat'=>$bagian_laborat, 'kso'=>$kso, 'menejemen'=>$menejemen, 'biaya_item'=>$biaya_item, 'urut'=>$urut
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
              $this->core->LogQuery('template_laboratorium => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('template_laboratorium')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_jenis_prw = $_POST['kd_jenis_prw'];
$id_template = $_POST['id_template'];
$Pemeriksaan = $_POST['Pemeriksaan'];
$satuan = $_POST['satuan'];
$nilai_rujukan_ld = $_POST['nilai_rujukan_ld'];
$nilai_rujukan_la = $_POST['nilai_rujukan_la'];
$nilai_rujukan_pd = $_POST['nilai_rujukan_pd'];
$nilai_rujukan_pa = $_POST['nilai_rujukan_pa'];
$bagian_rs = $_POST['bagian_rs'];
$bhp = $_POST['bhp'];
$bagian_perujuk = $_POST['bagian_perujuk'];
$bagian_dokter = $_POST['bagian_dokter'];
$bagian_laborat = $_POST['bagian_laborat'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$biaya_item = $_POST['biaya_item'];
$urut = $_POST['urut'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('template_laboratorium', [
'kd_jenis_prw'=>$kd_jenis_prw, 'id_template'=>$id_template, 'Pemeriksaan'=>$Pemeriksaan, 'satuan'=>$satuan, 'nilai_rujukan_ld'=>$nilai_rujukan_ld, 'nilai_rujukan_la'=>$nilai_rujukan_la, 'nilai_rujukan_pd'=>$nilai_rujukan_pd, 'nilai_rujukan_pa'=>$nilai_rujukan_pa, 'bagian_rs'=>$bagian_rs, 'bhp'=>$bhp, 'bagian_perujuk'=>$bagian_perujuk, 'bagian_dokter'=>$bagian_dokter, 'bagian_laborat'=>$bagian_laborat, 'kso'=>$kso, 'menejemen'=>$menejemen, 'biaya_item'=>$biaya_item, 'urut'=>$urut
            ], [
              'kd_jenis_prw'=>$kd_jenis_prw
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
              $this->core->LogQuery('template_laboratorium => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('template_laboratorium')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_jenis_prw= $_POST['kd_jenis_prw'];
            $result = $this->core->db->delete('template_laboratorium', [
              'AND' => [
                'kd_jenis_prw'=>$kd_jenis_prw
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
              $this->core->LogQuery('template_laboratorium => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('template_laboratorium')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_template_laboratorium= $_POST['search_field_template_laboratorium'];
            $search_text_template_laboratorium = $_POST['search_text_template_laboratorium'];

            if ($search_text_template_laboratorium != '') {
              $where[$search_field_template_laboratorium.'[~]'] = $search_text_template_laboratorium;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('template_laboratorium', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_jenis_prw'=>$row['kd_jenis_prw'],
'id_template'=>$row['id_template'],
'Pemeriksaan'=>$row['Pemeriksaan'],
'satuan'=>$row['satuan'],
'nilai_rujukan_ld'=>$row['nilai_rujukan_ld'],
'nilai_rujukan_la'=>$row['nilai_rujukan_la'],
'nilai_rujukan_pd'=>$row['nilai_rujukan_pd'],
'nilai_rujukan_pa'=>$row['nilai_rujukan_pa'],
'bagian_rs'=>$row['bagian_rs'],
'bhp'=>$row['bhp'],
'bagian_perujuk'=>$row['bagian_perujuk'],
'bagian_dokter'=>$row['bagian_dokter'],
'bagian_laborat'=>$row['bagian_laborat'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'biaya_item'=>$row['biaya_item'],
'urut'=>$row['urut']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('template_laboratorium => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_jenis_prw)
    {

        if($this->core->loadDisabledMenu('template_laboratorium')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('template_laboratorium', '*', ['kd_jenis_prw' => $kd_jenis_prw]);

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
          $this->core->LogQuery('template_laboratorium => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_jenis_prw)
    {

        if($this->core->loadDisabledMenu('template_laboratorium')['read'] == 'true') {
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
          $this->core->LogQuery('template_laboratorium => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_jenis_prw' => $kd_jenis_prw]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('template_laboratorium', 'satuan', ['GROUP' => 'satuan']);
      $datasets = $this->core->db->select('template_laboratorium', ['count' => \Medoo\Medoo::raw('COUNT(<satuan>)')], ['GROUP' => 'satuan']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('template_laboratorium', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('template_laboratorium', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'template_laboratorium';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('template_laboratorium => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/template_laboratorium/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/template_laboratorium/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('template_laboratorium')]);
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

        $this->core->addCSS(url([ 'template_laboratorium', 'css']));
        $this->core->addJS(url([ 'template_laboratorium', 'javascript']), 'footer');
    }

}
