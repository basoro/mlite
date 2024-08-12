<?php

namespace Bridging\Bpjs\VClaim;

use Bridging\Bpjs\BpjsService;

class Sep extends BpjsService
{

    public function insertSEP($data = [])
    {
        return $this->post('SEP/2.0/insert', $data);
    }

    public function updateSEP($data = [])
    {
        return $this->put('SEP/2.0/update', $data);
    }

    public function deleteSEP($data = [])
    {
        return $this->delete('SEP/2.0/delete', $data);
    }

    public function cariSEP($keyword)
    {
        return $this->get('SEP/' . $keyword);
    }

    public function suplesiJasaRaharja($noKartu, $tglPelayanan)
    {
        return $this->get('sep/JasaRaharja/Suplesi/' . $noKartu . '/tglPelayanan/' . $tglPelayanan);
    }

    public function listDataKll($noKartu)
    {
        return $this->get('sep/KllInduk/List/' . $noKartu);
    }

    public function pengajuanPenjaminanSep($data = [])
    {
        return $this->post('Sep/pengajuanSEP', $data);
    }

    public function approvalPenjaminanSep($data = [])
    {
        return $this->post('Sep/aprovalSEP', $data);
    }

    public function updateTglPlg($data = [])
    {
        return $this->put('SEP/2.0/updtglplg', $data);
    }

    public function inacbgSEP($keyword)
    {
        return $this->get('sep/cbg/' . $keyword);
    }

    public function getSepInternal($noSep)
    {
        return $this->get('SEP/Internal/' . $noSep);
    }

    public function deleteSepInternal($data = [])
    {
        return $this->delete('SEP/Internal/delete', $data);
    }

    // $tanggal: yyyy-mm-dd
    public function getFpByNoKartu($noKartu, $tanggal)
    {
        return $this->get('SEP/FingerPrint/Peserta/' . $noKartu . '/TglPelayanan/' . $tanggal);
    }

    // $tanggal: yyyy-mm-dd
    public function listFpByTanggal($tanggal)
    {
        return $this->get('SEP/FingerPrint/List/Peserta/TglPelayanan/' . $tanggal);
    }
}