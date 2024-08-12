<?php

namespace Bridging\Bpjs\Aplicare;

use Bridging\Bpjs\BpjsService;

class KetersediaanKamar extends BpjsService
{

    public function refKelas()
    {
        $response = $this->get('ref/kelas');
        return json_decode($response, true);
    }
    public function bedGet($kodePpk, $start, $limit)
    {
        $response = $this->get('bed/read/' . $kodePpk . '/' . $start . '/' . $limit);
        return json_decode($response, true);
    }
    public function bedCreate($kodePpk, $data = [])
    {
        $header = 'application/json';
        $response = $this->post('bed/create/' . $kodePpk, $data, $header);
        return json_decode($response, true);
    }
    public function bedUpdate($kodePpk, $data = [])
    {
        $response = $this->put('bed/update/' . $kodePpk, $data);
        return json_decode($response, true);
    }
    public function bedDelete($kodePpk, $data = [])
    {
        $response = $this->delete('bed/delete/' . $kodePpk, $data);
        return json_decode($response, true);
    }
}