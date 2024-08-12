<?php
namespace Plugins\Dokter;

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
        $this->assign['pegawai'] = $this->core->db->select('pegawai', '*');
        $this->assign['jk'] = $this->core->getEnum('dokter', 'jk');
        $this->assign['gol_drh'] = $this->core->getEnum('dokter', 'gol_drh');
        $this->assign['stts_nikah'] = $this->core->getEnum('dokter', 'stts_nikah');
        $this->assign['spesialis'] = $this->core->db->select('spesialis', '*');
        $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');

        $disabled_menu = $this->core->loadDisabledMenu('dokter'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['dokter' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_dokter');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_dokter= isset_or($_POST['search_field_dokter']);
        $search_text_dokter = isset_or($_POST['search_text_dokter']);

        if ($search_text_dokter != '') {
          $where[$search_field_dokter.'[~]'] = $search_text_dokter;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('dokter', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('dokter', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('dokter', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_dokter'=>$row['kd_dokter'],
                'nm_dokter'=>$row['nm_dokter'],
                'jk'=>$row['jk'],
                'tmp_lahir'=>$row['tmp_lahir'],
                'tgl_lahir'=>$row['tgl_lahir'],
                'gol_drh'=>$row['gol_drh'],
                'agama'=>$row['agama'],
                'almt_tgl'=>$row['almt_tgl'],
                'no_telp'=>$row['no_telp'],
                'stts_nikah'=>$row['stts_nikah'],
                'kd_sps'=>$row['kd_sps'],
                'alumni'=>$row['alumni'],
                'no_ijn_praktek'=>$row['no_ijn_praktek'],
                'status'=>$row['status']
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
          $this->core->LogQuery('dokter => postData');
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

            if($this->core->loadDisabledMenu('dokter')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_dokter = $_POST['kd_dokter'];
            $nm_dokter = $_POST['nm_dokter'];
            $jk = $_POST['jk'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $gol_drh = $_POST['gol_drh'];
            $agama = $_POST['agama'];
            $almt_tgl = $_POST['almt_tgl'];
            $no_telp = $_POST['no_telp'];
            $stts_nikah = $_POST['stts_nikah'];
            $kd_sps = $_POST['kd_sps'];
            $alumni = $_POST['alumni'];
            $no_ijn_praktek = $_POST['no_ijn_praktek'];
            $status = $_POST['status'];
            
            $result = $this->core->db->insert('dokter', [
              'kd_dokter'=>$kd_dokter, 'nm_dokter'=>$nm_dokter, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'gol_drh'=>$gol_drh, 'agama'=>$agama, 'almt_tgl'=>$almt_tgl, 'no_telp'=>$no_telp, 'stts_nikah'=>$stts_nikah, 'kd_sps'=>$kd_sps, 'alumni'=>$alumni, 'no_ijn_praktek'=>$no_ijn_praktek, 'status'=>$status
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
              $this->core->LogQuery('dokter => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('dokter')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_dokter = $_POST['kd_dokter'];
            $nm_dokter = $_POST['nm_dokter'];
            $jk = $_POST['jk'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $gol_drh = $_POST['gol_drh'];
            $agama = $_POST['agama'];
            $almt_tgl = $_POST['almt_tgl'];
            $no_telp = $_POST['no_telp'];
            $stts_nikah = $_POST['stts_nikah'];
            $kd_sps = $_POST['kd_sps'];
            $alumni = $_POST['alumni'];
            $no_ijn_praktek = $_POST['no_ijn_praktek'];
            $status = $_POST['status'];

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('dokter', [
              'nm_dokter'=>$nm_dokter, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'gol_drh'=>$gol_drh, 'agama'=>$agama, 'almt_tgl'=>$almt_tgl, 'no_telp'=>$no_telp, 'stts_nikah'=>$stts_nikah, 'kd_sps'=>$kd_sps, 'alumni'=>$alumni, 'no_ijn_praktek'=>$no_ijn_praktek, 'status'=>$status
            ], [
              'kd_dokter'=>$kd_dokter
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
              $this->core->LogQuery('dokter => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('dokter')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_dokter= $_POST['kd_dokter'];
            $result = $this->core->db->delete('dokter', [
              'AND' => [
                'kd_dokter'=>$kd_dokter
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
              $this->core->LogQuery('dokter => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('dokter')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_dokter= $_POST['search_field_dokter'];
            $search_text_dokter = $_POST['search_text_dokter'];

            if ($search_text_dokter != '') {
              $where[$search_field_dokter.'[~]'] = $search_text_dokter;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('dokter', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_dokter'=>$row['kd_dokter'],
                    'nm_dokter'=>$row['nm_dokter'],
                    'jk'=>$row['jk'],
                    'tmp_lahir'=>$row['tmp_lahir'],
                    'tgl_lahir'=>$row['tgl_lahir'],
                    'gol_drh'=>$row['gol_drh'],
                    'agama'=>$row['agama'],
                    'almt_tgl'=>$row['almt_tgl'],
                    'no_telp'=>$row['no_telp'],
                    'stts_nikah'=>$row['stts_nikah'],
                    'kd_sps'=>$row['kd_sps'],
                    'alumni'=>$row['alumni'],
                    'no_ijn_praktek'=>$row['no_ijn_praktek'],
                    'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('dokter => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_dokter)
    {

        if($this->core->loadDisabledMenu('dokter')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('dokter', '*', ['kd_dokter' => $kd_dokter]);

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
          $this->core->LogQuery('dokter => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_dokter)
    {

        if($this->core->loadDisabledMenu('dokter')['read'] == 'true') {
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
          $this->core->LogQuery('dokter => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_dokter' => $kd_dokter]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('dokter', 'status', ['GROUP' => 'status']);
      $datasets = $this->core->db->select('dokter', ['count' => \Medoo\Medoo::raw('COUNT(<status>)')], ['GROUP' => 'status']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('dokter', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('dokter', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'dokter';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('dokter => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/dokter/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/dokter/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('dokter')]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addCSS(url('assets/vendor/daterange/daterange.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.css'));
        $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/xlsx.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.plugin.autotable.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/daterange/daterange.js'), 'footer');
        $this->core->addJS(url('assets/js/jquery.contextMenu.js'), 'footer');

        $this->core->addCSS(url([ 'dokter', 'css']));
        $this->core->addJS(url([ 'dokter', 'javascript']), 'footer');
    }

}
