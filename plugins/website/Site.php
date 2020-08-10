<?php

namespace Plugins\Website;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('website', 'getIndex');
    }

    public function getIndex()
    {
        $setting['nama_instansi'] = $this->core->getSettings('nama_instansi');
        $setting['alamat_instansi'] = $this->core->getSettings('alamat_instansi');
        $setting['kabupaten'] = $this->core->getSettings('kabupaten');
        $setting['propinsi'] = $this->core->getSettings('propinsi');
        $setting['kontak'] = $this->core->getSettings('kontak');
        $setting['email'] = $this->core->getSettings('email');
        $setting['email'] = $this->core->getSettings('email');
        $poliklinik = $this->db('poliklinik')->where('status', '1')->toArray();
        $website = $this->options('website');
        $page = [
            'content' => $this->draw('index.html', ['setting' => $setting, 'poliklinik' => $poliklinik, 'website' => $website])
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }
}
