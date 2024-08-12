<?php

namespace Bridging\Bpjs\VClaim;

use Bridging\Bpjs\BpjsService;

class LembarPengajuanKlaim extends BpjsService
{
    public function insertLPK($data = [])
    {
        return $this->post('LPK/insert', $data);
    }
    public function updateLPK($data = [])
    {
        return $this->put('LPK/update', $data);
    }
    public function deleteLPK($data = [])
    {
        return $this->delete('LPK/delete', $data);
    }

    public function cariLPK($tglMasuk, $jnsPelayanan)
    {
        return $this->get('LPK/TglMasuk/' . $tglMasuk . '/JnsPelayanan/' . $jnsPelayanan);
    }
}