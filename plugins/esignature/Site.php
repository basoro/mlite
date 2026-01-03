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
        $signature = $this->db('esignatures')->where('signature_hash', $hash)->oneArray();
        
        if ($signature) {
             exit ($this->draw('verify.html', ['signature' => $signature, 'valid' => true]));
        } else {
             exit($this->draw('verify.html', ['valid' => false]));
        }
    }
}
