<?php
namespace Plugins\Esignature;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('esignature/verify/(:any)', 'getVerify');
    }

    public function getVerify($hash)
    {
        $signature = $this->db('mlite_esignatures')->where('signature_hash', $hash)->oneArray();
        
        if (!$signature) {
             exit($this->draw('verify.html', ['valid' => false]));
        }

        $no_rawat = $signature['ref_id'];
        if ($signature['ref_type'] == 'resep') {
            $resep = $this->db('resep_obat')->where('no_resep', $signature['ref_id'])->oneArray();
            $no_rawat = $resep['no_rawat'] ?? $signature['ref_id'];
        } else {
            $no_rawat = revertNoRawat($signature['ref_id']);
        }

        $berkas_perawatan = $this->db('berkas_digital_perawatan')
            ->where('no_rawat', $no_rawat)
            ->where('kode', $this->settings('esignature.kode_berkasdigital'))
            ->oneArray();
        
        exit ($this->draw('verify.html', ['signature' => $signature, 'berkas_perawatan' => $berkas_perawatan, 'valid' => true]));
    }
}
