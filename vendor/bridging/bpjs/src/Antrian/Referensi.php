<?php

namespace Bridging\Bpjs\Antrian;

use Bridging\Bpjs\AntrianService;

class Referensi extends AntrianService
{

    public function dokter()
    {
        return $this->get('ref/dokter');
    }

    public function poli()
    {
        return $this->get('ref/poli');
    }
}