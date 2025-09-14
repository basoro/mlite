<?php

namespace Plugins\Farmasi;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public $assign = [];

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Mutasi Obat & BHP' => 'mutasi',
            'Stok Opname' => 'opname',
            'Darurat Stok' => 'daruratstok',
            'Detail Pemberian Obat' => 'detailpemberianobat',
            'Riwayat Barang Medis' => 'riwayatbarangmedis',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Mutasi Obat & BHP', 'url' => url([ADMIN, 'farmasi', 'mutasi']), 'icon' => 'medkit', 'desc' => 'Data obat dan barang habis pakai'],
        ['name' => 'Stok Opname', 'url' => url([ADMIN, 'farmasi', 'opname']), 'icon' => 'medkit', 'desc' => 'Tambah stok opname'],
        ['name' => 'Darurat Stok', 'url' => url([ADMIN, 'farmasi', 'daruratstok']), 'icon' => 'warning', 'desc' => 'Monitoring stok darurat obat dan BHP'],
        ['name' => 'Detail Pemberian Obat', 'url' => url([ADMIN, 'farmasi', 'detailpemberianobat']), 'icon' => 'medkit', 'desc' => 'Detail pemberian obat pasien'],
        ['name' => 'Riwayat Barang Medis', 'url' => url([ADMIN, 'farmasi', 'riwayatbarangmedis']), 'icon' => 'medkit', 'desc' => 'Riwayat pergerakan barang medis'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'farmasi', 'settings']), 'icon' => 'medkit', 'desc' => 'Pengaturan farmasi dan depo'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getMutasi($status = '1')
    {
        $this->_addHeaderFiles();
        $databarang['title'] = 'Kelola Mutasi Obat';
        $databarang['bangsal']  = $this->db('bangsal')->toArray();
        $databarang['list'] = $this->_databarangList($status);
        return $this->draw('mutasi.html', ['databarang' => $databarang, 'tab' => $status]);
    }

    private function _databarangList($status)
    {
        $result = [];

        foreach ($this->db('databarang')->where('status', $status)->toArray() as $row) {
            $row['delURL']  = url([ADMIN, 'farmasi', 'delete', $row['kode_brng']]);
            $row['restoreURL']  = url([ADMIN, 'farmasi', 'restore', $row['kode_brng']]);
            $row['gudangbarang'] = $this->db('gudangbarang')->join('bangsal', 'bangsal.kd_bangsal=gudangbarang.kd_bangsal')->where('kode_brng', $row['kode_brng'])->toArray();
            $result[] = $row;
        }
        return $result;
    }

    public function getDelete($id)
    {
        if ($this->db('databarang')->where('kode_brng', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'mutasi']));
    }

    public function getRestore($id)
    {
        if ($this->db('databarang')->where('kode_brng', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'mutasi']));
    }

    public function postSetStok()
    {
      $databarang = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->oneArray();

      if($this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray()) {

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.gudang'))->oneArray();
        $gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray();

        if($_POST['kd_bangsal'] == $this->settings->get('farmasi.gudang')) {
          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $get_gudangbarang['stok'] + $_POST['stok'],
              'posisi' => 'Pengadaan',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0',
              'keterangan' => '1111'
            ]);
            if($query) {
              $query2 = $this->db('gudangbarang')
                ->where('kode_brng', $_POST['kode_brng'])
                ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
                ->save([
                  'stok' => $get_gudangbarang['stok'] + $_POST['stok']
              ]);
            }
        } else {

          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => '0',
              'keluar' => $_POST['stok'],
              'stok_akhir' => $get_gudangbarang['stok'] - $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0',
              'keterangan' => '-'
            ]);

          $query2 = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $gudangbarang['stok'],
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $gudangbarang['stok'] + $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $_POST['kd_bangsal'],
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0',
              'keterangan' => '-'
            ]);
        }

        if($query) {
          $this->db('gudangbarang')
            ->where('kode_brng', $_POST['kode_brng'])
            ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
            ->save([
              'stok' => $get_gudangbarang['stok'] - $_POST['stok']
          ]);
        }
        if($query2) {
          $this->db('gudangbarang')
            ->where('kode_brng', $_POST['kode_brng'])
            ->where('kd_bangsal', $_POST['kd_bangsal'])
            ->save([
              'stok' => $gudangbarang['stok'] + $_POST['stok']
          ]);
        }

        $this->db('mutasibarang')->save([
          'kode_brng' => $_POST['kode_brng'],
          'jml' => $_POST['stok'],
          'harga' => $_POST['harga'] ?? $databarang['dasar'],
          'kd_bangsaldari' => $this->settings->get('farmasi.gudang'),
          'kd_bangsalke' => $_POST['kd_bangsal'],
          'tanggal' => date('Y-m-d H:i:s'),
          'keterangan' => $_POST['keterangan'] ?? 'Set Stok - Mutasi',
          'no_batch' => $_POST['no_batch'] ?? '0',
          'no_faktur' => $_POST['no_faktur'] ?? '0'
        ]);

      } else {

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.gudang'))->oneArray();
        $stok = '0';
        if($get_gudangbarang) {
          $stok = $get_gudangbarang['stok'];
        }
        if($_POST['kd_bangsal'] == $this->settings->get('farmasi.gudang')) {
          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => '0',
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $_POST['stok'],
              'posisi' => 'Pengadaan',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0',
              'keterangan' => '222'
            ]);
            if($query) {
              $this->db('gudangbarang')->save([
                'kode_brng' => $_POST['kode_brng'],
                'kd_bangsal' => $this->settings->get('farmasi.gudang'),
                'stok' => $_POST['stok'],
                'no_batch' => '0',
                'no_faktur' => '0'
              ]);
            }

        } else {

          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $stok,
              'masuk' => '0',
              'keluar' => $_POST['stok'],
              'stok_akhir' => $stok - $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0',
              'keterangan' => '-'
            ]);

          $query2 = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => '0',
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $_POST['kd_bangsal'],
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0',
              'keterangan' => ''
            ]);
          if($query) {
            $this->db('gudangbarang')
              ->where('kode_brng', $_POST['kode_brng'])
              ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
              ->save([
                'stok' => $get_gudangbarang['stok'] - $_POST['stok']
            ]);
          }
          if($query2) {
            $this->db('gudangbarang')->save([
              'kode_brng' => $_POST['kode_brng'],
              'kd_bangsal' => $_POST['kd_bangsal'],
              'stok' => $_POST['stok'],
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);
          }

          $this->db('mutasibarang')->save([
            'kode_brng' => $_POST['kode_brng'],
            'jml' => $_POST['stok'],
            'harga' => $_POST['harga'] ?? $databarang['dasar'],
            'kd_bangsaldari' => $this->settings->get('farmasi.gudang'),
            'kd_bangsalke' => $_POST['kd_bangsal'],
            'tanggal' => date('Y-m-d H:i:s'),
            'keterangan' => $_POST['keterangan'] ?? 'Set Stok - Mutasi',
            'no_batch' => $_POST['no_batch'] ?? '0',
            'no_faktur' => $_POST['no_faktur'] ?? '0'
          ]);

        }
      }

      exit();
    }

    public function postReStok()
    {

      $databarang = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->oneArray();

      $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.gudang'))->oneArray();
      $gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray();

      $query = $this->db('riwayat_barang_medis')
        ->save([
          'kode_brng' => $_POST['kode_brng'],
          'stok_awal' => $get_gudangbarang['stok'],
          'masuk' => '0',
          'keluar' => $_POST['stok'],
          'stok_akhir' => $get_gudangbarang['stok'] + $_POST['stok'],
          'posisi' => 'Mutasi',
          'tanggal' => date('Y-m-d'),
          'jam' => date('H:i:s'),
          'petugas' => $this->core->getUserInfo('fullname', null, true),
          'kd_bangsal' => $this->settings->get('farmasi.gudang'),
          'status' => 'Simpan',
          'no_batch' => '0',
          'no_faktur' => '0',
          'keterangan' => ''
        ]);

      if($query) {
        $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kode_brng'])
          ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
          ->save([
            'stok' => $get_gudangbarang['stok'] + $_POST['stok']
        ]);
      }

      $query2 = $this->db('riwayat_barang_medis')
        ->save([
          'kode_brng' => $_POST['kode_brng'],
          'stok_awal' => $gudangbarang['stok'],
          'masuk' => $_POST['stok'],
          'keluar' => '0',
          'stok_akhir' => $gudangbarang['stok'] - $_POST['stok'],
          'posisi' => 'Mutasi',
          'tanggal' => date('Y-m-d'),
          'jam' => date('H:i:s'),
          'petugas' => $this->core->getUserInfo('fullname', null, true),
          'kd_bangsal' => $_POST['kd_bangsal'],
          'status' => 'Simpan',
          'no_batch' => '0',
          'no_faktur' => '0',
          'keterangan' => ''
        ]);

      if($query2) {
        $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kode_brng'])
          ->where('kd_bangsal', $_POST['kd_bangsal'])
          ->save([
            'stok' => $gudangbarang['stok'] - $_POST['stok']
        ]);
      }      

      $this->db('mutasibarang')->save([
        'kode_brng' => $_POST['kode_brng'],
        'jml' => $_POST['stok'],
        'harga' => $_POST['harga'] ?? $databarang['dasar'],
        'kd_bangsaldari' => $this->settings->get('farmasi.gudang'),
        'kd_bangsalke' => $_POST['kd_bangsal'],
        'tanggal' => date('Y-m-d H:i:s'),
        'keterangan' => $_POST['keterangan'] ?? 'Set restok - Mutasi',
        'no_batch' => $_POST['no_batch'] ?? '0',
        'no_faktur' => $_POST['no_faktur'] ?? '0'
      ]);

      exit();
    }

    /* End Databarang Section */

    public function getOpname($data='')
    {
      $this->_addHeaderFiles();
      if($data == 'data') {
        return $this->draw('opname.data.html');
      } else {
        return $this->draw('opname.html');
      }
    }

    public function postOpnameAll()
    {
      $gudangbarang = $this->db('gudangbarang')
        ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
        ->join('bangsal', 'bangsal.kd_bangsal=gudangbarang.kd_bangsal')
        ->where('databarang.status', '1')
        ->toJson();
      echo $gudangbarang;
      exit();
    }

    public function postOpnameData()
    {
      $opname = $this->db('opname')
        ->join('databarang', 'databarang.kode_brng=opname.kode_brng')
        ->join('bangsal', 'bangsal.kd_bangsal=opname.kd_bangsal')
        ->where('databarang.status', '1')
        ->toJson();
      echo $opname;
      exit();
    }

    public function postOpnameUpdate()
    {
      $kode_brng =$_POST['kode_brng'];
      $real = $_POST['real'];
      $stok = $_POST['stok'];
      $kd_bangsal = $_POST['kd_bangsal'];
      $tanggal = $_POST['tanggal'];
      $h_beli = $_POST['h_beli'];
      $keterangan = $_POST['keterangan'];
      $no_batch = $_POST['no_batch'];
      $no_faktur = $_POST['no_faktur'];
      for($count = 0; $count < count($kode_brng); $count++){
        $selisih = $real[$count] - $stok[$count];
        $nomihilang = $selisih * $h_beli[$count];
        $lebih = 0;
        $nomilebih = 0;
        if($selisih < 0) {
          $selisih = 0;
          $nomihilang = 0;
          $lebih = $stok[$count] - $real[$count];
          $nomilebih = $lebih * $h_beli[$count];
        }

        $query2 = "INSERT INTO `opname` (`kode_brng`, `h_beli`, `tanggal`, `stok`, `real`, `selisih`, `nomihilang`, `lebih`, `nomilebih`, `keterangan`, `kd_bangsal`, `no_batch`, `no_faktur`) VALUES ('$kode_brng[$count]', '$h_beli[$count]', '$tanggal[$count]', '$real[$count]', '$stok[$count]', '$selisih', '$nomihilang', '$lebih', '$nomilebih', '$keterangan[$count]', '$kd_bangsal[$count]', '$no_batch[$count]', '$no_faktur[$count]')";
        $opname2 = $this->db()->pdo()->prepare($query2);
        $opname2->execute();

        if ($opname2->errorInfo()[2] == ''){
          $query = "UPDATE gudangbarang SET stok=?, no_batch=?, no_faktur=? WHERE kode_brng=? AND kd_bangsal=?";
          $opname = $this->db()->pdo()->prepare($query);              
          $opname->execute([$real[$count], $no_batch[$count], $no_faktur[$count], $kode_brng[$count], $kd_bangsal[$count]]);
          $keluar = '0';
          $masuk = '0';
          if($real[$count]>$stok[$count]) {
          $masuk = $real[$count]-$stok[$count];
          }
          if($real[$count]<$stok[$count]) {
          $keluar = $stok[$count]-$real[$count];
          }
          $this->db('riwayat_barang_medis')
          ->save([
            'kode_brng' => $kode_brng[$count],
            'stok_awal' => $stok[$count],
            'masuk' => $masuk,
            'keluar' => $keluar,
            'stok_akhir' => $real[$count],
            'posisi' => 'Opname',
            'tanggal' => $tanggal[$count],
            'jam' => date('H:i:s'),
            'petugas' => $this->core->getUserInfo('fullname', null, true),
            'kd_bangsal' => $kd_bangsal[$count],
            'status' => 'Simpan',
            'no_batch' => $no_batch[$count],
            'no_faktur' => $no_faktur[$count],
            'keterangan' => $keterangan[$count]
          ]);   
                    
          $data = array(
            'status' => 'success', 
            'msg' => $this->db('databarang')->select('nama_brng')->where('kode_brng', $kode_brng[$count])->oneArray()['nama_brng'], 
            'info' => json_encode($opname2->errorInfo()[2])
          );

        } else {
          $data = array(
            'status' => 'error', 
            'msg' => $this->db('databarang')->select('nama_brng')->where('kode_brng', $kode_brng[$count])->oneArray()['nama_brng'], 
            'info' => json_encode($opname2->errorInfo()[2])
          );
        }
        echo json_encode($data);   

      }
      exit();
    }

    /* Settings Farmasi Section */
    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Farmasi';
        $this->assign['bangsal'] = $this->db('bangsal')->toArray();
        $this->assign['farmasi'] = htmlspecialchars_array($this->settings('farmasi'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['farmasi'] as $key => $val) {
            $this->settings('farmasi', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'farmasi', 'settings']));
    }
    /* End Settings Farmasi Section */

    /* Detail Pemberian Obat Section */
    public function getDetailpemberianobat()
    {
        $this->_addHeaderFiles();
        $this->core->addJS(url([ADMIN, 'farmasi', 'detailpemberianobatjs']), 'footer');
        return $this->draw('detailpemberianobat.html');
    }

    public function postDetailpemberianobatData()
    {
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_detail_pemberian_obat= $_POST['search_field_detail_pemberian_obat'];
        $search_text_detail_pemberian_obat = $_POST['search_text_detail_pemberian_obat'];

        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-d'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));

        $searchQuery = " ";
        if($search_text_detail_pemberian_obat != ''){
            $searchQuery .= " and (".$search_field_detail_pemberian_obat." like '%".$search_text_detail_pemberian_obat."%' ) ";
        }

        $searchQuery .= " and (tgl_perawatan between '".$tgl_awal."' and '".$tgl_akhir."') ";

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from detail_pemberian_obat");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from detail_pemberian_obat WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from detail_pemberian_obat WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $databarang = $this->db('databarang')->select('nama_brng')->where('kode_brng', $row['kode_brng'])->oneArray();
            $bangsal = $this->db('bangsal')->select('nm_bangsal')->where('kd_bangsal', $row['kd_bangsal'])->oneArray();
            $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']);
            $data[] = array(
                'tgl_perawatan'=>$row['tgl_perawatan'],
                'jam'=>$row['jam'],
                'no_rkm_medis' => $no_rkm_medis,
                'nm_pasien' => $this->core->getPasienInfo('nm_pasien', $no_rkm_medis),
                'no_rawat'=>$row['no_rawat'],
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$databarang['nama_brng'],
                'h_beli'=>$row['h_beli'],
                'biaya_obat'=>$row['biaya_obat'],
                'jml'=>$row['jml'],
                'embalase'=>$row['embalase'],
                'tuslah'=>$row['tuslah'],
                'total'=>$row['total'],
                'status'=>$row['status'],
                'kd_bangsal'=>$row['kd_bangsal'],
                'nm_bangsal'=>$bangsal['nm_bangsal'],
                'no_batch'=>$row['no_batch'],
                'no_faktur'=>$row['no_faktur']
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode($response);
        exit();
    }

    public function getDetailpemberianobatJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/detailpemberianobat.js');
        exit();
    }
    /* End Detail Pemberian Obat Section */

    /* Riwayat Barang Medis Section */
    public function getRiwayatbarangmedis()
    {
        $this->_addHeaderFiles();
        $this->core->addJS(url([ADMIN, 'farmasi', 'riwayatbarangmedisjs']), 'footer');
        return $this->draw('riwayatbarangmedis.html');
    }

    public function postRiwayatbarangmedisData()
    {
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_riwayat_barang_medis= $_POST['search_field_riwayat_barang_medis'];
        $search_text_riwayat_barang_medis = $_POST['search_text_riwayat_barang_medis'];

        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-d'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));

        $searchQuery = " ";
        if($search_text_riwayat_barang_medis != ''){
            $searchQuery .= " and (".$search_field_riwayat_barang_medis." like '%".$search_text_riwayat_barang_medis."%' ) ";
        }

        $searchQuery .= " and (tanggal between '".$tgl_awal."' and '".$tgl_akhir."') ";

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from riwayat_barang_medis");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from riwayat_barang_medis WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from riwayat_barang_medis WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $databarang = $this->db('databarang')->select('nama_brng')->where('kode_brng', $row['kode_brng'])->oneArray();
            $bangsal = $this->db('bangsal')->select('nm_bangsal')->where('kd_bangsal', $row['kd_bangsal'])->oneArray();
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$databarang['nama_brng'],
                'stok_awal'=>$row['stok_awal'],
                'masuk'=>$row['masuk'],
                'keluar'=>$row['keluar'],
                'stok_akhir'=>$row['stok_akhir'],
                'posisi'=>$row['posisi'],
                'tanggal'=>$row['tanggal'],
                'jam'=>$row['jam'],
                'petugas'=>$row['petugas'],
                'kd_bangsal'=>$row['kd_bangsal'],
                'nm_bangsal'=>$bangsal['nm_bangsal'],
                'status'=>$row['status'],
                'no_batch'=>$row['no_batch'],
                'no_faktur'=>$row['no_faktur'],
                'keterangan'=>$row['keterangan']
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode($response);
        exit();
    }

    public function getRiwayatbarangmedisJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/riwayatbarangmedis.js');
        exit();
    }
    /* End Riwayat Barang Medis Section */

    /* Darurat Stok Section */
    public function getDaruratStok()
    {
        $this->_addHeaderFiles();
        $this->core->addJS(url([ADMIN, 'farmasi', 'daruratstokjs']), 'footer');
        return $this->draw('daruratstok.html');
    }

    public function postDaruratStokData()
    {
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_databarang= $_POST['search_field_databarang'];
        $search_text_databarang = $_POST['search_text_databarang'];

        $searchQuery = " ";
        if($search_text_databarang != ''){
            $searchQuery .= " and (".$search_field_databarang." like '%".$search_text_databarang."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from databarang");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from databarang WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from databarang WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $stok = $this->db('gudangbarang')->select(['stok' => 'SUM(stok)'])->where('kode_brng', $row['kode_brng'])->toArray();
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$row['nama_brng'],
                'stok'=>$stok[0]['stok'],
                'stokminimal'=>$row['stokminimal'],
                'kode_satbesar'=>$row['kode_satbesar'],
                'kode_sat'=>$row['kode_sat'],
                'dasar'=>$row['dasar'],
                'h_beli'=>$row['h_beli'],
                'isi'=>$row['isi'],
                'kapasitas'=>$row['kapasitas'],
                'expire'=>$row['expire']
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode($response);
        exit();
    }

    public function getDaruratStokJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/daruratstok.js');
        exit();
    }
    /* End Darurat Stok Section */

    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/farmasi/css/admin/farmasi.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/farmasi.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'farmasi', 'css']));
        $this->core->addJS(url([ADMIN, 'farmasi', 'javascript']), 'footer');
    }

}
