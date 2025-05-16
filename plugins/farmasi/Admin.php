<?php

namespace Plugins\Farmasi;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Obat & BHP' => 'mutasi',
            'Stok Opname' => 'opname',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Mutasi Obat', 'url' => url([ADMIN, 'farmasi', 'mutasi']), 'icon' => 'medkit', 'desc' => 'Data obat dan barang habis pakai'],
        ['name' => 'Stok Opname', 'url' => url([ADMIN, 'farmasi', 'opname']), 'icon' => 'medkit', 'desc' => 'Tambah stok opname'],
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
              'stok_akhir' => $stok + $_POST['stok'],
              'posisi' => 'Pengadaan',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0',
              'keterangan' => '-'
            ]);
            if($query) {
              $this->db('gudangbarang')
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
              'stok' => $get_gudangbarang['stok'] + $_POST['stok']
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
              'keterangan' => '-'
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
        }
      }

      exit();
    }

    public function postReStok()
    {

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
