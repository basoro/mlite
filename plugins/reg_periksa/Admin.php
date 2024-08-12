<?php
namespace Plugins\Reg_Periksa;

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
        $this->assing = [];
        $this->assign['pasien'] = $this->core->db->select('pasien', ['no_rkm_medis','nm_pasien'], ['LIMIT' => 10]);
        $this->assign['poliklinik'] = $this->core->db->select('poliklinik', ['kd_poli', 'nm_poli'], ['status' => '1']);
        $this->assign['dokter'] = $this->core->db->select('dokter', ['kd_dokter', 'nm_dokter'], ['status' => '1']);
        $this->assign['penjab'] = $this->core->db->select('penjab', ['kd_pj', 'png_jawab'], ['status' => '1']);
        $this->assign['stts'] = $this->core->getEnum('reg_periksa', 'stts');
        $this->assign['stts_daftar'] = $this->core->getEnum('reg_periksa', 'stts_daftar');
        $this->assign['status_lanjut'] = $this->core->getEnum('reg_periksa', 'status_lanjut');
        $this->assign['sttsumur'] = $this->core->getEnum('reg_periksa', 'sttsumur');
        $this->assign['status_bayar'] = $this->core->getEnum('reg_periksa', 'status_bayar');
        $this->assign['status_poli'] = $this->core->getEnum('reg_periksa', 'status_poli');
        $disabled_menu = $this->core->loadDisabledMenu('reg_periksa'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['reg_periksa' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData(){
      $column_name = isset_or($_POST['column_name'], 'tgl_registrasi');
      $column_sort = isset_or($_POST['column_sort'], 'asc');
      $draw = isset_or($_POST['draw'], '0');
      $row1 = isset_or($_POST['start'], '0');
      $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
      $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
      $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
      $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_sort); // asc or desc
      $searchValue = isset_or($_POST['search']['value']); // Search value

      ## Custom Field value
      $search_field_reg_periksa= isset_or($_POST['search_field_reg_periksa'], '');
      $search_text_reg_periksa = isset_or($_POST['search_text_reg_periksa'], '');

      $searchByFromdate = isset_or($_POST['searchByFromdate'], date('Y-m-d'));
      $searchByTodate = isset_or($_POST['searchByTodate'], date('Y-m-d'));
    
      $cap = $this->core->dbmlite->get('mlite_users', 'cap', ['id' => $_SESSION['mlite_user']]);
      $role = $this->core->dbmlite->get('mlite_users', 'role', ['id' => $_SESSION['mlite_user']]);

      if ($search_text_reg_periksa != '') {
        $where[$search_field_reg_periksa.'[~]'] = $search_text_reg_periksa;
        if($role !='admin') {
          $where['kd_poli'] = explode(',', $cap);
        }
        $where = ["AND" => $where];
      } else {
        $where = [];
      }

      if ($searchByFromdate != '') {
        $where['tgl_registrasi[<>]'] = [$searchByFromdate,$searchByTodate];
        if($role !='admin') {
          $where['kd_poli'] = explode(',', $cap);
        }
        $where = ["AND" => $where];
      } else {
        $where['tgl_registrasi[<>]'] = [date('Y-m-d'),date('Y-m-d')];
        if($role !='admin') {
          $where['kd_poli'] = explode(',', $cap);
        }
        $where = ["AND" => $where];
      }

      ## Total number of records without filtering
      $totalRecords = $this->core->db->count('reg_periksa', '*');

      ## Total number of records with filtering
      $totalRecordwithFilter = $this->core->db->count('reg_periksa', '*', $where);

      ## Fetch records
      $where ['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
      $where ['LIMIT'] = [$row1, $rowperpage];
      $result = $this->core->db->select('reg_periksa', '*', $where);
              
      $data = array();
      foreach($result as $row) {
          $data[] = array(
            'no_reg'=>$row['no_reg'],
            'no_rawat'=>$row['no_rawat'],
            'tgl_registrasi'=>$row['tgl_registrasi'],
            'jam_reg'=>$row['jam_reg'],
            'kd_dokter'=>$row['kd_dokter'],
            'nm_dokter'=>$this->core->db->get('dokter', 'nm_dokter', ['kd_dokter' => $row['kd_dokter']]),
            'no_rkm_medis'=>$row['no_rkm_medis'],
            'nm_pasien'=>$this->core->db->get('pasien', 'nm_pasien', ['no_rkm_medis' => $row['no_rkm_medis']]),
            'kd_poli'=>$row['kd_poli'],
            'nm_poli'=>$this->core->db->get('poliklinik', 'nm_poli', ['kd_poli' => $row['kd_poli']]),
            'p_jawab'=>$row['p_jawab'],
            'almt_pj'=>$row['almt_pj'],
            'hubunganpj'=>$row['hubunganpj'],
            'biaya_reg'=>$row['biaya_reg'],
            'stts'=>$row['stts'],
            'stts_daftar'=>$row['stts_daftar'],
            'status_lanjut'=>$row['status_lanjut'],
            'kd_pj'=>$row['kd_pj'],
            'png_jawab'=>$this->core->db->get('penjab', 'png_jawab', ['kd_pj' => $row['kd_pj']]),
            'status_bayar'=>$row['status_bayar'],
            'status_poli'=>$row['status_poli']
          );
      }

      ## Response
      $response = array(
          "draw" => intval($draw), 
          "iTotalRecords" => $totalRecords,
          "iTotalDisplayRecords" => $totalRecordwithFilter,
          "aaData" => $data
      );

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => postData');
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

            $no_reg = $_POST['no_reg'];
            $no_rawat = $_POST['no_rawat'];
            $tgl_registrasi = $_POST['tgl_registrasi'];
            $jam_reg = $_POST['jam_reg'];
            $kd_dokter = $_POST['kd_dokter'];
            $no_rkm_medis = $_POST['no_rkm_medis'];
            $kd_poli = $_POST['kd_poli'];
            $p_jawab = $_POST['p_jawab'];
            $almt_pj = $_POST['almt_pj'];
            $hubunganpj = $_POST['hubunganpj'];
            if($_POST['status_poli'] == 'Baru') {
              $biaya_reg = '20000'; 
            } else {
              $biaya_reg = '0';
            }
            $stts = $_POST['stts'];
            $stts_daftar = $_POST['stts_daftar'];
            $status_lanjut = $_POST['status_lanjut'];
            $kd_pj = $_POST['kd_pj'];
            $umurdaftar = '20';
            $sttsumur = 'Th';
            $status_bayar = $_POST['status_bayar'];
            $status_poli = $_POST['status_poli'];

            $result = $this->core->db->insert('reg_periksa', [
                'no_reg'=>$no_reg, 'no_rawat'=>$no_rawat, 'tgl_registrasi'=>$tgl_registrasi, 'jam_reg'=>$jam_reg, 'kd_dokter'=>$kd_dokter, 'no_rkm_medis'=>$no_rkm_medis, 'kd_poli'=>$kd_poli, 'p_jawab'=>$p_jawab, 'almt_pj'=>$almt_pj, 'hubunganpj'=>$hubunganpj, 'biaya_reg'=>$biaya_reg, 'stts'=>$stts, 'stts_daftar'=>$stts_daftar, 'status_lanjut'=>$status_lanjut, 'kd_pj'=>$kd_pj, 'umurdaftar'=>$umurdaftar, 'sttsumur'=>$sttsumur, 'status_bayar'=>$status_bayar, 'status_poli'=>$status_poli
            ]);

            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => 'Data telah ditambah'
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('reg_periksa => postAksi => add');
            }
    
            echo json_encode($data);    
        }
        if ($act=="edit") {

            $no_reg = $_POST['no_reg'];
            $no_rawat = $_POST['no_rawat'];
            $tgl_registrasi = $_POST['tgl_registrasi'];
            $jam_reg = $_POST['jam_reg'];
            $kd_dokter = $_POST['kd_dokter'];
            $no_rkm_medis = $_POST['no_rkm_medis'];
            $kd_poli = $_POST['kd_poli'];
            $p_jawab = $_POST['p_jawab'];
            $almt_pj = $_POST['almt_pj'];
            $hubunganpj = $_POST['hubunganpj'];
            if($_POST['status_poli'] == 'Baru') {
              $biaya_reg = '20000'; 
            } else {
              $biaya_reg = '0';
            }
            $stts = $_POST['stts'];
            $stts_daftar = $_POST['stts_daftar'];
            $status_lanjut = $_POST['status_lanjut'];
            $kd_pj = $_POST['kd_pj'];
            $umurdaftar = '0';
            $sttsumur = 'Th';
            $status_bayar = $_POST['status_bayar'];
            $status_poli = $_POST['status_poli'];

            // BUANG FIELD PERTAMA
            $result = $this->core->db->update('reg_periksa', [
                'no_reg'=>$no_reg, 'tgl_registrasi'=>$tgl_registrasi, 'jam_reg'=>$jam_reg, 'kd_dokter'=>$kd_dokter, 'no_rkm_medis'=>$no_rkm_medis, 'kd_poli'=>$kd_poli, 'p_jawab'=>$p_jawab, 'almt_pj'=>$almt_pj, 'hubunganpj'=>$hubunganpj, 'biaya_reg'=>$biaya_reg, 'stts'=>$stts, 'stts_daftar'=>$stts_daftar, 'status_lanjut'=>$status_lanjut, 'kd_pj'=>$kd_pj, 'umurdaftar'=>$umurdaftar, 'sttsumur'=>$sttsumur, 'status_bayar'=>$status_bayar, 'status_poli'=>$status_poli
            ], [
              'no_rawat'=>$no_rawat
            ]);


            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => 'Data telah diubah'
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('reg_periksa => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $result = $this->core->db->delete('reg_periksa', [
              'AND' => [
                'no_rawat'=>$no_rawat
              ]
            ]);
            
            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => 'Data telah dihapus'
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('reg_periksa => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            $search_field_reg_periksa= $_POST['search_field_reg_periksa'];
            $search_text_reg_periksa = $_POST['search_text_reg_periksa'];

            $searchByFromdate = $_POST['searchByFromdate'];
            $searchByTodate = $_POST['searchByTodate'];
          
            $cap = $this->core->dbmlite->get('mlite_users', 'cap', ['id' => $_SESSION['mlite_user']]);
            $role = $this->core->dbmlite->get('mlite_users', 'role', ['id' => $_SESSION['mlite_user']]);
      
            if ($search_text_reg_periksa != '') {
              $where[$search_field_reg_periksa.'[~]'] = $search_text_reg_periksa;
              if($role !='admin') {
                $where['kd_poli'] = explode(',', $cap);
              }      
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            if ($searchByFromdate != '') {
              $where['tgl_registrasi[<>]'] = [$searchByFromdate,$searchByTodate];
              if($role !='admin') {
                $where['kd_poli'] = explode(',', $cap);
              }      
              $where = ["AND" => $where];
            } else {
              $where['tgl_registrasi[<>]'] = [date('Y-m-d'),date('Y-m-d')];
              if($role !='admin') {
                $where['kd_poli'] = explode(',', $cap);
              }
              $where = ["AND" => $where];      
            }

            $result = $this->core->db->select('reg_periksa', '*', $where);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_reg'=>$row['no_reg'],
                    'no_rawat'=>$row['no_rawat'],
                    'tgl_registrasi'=>$row['tgl_registrasi'],
                    'jam_reg'=>$row['jam_reg'],
                    'kd_dokter'=>$row['kd_dokter'],
                    'no_rkm_medis'=>$row['no_rkm_medis'],
                    'kd_poli'=>$row['kd_poli'],
                    'p_jawab'=>$row['p_jawab'],
                    'almt_pj'=>$row['almt_pj'],
                    'hubunganpj'=>$row['hubunganpj'],
                    'biaya_reg'=>$row['biaya_reg'],
                    'stts'=>$row['stts'],
                    'stts_daftar'=>$row['stts_daftar'],
                    'status_lanjut'=>$row['status_lanjut'],
                    'kd_pj'=>$row['kd_pj'],
                    'umurdaftar'=>$row['umurdaftar'],
                    'sttsumur'=>$row['sttsumur'],
                    'status_bayar'=>$row['status_bayar'],
                    'status_poli'=>$row['status_poli']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('reg_periksa => postAksi => lihat');
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getView($no_rawat)
    {
      $this->_addHeaderFiles();
      $this->core->addCSS(url('assets/vendor/jquery.pan/css/jquery.pan.css'));
      $this->core->addJS(url([ 'reg_periksa', 'javascriptpemeriksaanralan']), 'footer');
      $this->core->addJS(url([ 'reg_periksa', 'javascripttindakanralandokter']), 'footer');
      $this->core->addJS(url([ 'reg_periksa', 'javascripttindakanralanperawat']), 'footer');
      $this->core->addJS(url([ 'reg_periksa', 'javascripttindakanralandokterperawat']), 'footer');
      $this->core->addJS(url([ 'reg_periksa', 'javascriptresepdokter']), 'footer');
      $this->core->addJS(url([ 'reg_periksa', 'javascriptresepdokterracikan']), 'footer');
      $this->core->addJS(url([ 'reg_periksa', 'javascriptpermintaanlab']), 'footer');
      $this->core->addJS(url([ 'reg_periksa', 'javascriptpermintaanradiologi']), 'footer');
      $this->core->addJS(url('assets/vendor/jquery.pan/jquery.pan.min.js'), 'footer');

      $date = $this->core->db->get('reg_periksa', 'tgl_registrasi', ['no_rawat' => revertNoRawat($no_rawat)]);
      $rows = $this->core->db->select('reg_periksa', [
        "[>]pasien" => ["no_rkm_medis" => "no_rkm_medis"]
      ], [
        'reg_periksa.no_rawat', 
        'reg_periksa.no_rkm_medis', 
        'pasien.nm_pasien', 
        'pasien.alamat', 
        'pasien.no_tlp', 
        'reg_periksa.stts',
        'reg_periksa.status_bayar'
      ], [
        'tgl_registrasi' => $date
      ]);
      $reg_periksa = [];
      foreach($rows as $row) {
        $row['viewUrl'] = url(['reg_periksa','view',convertNoRawat($row['no_rawat'])]);
        if(isset($_GET['date'])) {
          $row['viewUrl'] = url().'/reg_periksa/view/'.convertNoRawat($row['no_rawat']).'?date='.$_GET['date'].'&t='.$_SESSION['token'];          
        }
        $reg_periksa[] = $row;
      }
      $disabled_menu['pemeriksaan_ralan'] = $this->core->loadDisabledMenu('pemeriksaan_ralan'); 
      foreach ($disabled_menu['pemeriksaan_ralan'] as &$row) { 
        if ($row == "true" ) $row = "disabled"; 
      } 
      $disabled_menu['tindakan_ralan_dokter'] = $this->core->loadDisabledMenu('tindakan_ralan_dokter'); 
      foreach ($disabled_menu['tindakan_ralan_dokter'] as &$row) { 
        if ($row == "true" ) $row = "disabled"; 
      } 
      $disabled_menu['tindakan_ralan_perawat'] = $this->core->loadDisabledMenu('tindakan_ralan_perawat'); 
      foreach ($disabled_menu['tindakan_ralan_perawat'] as &$row) { 
        if ($row == "true" ) $row = "disabled"; 
      } 
      $disabled_menu['tindakan_ralan_dokter_perawat'] = $this->core->loadDisabledMenu('tindakan_ralan_dokter_perawat'); 
      foreach ($disabled_menu['tindakan_ralan_dokter_perawat'] as &$row) { 
        if ($row == "true" ) $row = "disabled"; 
      } 
      $disabled_menu['resep_dokter'] = $this->core->loadDisabledMenu('resep_dokter'); 
      foreach ($disabled_menu['resep_dokter'] as &$row) { 
        if ($row == "true" ) $row = "disabled"; 
      } 
      $disabled_menu['resep_dokter_racikan'] = $this->core->loadDisabledMenu('resep_dokter_racikan'); 
      foreach ($disabled_menu['resep_dokter_racikan'] as &$row) { 
        if ($row == "true" ) $row = "disabled"; 
      } 
      $disabled_menu['permintaan_lab'] = $this->core->loadDisabledMenu('permintaan_lab'); 
      foreach ($disabled_menu['permintaan_lab'] as &$row) { 
        if ($row == "true" ) $row = "disabled"; 
      } 
      unset($row);
      $this->assign = [];
      $this->assign['user'] = $this->core->dbmlite->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
      $this->assign['pegawai'] = $this->core->db->select('pegawai', '*');
      $this->assign['dokter'] = $this->core->db->select('dokter', '*');
      $this->assign['petugas'] = $this->core->db->select('petugas', '*');
      $this->assign['jns_perawatan'] = $this->core->db->select('jns_perawatan', '*');
      $this->assign['kesadaran'] = $this->core->getEnum('pemeriksaan_ralan', 'kesadaran');
      $this->assign['databarang'] = $this->core->db->select('databarang', '*');
      $this->assign['metode_racik'] = $this->core->db->select('metode_racik', '*');
      $this->assign['jns_perawatan_lab'] = $this->core->db->select('jns_perawatan_lab', '*');
      $this->assign['jns_perawatan_radiologi'] = $this->core->db->select('jns_perawatan_radiologi', '*');

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => getView');
      }

      return $this->draw('view.html', [
        'reg_periksa' => $reg_periksa, 
        'view' => $this->assign, 
        'parseUrl' => parseUrl()[2], 
        'disabled_menu' => $disabled_menu
      ]);
    }

    public function getManagePasien()
    {

      $this->assign = [];
      $this->core->addCSS(url('assets/css/main.css'));
      $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
      $this->core->addCSS(url('themes/css/styles.css'));
      $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));

      $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
      $this->core->addJS(url('assets/js/jquery.min.js'));
      $this->core->addJS(url('assets/js/bootstrap.bundle.min.js'));
      $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'));

      $this->core->addJS(url('assets/js/selectator.js'));
      $this->core->addJS(url('themes/js/scripts.js'));

      echo $this->draw('manage.pasien.html', ['pasien' => $this->assign]);
      exit();
    }

    public function postDataPasien()
    {
      $draw = $_POST['draw'];
      $row1 = $_POST['start'];
      $rowperpage = $_POST['length']; // Rows display per page
      $columnIndex = $_POST['order'][0]['column']; // Column index
      $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
      $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
      $searchValue = $_POST['search']['value']; // Search value

      ## Custom Field value
      $search_field_pasien= $_POST['search_field_pasien'];
      $search_text_pasien = $_POST['search_text_pasien'];

      if ($search_text_pasien != '') {
        $where[$search_field_pasien.'[~]'] = $search_text_pasien;
        $where = ['AND' => $where];
      } else {
        $where = [];
      }

      ## Total number of records without filtering
      $totalRecords = $this->core->db->count('pasien', '*');

      ## Total number of records with filtering
      $totalRecordwithFilter = $this->core->db->count('pasien', '*', $where);

      ## Fetch records
      $where ['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
      $where ['LIMIT'] = [$row1, $rowperpage];
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
              'no_peserta'=>$row['no_peserta'],
              'kd_kel'=>$row['kd_kel'],
              'kd_kec'=>$row['kd_kec'],
              'kd_kab'=>$row['kd_kab'],
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
              'propinsipj'=>$row['propinsipj']

          );
      }

      ## Response
      $response = array(
          "draw" => intval($draw), 
          "iTotalRecords" => $totalRecords,
          "iTotalDisplayRecords" => $totalRecordwithFilter,
          "aaData" => $data
      );

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => postDataPasien');
      }

      echo json_encode($response);
      exit();
    }


    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('reg_periksa')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('reg_periksa', '*', ['no_rawat' => revertNoRawat($no_rawat)]);
        $result['nm_dokter'] = $this->core->db->get('dokter', 'nm_dokter', ['kd_dokter' => $result['kd_dokter']]);
        $result['nm_pasien'] = $this->core->db->get('pasien', 'nm_pasien', ['no_rkm_medis' => $result['no_rkm_medis']]);
        $result['png_jawab'] = $this->core->db->get('penjab', 'png_jawab', ['kd_pj' => $result['kd_pj']]);

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
          $this->core->LogQuery('reg_periksa => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {
        if($this->core->loadDisabledMenu('reg_periksa')['read'] == 'true') {
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
          $this->core->LogQuery('reg_periksa => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);

        exit();
    }

    public function getAntrian($no_rawat)
    {
        $row = $this->core->db->get('reg_periksa', [
          '[>]pasien' => ['no_rkm_medis' => 'no_rkm_medis'], 
          '[>]poliklinik' => ['kd_poli' => 'kd_poli'], 
          '[>]dokter' => ['kd_dokter' => 'kd_dokter'], 
          '[>]penjab' => ['kd_pj' => 'kd_pj'] 
        ], [
          'reg_periksa.no_rkm_medis', 
          'reg_periksa.no_rawat', 
          'reg_periksa.tgl_registrasi', 
          'reg_periksa.jam_reg', 
          'reg_periksa.no_reg', 
          'pasien.nm_pasien', 
          'poliklinik.nm_poli', 
          'dokter.nm_dokter', 
          'penjab.png_jawab'
        ], [
          'no_rawat' => revertNoRawat($no_rawat)
        ]);

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => getAntrian');
        }

        echo $this->draw('antrian.html', ['settings' => $this->settings('settings'), 'antrian' => $row]);
        exit();
    }

    public function getAntrol($no_rawat)
    {
        $row = $this->core->db->get('reg_periksa', '*', ['no_rawat' => revertNoRawat($no_rawat)]);
        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => getAntrol');
        }
        echo $this->draw('antrol.html', ['settings' => $this->settings('settings'), 'antrol' => $row]);
        exit();
    }

    public function getSep($no_rawat)
    {
        $row = $this->core->db->get('reg_periksa', '*', ['no_rawat' => revertNoRawat($no_rawat)]);
        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => getSep');
        }
        echo $this->draw('sep.html', ['settings' => $this->settings('settings'), 'sep' => $row]);
        exit();
    }

    public function postDataRiwayat(){
      $draw = $_POST['draw'];
      $row1 = $_POST['start'];
      $rowperpage = $_POST['length']; // Rows display per page
      $columnIndex = $_POST['order'][0]['column']; // Column index
      $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
      $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
      $searchValue = $_POST['search']['value']; // Search value

      $no_rkm_medis = $this->core->db->get('reg_periksa', 'no_rkm_medis', ['no_rawat' => $_POST['no_rawat']]);

      ## Total number of records without filtering
      $totalRecords = $this->core->db->count('reg_periksa', '*', ['no_rkm_medis' => $no_rkm_medis]);

      ## Total number of records with filtering
      $totalRecordwithFilter = $totalRecords;

      ## Fetch records
      $where = ['AND' => ['no_rkm_medis' => $no_rkm_medis]];
      $where ['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
      $where ['LIMIT'] = [$row1, $rowperpage];
      $result = $this->core->db->select('reg_periksa', '*', $where);

      $data = array();
      foreach($result as $row) {
          $data[] = array(
              'no_reg'=>$row['no_reg'],
              'no_rawat'=>$row['no_rawat'],
              'tgl_registrasi'=>$row['tgl_registrasi'],
              'jam_reg'=>$row['jam_reg'],
              'kd_dokter'=>$row['kd_dokter'],
              'no_rkm_medis'=>$row['no_rkm_medis'],
              'kd_poli'=>$row['kd_poli'],
              'p_jawab'=>$row['p_jawab'],
              'almt_pj'=>$row['almt_pj'],
              'hubunganpj'=>$row['hubunganpj'],
              'biaya_reg'=>$row['biaya_reg'],
              'stts'=>$row['stts'],
              'stts_daftar'=>$row['stts_daftar'],
              'status_lanjut'=>$row['status_lanjut'],
              'kd_pj'=>$row['kd_pj'],
              'status_bayar'=>$row['status_bayar'],
              'status_poli'=>$row['status_poli']
          );
      }

      ## Response
      $response = array(
          "draw" => intval($draw), 
          "iTotalRecords" => $totalRecords,
          "iTotalDisplayRecords" => $totalRecordwithFilter,
          "aaData" => $data
      );

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => postDataRiwayat');
      }

      echo json_encode($response);
      exit();
    }    

    public function postDataPemeriksaanRalan(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        $no_rawat = $_POST['no_rawat'];

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('pemeriksaan_ralan', '*', [
          'no_rawat' => $no_rawat
        ]);
        
        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('pemeriksaan_ralan', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Fetch records
        $result = $this->core->db->select('pemeriksaan_ralan', '*', [
          'no_rawat' => $no_rawat, 
          'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
          'LIMIT' => [$row1, $rowperpage]          
        ]);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
                'tgl_perawatan'=>$row['tgl_perawatan'],
                'jam_rawat'=>$row['jam_rawat'],
                'suhu_tubuh'=>$row['suhu_tubuh'],
                'tensi'=>$row['tensi'],
                'nadi'=>$row['nadi'],
                'respirasi'=>$row['respirasi'],
                'tinggi'=>$row['tinggi'],
                'berat'=>$row['berat'],
                'spo2'=>$row['spo2'],
                'gcs'=>$row['gcs'],
                'kesadaran'=>$row['kesadaran'],
                'keluhan'=>$row['keluhan'],
                'pemeriksaan'=>$row['pemeriksaan'],
                'alergi'=>$row['alergi'],
                'lingkar_perut'=>$row['lingkar_perut'],
                'rtl'=>$row['rtl'],
                'penilaian'=>$row['penilaian'],
                'instruksi'=>$row['instruksi'],
                'evaluasi'=>$row['evaluasi'],
                'nip'=>$row['nip']
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => postDataPemeriksaanRalan');
        }

        echo json_encode($response);
        exit();
    }

    public function postDataTindakanRalanDokter(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        $no_rawat = $_POST['no_rawat'];

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('rawat_jl_dr', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('rawat_jl_dr', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Fetch records
        $result = $this->core->db->select('rawat_jl_dr', '*', [
          'no_rawat' => $no_rawat, 
          'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
          'LIMIT' => [$row1, $rowperpage]          
        ]);


        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
                'kd_jenis_prw'=>$row['kd_jenis_prw'],
                'kd_dokter'=>$row['kd_dokter'],
                'tgl_perawatan'=>$row['tgl_perawatan'],
                'jam_rawat'=>$row['jam_rawat'],
                'material'=>$row['material'],
                'bhp'=>$row['bhp'],
                'tarif_tindakandr'=>$row['tarif_tindakandr'],
                'kso'=>$row['kso'],
                'menejemen'=>$row['menejemen'],
                'biaya_rawat'=>$row['biaya_rawat'],
                'stts_bayar'=>$row['stts_bayar']
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => postDataTindakanRalanDokter');
        }

        echo json_encode($response);
        exit();
    }

    public function postDataTindakanRalanPerawat(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        $no_rawat = $_POST['no_rawat'];

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('rawat_jl_pr', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('rawat_jl_pr', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Fetch records
        $result = $this->core->db->select('rawat_jl_pr', '*', [
          'no_rawat' => $no_rawat, 
          'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
          'LIMIT' => [$row1, $rowperpage]          
        ]);

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
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => postDataTindakanRalanPerawat');
        }

        echo json_encode($response);
        exit();
    }

    public function postDataTindakanRalanDokterPerawat(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        $no_rawat = $_POST['no_rawat'];

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('rawat_jl_drpr', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('rawat_jl_drpr', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Fetch records
        $result = $this->core->db->select('rawat_jl_drpr', '*', [
          'no_rawat' => $no_rawat, 
          'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
          'LIMIT' => [$row1, $rowperpage]
        ]);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
                'kd_jenis_prw'=>$row['kd_jenis_prw'],
                'kd_dokter'=>$row['kd_dokter'],
                'nip'=>$row['nip'],
                'tgl_perawatan'=>$row['tgl_perawatan'],
                'jam_rawat'=>$row['jam_rawat'],
                'material'=>$row['material'],
                'bhp'=>$row['bhp'],
                'tarif_tindakandr'=>$row['tarif_tindakandr'],
                'tarif_tindakanpr'=>$row['tarif_tindakanpr'],
                'kso'=>$row['kso'],
                'menejemen'=>$row['menejemen'],
                'biaya_rawat'=>$row['biaya_rawat'],
                'stts_bayar'=>$row['stts_bayar']
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => postDataTindakanRalanDokterPerawat');
        }

        echo json_encode($response);
        exit();
    }

    public function postDataResepDokter(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        $no_rawat = $_POST['no_rawat'];

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('resep_obat', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('resep_obat', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Fetch records
        $result = $this->core->db->select('resep_obat', '*', [
          'no_rawat' => $no_rawat
        ],[
          'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
          'LIMIT' => [$row1, $rowperpage]
        ]);

        $data = array();
        foreach($result as $row) {
            $isResepDokter = $this->core->db->has('resep_dokter', ['no_resep' => $row['no_resep']]);
            if($isResepDokter) {
              $data[] = array(
                  'no_resep'=>$row['no_resep'],
                  'tgl_perawatan'=>$row['tgl_perawatan'],
                  'jam'=>$row['jam'],
                  'no_rawat'=>$row['no_rawat'],
                  'kd_dokter'=>$row['kd_dokter'],
                  'tgl_peresepan'=>$row['tgl_peresepan'],
                  'jam_peresepan'=>$row['jam_peresepan'],
                  'status'=>$row['status'],
                  'tgl_penyerahan'=>$row['tgl_penyerahan'],
                  'jam_penyerahan'=>$row['jam_penyerahan']
              );
            }
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => postDataResepDokter');
        }

        echo json_encode($response);
        exit();
    }

    public function postDataResepDokterRacikan(){
      $draw = $_POST['draw'];
      $row1 = $_POST['start'];
      $rowperpage = $_POST['length']; // Rows display per page
      $columnIndex = $_POST['order'][0]['column']; // Column index
      $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
      $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
      $searchValue = $_POST['search']['value']; // Search value

      $no_rawat = $_POST['no_rawat'];

      ## Total number of records without filtering
      $totalRecords = $this->core->db->count('resep_obat', '*', [
        'no_rawat' => $no_rawat
      ]);

      ## Total number of records with filtering
      $totalRecordwithFilter = $this->core->db->count('resep_obat', '*', [
        'no_rawat' => $no_rawat
      ]);

      ## Fetch records
      $result = $this->core->db->select('resep_obat', '*', [
        'no_rawat' => $no_rawat
      ],[
        'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
        'LIMIT' => [$row1, $rowperpage]
      ]);

      $data = array();
      foreach($result as $row) {
          $isResepDokterRacikan = $this->core->db->has('resep_dokter_racikan', ['no_resep' => $row['no_resep']]);
          if($isResepDokterRacikan) {
            $row2 = $this->core->db->get('resep_dokter_racikan', '*', ['no_resep' => $row['no_resep']]);
            $data[] = array(
                'no_resep'=>$row['no_resep'],
                'tgl_perawatan'=>$row['tgl_perawatan'],
                'jam'=>$row['jam'],
                'no_rawat'=>$row['no_rawat'],
                'kd_dokter'=>$row['kd_dokter'],
                'tgl_peresepan'=>$row['tgl_peresepan'],
                'jam_peresepan'=>$row['jam_peresepan'],
                'status'=>$row['status'],
                'tgl_penyerahan'=>$row['tgl_penyerahan'],
                'jam_penyerahan'=>$row['jam_penyerahan'],
                'no_racik'=>isset_or($row2['no_racik']),
                'nama_racik'=>isset_or($row2['nama_racik']),
                'kd_racik'=>isset_or($row2['kd_racik']),
                'jml_dr'=>isset_or($row2['jml_dr']),
                'aturan_pakai'=>isset_or($row2['aturan_pakai']),
                'keterangan'=>isset_or($row2['keterangan'])
            );
          }
      }

      ## Response
      $response = array(
          "draw" => intval($draw), 
          "iTotalRecords" => $totalRecords,
          "iTotalDisplayRecords" => $totalRecordwithFilter,
          "aaData" => $data
      );

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => postDataResepDokterRacikan');
      }

      echo json_encode($response);
      exit();
    }

    public function postDataPermintaanLab(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        $no_rawat = $_POST['no_rawat'];

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('permintaan_lab', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('permintaan_lab', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Fetch records
        $result = $this->core->db->select('permintaan_lab', '*', [
          'no_rawat' => $no_rawat
        ],[
          'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
          'LIMIT' => [$row1, $rowperpage]
        ]);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'noorder'=>$row['noorder'],
                'no_rawat'=>$row['no_rawat'],
                'tgl_permintaan'=>$row['tgl_permintaan'],
                'jam_permintaan'=>$row['jam_permintaan'],
                'tgl_sampel'=>$row['tgl_sampel'],
                'jam_sampel'=>$row['jam_sampel'],
                'tgl_hasil'=>$row['tgl_hasil'],
                'jam_hasil'=>$row['jam_hasil'],
                'dokter_perujuk'=>$row['dokter_perujuk'],
                'status'=>$row['status'],
                'informasi_tambahan'=>$row['informasi_tambahan'],
                'diagnosa_klinis'=>$row['diagnosa_klinis']
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => postDataPermintaanLab');
        }

        echo json_encode($response);
        exit();
    }

    public function postDataPermintaanRadiologi(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        $no_rawat = $_POST['no_rawat'];

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('permintaan_radiologi', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('permintaan_radiologi', '*', [
          'no_rawat' => $no_rawat
        ]);

        ## Fetch records
        $result = $this->core->db->select('permintaan_radiologi', '*', [
          'no_rawat' => $no_rawat
        ],[
          'ORDER' => [$columnName => strtoupper($columnSortOrder)], 
          'LIMIT' => [$row1, $rowperpage]
        ]);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'noorder'=>$row['noorder'],
                'no_rawat'=>$row['no_rawat'],
                'tgl_permintaan'=>$row['tgl_permintaan'],
                'jam_permintaan'=>$row['jam_permintaan'],
                'tgl_sampel'=>$row['tgl_sampel'],
                'jam_sampel'=>$row['jam_sampel'],
                'tgl_hasil'=>$row['tgl_hasil'],
                'jam_hasil'=>$row['jam_hasil'],
                'dokter_perujuk'=>$row['dokter_perujuk'],
                'status'=>$row['status'],
                'informasi_tambahan'=>$row['informasi_tambahan'],
                'diagnosa_klinis'=>$row['diagnosa_klinis']

            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('reg_periksa => postDataPermintaanRadiologi');
        }

        echo json_encode($response);
        exit();
    }

    public function getJnsPerawatanLab($kd_jenis_prw)
    {
      echo json_encode($this->core->db->select('template_laboratorium', '*', ['kd_jenis_prw' => $kd_jenis_prw]));
      exit();
    }

    public function postGetNoRawat()
    {
      $result = $this->core->setNoRawat(date('Y-m-d'));
      echo $result;
      exit();
    }

    public function postGetNoReg()
    {
      $result = $this->core->setNoReg(isset_or($_POST['kd_poli'], false), isset_or($_POST['kd_dokter'], false));
      echo $result;
      exit();
    }

    public function postGetNoResep()
    {
      $result = $this->core->setNoResep(isset_or($_POST['kd_poli'], date('Y-m-d')));
      echo $result;
      exit();
    }

    public function postGetSttsDaftar()
    {
      $result = $this->core->db->get('reg_periksa', 'no_rawat', ['no_rkm_medis' => $_POST['no_rkm_medis']]);
      if(!empty($result))
      {
        echo 'Lama';
      } else {
        echo 'Baru';
      }
      exit();
    }

    public function postGetStatusPoli()
    {
      $result = $this->core->db->get('reg_periksa', 'no_rawat', ['no_rkm_medis' => $_POST['no_rkm_medis'], 'kd_poli' => $_POST['kd_poli']]);
      if(!empty($result))
      {
        echo 'Lama';
      } else {
        echo 'Baru';
      }
      exit();
    }

    public function getPemeriksaanRalan()
    {
      echo $this->draw('pemeriksaa.ralan.html');
      exit();
    }

    public function postGetJnsPerawatan()
    {
      $kd_jenis_prw = $_POST['kd_jenis_prw'];
      $result = $this->core->db->get('jns_perawatan', '*', ['kd_jenis_prw' => $kd_jenis_prw]);
      echo json_encode($result);
      exit();
    }

    public function getCekReferensi($type)
    {
        $bpjs = new \Bridging\Bpjs\VClaim\Referensi($this->core->vclaim);
        if($type == 'diagnosa') {
          $array = $bpjs->diagnosa('A00');
          echo '<pre>'.json_encode($array, JSON_PRETTY_PRINT).'</pre>';
          // echo array_to_table($array);
        }
        exit();
    }

    public function postGetResepDokter()
    {
      echo json_encode($this->core->db->select('resep_dokter', [
        '[>]databarang' => ['kode_brng' => 'kode_brng']
      ], ['resep_dokter.kode_brng', 'nama_brng', 'jml', 'aturan_pakai'], ['no_resep' => $_POST['no_resep']]));
      exit();
    }

    public function postGetResepDokterRacikan()
    {
      echo json_encode($this->core->db->select('resep_dokter_racikan_detail', [
        '[>]databarang' => ['kode_brng' => 'kode_brng']
      ], ['resep_dokter_racikan_detail.kode_brng', 'nama_brng', 'kapasitas', 'jml', 'kandungan'], ['no_resep' => $_POST['no_resep']]));
      exit();
    }

    public function postGetPermintaanLab()
    {
      // $_POST['noorder'] = 'PL202407140002';
      $permintaan_pemeriksaan_lab = $this->core->db->select('permintaan_pemeriksaan_lab', '*', ['noorder' => $_POST['noorder']]);
      $permintaan_lab = [];
      $nomor = '1';
      foreach($permintaan_pemeriksaan_lab as $row) {
        $nomor2 = '1';
        $row['nomor'] = $nomor++;
        $row['detail_permintaan'] = [];
        $permintaan_detail_permintaan_lab = $this->core->db->select('permintaan_detail_permintaan_lab', '*', ['noorder' => $_POST['noorder'], 'kd_jenis_prw' => $row['kd_jenis_prw']]);
        foreach($permintaan_detail_permintaan_lab as $row2) {
          $row2['nomor2'] = $nomor2++;
          $row['detail_permintaan'][] = $row2;
        }
        $permintaan_lab[] = $row;
      }

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => postGetPermintaanLab');
      }

      echo json_encode($permintaan_lab);
      exit();
    }

    public function postGetPermintaanRadiologi()
    {
      // $_POST['noorder'] = 'PL202407140002';
      $permintaan_pemeriksaan_radiologi = $this->core->db->select('permintaan_pemeriksaan_radiologi', '*', ['noorder' => $_POST['noorder']]);
      $permintaan_radiologi = [];
      $nomor = '1';
      foreach($permintaan_pemeriksaan_radiologi as $row) {
        $row['nomor'] = $nomor++;
        $permintaan_radiologi[] = $row;
      }

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => postGetPermintaanRadiologi');
      }

      echo json_encode($permintaan_radiologi);
      exit();
    }    

    public function getGetRiwayat($no_rawat)
    {
      header('Content-type: text/json');

      // $_POST['no_rawat'] = '2024/07/08/000002';
      $no_rawat = revertNoRawat($no_rawat);
      $result = [];
      $result['pemeriksaan_ralan'] = $this->core->db->select('pemeriksaan_ralan', '*', ['no_rawat' => $no_rawat]);
      $result['rawat_jl_dr'] = $this->core->db->select('rawat_jl_dr', '*', ['no_rawat' => $no_rawat]);
      $result['rawat_jl_pr'] = $this->core->db->select('rawat_jl_pr', '*', ['no_rawat' => $no_rawat]);
      $result['rawat_jl_drpr'] = $this->core->db->select('rawat_jl_drpr', '*', ['no_rawat' => $no_rawat]);

      // $no_resep = $this->core->db->get('resep_obat', 'no_resep', ['no_rawat' => $no_rawat]);

      // $result['resep_dokter'] = $this->core->db->select('resep_dokter', '*', ['no_resep' => $no_resep]);

      $result['resep_dokter'] = $this->core->db->select('resep_obat', [
        '[<]resep_dokter' => ['no_resep' => 'no_resep']
      ], '*', [
        'no_rawat' => $no_rawat
      ]);

      // $result['resep_dokter_racikan'] = $this->core->db->select('resep_dokter_racikan', '*', ['no_resep' => $no_resep]);

      $resep_dokter_racikan = $this->core->db->select('resep_obat', [
        '[<]resep_dokter_racikan' => ['no_resep' => 'no_resep']
      ], '*', [
        'no_rawat' => $no_rawat
      ]);

      $result['resep_dokter_racikan'] = [];
      foreach($resep_dokter_racikan as $row) {
        $row['resep_dokter_racikan_detail'] = $this->core->db->select('resep_dokter_racikan_detail', '*', ['no_resep' => $row['no_resep']]);
        $result['resep_dokter_racikan'][] = $row;
      }
       
      $result['berkas_digital_perawatan'] = $this->core->db->select('berkas_digital_perawatan', '*', ['no_rawat' => $no_rawat]);

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => postGetRiwayat');
      }

      echo json_encode($result);
      exit();
    }

    public function getCopySoap($no_rawat)
    {
      $no_rkm_medis = $this->core->db->get('reg_periksa', 'no_rkm_medis', ['no_rawat' => revertNoRawat($no_rawat)]);
      $soap = $this->core->db->select('pemeriksaan_ralan', '*', [
        'no_rawat' => $this->core->db->select('reg_periksa', 'no_rawat', ['no_rkm_medis' => $no_rkm_medis]), 
        'ORDER' => ['tgl_perawatan' => 'DESC'], 
        'LIMIT' => '10'
      ]);

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => getCopySoap');
      }

      echo $this->draw('copy.soap.html', ['soap' => $soap]);
      exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('reg_periksa', 'kd_poli', ['GROUP' => 'kd_poli']);
      $datasets = $this->core->db->select('reg_periksa', ['count' => \Medoo\Medoo::raw('COUNT(<kd_poli>)')], ['GROUP' => 'kd_poli']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('reg_periksa', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('reg_periksa', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'reg_periksa';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('reg_periksa => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }    

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/reg_periksa/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        // $this->assign['tanggal'] = date('Y-m-d');
        // $this->assign['jam'] = date('H:i:s');
        $this->assign['no_rawat'] = $this->core->setNoRawat(date('Y-m-d'));
        $this->assign['no_reg'] = $this->core->setNoReg();

        $this->assign['poliklinik'] = $this->core->db->select('poliklinik', ['kd_poli', 'nm_poli'], ['status' => '1']);
        $this->assign['dokter'] = $this->core->db->select('dokter', ['kd_dokter', 'nm_dokter'], ['status' => '1']);
        $this->assign['penjab'] = $this->core->db->select('penjab', ['kd_pj', 'png_jawab'], ['status' => '1']);
        $this->assign['stts'] = $this->core->getEnum('reg_periksa', 'stts');
        $this->assign['stts_daftar'] = $this->core->getEnum('reg_periksa', 'stts_daftar');
        $this->assign['status_lanjut'] = $this->core->getEnum('reg_periksa', 'status_lanjut');
        $this->assign['sttsumur'] = $this->core->getEnum('reg_periksa', 'sttsumur');
        $this->assign['status_bayar'] = $this->core->getEnum('reg_periksa', 'status_bayar');
        $this->assign['status_poli'] = $this->core->getEnum('reg_periksa', 'status_poli');

        echo $this->draw(MODULES.'/reg_periksa/js/scripts.js', ['settings' => $settings, 'reg_periksa' => $this->assign, 'disabled_menu' => $this->core->loadDisabledMenu('reg_periksa')]);
        exit();
    }

    public function getJavascriptPemeriksaanRalan()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/pemeriksaan.ralan.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('pemeriksaan_ralan')]);
        exit();
    }

    public function getJavascriptTindakanRalanDokter()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/tindakan.ralan.dokter.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('tindakan_ralan_dokter')]);
        exit();
    }

    public function getJavascriptTindakanRalanPerawat()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/tindakan.ralan.perawat.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('tindakan_ralan_perawat')]);
        exit();
    }

    public function getJavascriptTindakanRalanDokterPerawat()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/tindakan.ralan.dokter.perawat.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('tindakan_ralan_dokter_perawat')]);
        exit();
    }

    public function getJavascriptResepDokter()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/resep.dokter.js', ['settings' => $settings, 'setnoresep' => $this->core->setNoResep(date('Y-m-d')), 'disabled_menu' => $this->core->loadDisabledMenu('resep_dokter')]);
        exit();
    }

    public function getJavascriptResepDokterRacikan()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/resep.dokter.racikan.js', ['settings' => $settings, 'setnoresep' => $this->core->setNoResep(date('Y-m-d')), 'disabled_menu' => $this->core->loadDisabledMenu('resep_dokter_racikan')]);
        exit();
    }

    public function getJavascriptPermintaanLab()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/permintaan.lab.js', ['settings' => $settings, 'setnoorder' => $this->core->setNoOrderLab(date('Y-m-d')), 'disabled_menu' => $this->core->loadDisabledMenu('permintaan_lab')]);
        exit();
    }

    public function getJavascriptPermintaanRadiologi()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/reg_periksa/js/permintaan.radiologi.js', ['settings' => $settings, 'setnoorder' => $this->core->setNoOrderRadiologi(date('Y-m-d')), 'disabled_menu' => $this->core->loadDisabledMenu('permintaan_lab')]);
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

        $this->core->addCSS(url([ 'reg_periksa', 'css']));
        $this->core->addJS(url([ 'reg_periksa', 'javascript']), 'footer');
    }

}
