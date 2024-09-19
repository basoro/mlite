<?php
namespace Plugins\Bridging_VClaim_BPJS;

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
        $disabled_menu = $this->core->loadDisabledMenu('bridging_vclaim_bpjs'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function anyPeserta($type='', $param='')
    {
      $this->_addHeaderFiles();
      if($type == 'peserta') {
        $_POST['noKartu'] = '0000976392055';
        $noka = $_POST['noKartu'];
        if($param) {
          $noka = $param;
        }
        $peserta = new \Bridging\Bpjs\VClaim\Peserta($this->core->vclaim);
        $array = $peserta->getByNoKartu($noka,date('Y-m-d'));
        echo json_encode($array, JSON_PRETTY_PRINT);
        exit();
      } else {
        return $this->draw('peserta.html');
      }
    }

    public function getCariSep($nosep='')
    {
      $peserta = new \Bridging\Bpjs\VClaim\Sep($this->core->vclaim);
      $array = $peserta->cariSEP($nosep);
      echo json_encode($array, JSON_PRETTY_PRINT);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/bridging_vclaim_bpjs/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/bridging_vclaim_bpjs/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('bridging_vclaim_bpjs')]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addJS(url('assets/js/prism.js'), 'footer');

        $this->core->addCSS(url([ 'bridging_vclaim_bpjs', 'css']));
        $this->core->addJS(url([ 'bridging_vclaim_bpjs', 'javascript']), 'footer');
    }

}
