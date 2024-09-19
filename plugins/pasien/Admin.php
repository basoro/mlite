<?php
namespace Plugins\Pasien;

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
        $this->assign['stts_nikah'] = $this->core->getEnum('pasien', 'stts_nikah');
        $this->assign['gol_darah'] = $this->core->getEnum('pasien', 'gol_darah');
        $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
        $this->assign['pnd'] = $this->core->getEnum('pasien', 'pnd');
        $this->assign['keluarga'] = $this->core->getEnum('pasien', 'keluarga');
        $this->assign['penjab'] = $this->core->db->select('penjab', '*', ['status' => '1']);
        // $this->assign['kelurahan'] = [];
        // $this->assign['kecamatan'] = [];
        // $this->assign['kabupaten'] = [];
        $this->assign['propinsi'] = $this->core->db->select('propinsi', '*');
        $this->assign['perusahaan_pasien'] = $this->core->db->select('perusahaan_pasien', '*');
        $this->assign['suku_bangsa'] = $this->core->db->select('suku_bangsa', '*');
        $this->assign['bahasa_pasien'] = $this->core->db->select('bahasa_pasien', '*');
        $this->assign['cacat_fisik'] = $this->core->db->select('cacat_fisik', '*');        
        $disabled_menu = $this->core->loadDisabledMenu('pasien'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['pasien' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_rkm_medis');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_pasien= isset_or($_POST['search_field_pasien']);
        $search_text_pasien = isset_or($_POST['search_text_pasien']);

        if ($search_text_pasien != '') {
          $where[$search_field_pasien.'[~]'] = $search_text_pasien;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        $searchByFromdate = $_POST['searchByFromdate'];
        $searchByTodate = $_POST['searchByTodate'];

        if ($searchByFromdate != '') {
          $where['tgl_daftar[<>]'] = [$searchByFromdate,$searchByTodate];
          $where = ["AND" => $where];
        }  

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('pasien', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('pasien', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('pasien', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rkm_medis'=>$row['no_rkm_medis'],
                'nm_pasien'=>$row['nm_pasien'],
                'no_ktp'=>$row['no_ktp'],
                'jk'=>$row['jk'],
                'tmp_lahir'=>$row['tmp_lahir'],
                'tgl_lahir'=>$row['tgl_lahir'],
                'nm_ibu'=>$row['nm_ibu'],
                'alamat'=>$row['alamat'],
                'gol_darah'=>$row['gol_darah'],
                'pekerjaan'=>$row['pekerjaan'],
                'stts_nikah'=>$row['stts_nikah'],
                'agama'=>$row['agama'],
                'tgl_daftar'=>$row['tgl_daftar'],
                'no_tlp'=>$row['no_tlp'],
                'umur'=>$row['umur'],
                'pnd'=>$row['pnd'],
                'keluarga'=>$row['keluarga'],
                'namakeluarga'=>$row['namakeluarga'],
                'kd_pj'=>$row['kd_pj'],
                'png_jawab'=>$this->core->db->get('penjab', 'png_jawab', ['kd_pj' => $row['kd_pj']]), 
                'no_peserta'=>$row['no_peserta'],
                'kd_kel'=>$row['kd_kel'],
                'nm_kel'=>$this->core->db->get('kelurahan', 'nm_kel', ['kd_kel' => $row['kd_kel']]), 
                'kd_kec'=>$row['kd_kec'],
                'nm_kec'=>$this->core->db->get('kecamatan', 'nm_kec', ['kd_kec' => $row['kd_kec']]), 
                'kd_kab'=>$row['kd_kab'],
                'nm_kab'=>$this->core->db->get('kabupaten', 'nm_kab', ['kd_kab' => $row['kd_kab']]), 
                'kd_prop'=>$row['kd_prop'],
                'nm_prop'=>$this->core->db->get('propinsi', 'nm_prop', ['kd_prop' => $row['kd_prop']]), 
                'pekerjaanpj'=>$row['pekerjaanpj'],
                'alamatpj'=>$row['alamatpj'],
                'kelurahanpj'=>$row['kelurahanpj'],
                'kecamatanpj'=>$row['kecamatanpj'],
                'kabupatenpj'=>$row['kabupatenpj'],
                'propinsipj'=>$row['propinsipj'],
                'perusahaan_pasien'=>$row['perusahaan_pasien'],
                'nama_perusahaan'=>$this->core->db->get('perusahaan_pasien', 'nama_perusahaan', ['kode_perusahaan' => $row['perusahaan_pasien']]), 
                'suku_bangsa'=>$row['suku_bangsa'],
                'nama_suku_bangsa'=>$this->core->db->get('suku_bangsa', 'nama_suku_bangsa', ['id' => $row['suku_bangsa']]), 
                'bahasa_pasien'=>$row['bahasa_pasien'],
                'nama_bahasa'=>$this->core->db->get('bahasa_pasien', 'nama_bahasa', ['id' => $row['bahasa_pasien']]), 
                'cacat_fisik'=>$row['cacat_fisik'],
                'nama_cacat'=>$this->core->db->get('cacat_fisik', 'nama_cacat', ['id' => $row['cacat_fisik']]), 
                'email'=>$row['email'],
                'nip'=>$row['nip']
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
          $this->core->LogQuery('pasien => postData');
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

            if($this->core->loadDisabledMenu('pasien')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rkm_medis = $_POST['no_rkm_medis'];
            $nm_pasien = $_POST['nm_pasien'];
            $no_ktp = $_POST['no_ktp'];
            $jk = $_POST['jk'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $nm_ibu = $_POST['nm_ibu'];
            $alamat = $_POST['alamat'];
            $gol_darah = $_POST['gol_darah'];
            $pekerjaan = $_POST['pekerjaan'];
            $stts_nikah = $_POST['stts_nikah'];
            $agama = $_POST['agama'];
            $tgl_daftar = $_POST['tgl_daftar'];
            $no_tlp = $_POST['no_tlp'];
            $umur = $_POST['umur'];
            $pnd = $_POST['pnd'];
            $keluarga = $_POST['keluarga'];
            $namakeluarga = $_POST['namakeluarga'];
            $kd_pj = $_POST['kd_pj'];
            $no_peserta = $_POST['no_peserta'];
            $kd_kel = $_POST['kd_kel'];
            $nm_kel = $_POST['nm_kel'];
            $kd_kec = $_POST['kd_kec'];
            $nm_kec = $_POST['nm_kec'];
            $kd_kab = $_POST['kd_kab'];
            $nm_kab = $_POST['nm_kab'];
            $pekerjaanpj = $_POST['pekerjaanpj'];
            $alamatpj = $_POST['alamatpj'];
            $kelurahanpj = $_POST['kelurahanpj'];
            $kecamatanpj = $_POST['kecamatanpj'];
            $kabupatenpj = $_POST['kabupatenpj'];
            $perusahaan_pasien = $_POST['perusahaan_pasien'];
            $suku_bangsa = $_POST['suku_bangsa'];
            $bahasa_pasien = $_POST['bahasa_pasien'];
            $cacat_fisik = $_POST['cacat_fisik'];
            $email = $_POST['email'];
            $nip = $_POST['nip'];
            $kd_prop = $_POST['kd_prop'];
            $nm_prop = $_POST['nm_prop'];
            $propinsipj = $_POST['propinsipj'];

            if(!$this->core->db->has('propinsi', ['AND' => ['kd_prop' => $kd_prop]])) {
              $this->core->db->insert('propinsi', ['kd_prop' => $kd_prop, 'nm_prop' => $nm_prop]);
            }
            if(!$this->core->db->has('kabupaten', ['AND' => ['kd_kab' => $kd_kab]])) {
              $this->core->db->insert('kabupaten', ['kd_kab' => $kd_kab, 'nm_kab' => $nm_kab]);
            }
            if(!$this->core->db->has('kecamatan', ['AND' => ['kd_kec' => $kd_kec]])) {
              $this->core->db->insert('kecamatan', ['kd_kec' => $kd_kec, 'nm_kec' => $nm_kec]);
            }
            if(!$this->core->db->has('kelurahan', ['AND' => ['kd_kel' => $kd_kel]])) {
              $this->core->db->insert('kelurahan', ['kd_kel' => $kd_kel, 'nm_kel' => $nm_kel]);
            }
            $result = $this->core->db->insert('pasien', [
              'no_rkm_medis'=>$no_rkm_medis, 'nm_pasien'=>$nm_pasien, 'no_ktp'=>$no_ktp, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'nm_ibu'=>$nm_ibu, 'alamat'=>$alamat, 'gol_darah'=>$gol_darah, 'pekerjaan'=>$pekerjaan, 'stts_nikah'=>$stts_nikah, 'agama'=>$agama, 'tgl_daftar'=>$tgl_daftar, 'no_tlp'=>$no_tlp, 'umur'=>$umur, 'pnd'=>$pnd, 'keluarga'=>$keluarga, 'namakeluarga'=>$namakeluarga, 'kd_pj'=>$kd_pj, 'no_peserta'=>$no_peserta, 'kd_kel'=>$kd_kel, 'kd_kec'=>$kd_kec, 'kd_kab'=>$kd_kab, 'pekerjaanpj'=>$pekerjaanpj, 'alamatpj'=>$alamatpj, 'kelurahanpj'=>$kelurahanpj, 'kecamatanpj'=>$kecamatanpj, 'kabupatenpj'=>$kabupatenpj, 'perusahaan_pasien'=>$perusahaan_pasien, 'suku_bangsa'=>$suku_bangsa, 'bahasa_pasien'=>$bahasa_pasien, 'cacat_fisik'=>$cacat_fisik, 'email'=>$email, 'nip'=>$nip, 'kd_prop'=>$kd_prop, 'propinsipj'=>$propinsipj
            ]);

            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah ditambah'
              );
              $this->core->db->replace('set_no_rkm_medis', ['no_rkm_medis' => [$this->core->db->get('set_no_rkm_medis', 'no_rkm_medis') => $no_rkm_medis]]);
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pasien => postAksi => add');
            }
            
            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('pasien')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rkm_medis = $_POST['no_rkm_medis'];
            $nm_pasien = $_POST['nm_pasien'];
            $no_ktp = $_POST['no_ktp'];
            $jk = $_POST['jk'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $nm_ibu = $_POST['nm_ibu'];
            $alamat = $_POST['alamat'];
            $gol_darah = $_POST['gol_darah'];
            $pekerjaan = $_POST['pekerjaan'];
            $stts_nikah = $_POST['stts_nikah'];
            $agama = $_POST['agama'];
            $tgl_daftar = $_POST['tgl_daftar'];
            $no_tlp = $_POST['no_tlp'];
            $umur = $_POST['umur'];
            $pnd = $_POST['pnd'];
            $keluarga = $_POST['keluarga'];
            $namakeluarga = $_POST['namakeluarga'];
            $kd_pj = $_POST['kd_pj'];
            $no_peserta = $_POST['no_peserta'];
            $kd_kel = $_POST['kd_kel'];
            $nm_kel = $_POST['nm_kel'];
            $kd_kec = $_POST['kd_kec'];
            $nm_kec = $_POST['nm_kec'];
            $kd_kab = $_POST['kd_kab'];
            $nm_kab = $_POST['nm_kab'];
            $pekerjaanpj = $_POST['pekerjaanpj'];
            $alamatpj = $_POST['alamatpj'];
            $kelurahanpj = $_POST['kelurahanpj'];
            $kecamatanpj = $_POST['kecamatanpj'];
            $kabupatenpj = $_POST['kabupatenpj'];
            $perusahaan_pasien = $_POST['perusahaan_pasien'];
            $suku_bangsa = $_POST['suku_bangsa'];
            $bahasa_pasien = $_POST['bahasa_pasien'];
            $cacat_fisik = $_POST['cacat_fisik'];
            $email = $_POST['email'];
            $nip = $_POST['nip'];
            $kd_prop = $_POST['kd_prop'];
            $nm_prop = $_POST['nm_prop'];
            $propinsipj = $_POST['propinsipj'];

            $cek_propinsi = $this->core->db->has('propinsi', ['AND' => ['kd_prop' => $kd_prop]]);
            if(!$cek_propinsi) {
              $this->core->db->insert('propinsi', ['kd_prop' => $kd_prop, 'nm_prop' => $nm_prop]);
            }
            $cek_kabupaten = $this->core->db->has('kabupaten', ['AND' => ['kd_kab' => $kd_kab]]);
            if(!$cek_kabupaten) {
              $this->core->db->insert('kabupaten', ['kd_kab' => $kd_kab, 'nm_kab' => $nm_kab]);
            }
            $cek_kecamatan = $this->core->db->has('kecamatan', ['AND' => ['kd_kec' => $kd_kec]]);
            if(!$cek_kecamatan) {
              $this->core->db->insert('kecamatan', ['kd_kec' => $kd_kec, 'nm_kec' => $nm_kec]);
            }
            $cek_kelurahan = $this->core->db->has('kelurahan', ['AND' => ['kd_kel' => $kd_kel]]);
            if(!$cek_kelurahan) {
              $this->core->db->insert('kelurahan', ['kd_kel' => $kd_kel, 'nm_kel' => $nm_kel]);
            }

            // BUANG FIELD PERTAMA
            $result = $this->core->db->update('pasien', [
              'nm_pasien'=>$nm_pasien, 'no_ktp'=>$no_ktp, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'nm_ibu'=>$nm_ibu, 'alamat'=>$alamat, 'gol_darah'=>$gol_darah, 'pekerjaan'=>$pekerjaan, 'stts_nikah'=>$stts_nikah, 'agama'=>$agama, 'tgl_daftar'=>$tgl_daftar, 'no_tlp'=>$no_tlp, 'umur'=>$umur, 'pnd'=>$pnd, 'keluarga'=>$keluarga, 'namakeluarga'=>$namakeluarga, 'kd_pj'=>$kd_pj, 'no_peserta'=>$no_peserta, 'kd_kel'=>$kd_kel, 'kd_kec'=>$kd_kec, 'kd_kab'=>$kd_kab, 'pekerjaanpj'=>$pekerjaanpj, 'alamatpj'=>$alamatpj, 'kelurahanpj'=>$kelurahanpj, 'kecamatanpj'=>$kecamatanpj, 'kabupatenpj'=>$kabupatenpj, 'perusahaan_pasien'=>$perusahaan_pasien, 'suku_bangsa'=>$suku_bangsa, 'bahasa_pasien'=>$bahasa_pasien, 'cacat_fisik'=>$cacat_fisik, 'email'=>$email, 'nip'=>$nip, 'kd_prop'=>$kd_prop, 'propinsipj'=>$propinsipj
            ], [
              'no_rkm_medis'=>$no_rkm_medis
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
              $this->core->LogQuery('pasien => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('pasien')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rkm_medis= $_POST['no_rkm_medis'];
            $result = $this->core->db->delete('pasien', [
              'AND' => [
                'no_rkm_medis'=>$no_rkm_medis
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
              $this->core->LogQuery('pasien => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('pasien')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_pasien= $_POST['search_field_pasien'];
            $search_text_pasien = $_POST['search_text_pasien'];

            $searchByFromdate = $_POST['searchByFromdate'];
            $searchByTodate = $_POST['searchByTodate'];

            if ($search_text_pasien != '') {
              $where[$search_field_pasien.'[~]'] = $search_text_pasien;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            if ($searchByFromdate != '') {
              $where['tgl_daftar[<>]'] = [$searchByFromdate,$searchByTodate];
              $where = ["AND" => $where];
            } else {
              $where = [];
            }  

            ## Fetch records
            $result = $this->core->db->select('pasien', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rkm_medis'=>$row['no_rkm_medis'],
                    'nm_pasien'=>$row['nm_pasien'],
                    'no_ktp'=>$row['no_ktp'],
                    'jk'=>$row['jk'],
                    'tmp_lahir'=>$row['tmp_lahir'],
                    'tgl_lahir'=>$row['tgl_lahir'],
                    'nm_ibu'=>$row['nm_ibu'],
                    'alamat'=>$row['alamat'],
                    'gol_darah'=>$row['gol_darah'],
                    'pekerjaan'=>$row['pekerjaan'],
                    'stts_nikah'=>$row['stts_nikah'],
                    'agama'=>$row['agama'],
                    'tgl_daftar'=>$row['tgl_daftar'],
                    'no_tlp'=>$row['no_tlp'],
                    'umur'=>$row['umur'],
                    'pnd'=>$row['pnd'],
                    'keluarga'=>$row['keluarga'],
                    'namakeluarga'=>$row['namakeluarga'],
                    'kd_pj'=>$row['kd_pj'],
                    'png_jawab'=>$this->core->db->get('penjab', 'png_jawab', ['kd_pj' => $row['kd_pj']]), 
                    'no_peserta'=>$row['no_peserta'],
                    'kd_kel'=>$row['kd_kel'],
                    'nm_kel'=>$this->core->db->get('kelurahan', 'nm_kel', ['kd_kel' => $row['kd_kel']]), 
                    'kd_kec'=>$row['kd_kec'],
                    'nm_kec'=>$this->core->db->get('kecamatan', 'nm_kec', ['kd_kec' => $row['kd_kec']]), 
                    'kd_kab'=>$row['kd_kab'],
                    'nm_kab'=>$this->core->db->get('kabupaten', 'nm_kab', ['kd_kab' => $row['kd_kab']]), 
                    'pekerjaanpj'=>$row['pekerjaanpj'],
                    'alamatpj'=>$row['alamatpj'],
                    'kelurahanpj'=>$row['kelurahanpj'],
                    'kecamatanpj'=>$row['kecamatanpj'],
                    'kabupatenpj'=>$row['kabupatenpj'],
                    'perusahaan_pasien'=>$row['perusahaan_pasien'],
                    'suku_bangsa'=>$row['suku_bangsa'],
                    'bahasa_pasien'=>$row['bahasa_pasien'],
                    'cacat_fisik'=>$row['cacat_fisik'],
                    'email'=>$row['email'],
                    'nip'=>$row['nip'],
                    'kd_prop'=>$row['kd_prop'],
                    'nm_prop'=>$this->core->db->get('propinsi', 'nm_prop', ['kd_prop' => $row['kd_prop']]), 
                    'propinsipj'=>$row['propinsipj']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pasien => postAksi => lihat');
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rkm_medis)
    {

        if($this->core->loadDisabledMenu('pasien')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('pasien', '*', ['no_rkm_medis' => $no_rkm_medis]);
        $result['png_jawab'] = $this->core->db->get('penjab', 'png_jawab', ['kd_pj' => $result['kd_pj']]);
        $result['nm_kel'] = $this->core->db->get('kelurahan', 'nm_kel', ['kd_kel' => $result['kd_kel']]);
        $result['nm_kec'] = $this->core->db->get('kecamatan', 'nm_kec', ['kd_kec' => $result['kd_kec']]);
        $result['nm_kab'] = $this->core->db->get('kabupaten', 'nm_kab', ['kd_kab' => $result['kd_kab']]);
        $result['nm_prop'] = $this->core->db->get('propinsi', 'nm_prop', ['kd_prop' => $result['kd_prop']]);

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
          $this->core->LogQuery('pasien => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rkm_medis)
    {

        if($this->core->loadDisabledMenu('pasien')['read'] == 'true') {
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
          $this->core->LogQuery('pasien => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rkm_medis' => $no_rkm_medis]);
        exit();
    }

    
    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('pasien', 'stts_nikah', ['GROUP' => 'stts_nikah']);
      $datasets = $this->core->db->select('pasien', ['count' => \Medoo\Medoo::raw('COUNT(<stts_nikah>)')], ['GROUP' => 'stts_nikah']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('pasien', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('pasien', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'pasien';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('pasien => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
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

          $result = $array;
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

    public function postGetNoRM()
    {
      $result = $this->core->setNoRm();
      echo $result;
      exit();
    }

    public function getRiwayat($no_rkm_medis)
    {
      $this->assign['pasien'] = $this->core->db->get('pasien', '*', ['no_rkm_medis' => $no_rkm_medis]);
      $reg_periksa = $this->core->db->select('reg_periksa', [
        '[>]poliklinik' => ['kd_poli' => 'kd_poli'], 
        '[>]dokter' => ['kd_dokter' => 'kd_dokter'], 
        '[>]penjab' => ['kd_pj' => 'kd_pj']        
      ], '*', ['no_rkm_medis' => $no_rkm_medis, 'ORDER' => ['tgl_registrasi' => 'ASC']]);

      $this->assign['reg_periksa'] = [];
      $i = 1;
      foreach($reg_periksa as $row) {
        $row['nomor'] = $i++;
        $row['pemeriksaan_ralan'] = $this->core->db->select('pemeriksaan_ralan', '*', ['no_rawat' => $row['no_rawat']]);
        $this->assign['reg_periksa'][] = $row;
      }
      $settings = $this->settings('settings');
      echo $this->draw('riwayat.perawatan.html', ['settings' => $settings, 'riwayat' => $this->assign]);
      exit();
    }

    public function getCoverRM($no_rkm_medis)
    {

      
        $query = "select pasien.no_rkm_medis, pasien.nm_pasien, pasien.no_ktp, pasien.jk,
        pasien.tmp_lahir, pasien.tgl_lahir,pasien.nm_ibu, concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab) as alamat, pasien.gol_darah, pasien.pekerjaan,
        pasien.stts_nikah,pasien.agama,pasien.tgl_daftar,pasien.no_tlp,pasien.umur,
        pasien.pnd, pasien.keluarga, pasien.namakeluarga,penjab.png_jawab,pasien.pekerjaanpj,
        concat(pasien.alamatpj,', ',pasien.kelurahanpj,', ',pasien.kecamatanpj,', ',pasien.kabupatenpj) as alamatpj from pasien
        inner join kelurahan inner join kecamatan inner join kabupaten
        inner join penjab on pasien.kd_pj=penjab.kd_pj and pasien.kd_kel=kelurahan.kd_kel
        and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kab=kabupaten.kd_kab where pasien.no_rkm_medis=$no_rkm_medis";

        $this->core->JasperPrint('rptCoverMap', $query);

        exit();
    }

    public function getPropinsi()
    {
      
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/pasien/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/pasien/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('pasien')]);
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
        
        $this->core->addCSS(url([ 'pasien', 'css']));
        $this->core->addJS(url([ 'pasien', 'javascript']), 'footer');
    }

    public function getTest()
    {
      $cek = $this->core->db->has('propinsi', ['AND' => ['kd_prop' => '']]);
      if(!$cek) {
        echo 'Tidak Ada';
        // $this->core->db->insert('propinsi', ['kd_prop' => $kd_prop, 'nm_prop' => $nm_prop]);
      } else {
        echo 'ada';
      }
      exit();
    }

}