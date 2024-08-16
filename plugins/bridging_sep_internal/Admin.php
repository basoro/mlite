<?php
namespace Plugins\Bridging_Sep_Internal;

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
        $disabled_menu = $this->core->loadDisabledMenu('bridging_sep_internal'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_sep');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_bridging_sep_internal= isset_or($_POST['search_field_bridging_sep_internal']);
        $search_text_bridging_sep_internal = isset_or($_POST['search_text_bridging_sep_internal']);

        if ($search_text_bridging_sep_internal != '') {
          $where[$search_field_bridging_sep_internal.'[~]'] = $search_text_bridging_sep_internal;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('bridging_sep_internal', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('bridging_sep_internal', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('bridging_sep_internal', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_sep'=>$row['no_sep'],
'no_rawat'=>$row['no_rawat'],
'tglsep'=>$row['tglsep'],
'tglrujukan'=>$row['tglrujukan'],
'no_rujukan'=>$row['no_rujukan'],
'kdppkrujukan'=>$row['kdppkrujukan'],
'nmppkrujukan'=>$row['nmppkrujukan'],
'kdppkpelayanan'=>$row['kdppkpelayanan'],
'nmppkpelayanan'=>$row['nmppkpelayanan'],
'jnspelayanan'=>$row['jnspelayanan'],
'catatan'=>$row['catatan'],
'diagawal'=>$row['diagawal'],
'nmdiagnosaawal'=>$row['nmdiagnosaawal'],
'kdpolitujuan'=>$row['kdpolitujuan'],
'nmpolitujuan'=>$row['nmpolitujuan'],
'klsrawat'=>$row['klsrawat'],
'klsnaik'=>$row['klsnaik'],
'pembiayaan'=>$row['pembiayaan'],
'pjnaikkelas'=>$row['pjnaikkelas'],
'lakalantas'=>$row['lakalantas'],
'user'=>$row['user'],
'nomr'=>$row['nomr'],
'nama_pasien'=>$row['nama_pasien'],
'tanggal_lahir'=>$row['tanggal_lahir'],
'peserta'=>$row['peserta'],
'jkel'=>$row['jkel'],
'no_kartu'=>$row['no_kartu'],
'tglpulang'=>$row['tglpulang'],
'asal_rujukan'=>$row['asal_rujukan'],
'eksekutif'=>$row['eksekutif'],
'cob'=>$row['cob'],
'notelep'=>$row['notelep'],
'katarak'=>$row['katarak'],
'tglkkl'=>$row['tglkkl'],
'keterangankkl'=>$row['keterangankkl'],
'suplesi'=>$row['suplesi'],
'no_sep_suplesi'=>$row['no_sep_suplesi'],
'kdprop'=>$row['kdprop'],
'nmprop'=>$row['nmprop'],
'kdkab'=>$row['kdkab'],
'nmkab'=>$row['nmkab'],
'kdkec'=>$row['kdkec'],
'nmkec'=>$row['nmkec'],
'noskdp'=>$row['noskdp'],
'kddpjp'=>$row['kddpjp'],
'nmdpdjp'=>$row['nmdpdjp'],
'tujuankunjungan'=>$row['tujuankunjungan'],
'flagprosedur'=>$row['flagprosedur'],
'penunjang'=>$row['penunjang'],
'asesmenpelayanan'=>$row['asesmenpelayanan'],
'kddpjplayanan'=>$row['kddpjplayanan'],
'nmdpjplayanan'=>$row['nmdpjplayanan']

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
          $this->core->LogQuery('bridging_sep_internal => postData');
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

            if($this->core->loadDisabledMenu('bridging_sep_internal')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_sep = $_POST['no_sep'];
$no_rawat = $_POST['no_rawat'];
$tglsep = $_POST['tglsep'];
$tglrujukan = $_POST['tglrujukan'];
$no_rujukan = $_POST['no_rujukan'];
$kdppkrujukan = $_POST['kdppkrujukan'];
$nmppkrujukan = $_POST['nmppkrujukan'];
$kdppkpelayanan = $_POST['kdppkpelayanan'];
$nmppkpelayanan = $_POST['nmppkpelayanan'];
$jnspelayanan = $_POST['jnspelayanan'];
$catatan = $_POST['catatan'];
$diagawal = $_POST['diagawal'];
$nmdiagnosaawal = $_POST['nmdiagnosaawal'];
$kdpolitujuan = $_POST['kdpolitujuan'];
$nmpolitujuan = $_POST['nmpolitujuan'];
$klsrawat = $_POST['klsrawat'];
$klsnaik = $_POST['klsnaik'];
$pembiayaan = $_POST['pembiayaan'];
$pjnaikkelas = $_POST['pjnaikkelas'];
$lakalantas = $_POST['lakalantas'];
$user = $_POST['user'];
$nomr = $_POST['nomr'];
$nama_pasien = $_POST['nama_pasien'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$peserta = $_POST['peserta'];
$jkel = $_POST['jkel'];
$no_kartu = $_POST['no_kartu'];
$tglpulang = $_POST['tglpulang'];
$asal_rujukan = $_POST['asal_rujukan'];
$eksekutif = $_POST['eksekutif'];
$cob = $_POST['cob'];
$notelep = $_POST['notelep'];
$katarak = $_POST['katarak'];
$tglkkl = $_POST['tglkkl'];
$keterangankkl = $_POST['keterangankkl'];
$suplesi = $_POST['suplesi'];
$no_sep_suplesi = $_POST['no_sep_suplesi'];
$kdprop = $_POST['kdprop'];
$nmprop = $_POST['nmprop'];
$kdkab = $_POST['kdkab'];
$nmkab = $_POST['nmkab'];
$kdkec = $_POST['kdkec'];
$nmkec = $_POST['nmkec'];
$noskdp = $_POST['noskdp'];
$kddpjp = $_POST['kddpjp'];
$nmdpdjp = $_POST['nmdpdjp'];
$tujuankunjungan = $_POST['tujuankunjungan'];
$flagprosedur = $_POST['flagprosedur'];
$penunjang = $_POST['penunjang'];
$asesmenpelayanan = $_POST['asesmenpelayanan'];
$kddpjplayanan = $_POST['kddpjplayanan'];
$nmdpjplayanan = $_POST['nmdpjplayanan'];

            
            $result = $this->core->db->insert('bridging_sep_internal', [
'no_sep'=>$no_sep, 'no_rawat'=>$no_rawat, 'tglsep'=>$tglsep, 'tglrujukan'=>$tglrujukan, 'no_rujukan'=>$no_rujukan, 'kdppkrujukan'=>$kdppkrujukan, 'nmppkrujukan'=>$nmppkrujukan, 'kdppkpelayanan'=>$kdppkpelayanan, 'nmppkpelayanan'=>$nmppkpelayanan, 'jnspelayanan'=>$jnspelayanan, 'catatan'=>$catatan, 'diagawal'=>$diagawal, 'nmdiagnosaawal'=>$nmdiagnosaawal, 'kdpolitujuan'=>$kdpolitujuan, 'nmpolitujuan'=>$nmpolitujuan, 'klsrawat'=>$klsrawat, 'klsnaik'=>$klsnaik, 'pembiayaan'=>$pembiayaan, 'pjnaikkelas'=>$pjnaikkelas, 'lakalantas'=>$lakalantas, 'user'=>$user, 'nomr'=>$nomr, 'nama_pasien'=>$nama_pasien, 'tanggal_lahir'=>$tanggal_lahir, 'peserta'=>$peserta, 'jkel'=>$jkel, 'no_kartu'=>$no_kartu, 'tglpulang'=>$tglpulang, 'asal_rujukan'=>$asal_rujukan, 'eksekutif'=>$eksekutif, 'cob'=>$cob, 'notelep'=>$notelep, 'katarak'=>$katarak, 'tglkkl'=>$tglkkl, 'keterangankkl'=>$keterangankkl, 'suplesi'=>$suplesi, 'no_sep_suplesi'=>$no_sep_suplesi, 'kdprop'=>$kdprop, 'nmprop'=>$nmprop, 'kdkab'=>$kdkab, 'nmkab'=>$nmkab, 'kdkec'=>$kdkec, 'nmkec'=>$nmkec, 'noskdp'=>$noskdp, 'kddpjp'=>$kddpjp, 'nmdpdjp'=>$nmdpdjp, 'tujuankunjungan'=>$tujuankunjungan, 'flagprosedur'=>$flagprosedur, 'penunjang'=>$penunjang, 'asesmenpelayanan'=>$asesmenpelayanan, 'kddpjplayanan'=>$kddpjplayanan, 'nmdpjplayanan'=>$nmdpjplayanan
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
              $this->core->LogQuery('bridging_sep_internal => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('bridging_sep_internal')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_sep = $_POST['no_sep'];
$no_rawat = $_POST['no_rawat'];
$tglsep = $_POST['tglsep'];
$tglrujukan = $_POST['tglrujukan'];
$no_rujukan = $_POST['no_rujukan'];
$kdppkrujukan = $_POST['kdppkrujukan'];
$nmppkrujukan = $_POST['nmppkrujukan'];
$kdppkpelayanan = $_POST['kdppkpelayanan'];
$nmppkpelayanan = $_POST['nmppkpelayanan'];
$jnspelayanan = $_POST['jnspelayanan'];
$catatan = $_POST['catatan'];
$diagawal = $_POST['diagawal'];
$nmdiagnosaawal = $_POST['nmdiagnosaawal'];
$kdpolitujuan = $_POST['kdpolitujuan'];
$nmpolitujuan = $_POST['nmpolitujuan'];
$klsrawat = $_POST['klsrawat'];
$klsnaik = $_POST['klsnaik'];
$pembiayaan = $_POST['pembiayaan'];
$pjnaikkelas = $_POST['pjnaikkelas'];
$lakalantas = $_POST['lakalantas'];
$user = $_POST['user'];
$nomr = $_POST['nomr'];
$nama_pasien = $_POST['nama_pasien'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$peserta = $_POST['peserta'];
$jkel = $_POST['jkel'];
$no_kartu = $_POST['no_kartu'];
$tglpulang = $_POST['tglpulang'];
$asal_rujukan = $_POST['asal_rujukan'];
$eksekutif = $_POST['eksekutif'];
$cob = $_POST['cob'];
$notelep = $_POST['notelep'];
$katarak = $_POST['katarak'];
$tglkkl = $_POST['tglkkl'];
$keterangankkl = $_POST['keterangankkl'];
$suplesi = $_POST['suplesi'];
$no_sep_suplesi = $_POST['no_sep_suplesi'];
$kdprop = $_POST['kdprop'];
$nmprop = $_POST['nmprop'];
$kdkab = $_POST['kdkab'];
$nmkab = $_POST['nmkab'];
$kdkec = $_POST['kdkec'];
$nmkec = $_POST['nmkec'];
$noskdp = $_POST['noskdp'];
$kddpjp = $_POST['kddpjp'];
$nmdpdjp = $_POST['nmdpdjp'];
$tujuankunjungan = $_POST['tujuankunjungan'];
$flagprosedur = $_POST['flagprosedur'];
$penunjang = $_POST['penunjang'];
$asesmenpelayanan = $_POST['asesmenpelayanan'];
$kddpjplayanan = $_POST['kddpjplayanan'];
$nmdpjplayanan = $_POST['nmdpjplayanan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('bridging_sep_internal', [
'no_sep'=>$no_sep, 'no_rawat'=>$no_rawat, 'tglsep'=>$tglsep, 'tglrujukan'=>$tglrujukan, 'no_rujukan'=>$no_rujukan, 'kdppkrujukan'=>$kdppkrujukan, 'nmppkrujukan'=>$nmppkrujukan, 'kdppkpelayanan'=>$kdppkpelayanan, 'nmppkpelayanan'=>$nmppkpelayanan, 'jnspelayanan'=>$jnspelayanan, 'catatan'=>$catatan, 'diagawal'=>$diagawal, 'nmdiagnosaawal'=>$nmdiagnosaawal, 'kdpolitujuan'=>$kdpolitujuan, 'nmpolitujuan'=>$nmpolitujuan, 'klsrawat'=>$klsrawat, 'klsnaik'=>$klsnaik, 'pembiayaan'=>$pembiayaan, 'pjnaikkelas'=>$pjnaikkelas, 'lakalantas'=>$lakalantas, 'user'=>$user, 'nomr'=>$nomr, 'nama_pasien'=>$nama_pasien, 'tanggal_lahir'=>$tanggal_lahir, 'peserta'=>$peserta, 'jkel'=>$jkel, 'no_kartu'=>$no_kartu, 'tglpulang'=>$tglpulang, 'asal_rujukan'=>$asal_rujukan, 'eksekutif'=>$eksekutif, 'cob'=>$cob, 'notelep'=>$notelep, 'katarak'=>$katarak, 'tglkkl'=>$tglkkl, 'keterangankkl'=>$keterangankkl, 'suplesi'=>$suplesi, 'no_sep_suplesi'=>$no_sep_suplesi, 'kdprop'=>$kdprop, 'nmprop'=>$nmprop, 'kdkab'=>$kdkab, 'nmkab'=>$nmkab, 'kdkec'=>$kdkec, 'nmkec'=>$nmkec, 'noskdp'=>$noskdp, 'kddpjp'=>$kddpjp, 'nmdpdjp'=>$nmdpdjp, 'tujuankunjungan'=>$tujuankunjungan, 'flagprosedur'=>$flagprosedur, 'penunjang'=>$penunjang, 'asesmenpelayanan'=>$asesmenpelayanan, 'kddpjplayanan'=>$kddpjplayanan, 'nmdpjplayanan'=>$nmdpjplayanan
            ], [
              'no_sep'=>$no_sep
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
              $this->core->LogQuery('bridging_sep_internal => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('bridging_sep_internal')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_sep= $_POST['no_sep'];
            $result = $this->core->db->delete('bridging_sep_internal', [
              'AND' => [
                'no_sep'=>$no_sep
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
              $this->core->LogQuery('bridging_sep_internal => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('bridging_sep_internal')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_bridging_sep_internal= $_POST['search_field_bridging_sep_internal'];
            $search_text_bridging_sep_internal = $_POST['search_text_bridging_sep_internal'];

            if ($search_text_bridging_sep_internal != '') {
              $where[$search_field_bridging_sep_internal.'[~]'] = $search_text_bridging_sep_internal;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('bridging_sep_internal', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_sep'=>$row['no_sep'],
'no_rawat'=>$row['no_rawat'],
'tglsep'=>$row['tglsep'],
'tglrujukan'=>$row['tglrujukan'],
'no_rujukan'=>$row['no_rujukan'],
'kdppkrujukan'=>$row['kdppkrujukan'],
'nmppkrujukan'=>$row['nmppkrujukan'],
'kdppkpelayanan'=>$row['kdppkpelayanan'],
'nmppkpelayanan'=>$row['nmppkpelayanan'],
'jnspelayanan'=>$row['jnspelayanan'],
'catatan'=>$row['catatan'],
'diagawal'=>$row['diagawal'],
'nmdiagnosaawal'=>$row['nmdiagnosaawal'],
'kdpolitujuan'=>$row['kdpolitujuan'],
'nmpolitujuan'=>$row['nmpolitujuan'],
'klsrawat'=>$row['klsrawat'],
'klsnaik'=>$row['klsnaik'],
'pembiayaan'=>$row['pembiayaan'],
'pjnaikkelas'=>$row['pjnaikkelas'],
'lakalantas'=>$row['lakalantas'],
'user'=>$row['user'],
'nomr'=>$row['nomr'],
'nama_pasien'=>$row['nama_pasien'],
'tanggal_lahir'=>$row['tanggal_lahir'],
'peserta'=>$row['peserta'],
'jkel'=>$row['jkel'],
'no_kartu'=>$row['no_kartu'],
'tglpulang'=>$row['tglpulang'],
'asal_rujukan'=>$row['asal_rujukan'],
'eksekutif'=>$row['eksekutif'],
'cob'=>$row['cob'],
'notelep'=>$row['notelep'],
'katarak'=>$row['katarak'],
'tglkkl'=>$row['tglkkl'],
'keterangankkl'=>$row['keterangankkl'],
'suplesi'=>$row['suplesi'],
'no_sep_suplesi'=>$row['no_sep_suplesi'],
'kdprop'=>$row['kdprop'],
'nmprop'=>$row['nmprop'],
'kdkab'=>$row['kdkab'],
'nmkab'=>$row['nmkab'],
'kdkec'=>$row['kdkec'],
'nmkec'=>$row['nmkec'],
'noskdp'=>$row['noskdp'],
'kddpjp'=>$row['kddpjp'],
'nmdpdjp'=>$row['nmdpdjp'],
'tujuankunjungan'=>$row['tujuankunjungan'],
'flagprosedur'=>$row['flagprosedur'],
'penunjang'=>$row['penunjang'],
'asesmenpelayanan'=>$row['asesmenpelayanan'],
'kddpjplayanan'=>$row['kddpjplayanan'],
'nmdpjplayanan'=>$row['nmdpjplayanan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('bridging_sep_internal => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_sep_internal')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('bridging_sep_internal', '*', ['no_sep' => $no_sep]);

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
          $this->core->LogQuery('bridging_sep_internal => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_sep_internal')['read'] == 'true') {
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
          $this->core->LogQuery('bridging_sep_internal => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_sep' => $no_sep]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('bridging_sep_internal', 'jnspelayanan', ['GROUP' => 'jnspelayanan']);
      $datasets = $this->core->db->select('bridging_sep_internal', ['count' => \Medoo\Medoo::raw('COUNT(<jnspelayanan>)')], ['GROUP' => 'jnspelayanan']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('bridging_sep_internal', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('bridging_sep_internal', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'bridging_sep_internal';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('bridging_sep_internal => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/bridging_sep_internal/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/bridging_sep_internal/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('bridging_sep_internal')]);
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

        $this->core->addCSS(url([ 'bridging_sep_internal', 'css']));
        $this->core->addJS(url([ 'bridging_sep_internal', 'javascript']), 'footer');
    }

}
