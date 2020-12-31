<?php
namespace Plugins\Dokter;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Rawat Jalan'   => 'ralan',
            'Rawat Inap'   => 'ranap',
        ];
    }

    public function getRalan()
    {
      return $this->draw('ralan.html');
    }

}
