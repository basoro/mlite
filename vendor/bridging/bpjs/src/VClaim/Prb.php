<?php

namespace Bridging\Bpjs\VClaim;

use Bridging\Bpjs\BpjsService;

class PRB extends BpjsService
{
    public function insertPrb($data = [])
    {
        return $this->post('PRB/insert', $data);
    }

    public function updatePrb($data = [])
    {
        return $this->put('PRB/Update', $data);
    }

    public function deletePrb($data = [])
    {
        return $this->delete('PRB/Delete', $data);
    }

    public function getByNoSrb($noSrb, $noSep)
    {
        return $this->get('prb/' . $noSrb . '/sep/' . $noSep);
    }

    // $tglAwal, $tglAkhir yyyy-mm-dd
    public function getByTanggal($tglAwal, $tglAkhir)
    {
        return $this->get('prb/tglMulai/' . $tglAwal . '/tglAkhir/' . $tglAkhir);
    }
}