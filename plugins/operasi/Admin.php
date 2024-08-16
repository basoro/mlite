<?php
namespace Plugins\Operasi;

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
        $disabled_menu = $this->core->loadDisabledMenu('operasi'); 
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
        $search_field_operasi= isset_or($_POST['search_field_operasi']);
        $search_text_operasi = isset_or($_POST['search_text_operasi']);

        if ($search_text_operasi != '') {
          $where[$search_field_operasi.'[~]'] = $search_text_operasi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('operasi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('operasi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('operasi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tgl_operasi'=>$row['tgl_operasi'],
'jenis_anasthesi'=>$row['jenis_anasthesi'],
'kategori'=>$row['kategori'],
'operator1'=>$row['operator1'],
'operator2'=>$row['operator2'],
'operator3'=>$row['operator3'],
'asisten_operator1'=>$row['asisten_operator1'],
'asisten_operator2'=>$row['asisten_operator2'],
'asisten_operator3'=>$row['asisten_operator3'],
'instrumen'=>$row['instrumen'],
'dokter_anak'=>$row['dokter_anak'],
'perawaat_resusitas'=>$row['perawaat_resusitas'],
'dokter_anestesi'=>$row['dokter_anestesi'],
'asisten_anestesi'=>$row['asisten_anestesi'],
'asisten_anestesi2'=>$row['asisten_anestesi2'],
'bidan'=>$row['bidan'],
'bidan2'=>$row['bidan2'],
'bidan3'=>$row['bidan3'],
'perawat_luar'=>$row['perawat_luar'],
'omloop'=>$row['omloop'],
'omloop2'=>$row['omloop2'],
'omloop3'=>$row['omloop3'],
'omloop4'=>$row['omloop4'],
'omloop5'=>$row['omloop5'],
'dokter_pjanak'=>$row['dokter_pjanak'],
'dokter_umum'=>$row['dokter_umum'],
'kode_paket'=>$row['kode_paket'],
'biayaoperator1'=>$row['biayaoperator1'],
'biayaoperator2'=>$row['biayaoperator2'],
'biayaoperator3'=>$row['biayaoperator3'],
'biayaasisten_operator1'=>$row['biayaasisten_operator1'],
'biayaasisten_operator2'=>$row['biayaasisten_operator2'],
'biayaasisten_operator3'=>$row['biayaasisten_operator3'],
'biayainstrumen'=>$row['biayainstrumen'],
'biayadokter_anak'=>$row['biayadokter_anak'],
'biayaperawaat_resusitas'=>$row['biayaperawaat_resusitas'],
'biayadokter_anestesi'=>$row['biayadokter_anestesi'],
'biayaasisten_anestesi'=>$row['biayaasisten_anestesi'],
'biayaasisten_anestesi2'=>$row['biayaasisten_anestesi2'],
'biayabidan'=>$row['biayabidan'],
'biayabidan2'=>$row['biayabidan2'],
'biayabidan3'=>$row['biayabidan3'],
'biayaperawat_luar'=>$row['biayaperawat_luar'],
'biayaalat'=>$row['biayaalat'],
'biayasewaok'=>$row['biayasewaok'],
'akomodasi'=>$row['akomodasi'],
'bagian_rs'=>$row['bagian_rs'],
'biaya_omloop'=>$row['biaya_omloop'],
'biaya_omloop2'=>$row['biaya_omloop2'],
'biaya_omloop3'=>$row['biaya_omloop3'],
'biaya_omloop4'=>$row['biaya_omloop4'],
'biaya_omloop5'=>$row['biaya_omloop5'],
'biayasarpras'=>$row['biayasarpras'],
'biaya_dokter_pjanak'=>$row['biaya_dokter_pjanak'],
'biaya_dokter_umum'=>$row['biaya_dokter_umum'],
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
          $this->core->LogQuery('operasi => postData');
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

            if($this->core->loadDisabledMenu('operasi')['create'] == 'true') {
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
$tgl_operasi = $_POST['tgl_operasi'];
$jenis_anasthesi = $_POST['jenis_anasthesi'];
$kategori = $_POST['kategori'];
$operator1 = $_POST['operator1'];
$operator2 = $_POST['operator2'];
$operator3 = $_POST['operator3'];
$asisten_operator1 = $_POST['asisten_operator1'];
$asisten_operator2 = $_POST['asisten_operator2'];
$asisten_operator3 = $_POST['asisten_operator3'];
$instrumen = $_POST['instrumen'];
$dokter_anak = $_POST['dokter_anak'];
$perawaat_resusitas = $_POST['perawaat_resusitas'];
$dokter_anestesi = $_POST['dokter_anestesi'];
$asisten_anestesi = $_POST['asisten_anestesi'];
$asisten_anestesi2 = $_POST['asisten_anestesi2'];
$bidan = $_POST['bidan'];
$bidan2 = $_POST['bidan2'];
$bidan3 = $_POST['bidan3'];
$perawat_luar = $_POST['perawat_luar'];
$omloop = $_POST['omloop'];
$omloop2 = $_POST['omloop2'];
$omloop3 = $_POST['omloop3'];
$omloop4 = $_POST['omloop4'];
$omloop5 = $_POST['omloop5'];
$dokter_pjanak = $_POST['dokter_pjanak'];
$dokter_umum = $_POST['dokter_umum'];
$kode_paket = $_POST['kode_paket'];
$biayaoperator1 = $_POST['biayaoperator1'];
$biayaoperator2 = $_POST['biayaoperator2'];
$biayaoperator3 = $_POST['biayaoperator3'];
$biayaasisten_operator1 = $_POST['biayaasisten_operator1'];
$biayaasisten_operator2 = $_POST['biayaasisten_operator2'];
$biayaasisten_operator3 = $_POST['biayaasisten_operator3'];
$biayainstrumen = $_POST['biayainstrumen'];
$biayadokter_anak = $_POST['biayadokter_anak'];
$biayaperawaat_resusitas = $_POST['biayaperawaat_resusitas'];
$biayadokter_anestesi = $_POST['biayadokter_anestesi'];
$biayaasisten_anestesi = $_POST['biayaasisten_anestesi'];
$biayaasisten_anestesi2 = $_POST['biayaasisten_anestesi2'];
$biayabidan = $_POST['biayabidan'];
$biayabidan2 = $_POST['biayabidan2'];
$biayabidan3 = $_POST['biayabidan3'];
$biayaperawat_luar = $_POST['biayaperawat_luar'];
$biayaalat = $_POST['biayaalat'];
$biayasewaok = $_POST['biayasewaok'];
$akomodasi = $_POST['akomodasi'];
$bagian_rs = $_POST['bagian_rs'];
$biaya_omloop = $_POST['biaya_omloop'];
$biaya_omloop2 = $_POST['biaya_omloop2'];
$biaya_omloop3 = $_POST['biaya_omloop3'];
$biaya_omloop4 = $_POST['biaya_omloop4'];
$biaya_omloop5 = $_POST['biaya_omloop5'];
$biayasarpras = $_POST['biayasarpras'];
$biaya_dokter_pjanak = $_POST['biaya_dokter_pjanak'];
$biaya_dokter_umum = $_POST['biaya_dokter_umum'];
$status = $_POST['status'];

            
            $result = $this->core->db->insert('operasi', [
'no_rawat'=>$no_rawat, 'tgl_operasi'=>$tgl_operasi, 'jenis_anasthesi'=>$jenis_anasthesi, 'kategori'=>$kategori, 'operator1'=>$operator1, 'operator2'=>$operator2, 'operator3'=>$operator3, 'asisten_operator1'=>$asisten_operator1, 'asisten_operator2'=>$asisten_operator2, 'asisten_operator3'=>$asisten_operator3, 'instrumen'=>$instrumen, 'dokter_anak'=>$dokter_anak, 'perawaat_resusitas'=>$perawaat_resusitas, 'dokter_anestesi'=>$dokter_anestesi, 'asisten_anestesi'=>$asisten_anestesi, 'asisten_anestesi2'=>$asisten_anestesi2, 'bidan'=>$bidan, 'bidan2'=>$bidan2, 'bidan3'=>$bidan3, 'perawat_luar'=>$perawat_luar, 'omloop'=>$omloop, 'omloop2'=>$omloop2, 'omloop3'=>$omloop3, 'omloop4'=>$omloop4, 'omloop5'=>$omloop5, 'dokter_pjanak'=>$dokter_pjanak, 'dokter_umum'=>$dokter_umum, 'kode_paket'=>$kode_paket, 'biayaoperator1'=>$biayaoperator1, 'biayaoperator2'=>$biayaoperator2, 'biayaoperator3'=>$biayaoperator3, 'biayaasisten_operator1'=>$biayaasisten_operator1, 'biayaasisten_operator2'=>$biayaasisten_operator2, 'biayaasisten_operator3'=>$biayaasisten_operator3, 'biayainstrumen'=>$biayainstrumen, 'biayadokter_anak'=>$biayadokter_anak, 'biayaperawaat_resusitas'=>$biayaperawaat_resusitas, 'biayadokter_anestesi'=>$biayadokter_anestesi, 'biayaasisten_anestesi'=>$biayaasisten_anestesi, 'biayaasisten_anestesi2'=>$biayaasisten_anestesi2, 'biayabidan'=>$biayabidan, 'biayabidan2'=>$biayabidan2, 'biayabidan3'=>$biayabidan3, 'biayaperawat_luar'=>$biayaperawat_luar, 'biayaalat'=>$biayaalat, 'biayasewaok'=>$biayasewaok, 'akomodasi'=>$akomodasi, 'bagian_rs'=>$bagian_rs, 'biaya_omloop'=>$biaya_omloop, 'biaya_omloop2'=>$biaya_omloop2, 'biaya_omloop3'=>$biaya_omloop3, 'biaya_omloop4'=>$biaya_omloop4, 'biaya_omloop5'=>$biaya_omloop5, 'biayasarpras'=>$biayasarpras, 'biaya_dokter_pjanak'=>$biaya_dokter_pjanak, 'biaya_dokter_umum'=>$biaya_dokter_umum, 'status'=>$status
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
              $this->core->LogQuery('operasi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('operasi')['update'] == 'true') {
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
$tgl_operasi = $_POST['tgl_operasi'];
$jenis_anasthesi = $_POST['jenis_anasthesi'];
$kategori = $_POST['kategori'];
$operator1 = $_POST['operator1'];
$operator2 = $_POST['operator2'];
$operator3 = $_POST['operator3'];
$asisten_operator1 = $_POST['asisten_operator1'];
$asisten_operator2 = $_POST['asisten_operator2'];
$asisten_operator3 = $_POST['asisten_operator3'];
$instrumen = $_POST['instrumen'];
$dokter_anak = $_POST['dokter_anak'];
$perawaat_resusitas = $_POST['perawaat_resusitas'];
$dokter_anestesi = $_POST['dokter_anestesi'];
$asisten_anestesi = $_POST['asisten_anestesi'];
$asisten_anestesi2 = $_POST['asisten_anestesi2'];
$bidan = $_POST['bidan'];
$bidan2 = $_POST['bidan2'];
$bidan3 = $_POST['bidan3'];
$perawat_luar = $_POST['perawat_luar'];
$omloop = $_POST['omloop'];
$omloop2 = $_POST['omloop2'];
$omloop3 = $_POST['omloop3'];
$omloop4 = $_POST['omloop4'];
$omloop5 = $_POST['omloop5'];
$dokter_pjanak = $_POST['dokter_pjanak'];
$dokter_umum = $_POST['dokter_umum'];
$kode_paket = $_POST['kode_paket'];
$biayaoperator1 = $_POST['biayaoperator1'];
$biayaoperator2 = $_POST['biayaoperator2'];
$biayaoperator3 = $_POST['biayaoperator3'];
$biayaasisten_operator1 = $_POST['biayaasisten_operator1'];
$biayaasisten_operator2 = $_POST['biayaasisten_operator2'];
$biayaasisten_operator3 = $_POST['biayaasisten_operator3'];
$biayainstrumen = $_POST['biayainstrumen'];
$biayadokter_anak = $_POST['biayadokter_anak'];
$biayaperawaat_resusitas = $_POST['biayaperawaat_resusitas'];
$biayadokter_anestesi = $_POST['biayadokter_anestesi'];
$biayaasisten_anestesi = $_POST['biayaasisten_anestesi'];
$biayaasisten_anestesi2 = $_POST['biayaasisten_anestesi2'];
$biayabidan = $_POST['biayabidan'];
$biayabidan2 = $_POST['biayabidan2'];
$biayabidan3 = $_POST['biayabidan3'];
$biayaperawat_luar = $_POST['biayaperawat_luar'];
$biayaalat = $_POST['biayaalat'];
$biayasewaok = $_POST['biayasewaok'];
$akomodasi = $_POST['akomodasi'];
$bagian_rs = $_POST['bagian_rs'];
$biaya_omloop = $_POST['biaya_omloop'];
$biaya_omloop2 = $_POST['biaya_omloop2'];
$biaya_omloop3 = $_POST['biaya_omloop3'];
$biaya_omloop4 = $_POST['biaya_omloop4'];
$biaya_omloop5 = $_POST['biaya_omloop5'];
$biayasarpras = $_POST['biayasarpras'];
$biaya_dokter_pjanak = $_POST['biaya_dokter_pjanak'];
$biaya_dokter_umum = $_POST['biaya_dokter_umum'];
$status = $_POST['status'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('operasi', [
'no_rawat'=>$no_rawat, 'tgl_operasi'=>$tgl_operasi, 'jenis_anasthesi'=>$jenis_anasthesi, 'kategori'=>$kategori, 'operator1'=>$operator1, 'operator2'=>$operator2, 'operator3'=>$operator3, 'asisten_operator1'=>$asisten_operator1, 'asisten_operator2'=>$asisten_operator2, 'asisten_operator3'=>$asisten_operator3, 'instrumen'=>$instrumen, 'dokter_anak'=>$dokter_anak, 'perawaat_resusitas'=>$perawaat_resusitas, 'dokter_anestesi'=>$dokter_anestesi, 'asisten_anestesi'=>$asisten_anestesi, 'asisten_anestesi2'=>$asisten_anestesi2, 'bidan'=>$bidan, 'bidan2'=>$bidan2, 'bidan3'=>$bidan3, 'perawat_luar'=>$perawat_luar, 'omloop'=>$omloop, 'omloop2'=>$omloop2, 'omloop3'=>$omloop3, 'omloop4'=>$omloop4, 'omloop5'=>$omloop5, 'dokter_pjanak'=>$dokter_pjanak, 'dokter_umum'=>$dokter_umum, 'kode_paket'=>$kode_paket, 'biayaoperator1'=>$biayaoperator1, 'biayaoperator2'=>$biayaoperator2, 'biayaoperator3'=>$biayaoperator3, 'biayaasisten_operator1'=>$biayaasisten_operator1, 'biayaasisten_operator2'=>$biayaasisten_operator2, 'biayaasisten_operator3'=>$biayaasisten_operator3, 'biayainstrumen'=>$biayainstrumen, 'biayadokter_anak'=>$biayadokter_anak, 'biayaperawaat_resusitas'=>$biayaperawaat_resusitas, 'biayadokter_anestesi'=>$biayadokter_anestesi, 'biayaasisten_anestesi'=>$biayaasisten_anestesi, 'biayaasisten_anestesi2'=>$biayaasisten_anestesi2, 'biayabidan'=>$biayabidan, 'biayabidan2'=>$biayabidan2, 'biayabidan3'=>$biayabidan3, 'biayaperawat_luar'=>$biayaperawat_luar, 'biayaalat'=>$biayaalat, 'biayasewaok'=>$biayasewaok, 'akomodasi'=>$akomodasi, 'bagian_rs'=>$bagian_rs, 'biaya_omloop'=>$biaya_omloop, 'biaya_omloop2'=>$biaya_omloop2, 'biaya_omloop3'=>$biaya_omloop3, 'biaya_omloop4'=>$biaya_omloop4, 'biaya_omloop5'=>$biaya_omloop5, 'biayasarpras'=>$biayasarpras, 'biaya_dokter_pjanak'=>$biaya_dokter_pjanak, 'biaya_dokter_umum'=>$biaya_dokter_umum, 'status'=>$status
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
              $this->core->LogQuery('operasi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('operasi')['delete'] == 'true') {
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
            $result = $this->core->db->delete('operasi', [
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
              $this->core->LogQuery('operasi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('operasi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_operasi= $_POST['search_field_operasi'];
            $search_text_operasi = $_POST['search_text_operasi'];

            if ($search_text_operasi != '') {
              $where[$search_field_operasi.'[~]'] = $search_text_operasi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('operasi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tgl_operasi'=>$row['tgl_operasi'],
'jenis_anasthesi'=>$row['jenis_anasthesi'],
'kategori'=>$row['kategori'],
'operator1'=>$row['operator1'],
'operator2'=>$row['operator2'],
'operator3'=>$row['operator3'],
'asisten_operator1'=>$row['asisten_operator1'],
'asisten_operator2'=>$row['asisten_operator2'],
'asisten_operator3'=>$row['asisten_operator3'],
'instrumen'=>$row['instrumen'],
'dokter_anak'=>$row['dokter_anak'],
'perawaat_resusitas'=>$row['perawaat_resusitas'],
'dokter_anestesi'=>$row['dokter_anestesi'],
'asisten_anestesi'=>$row['asisten_anestesi'],
'asisten_anestesi2'=>$row['asisten_anestesi2'],
'bidan'=>$row['bidan'],
'bidan2'=>$row['bidan2'],
'bidan3'=>$row['bidan3'],
'perawat_luar'=>$row['perawat_luar'],
'omloop'=>$row['omloop'],
'omloop2'=>$row['omloop2'],
'omloop3'=>$row['omloop3'],
'omloop4'=>$row['omloop4'],
'omloop5'=>$row['omloop5'],
'dokter_pjanak'=>$row['dokter_pjanak'],
'dokter_umum'=>$row['dokter_umum'],
'kode_paket'=>$row['kode_paket'],
'biayaoperator1'=>$row['biayaoperator1'],
'biayaoperator2'=>$row['biayaoperator2'],
'biayaoperator3'=>$row['biayaoperator3'],
'biayaasisten_operator1'=>$row['biayaasisten_operator1'],
'biayaasisten_operator2'=>$row['biayaasisten_operator2'],
'biayaasisten_operator3'=>$row['biayaasisten_operator3'],
'biayainstrumen'=>$row['biayainstrumen'],
'biayadokter_anak'=>$row['biayadokter_anak'],
'biayaperawaat_resusitas'=>$row['biayaperawaat_resusitas'],
'biayadokter_anestesi'=>$row['biayadokter_anestesi'],
'biayaasisten_anestesi'=>$row['biayaasisten_anestesi'],
'biayaasisten_anestesi2'=>$row['biayaasisten_anestesi2'],
'biayabidan'=>$row['biayabidan'],
'biayabidan2'=>$row['biayabidan2'],
'biayabidan3'=>$row['biayabidan3'],
'biayaperawat_luar'=>$row['biayaperawat_luar'],
'biayaalat'=>$row['biayaalat'],
'biayasewaok'=>$row['biayasewaok'],
'akomodasi'=>$row['akomodasi'],
'bagian_rs'=>$row['bagian_rs'],
'biaya_omloop'=>$row['biaya_omloop'],
'biaya_omloop2'=>$row['biaya_omloop2'],
'biaya_omloop3'=>$row['biaya_omloop3'],
'biaya_omloop4'=>$row['biaya_omloop4'],
'biaya_omloop5'=>$row['biaya_omloop5'],
'biayasarpras'=>$row['biayasarpras'],
'biaya_dokter_pjanak'=>$row['biaya_dokter_pjanak'],
'biaya_dokter_umum'=>$row['biaya_dokter_umum'],
'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('operasi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('operasi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('operasi', '*', ['no_rawat' => $no_rawat]);

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
          $this->core->LogQuery('operasi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('operasi')['read'] == 'true') {
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
          $this->core->LogQuery('operasi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('operasi', 'status', ['GROUP' => 'status']);
      $datasets = $this->core->db->select('operasi', ['count' => \Medoo\Medoo::raw('COUNT(<status>)')], ['GROUP' => 'status']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('operasi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('operasi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'operasi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('operasi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/operasi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/operasi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('operasi')]);
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

        $this->core->addCSS(url([ 'operasi', 'css']));
        $this->core->addJS(url([ 'operasi', 'javascript']), 'footer');
    }

}
