<?php
namespace Plugins\Bridging_Sep;

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
        $disabled_menu = $this->core->loadDisabledMenu('bridging_sep'); 
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
        $search_field_bridging_sep= isset_or($_POST['search_field_bridging_sep']);
        $search_text_bridging_sep = isset_or($_POST['search_text_bridging_sep']);

        if ($search_text_bridging_sep != '') {
          $where[$search_field_bridging_sep.'[~]'] = $search_text_bridging_sep;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('bridging_sep', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('bridging_sep', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('bridging_sep', '*', $where);

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
          $this->core->LogQuery('bridging_sep => postData');
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

            if($this->core->loadDisabledMenu('bridging_sep')['create'] == 'true') {
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
            $tglsep = $_POST['tglsep'];
                        
            $no_rawat = $_POST['no_rawat'];
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
            $nolp = $_POST['nolp'];
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
            if($no_rujukan == $noskdp) {
              $noskdp = '';
            }
            $kddpjp = $_POST['kddpjp'];
            $nmdpdjp = $_POST['nmdpdjp'];
            $tujuankunjungan = $_POST['tujuankunjungan'];
            $flagprosedur = $_POST['flagprosedur'];
            $penunjang = $_POST['penunjang'];
            $asesmenpelayanan = $_POST['asesmenpelayanan'];
            $kddpjplayanan = $_POST['kddpjplayanan'];
            $nmdpjplayanan = $_POST['nmdpjplayanan'];

            $data = [
              'request' => [
                't_sep' => [
                  'noKartu' => $no_kartu,
                  'tglSep' => $tglsep,
                  'ppkPelayanan' => $kdppkpelayanan,
                  'jnsPelayanan' => $jnspelayanan,
                  'klsRawat' => [
                    'klsRawatHak' => $klsrawat,
                    'klsRawatNaik' => $klsnaik,
                    'pembiayaan' => $pembiayaan,
                    'penanggungJawab' => $pjnaikkelas
                  ],
                  'noMR' => $nomr,
                  'rujukan' => [
                    'asalRujukan' => $asal_rujukan,
                    'tglRujukan' => $tglrujukan,
                    'noRujukan' => $no_rujukan,
                    'ppkRujukan' => $kdppkrujukan
                  ],
                  'catatan' => $catatan,
                  'diagAwal' => $diagawal,
                  'poli' => [
                    'tujuan' => $kdpolitujuan,
                    'eksekutif' => $eksekutif
                  ],
                  'cob' => [
                    'cob' => $cob
                  ],
                  'katarak' => [
                    'katarak' => $katarak
                  ],
                  'jaminan' => [
                    'lakaLantas' => $lakalantas,
                    'noLP' => $nolp,
                    'penjamin' => [
                      'tglKejadian' => $tglkkl,
                      'keterangan' => $keterangankkl,
                      'suplesi' => [
                        'suplesi' => $suplesi,
                        'noSepSuplesi' => $no_sep_suplesi,
                        'lokasiLaka' => [
                          'kdPropinsi' => $kdprop,
                          'kdKabupaten' => $kdkab,
                          'kdKecamatan' => $kdkec
                        ]
                      ]
                    ]
                  ],
                  'tujuanKunj' => $tujuankunjungan,
                  'flagProcedure' => $flagprosedur,
                  'kdPenunjang' => $penunjang,
                  'assesmentPel' => $asesmenpelayanan,
                  'skdp' => [
                    'noSurat' => $noskdp,
                    'kodeDPJP' => $kddpjp
                  ],
                  'dpjpLayan' => $kddpjplayanan,
                  'noTelp' => $notelep,
                  'user' => $user
                ]
              ]
            ];

            $user = $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
            $tanggal = date('Y-m-d');
            $endpoint = 'postAksi => insertSEP';

            $this->core->db->insert('mlite_log_api_vclaim', [
              'user' => $user, 
              'tanggal' => $tanggal, 
              'endpoint' => $endpoint, 
              'result' => json_encode($data, JSON_PRETTY_PRINT)
            ]);

            // $data = json_encode($data);
            $query = new \Bridging\Bpjs\Vclaim\Sep($this->core->vclaim);
            $array = $query->insertSEP($data);
            // echo json_encode($array);
            if($array['metaData']['code'] == '200') {
              $no_sep = $array['response']['sep']['noSep'];
              $tglsep = $array['response']['sep']['tglSep']; 
              if($_POST['asal_rujukan'] == '1') {
                $asal_rujukan = '1. Faskes 1';
              }
              if($_POST['asal_rujukan'] == '2') {
                $asal_rujukan = '2. Faskes 2(RS)';
              }

              $result = $this->core->db->insert('bridging_sep', [
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
                $this->core->LogQuery('bridging_sep => postAksi => add');
              }
  
              echo json_encode($data);   

            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $array
              );
              echo json_encode($data);
            }

            $endpoint = 'insertSEP';

            $this->core->db->insert('mlite_log_api_vclaim', [
              'user' => $user, 
              'tanggal' => $tanggal, 
              'endpoint' => $endpoint, 
              'result' => json_encode($array, JSON_PRETTY_PRINT)
            ]);
 
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('bridging_sep')['update'] == 'true') {
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

            $result = $this->core->db->update('bridging_sep', [
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
              $this->core->LogQuery('bridging_sep => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('bridging_sep')['delete'] == 'true') {
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
            $result = $this->core->db->delete('bridging_sep', [
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
              $this->core->LogQuery('bridging_sep => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('bridging_sep')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_bridging_sep= $_POST['search_field_bridging_sep'];
            $search_text_bridging_sep = $_POST['search_text_bridging_sep'];

            if ($search_text_bridging_sep != '') {
              $where[$search_field_bridging_sep.'[~]'] = $search_text_bridging_sep;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('bridging_sep', '*', $where);

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
              $this->core->LogQuery('bridging_sep => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_sep')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('bridging_sep', '*', ['no_sep' => $no_sep]);

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
          $this->core->LogQuery('bridging_sep => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_sep')['read'] == 'true') {
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
          $this->core->LogQuery('bridging_sep => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_sep' => $no_sep]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('bridging_sep', 'kdpolitujuan', ['GROUP' => 'kdpolitujuan']);
      $datasets = $this->core->db->select('bridging_sep', ['count' => \Medoo\Medoo::raw('COUNT(<kdpolitujuan>)')], ['GROUP' => 'kdpolitujuan']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('bridging_sep', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('bridging_sep', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'bridging_sep';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('bridging_sep => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }
    
    public function getSep()
    {
      echo $this->draw('sep.html');
      exit();
    }

    public function getCekPeserta($term, $type = '')
    {
        $user = $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
        $tanggal = date('Y-m-d');
        $endpoint = 'getCekPeserta';
    
        $pasien = $this->core->db->get('pasien', '*', ['no_rkm_medis' => $term]);

        $cek_log = $this->core->db->has('mlite_log_api_vclaim', [
          'AND' => [
            'tanggal' => $tanggal, 
            'endpoint' => $endpoint, 
            'result[~]' => ['"noKartu": "'.isset_or($pasien['no_peserta']).'"', '"nik": "'.isset_or($pasien['no_ktp']).'"', ]
          ]
        ]);
        if($cek_log) {
          if($type == 'noka' || $type == 'nik') { 
            echo '<pre class="text-info">'.$this->core->db->get('mlite_log_api_vclaim', 'result', [
              'tanggal' => $tanggal, 
              'endpoint' => $endpoint, 
              'result[~]' => ['"noKartu": "'.$pasien['no_peserta'].'"', '"nik": "'.$pasien['no_ktp'].'"', ]
            ]).'</pre>';
          } else {
            echo $this->core->db->get('mlite_log_api_vclaim', 'result', [
              'tanggal' => $tanggal, 
              'endpoint' => $endpoint, 
              'result[~]' => ['"noKartu": "'.$pasien['no_peserta'].'"', '"nik": "'.$pasien['no_ktp'].'"', ]
            ]);  
          }
          
        } else {
          
          $peserta = new \Bridging\Bpjs\VClaim\Peserta($this->core->vclaim);
          if($type == 'noka') {
            $array = $peserta->getByNoKartu($pasien['no_peserta'],date('Y-m-d'));
            echo '<pre class="text-danger">'.json_encode($array, JSON_PRETTY_PRINT).'</pre>';
          } 
          if($type == 'nik') {
            $array = $peserta->getByNIK($pasien['no_ktp'],date('Y-m-d'));
            echo '<pre class="text-danger">'.json_encode($array, JSON_PRETTY_PRINT).'</pre>';
          }  
          if($type == '1'){
            $array = $peserta->getByNoKartu($term,date('Y-m-d'));
            echo json_encode($array, JSON_PRETTY_PRINT);
          } 
          if($type == '2') {
            $array = $peserta->getByNIK($term,date('Y-m-d'));
            echo json_encode($array, JSON_PRETTY_PRINT);
          }

          // $result = $array;
          $this->core->db->insert('mlite_log_api_vclaim', [
            'user' => $user, 
            'tanggal' => $tanggal, 
            'endpoint' => $endpoint, 
            'result' => json_encode($array, JSON_PRETTY_PRINT)
          ]);

        }

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('getCekPeserta');
        }

        exit();
    }

    public function postCekRujukan()
    {
      $searchBy = $_POST['searchBy'];
      if($_POST['searchBy'] == '2') {
        $searchBy = 'RS';
      }
      $keyword = $_POST['keyword'];
      $multi = $_POST['multi'];
      $query = new \Bridging\Bpjs\VClaim\Rujukan($this->core->vclaim);
      $array = $query->cariByNoKartu($searchBy, $keyword, $multi);
      echo json_encode($array);
      // echo '{"metaData":{"code":"200","message":"OK"},"response":{"asalFaskes":"1","rujukan":[{"noKunjungan":"0287G0040324Y000050","tglKunjungan":"2024-03-28","provPerujuk":{"kode":"0287G004","nama":"DRG. FAISOL BASORO"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"31 tahun ,9 bulan ,20 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"K04.1","nama":"Necrosis of pulp"},"keluhan":"-","poliRujukan":{"kode":"GIG","nama":"GIGI"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}},{"noKunjungan":"0287G0040324Y000014","tglKunjungan":"2024-03-07","provPerujuk":{"kode":"0287G004","nama":"DRG. FAISOL BASORO"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"31 tahun ,9 bulan ,1 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"K04.2","nama":"Pulp degeneration"},"keluhan":"-","poliRujukan":{"kode":"GIG","nama":"GIGI"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}},{"noKunjungan":"0287G0040324Y000010","tglKunjungan":"2024-03-03","provPerujuk":{"kode":"0287G004","nama":"DRG. FAISOL BASORO"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"31 tahun ,9 bulan ,5 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"K04.1","nama":"Necrosis of pulp"},"keluhan":"-","poliRujukan":{"kode":"GIG","nama":"GIGI"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}},{"noKunjungan":"0287G0040324Y000001","tglKunjungan":"2024-03-01","provPerujuk":{"kode":"0287G004","nama":"DRG. FAISOL BASORO"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"31 tahun ,9 bulan ,7 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"K04.0","nama":"Pulpitis"},"keluhan":"Sakit gigi","poliRujukan":{"kode":"GIG","nama":"GIGI"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}},{"noKunjungan":"0287G0040124Y000025","tglKunjungan":"2024-01-13","provPerujuk":{"kode":"0287G004","nama":"DRG. FAISOL BASORO"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"31 tahun ,7 bulan ,5 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"K04","nama":"Diseases of pulp and periapical tissues"},"keluhan":"-","poliRujukan":{"kode":"GIG","nama":"GIGI"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}},{"noKunjungan":"0287G0040523P000026","tglKunjungan":"2023-05-25","provPerujuk":{"kode":"0287G004","nama":"DRG. FAISOL BASORO"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"30 tahun ,11 bulan ,17 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"K04.2","nama":"Pulp degeneration"},"keluhan":"Sakit gigi","poliRujukan":{"kode":"GIG","nama":"GIGI"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}},{"noKunjungan":"0287U0110922P000022","tglKunjungan":"2022-09-30","provPerujuk":{"kode":"0287U011","nama":"dr. RESMILASARI"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"30 tahun ,3 bulan ,22 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"G44.2","nama":"Tension-type headache"},"keluhan":"Kontrol","poliRujukan":{"kode":"INT","nama":"PENYAKIT DALAM"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}},{"noKunjungan":"0287U0110821P000006","tglKunjungan":"2021-08-15","provPerujuk":{"kode":"0287U011","nama":"dr. RESMILASARI"},"peserta":{"noKartu":"0001098521223","nik":"6307070806920002","nama":"M. ADLY HIDAYAT","pisa":"1","sex":"L","mr":{"noMR":"062690","noTelepon":"082149099444"},"tglLahir":"1992-06-08","tglCetakKartu":"2018-06-05","tglTAT":"2024-12-31","tglTMT":"2018-05-07","statusPeserta":{"kode":"0","keterangan":"AKTIF"},"provUmum":{"kdProvider":"0287U011","nmProvider":"dr. RESMILASARI"},"jenisPeserta":{"kode":"10","keterangan":"PEGAWAI PEMERINTAH DENGAN PERJANJIAN KERJA"},"hakKelas":{"kode":"2","keterangan":"KELAS II"},"umur":{"umurSekarang":"32 tahun ,2 bulan ,23 hari","umurSaatPelayanan":"29 tahun ,2 bulan ,7 hari"},"informasi":{"dinsos":null,"prolanisPRB":null,"noSKTM":null,"eSEP":null},"cob":{"noAsuransi":null,"nmAsuransi":null,"tglTMT":null,"tglTAT":null}},"diagnosa":{"kode":"M51.9","nama":"Intervertebral disc disorder, unspecified"},"keluhan":"Kontrol","poliRujukan":{"kode":"BED","nama":"BEDAH"},"pelayanan":{"kode":"2","nama":"Rawat Jalan"}}]}}';
      exit();
    }

    public function postRencanaKontrol()
    {

      $bulan = $_POST['bulan'];
      $tahun = $_POST['tahun'];
      $nokartu = $_POST['nokartu'];
      $filter = $_POST['filter'];
      
      $query = new \Bridging\Bpjs\VClaim\RencanaKontrol($this->core->vclaim);
      $array = $query->getByNoKartu($bulan, $tahun, $nokartu, $filter);
      echo json_encode($array);
      exit();
    }

    public function postAddAntrian()
    {
      $tentukan_hari=date('D',strtotime(date('Y-m-d')));
      $day = array(
        'Sun' => 'AKHAD',
        'Mon' => 'SENIN',
        'Tue' => 'SELASA',
        'Wed' => 'RABU',
        'Thu' => 'KAMIS',
        'Fri' => 'JUMAT',
        'Sat' => 'SABTU'
      );
      $hari_kerja=$day[$tentukan_hari];      
      $maping_poli_bpjs = $this->core->db->get('maping_poli_bpjs', '*', ['kd_poli_bpjs' => $_POST['kodepoli']]);
      $maping_dokter_dpjpvclaim = $this->core->db->get('maping_dokter_dpjpvclaim', '*', ['kd_dokter_bpjs' => $_POST['kodedokter']]);
      $jadwal = $this->core->db->get('jadwal', '*', ['kd_poli' => $maping_poli_bpjs['kd_poli_rs'], 'kd_dokter' => $maping_dokter_dpjpvclaim['kd_dokter'], 'hari_kerja' => $hari_kerja]);
      if(!$jadwal) {
        $jadwal['jam_mulai'] = '00:00:00';
        $jadwal['jam_selesai'] = '00:00:00';
        $jadwal['kuota'] = '0';
      }
      $no_urut_reg = substr($_POST['angkaantrean'], 0, 3);
      $minutes = $no_urut_reg * 10;
      $jam_estimasi = date('H:i:s',strtotime('+'.$minutes.' minutes',strtotime($jadwal['jam_mulai'])));

      $data = [
          'kodebooking' => $_POST['kodebooking'],
          'jenispasien' => $_POST['jenispasien'],
          'nomorkartu' => $_POST['nomorkartu'],
          'nik' => $_POST['nik'],
          'nohp' => $_POST['nohp'],
          'kodepoli' => $_POST['kodepoli'],
          'namapoli' => $_POST['namapoli'],
          'pasienbaru' => $_POST['pasienbaru'],
          'norm' => $_POST['norm'],
          'tanggalperiksa' => $_POST['tanggalperiksa'],
          'kodedokter' => $_POST['kodedokter'],
          'namadokter' => $_POST['namadokter'],
          'jampraktek' => substr($jadwal['jam_mulai'],0,5).'-'.substr($jadwal['jam_selesai'],0,5),
          'jeniskunjungan' => $_POST['jeniskunjungan'],
          'nomorreferensi' => $_POST['nomorreferensi'],
          'nomorantrean' => $_POST['nomorantrean'],
          'angkaantrean' => $_POST['angkaantrean'],
          'estimasidilayani' => strtotime($_POST['tanggalperiksa'].' '.$jam_estimasi) * 1000,
          'sisakuotajkn' => $jadwal['kuota']-ltrim($_POST['angkaantrean'],'0'),
          'kuotajkn' => intval($jadwal['kuota']),
          'sisakuotanonjkn' => $jadwal['kuota']-ltrim($_POST['angkaantrean'],'0'),
          'kuotanonjkn' => intval($jadwal['kuota']),
          'keterangan' => 'Peserta harap 30 menit lebih awal guna pencatatan administrasi.'
      ];

      $user = $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
      $tanggal = date('Y-m-d');
      $endpoint = 'postAddAntrian';

      $this->core->db->insert('mlite_log_api_vclaim', [
        'user' => $user, 
        'tanggal' => $tanggal, 
        'endpoint' => $endpoint, 
        'result' => json_encode($data, JSON_PRETTY_PRINT)
      ]);
  
      // $data = json_encode($data);
      $query = new \Bridging\Bpjs\Antrian\Antrean($this->core->antrean);
      $array = $query->addAntrean($data);
      echo json_encode($array);
      exit();
    }

    public function getBatalAntrian()
    {
      $data = [
          'kodebooking' => '1708R00820240904000002INT001',
          'keterangan' => 'Salah pilih dokter.'
      ];

      $user = $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
      $tanggal = date('Y-m-d');
      $endpoint = 'postBatalAntrian';

      $this->core->db->insert('mlite_log_api_vclaim', [
        'user' => $user, 
        'tanggal' => $tanggal, 
        'endpoint' => $endpoint, 
        'result' => json_encode($data, JSON_PRETTY_PRINT)
      ]);

      $query = new \Bridging\Bpjs\Antrian\Antrean($this->core->antrean);
      $array = $query->batalAntrean($data);
      echo json_encode($array);
      exit();
    }    

    public function postPengajuanAprovalSep()
    {
      $noKartu = $_POST['no_kartu_aproval'];
      $tglSep = $_POST['tgl_sep_aproval'];
      $jnsPelayanan = $_POST['jns_pelayanan_aproval'];
      $jnsPengajuan = $_POST['jns_pengajuan_aproval'];
      $keterangan = $_POST['keterangan_aproval'];

      $data = [
        "request" => [
          "t_sep" => [
            "noKartu" => $noKartu,
            "tglSep" => $tglSep,
            "jnsPelayanan" => $jnsPelayanan,
            "jnsPengajuan" => $jnsPengajuan,
            "keterangan" => $keterangan,
            "user" => $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']])
          ]
        ]
      ];

      $user = $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
      $tanggal = date('Y-m-d');
      $endpoint = 'getAprovalSep';

      $this->core->db->insert('mlite_log_api_vclaim', [
        'user' => $user, 
        'tanggal' => $tanggal, 
        'endpoint' => $endpoint, 
        'result' => json_encode($data, JSON_PRETTY_PRINT)
      ]);

      $query = new \Bridging\Bpjs\VClaim\Sep($this->core->vclaim);
      $array = $query->pengajuanPenjaminanSep($data);
      echo json_encode($array);
      exit();
    }       
    public function postAprovalSep()
    {
      $noKartu = $_POST['no_kartu_aproval'];
      $tglSep = $_POST['tgl_sep_aproval'];
      $jnsPelayanan = $_POST['jns_pelayanan_aproval'];
      $jnsPengajuan = $_POST['jns_pengajuan_aproval'];
      $keterangan = $_POST['keterangan_aproval'];

      $data = [
        "request" => [
          "t_sep" => [
            "noKartu" => $noKartu,
            "tglSep" => $tglSep,
            "jnsPelayanan" => $jnsPelayanan,
            "jnsPengajuan" => $jnsPengajuan,
            "keterangan" => $keterangan,
            "user" => $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']])
          ]
        ]
      ];

      $user = $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
      $tanggal = date('Y-m-d');
      $endpoint = 'getAprovalSep';

      $this->core->db->insert('mlite_log_api_vclaim', [
        'user' => $user, 
        'tanggal' => $tanggal, 
        'endpoint' => $endpoint, 
        'result' => json_encode($data, JSON_PRETTY_PRINT)
      ]);

      $query = new \Bridging\Bpjs\VClaim\Sep($this->core->vclaim);
      $array = $query->approvalPenjaminanSep($data);
      echo json_encode($array);
      exit();
    }        

    public function postReferensiPoli()
    {
      $keyword = $_POST['cari_referensi_poli'];
      $query = new \Bridging\Bpjs\VClaim\Referensi($this->core->vclaim);
      $array = $query->poli($keyword);
      echo json_encode($array);
      exit();
    }

    public function postReferensiDPJP()
    {
      $jnsPelayanan = '2';
      $tglPelayanan = date('Y-m-d');
      $spesialis = $_POST['poli_bpjs'];
      $query = new \Bridging\Bpjs\VClaim\Referensi($this->core->vclaim);
      $array = $query->dokterDpjp($jnsPelayanan, $tglPelayanan, $spesialis);
      echo json_encode($array);
      exit();
    }

    public function postReferensiSpesialistik()
    {
      $query = new \Bridging\Bpjs\VClaim\Referensi($this->core->vclaim);
      $array = $query->spesialistik();
      echo json_encode($array);
      exit();
    }

    public function postReferensiDokter()
    {
      $keyword = $_POST['cari_referensi_dokter'];
      $query = new \Bridging\Bpjs\VClaim\Referensi($this->core->vclaim);
      $array = $query->dokter($keyword);
      echo json_encode($array);
      exit();
    }

    public function getTest()
    {
      $kodebooking = '1708R00820240904000002INT002';
      $query = new \Bridging\Bpjs\Antrian\Antrean($this->core->antrean);
      $array = $query->perKodeBooking($kodebooking);
      echo json_encode($array);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/bridging_sep/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/bridging_sep/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('bridging_sep')]);
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

        $this->core->addCSS(url([ 'bridging_sep', 'css']));
        $this->core->addJS(url([ 'bridging_sep', 'javascript']), 'footer');
    }

}
