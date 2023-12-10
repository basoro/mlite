<?php

namespace Plugins\JKN_Mobile_FKTP;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Katalog' => 'index',
            'Mapping Poli' => 'mappingpoli',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Katalog', 'url' => url([ADMIN, 'jkn_mobile_fktp', 'index']), 'icon' => 'cube', 'desc' => 'Data obat dan barang habis pakai'],
        ['name' => 'Mapping Poli', 'url' => url([ADMIN, 'jkn_mobile_fktp', 'mappingpoli']), 'icon' => 'cube', 'desc' => 'Tambah stok opname'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'jkn_mobile_fktp', 'settings']), 'icon' => 'cube', 'desc' => 'Pengaturan farmasi dan depo'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul JKN Mobile FKTP';
        $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->settings->get('jkn_mobile_fktp.display'));
        $this->assign['jkn_mobile_fktp'] = htmlspecialchars_array($this->settings('jkn_mobile_fktp'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    private function _getPoliklinik($kd_poli = null)
    {
        $result = [];
        $rows = $this->db('poliklinik')->toArray();

        if (!$kd_poli) {
            $kd_poliArray = [];
        } else {
            $kd_poliArray = explode(',', $kd_poli);
        }

        foreach ($rows as $row) {
            if (empty($kd_poliArray)) {
                $attr = '';
            } else {
                if (in_array($row['kd_poli'], $kd_poliArray)) {
                    $attr = 'selected';
                } else {
                    $attr = '';
                }
            }
            $result[] = ['kd_poli' => $row['kd_poli'], 'nm_poli' => $row['nm_poli'], 'attr' => $attr];
        }
        return $result;
    }

    public function postSaveSettings()
    {
        $_POST['jkn_mobile_fktp']['display'] = implode(',', $_POST['jkn_mobile_fktp']['display']);
        foreach ($_POST['jkn_mobile_fktp'] as $key => $val) {
            $this->settings('jkn_mobile_fktp', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'jkn_mobile_fktp', 'settings']));
    }

    // Mapping section
    public function getMappingPoli()
    {
        $this->core->addJS(url([ADMIN, 'jkn_mobile_fktp', 'mappingpolijs']), 'footer');

        $totalRecords = $this->db('maping_poliklinik_pcare')
          ->select('kd_poli_rs')
          ->toArray();
        $jumlah_data    = count($totalRecords);
        $offset         = 10;
        $jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        $mappingpoli = $this->db('maping_poliklinik_pcare')
          ->desc('kd_poli_rs')
          ->limit(10)
          ->toArray();
        return $this->draw('mappingpoli.html', [
          'mappingpoli' => $mappingpoli,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

    }

    public function anyMappingPoliDisplay()
    {
        $this->core->addJS(url([ADMIN, 'jkn_mobile_fktp', 'mappingpolijs']), 'footer');

        $perpage = '10';
        $totalRecords = $this->db('maping_poliklinik_pcare')
          ->select('kd_poli_rs')
          ->toArray();
        $jumlah_data    = count($totalRecords);
        $offset         = 10;
        $jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        if(isset($_POST['cari'])) {
          $mappingpoli = $this->db('maping_poliklinik_pcare')
            ->like('kd_poli_pcare', '%'.$_POST['cari'].'%')
            ->orLike('nm_poli_pcare', '%'.$_POST['cari'].'%')
            ->desc('kd_poli_rs')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($mappingpoli);
          $jml_halaman = ceil($jumlah_data / $offset);
        }elseif(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $mappingpoli = $this->db('maping_poliklinik_pcare')
            ->desc('kd_poli_rs')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $halaman = $_POST['halaman'];
        }else{
          $mappingpoli = $this->db('maping_poliklinik_pcare')
            ->desc('kd_poli_rs')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
        }

        echo $this->draw('mappingpoli.display.html', [
          'mappingpoli' => $mappingpoli,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

        exit();
    }

    public function anyMappingPoliForm()
    {
      $poliklinik = $this->db('poliklinik')->toArray();
      if (isset($_POST['kd_poli_rs'])){
        $mappingpoli = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->oneArray();
        echo $this->draw('mappingpoli.form.html', ['poliklinik' => $poliklinik, 'mappingpoli' => $mappingpoli]);
      } else {
        $mappingpoli = [
          'kd_poli_rs' => '',
          'kd_poli_pcare' => '',
          'nm_poli_pcare' => ''
        ];
        echo $this->draw('mappingpoli.form.html', ['poliklinik' => $poliklinik, 'mappingpoli' => $mappingpoli]);
      }
      exit();
    }

    public function postMappingPoliSave()
    {
      $kd_poli_pcare = strtok($_POST['getPoli'], ':');
      $nm_poli_pcare = substr($_POST['getPoli'], strpos($_POST['getPoli'], ': ') + 1);
      if (!$this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->oneArray()) {
        $query = $this->db('maping_poliklinik_pcare')->save([
          'kd_poli_rs' => $_POST['kd_poli_rs'],
          'kd_poli_pcare' => $kd_poli_pcare,
          'nm_poli_pcare' => $nm_poli_pcare
        ]);
      } else {
        $query = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->save([
          'kd_poli_pcare' => $kd_poli_pcare,
          'nm_poli_pcare' => $nm_poli_pcare
        ]);
      }
      exit();
    }

    public function postMappingPoliHapus()
    {
      $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->delete();
      exit();
    }

    public function getMappingPoliJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/jkn_mobile_fktp/js/admin/mappingpoli.js');
        exit();
    }
    // End mappingpoli section

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

    }

}
