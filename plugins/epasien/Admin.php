<?php

namespace Plugins\Epasien;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
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
        $this->assign['title'] = 'Pengaturan Modul e-Pasien';
        $this->assign['propinsi'] = $this->db('propinsi')->where('kd_prop', $this->options->get('epasien.kdprop'))->oneArray();
        $this->assign['kabupaten'] = $this->db('kabupaten')->where('kd_kab', $this->options->get('epasien.kdkab'))->oneArray();
        $this->assign['kecamatan'] = $this->db('kecamatan')->where('kd_kec', $this->options->get('epasien.kdkec'))->oneArray();
        $this->assign['kelurahan'] = $this->db('kelurahan')->where('kd_kel', $this->options->get('epasien.kdkel'))->oneArray();
        $this->assign['suku_bangsa'] = $this->db('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->db('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->db('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->db('perusahaan_pasien')->toArray();

        $this->assign['epasien'] = htmlspecialchars_array($this->options('epasien'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['epasien'] as $key => $val) {
            $this->options('epasien', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'epasien', 'settings']));
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
