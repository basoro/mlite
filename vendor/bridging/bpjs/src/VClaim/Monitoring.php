<?php

namespace Bridging\Bpjs\VClaim;

use Bridging\Bpjs\BpjsService;

class Monitoring extends BpjsService
{

    public function dataKunjungan($tglSep, $jnsPelayanan)
    {
        return $this->get('Monitoring/Kunjungan/Tanggal/' . $tglSep . '/JnsPelayanan/' . $jnsPelayanan);
    }

    public function dataKlaim($tglPulang, $jnsPelayanan, $statusKlaim)
    {
        return $this->get('Monitoring/Klaim/Tanggal/' . $tglPulang . '/JnsPelayanan/' . $jnsPelayanan . '/Status/' . $statusKlaim);
    }
    public function historyPelayanan($noKartu, $tglAwal, $tglAkhir)
    {
        return $this->get('monitoring/HistoriPelayanan/NoKartu/' . $noKartu . '/tglMulai/' . $tglAwal . '/tglAkhir/' . $tglAkhir);
    }
    public function dataKlaimJasaRaharja($jnsPelayanan, $tglMulai, $tglAkhir)
    {
        return $this->get('monitoring/JasaRaharja/JnsPelayanan/'  . $jnsPelayanan . '/tglMulai/' . $tglMulai . '/tglAkhir/' . $tglAkhir);
    }
}