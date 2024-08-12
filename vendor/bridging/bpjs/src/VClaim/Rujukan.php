<?php

namespace Bridging\Bpjs\VClaim;

use Bridging\Bpjs\BpjsService;

class Rujukan extends BpjsService
{

    public function insertRujukan($data = [])
    {
        return $this->post('Rujukan/2.0/insert', $data);
    }
    public function updateRujukan($data = [])
    {
        return $this->put('Rujukan/2.0/Update', $data);
    }
    public function deleteRujukan($data = [])
    {
        $this->delete('Rujukan/delete', $data);
    }

    public function cariByNoRujukan($searchBy, $keyword)
    {
        if ($searchBy == 'RS') {
            $url = 'Rujukan/RS/' . $keyword;
        } else {
            $url = 'Rujukan/' . $keyword;
        }
        return $this->get($url);
    }

    public function cariByNoKartu($searchBy, $keyword, $multi = false)
    {
        $record = $multi ? 'List/' : '';
        if ($searchBy == 'RS') {
            $url = 'Rujukan/RS/' . $record . 'Peserta/' . $keyword;
        } else {
            $url = 'Rujukan/' . $record . 'Peserta/' . $keyword;
        }
        return $this->get($url);
    }

    public function cariByTglRujukan($searchBy, $keyword)
    {
        if ($searchBy == 'RS') {
            $url = 'Rujukan/RS/TglRujukan/' . $keyword;
        } else {
            $url = 'Rujukan/List/Peserta/' . $keyword;
        }
        return $this->get($url);
    }

    public function insertKhusus($data = [])
    {
        return $this->post('Rujukan/Khusus/insert', $data);
    }

    public function deleteKhusus($data = [])
    {
        return $this->delete('Rujukan/Khusus/delete', $data);
    }

    // $bulan: 1..12
    // $tahun: yyyy
    public function listKhusus($bulan, $tahun)
    {
        return $this->get('Rujukan/Khusus/List/Bulan/' . $bulan . '/Tahun/' . $tahun);
    }

    // $kodePpk: kode ppk 8 digit
    // $tanggal: tanggal rujukan yyyy-mm-dd
    public function listSpesialistik($kodePpk, $tanggal)
    {
        return $this->get('Rujukan/ListSpesialistik/PPKRujukan/' . $kodePpk . '/TglRujukan/' . $tanggal);
    }

    // $kodePpk: kode ppk 8 digit
    public function listSarana($kodePpk)
    {
        return $this->get('Rujukan/ListSarana/PPKRujukan/' . $kodePpk);
    }
}