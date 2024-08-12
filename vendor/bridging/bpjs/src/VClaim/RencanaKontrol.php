<?php

namespace Bridging\Bpjs\VClaim;

use Bridging\Bpjs\BpjsService;

class RencanaKontrol extends BpjsService
{
    public function insertRencanaKontrol($data = [])
    {
        return $this->post('RencanaKontrol/insert', $data);
    }

    public function updateRencanaKontrol($data = [])
    {
        return $this->put('RencanaKontrol/Update', $data);
    }

    public function deleteRencanaKontrol($data = [])
    {
        return $this->delete('RencanaKontrol/Delete', $data);
    }

    public function insertSpri($data = [])
    {
        return $this->post('RencanaKontrol/InsertSPRI', $data);
    }

    public function updateSpri($data = [])
    {
        return $this->put('RencanaKontrol/UpdateSPRI', $data);
    }

    public function getBySep($noSep)
    {
        return $this->get('RencanaKontrol/nosep/' . $noSep);
    }

    public function getByNoSurat($noSurat)
    {
        return $this->get('RencanaKontrol/noSuratKontrol/' . $noSurat);
    }

    // $tglAwal, $tglAkhir yyyy-mm-dd
    // $filter: [1|2] tanggal entri|tanggal rencana kontrol
    public function getByTanggal($tglAwal, $tglAkhir, $filter)
    {
        return $this->get('RencanaKontrol/ListRencanaKontrol/tglAwal/' . $tglAwal . '/tglAkhir/' . $tglAkhir . '/filter/' . $filter);
    }

    // $jenis: [1:SPRI|2:rencana kontrol]
    // $nomor: jika 1, diisi nomor kartu, jika 2, diisi no. SEP
    // $tanggal: tanggal rencana kontrol yyyy-mm-dd
    public function listSpesialistik($jenis, $nomor, $tanggal)
    {
        return $this->get('RencanaKontrol/ListSpesialistik/JnsKontrol/' . $jenis . '/nomor/' . $nomor . '/TglRencanaKontrol/' . $tanggal);
    }

    // $jenis: [1:SPRI|2:rencana kontrol]
    // $tanggal: tanggal rencana kontrol yyyy-mm-dd
    public function jadwalDokter($jenis, $kodePoli, $tanggal)
    {
        return $this->get('RencanaKontrol/JadwalPraktekDokter/JnsKontrol/' . $jenis . '/KdPoli/' . $kodePoli . '/TglRencanaKontrol/' . $tanggal);
    }
}