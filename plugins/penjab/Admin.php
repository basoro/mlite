<?php
namespace Plugins\Penjab;

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
        $disabled_menu = $this->core->loadDisabledMenu('penjab'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_pj');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_penjab= isset_or($_POST['search_field_penjab']);
        $search_text_penjab = isset_or($_POST['search_text_penjab']);

        if ($search_text_penjab != '') {
          $where[$search_field_penjab.'[~]'] = $search_text_penjab;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('penjab', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('penjab', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('penjab', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_pj'=>$row['kd_pj'],
                'png_jawab'=>$row['png_jawab'],
                'nama_perusahaan'=>$row['nama_perusahaan'],
                'alamat_asuransi'=>$row['alamat_asuransi'],
                'no_telp'=>$row['no_telp'],
                'attn'=>$row['attn'],
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
          $this->core->LogQuery('penjab => postData');
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

            if($this->core->loadDisabledMenu('penjab')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_pj = $_POST['kd_pj'];
            $png_jawab = $_POST['png_jawab'];
            $nama_perusahaan = $_POST['nama_perusahaan'];
            $alamat_asuransi = $_POST['alamat_asuransi'];
            $no_telp = $_POST['no_telp'];
            $attn = $_POST['attn'];
            $status = $_POST['status'];
            
            $result = $this->core->db->insert('penjab', [
              'kd_pj'=>$kd_pj, 'png_jawab'=>$png_jawab, 'nama_perusahaan'=>$nama_perusahaan, 'alamat_asuransi'=>$alamat_asuransi, 'no_telp'=>$no_telp, 'attn'=>$attn, 'status'=>$status
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
              $this->core->LogQuery('penjab => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('penjab')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_pj = $_POST['kd_pj'];
            $png_jawab = $_POST['png_jawab'];
            $nama_perusahaan = $_POST['nama_perusahaan'];
            $alamat_asuransi = $_POST['alamat_asuransi'];
            $no_telp = $_POST['no_telp'];
            $attn = $_POST['attn'];
            $status = $_POST['status'];

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('penjab', [
              'png_jawab'=>$png_jawab, 'nama_perusahaan'=>$nama_perusahaan, 'alamat_asuransi'=>$alamat_asuransi, 'no_telp'=>$no_telp, 'attn'=>$attn, 'status'=>$status
            ], [
              'kd_pj'=>$kd_pj
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
              $this->core->LogQuery('penjab => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('penjab')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_pj= $_POST['kd_pj'];
            $result = $this->core->db->delete('penjab', [
              'AND' => [
                'kd_pj'=>$kd_pj
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
              $this->core->LogQuery('penjab => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('penjab')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_penjab= $_POST['search_field_penjab'];
            $search_text_penjab = $_POST['search_text_penjab'];

            if ($search_text_penjab != '') {
              $where[$search_field_penjab.'[~]'] = $search_text_penjab;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('penjab', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_pj'=>$row['kd_pj'],
                    'png_jawab'=>$row['png_jawab'],
                    'nama_perusahaan'=>$row['nama_perusahaan'],
                    'alamat_asuransi'=>$row['alamat_asuransi'],
                    'no_telp'=>$row['no_telp'],
                    'attn'=>$row['attn'],
                    'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('penjab => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_pj)
    {

        if($this->core->loadDisabledMenu('penjab')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('penjab', '*', ['kd_pj' => $kd_pj]);

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
          $this->core->LogQuery('penjab => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_pj)
    {

        if($this->core->loadDisabledMenu('penjab')['read'] == 'true') {
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
          $this->core->LogQuery('penjab => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_pj' => $kd_pj]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('penjab', 'status', ['GROUP' => 'status']);
      $datasets = $this->core->db->select('penjab', ['count' => \Medoo\Medoo::raw('COUNT(<status>)')], ['GROUP' => 'status']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('penjab', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('penjab', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'penjab';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('penjab => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/penjab/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penjab/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('penjab')]);
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

        $this->core->addCSS(url([ 'penjab', 'css']));
        $this->core->addJS(url([ 'penjab', 'javascript']), 'footer');
    }

}
