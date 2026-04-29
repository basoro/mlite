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
        $berkas_perawatan = $this->db('berkas_digital_perawatan')->where('no_rawat', revertNoRawat($signature['ref_id']))->where('kode', $this->settings('esignature.kode_berkasdigital'))->oneArray();
        
        if ($signature) {
             exit ($this->draw('verify.html', ['signature' => $signature, 'berkas_perawatan' => $berkas_perawatan, 'valid' => true]));
        } else {
             exit($this->draw('verify.html', ['valid' => false]));
        }
    }
}
