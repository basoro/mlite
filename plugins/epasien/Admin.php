<?php

namespace Plugins\Epasien;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Index' => 'index',
        ];
    }

    public function getIndex()
    {
        $text = 'Modul e-Pasien';
        return $this->draw('index.html', ['text' => $text]);
    }
}
