<?php
namespace Plugins\Databarang;

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
        $this->assign['jenis'] = $this->core->db->select('jenis', '*');
        $this->assign['kodesatuan'] = $this->core->db->select('kodesatuan', '*');
        $this->assign['industrifarmasi'] = $this->core->db->select('industrifarmasi', '*');
        $this->assign['kategori_barang'] = $this->core->db->select('kategori_barang', '*');
        $this->assign['golongan_barang'] = $this->core->db->select('golongan_barang', '*');
        $disabled_menu = $this->core->loadDisabledMenu('databarang'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['databarang' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_brng');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_databarang= isset_or($_POST['search_field_databarang']);
        $search_text_databarang = isset_or($_POST['search_text_databarang']);

        if ($search_text_databarang != '') {
          $where[$search_field_databarang.'[~]'] = $search_text_databarang;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('databarang', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('databarang', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('databarang', '*', $where);

        $data = array();
        foreach($result as $row) {
            $nama_jenis = $this->core->db->get('jenis', 'nama', ['kdjns' => $row['kdjns']]);
            $nama_industri = $this->core->db->get('industrifarmasi', 'nama_industri', ['kode_industri' => $row['kode_industri']]);
            $nama_kategori = $this->core->db->get('kategori_barang', 'nama', ['kode' => $row['kode_kategori']]);
            $nama_golongan = $this->core->db->get('golongan_barang', 'nama', ['kode' => $row['kode_kategori']]);
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$row['nama_brng'],
                'kode_satbesar'=>$row['kode_satbesar'],
                'kode_sat'=>$row['kode_sat'],
                'letak_barang'=>$row['letak_barang'],
                'dasar'=>$row['dasar'],
                'h_beli'=>$row['h_beli'],
                'ralan'=>$row['ralan'],
                'kelas1'=>$row['kelas1'],
                'kelas2'=>$row['kelas2'],
                'kelas3'=>$row['kelas3'],
                'utama'=>$row['utama'],
                'vip'=>$row['vip'],
                'vvip'=>$row['vvip'],
                'beliluar'=>$row['beliluar'],
                'jualbebas'=>$row['jualbebas'],
                'karyawan'=>$row['karyawan'],
                'stokminimal'=>$row['stokminimal'],
                'kdjns'=>$row['kdjns'],
                'nama_jenis'=>$nama_jenis, 
                'isi'=>$row['isi'],
                'kapasitas'=>$row['kapasitas'],
                'expire'=>$row['expire'],
                'status'=>$row['status'],
                'kode_industri'=>$row['kode_industri'],
                'nama_industri'=>$nama_industri, 
                'kode_kategori'=>$row['kode_kategori'],
                'nama_kategori'=>$nama_kategori, 
                'kode_golongan'=>$row['kode_golongan'], 
                'nama_golongan'=>$nama_golongan
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
          $this->core->LogQuery('databarang => postData');
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

            if($this->core->loadDisabledMenu('databarang')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_brng = $_POST['kode_brng'];
            $nama_brng = $_POST['nama_brng'];
            $kode_satbesar = $_POST['kode_satbesar'];
            $kode_sat = $_POST['kode_sat'];
            $letak_barang = $_POST['letak_barang'];
            $dasar = $_POST['dasar'];
            $h_beli = $_POST['h_beli'];
            $ralan = $_POST['ralan'];
            $kelas1 = $_POST['kelas1'];
            $kelas2 = $_POST['kelas2'];
            $kelas3 = $_POST['kelas3'];
            $utama = $_POST['utama'];
            $vip = $_POST['vip'];
            $vvip = $_POST['vvip'];
            $beliluar = $_POST['beliluar'];
            $jualbebas = $_POST['jualbebas'];
            $karyawan = $_POST['karyawan'];
            $stokminimal = $_POST['stokminimal'];
            $kdjns = $_POST['kdjns'];
            $isi = $_POST['isi'];
            $kapasitas = $_POST['kapasitas'];
            $expire = $_POST['expire'];
            $status = $_POST['status'];
            $kode_industri = $_POST['kode_industri'];
            $kode_kategori = $_POST['kode_kategori'];
            $kode_golongan = $_POST['kode_golongan'];

            
            $result = $this->core->db->insert('databarang', [
              'kode_brng'=>$kode_brng, 'nama_brng'=>$nama_brng, 'kode_satbesar'=>$kode_satbesar, 'kode_sat'=>$kode_sat, 'letak_barang'=>$letak_barang, 'dasar'=>$dasar, 'h_beli'=>$h_beli, 'ralan'=>$ralan, 'kelas1'=>$kelas1, 'kelas2'=>$kelas2, 'kelas3'=>$kelas3, 'utama'=>$utama, 'vip'=>$vip, 'vvip'=>$vvip, 'beliluar'=>$beliluar, 'jualbebas'=>$jualbebas, 'karyawan'=>$karyawan, 'stokminimal'=>$stokminimal, 'kdjns'=>$kdjns, 'isi'=>$isi, 'kapasitas'=>$kapasitas, 'expire'=>$expire, 'status'=>$status, 'kode_industri'=>$kode_industri, 'kode_kategori'=>$kode_kategori, 'kode_golongan'=>$kode_golongan
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
              $this->core->LogQuery('databarang => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('databarang')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_brng = $_POST['kode_brng'];
            $nama_brng = $_POST['nama_brng'];
            $kode_satbesar = $_POST['kode_satbesar'];
            $kode_sat = $_POST['kode_sat'];
            $letak_barang = $_POST['letak_barang'];
            $dasar = $_POST['dasar'];
            $h_beli = $_POST['h_beli'];
            $ralan = $_POST['ralan'];
            $kelas1 = $_POST['kelas1'];
            $kelas2 = $_POST['kelas2'];
            $kelas3 = $_POST['kelas3'];
            $utama = $_POST['utama'];
            $vip = $_POST['vip'];
            $vvip = $_POST['vvip'];
            $beliluar = $_POST['beliluar'];
            $jualbebas = $_POST['jualbebas'];
            $karyawan = $_POST['karyawan'];
            $stokminimal = $_POST['stokminimal'];
            $kdjns = $_POST['kdjns'];
            $isi = $_POST['isi'];
            $kapasitas = $_POST['kapasitas'];
            $expire = $_POST['expire'];
            $status = $_POST['status'];
            $kode_industri = $_POST['kode_industri'];
            $kode_kategori = $_POST['kode_kategori'];
            $kode_golongan = $_POST['kode_golongan'];

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('databarang', [
              'nama_brng'=>$nama_brng, 'kode_satbesar'=>$kode_satbesar, 'kode_sat'=>$kode_sat, 'letak_barang'=>$letak_barang, 'dasar'=>$dasar, 'h_beli'=>$h_beli, 'ralan'=>$ralan, 'kelas1'=>$kelas1, 'kelas2'=>$kelas2, 'kelas3'=>$kelas3, 'utama'=>$utama, 'vip'=>$vip, 'vvip'=>$vvip, 'beliluar'=>$beliluar, 'jualbebas'=>$jualbebas, 'karyawan'=>$karyawan, 'stokminimal'=>$stokminimal, 'kdjns'=>$kdjns, 'isi'=>$isi, 'kapasitas'=>$kapasitas, 'expire'=>$expire, 'status'=>$status, 'kode_industri'=>$kode_industri, 'kode_kategori'=>$kode_kategori, 'kode_golongan'=>$kode_golongan
            ], [
              'kode_brng'=>$kode_brng
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
              $this->core->LogQuery('databarang => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('databarang')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_brng= $_POST['kode_brng'];
            $result = $this->core->db->delete('databarang', [
              'AND' => [
                'kode_brng'=>$kode_brng
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
              $this->core->LogQuery('databarang => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('databarang')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_databarang= $_POST['search_field_databarang'];
            $search_text_databarang = $_POST['search_text_databarang'];

            if ($search_text_databarang != '') {
              $where[$search_field_databarang.'[~]'] = $search_text_databarang;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('databarang', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_brng'=>$row['kode_brng'],
                    'nama_brng'=>$row['nama_brng'],
                    'kode_satbesar'=>$row['kode_satbesar'],
                    'kode_sat'=>$row['kode_sat'],
                    'letak_barang'=>$row['letak_barang'],
                    'dasar'=>$row['dasar'],
                    'h_beli'=>$row['h_beli'],
                    'ralan'=>$row['ralan'],
                    'kelas1'=>$row['kelas1'],
                    'kelas2'=>$row['kelas2'],
                    'kelas3'=>$row['kelas3'],
                    'utama'=>$row['utama'],
                    'vip'=>$row['vip'],
                    'vvip'=>$row['vvip'],
                    'beliluar'=>$row['beliluar'],
                    'jualbebas'=>$row['jualbebas'],
                    'karyawan'=>$row['karyawan'],
                    'stokminimal'=>$row['stokminimal'],
                    'kdjns'=>$row['kdjns'],
                    'isi'=>$row['isi'],
                    'kapasitas'=>$row['kapasitas'],
                    'expire'=>$row['expire'],
                    'status'=>$row['status'],
                    'kode_industri'=>$row['kode_industri'],
                    'kode_kategori'=>$row['kode_kategori'],
                    'kode_golongan'=>$row['kode_golongan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('databarang => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_brng)
    {

        if($this->core->loadDisabledMenu('databarang')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('databarang', '*', ['kode_brng' => $kode_brng]);

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
          $this->core->LogQuery('databarang => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_brng)
    {

        if($this->core->loadDisabledMenu('databarang')['read'] == 'true') {
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
          $this->core->LogQuery('databarang => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_brng' => $kode_brng]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('databarang', 'status', ['GROUP' => 'status']);
      $datasets = $this->core->db->select('databarang', ['count' => \Medoo\Medoo::raw('COUNT(<status>)')], ['GROUP' => 'status']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('databarang', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('databarang', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'databarang';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('databarang => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/databarang/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/databarang/js/scripts.js', ['settings' => $settings, 'setKodeDatabarang' => $this->core->setKodeDatabarang(), 'disabled_menu' => $this->core->loadDisabledMenu('databarang')]);
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

        $this->core->addCSS(url([ 'databarang', 'css']));
        $this->core->addJS(url([ 'databarang', 'javascript']), 'footer');
    }

}
