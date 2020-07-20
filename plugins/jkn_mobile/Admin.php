<?php

namespace Plugins\JKN_Mobile;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'index',
            'Pengaturan' => 'settings',
        ];
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul JKN Mobile';
        $this->assign['propinsi'] = $this->db('propinsi')->where('kd_prop', $this->options->get('jkn_mobile.kdprop'))->oneArray();
        $this->assign['kabupaten'] = $this->db('kabupaten')->where('kd_kab', $this->options->get('jkn_mobile.kdkab'))->oneArray();
        $this->assign['kecamatan'] = $this->db('kecamatan')->where('kd_kec', $this->options->get('jkn_mobile.kdkec'))->oneArray();
        $this->assign['kelurahan'] = $this->db('kelurahan')->where('kd_kel', $this->options->get('jkn_mobile.kdkel'))->oneArray();
        $this->assign['suku_bangsa'] = $this->db('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->db('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->db('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->db('perusahaan_pasien')->toArray();
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->options->get('jkn_mobile.display'));

        $this->assign['jkn_mobile'] = htmlspecialchars_array($this->options('jkn_mobile'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        $_POST['jkn_mobile']['display'] = implode(',', $_POST['jkn_mobile']['display']);
        foreach ($_POST['jkn_mobile'] as $key => $val) {
            $this->options('jkn_mobile', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'jkn_mobile', 'settings']));
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

    public function getAjax()
    {
        header('Content-type: text/html');
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
        	default:
          break;
        	case "propinsi":
          $propinsi = $this->db('propinsi')->toArray();
          foreach ($propinsi as $row) {
            echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nm_prop'].'">';
      			echo '<td>'.$row['kd_prop'].'</td>';
      			echo '<td>'.$row['nm_prop'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kabupaten":
          $kabupaten = $this->db('kabupaten')->toArray();
          foreach ($kabupaten as $row) {
            echo '<tr class="pilihkabupaten" data-kdkab="'.$row['kd_kab'].'" data-namakab="'.$row['nm_kab'].'">';
      			echo '<td>'.$row['kd_kab'].'</td>';
      			echo '<td>'.$row['nm_kab'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kecamatan":
          $kecamatan = $this->db('kecamatan')->toArray();
          foreach ($kecamatan as $row) {
            echo '<tr class="pilihkecamatan" data-kdkec="'.$row['kd_kec'].'" data-namakec="'.$row['nm_kec'].'">';
      			echo '<td>'.$row['kd_kec'].'</td>';
      			echo '<td>'.$row['nm_kec'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kelurahan":
          $kelurahan = $this->db('kelurahan')->toArray();
          foreach ($kelurahan as $row) {
            echo '<tr class="pilihkelurahan" data-kdkel="'.$row['kd_kel'].'" data-namakel="'.$row['nm_kel'].'">';
      			echo '<td>'.$row['kd_kel'].'</td>';
      			echo '<td>'.$row['nm_kel'].'</td>';
      			echo '</tr>';
          }
          break;
        }
        exit();
    }

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
