<?php

namespace Plugins\Dashboard;

use Systems\SiteModule;

class Site extends SiteModule
{


    public function routes()
    {
        // Simple:
        $this->route('main', 'getIndex');
    }

    public function getIndex()
    {

      //$this->route('login', function () {
        $assign = [];
        $assign['notify']   = $this->core->getNotify();
        $assign['tanggal']       = getDayIndonesia(date('Y-m-d')).', '.dateIndonesia(date('Y-m-d'));
        $assign['powered']  = 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>';
        $assign['version']       = $this->settings->get('settings.version');
        $assign['nama_instansi']         = $this->settings->get('settings.nama_instansi');
        $assign['logo']         = $this->settings->get('settings.logo');
        $assign['theme_admin'] = $this->settings->get('settings.theme_admin');
        echo $this->draw('main.html', ['mlite' => $assign]);
        exit();
      //});
    }

}
