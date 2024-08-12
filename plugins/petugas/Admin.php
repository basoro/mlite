<?php
namespace Plugins\Petugas;

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
        $this->assign['gol_darah'] = $this->core->getEnum('petugas', 'gol_darah');
        $this->assign['stts_nikah'] = $this->core->getEnum('petugas', 'stts_nikah');
        $this->assign['jabatan'] = $this->core->db->select('jabatan', '*');
        $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');

        $disabled_menu = $this->core->loadDisabledMenu('petugas'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['petugas' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'nip');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_petugas= isset_or($_POST['search_field_petugas']);
        $search_text_petugas = isset_or($_POST['search_text_petugas']);

        if ($search_text_petugas != '') {
          $where[$search_field_petugas.'[~]'] = $search_text_petugas;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('petugas', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('petugas', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('petugas', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'nip'=>$row['nip'],
                'nama'=>$row['nama'],
                'jk'=>$row['jk'],
                'tmp_lahir'=>$row['tmp_lahir'],
                'tgl_lahir'=>$row['tgl_lahir'],
                'gol_darah'=>$row['gol_darah'],
                'agama'=>$row['agama'],
                'stts_nikah'=>$row['stts_nikah'],
                'alamat'=>$row['alamat'],
                'kd_jbtn'=>$row['kd_jbtn'],
                'no_telp'=>$row['no_telp'],
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
          $this->core->LogQuery('petugas => postData');
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

            if($this->core->loadDisabledMenu('petugas')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $nip = $_POST['nip'];
            $nama = $_POST['nama'];
            $jk = $_POST['jk'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $gol_darah = $_POST['gol_darah'];
            $agama = $_POST['agama'];
            $stts_nikah = $_POST['stts_nikah'];
            $alamat = $_POST['alamat'];
            $kd_jbtn = $_POST['kd_jbtn'];
            $no_telp = $_POST['no_telp'];
            $status = $_POST['status'];

            
            $result = $this->core->db->insert('petugas', [
              'nip'=>$nip, 'nama'=>$nama, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'gol_darah'=>$gol_darah, 'agama'=>$agama, 'stts_nikah'=>$stts_nikah, 'alamat'=>$alamat, 'kd_jbtn'=>$kd_jbtn, 'no_telp'=>$no_telp, 'status'=>$status
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
              $this->core->LogQuery('petugas => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('petugas')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $nip = $_POST['nip'];
            $nama = $_POST['nama'];
            $jk = $_POST['jk'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $gol_darah = $_POST['gol_darah'];
            $agama = $_POST['agama'];
            $stts_nikah = $_POST['stts_nikah'];
            $alamat = $_POST['alamat'];
            $kd_jbtn = $_POST['kd_jbtn'];
            $no_telp = $_POST['no_telp'];
            $status = $_POST['status'];

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('petugas', [
              'nama'=>$nama, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'gol_darah'=>$gol_darah, 'agama'=>$agama, 'stts_nikah'=>$stts_nikah, 'alamat'=>$alamat, 'kd_jbtn'=>$kd_jbtn, 'no_telp'=>$no_telp, 'status'=>$status
            ], [
              'nip'=>$nip
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
              $this->core->LogQuery('petugas => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('petugas')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $nip= $_POST['nip'];
            $result = $this->core->db->delete('petugas', [
              'AND' => [
                'nip'=>$nip
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
              $this->core->LogQuery('petugas => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('petugas')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_petugas= $_POST['search_field_petugas'];
            $search_text_petugas = $_POST['search_text_petugas'];

            if ($search_text_petugas != '') {
              $where[$search_field_petugas.'[~]'] = $search_text_petugas;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('petugas', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'nip'=>$row['nip'],
                    'nama'=>$row['nama'],
                    'jk'=>$row['jk'],
                    'tmp_lahir'=>$row['tmp_lahir'],
                    'tgl_lahir'=>$row['tgl_lahir'],
                    'gol_darah'=>$row['gol_darah'],
                    'agama'=>$row['agama'],
                    'stts_nikah'=>$row['stts_nikah'],
                    'alamat'=>$row['alamat'],
                    'kd_jbtn'=>$row['kd_jbtn'],
                    'no_telp'=>$row['no_telp'],
                    'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('petugas => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($nip)
    {

        if($this->core->loadDisabledMenu('petugas')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('petugas', '*', ['nip' => $nip]);

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
          $this->core->LogQuery('petugas => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($nip)
    {

        if($this->core->loadDisabledMenu('petugas')['read'] == 'true') {
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
          $this->core->LogQuery('petugas => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'nip' => $nip]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('petugas', 'stts_nikah', ['GROUP' => 'stts_nikah']);
      $datasets = $this->core->db->select('petugas', ['count' => \Medoo\Medoo::raw('COUNT(<stts_nikah>)')], ['GROUP' => 'stts_nikah']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('petugas', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('petugas', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'petugas';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('petugas => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/petugas/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/petugas/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('petugas')]);
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

        $this->core->addCSS(url([ 'petugas', 'css']));
        $this->core->addJS(url([ 'petugas', 'javascript']), 'footer');
    }

}
