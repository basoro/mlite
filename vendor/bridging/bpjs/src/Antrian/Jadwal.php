<?php

namespace Bridging\Bpjs\Antrian;

use Bridging\Bpjs\AntrianService;

class Jadwal extends AntrianService
{
    /**
        Parameter1 : {diisi kode poli BPJS}=> ANA
        Parameter2 : {diisi tanggal}=> 2021-08-07
        Respon : Perlu dilakukan dekripsi disisi client
     */
    public function jadwaldokter($kdpoli, $tanggal)
    {
        return $this->get('jadwaldokter/kodepoli/' . $kdpoli . '/tanggal/' . $tanggal);
    }

    /**
     * JSON Data Request
      {
        "kodepoli": "ANA",
        "kodesubspesialis": "ANA",
        "kodedokter": 12346,
        "jadwal": [
            {
                "hari": "1",
                "buka": "08:00",
                "tutup": "10:00"
            },
            {
                "hari": "2",
                "buka": "15:00",
                "tutup": "17:00"
            }
        ]
    }
     */
    public function updatejadwal($data = [])
    {
        return $this->get('jadwaldokter/updatejadwaldokter/', $data);
    }
}