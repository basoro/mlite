<?php

namespace Plugins\Surat;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function init()
    {
        if (isset($_GET['verifikasiSurat'])) {
            $token = trim((string) $_GET['verifikasiSurat']);
            $verification = \Systems\Lib\SuratVerification::resolveVerification($this->core, $token);

            $this->setTemplate(false);
            echo $this->draw('verifikasi.html', [
                'verification' => $this->tpl->noParse_array(htmlspecialchars_array($verification)),
            ]);
            exit();
        }

        if (!isset($_GET['verifikasiPermintaan'])) {
            return;
        }

        $token = trim((string) $_GET['verifikasiPermintaan']);
        $verification = \Systems\Lib\SuratVerification::resolveRequestVerification($this->core, $token);

        $this->setTemplate(false);
        echo $this->draw('verifikasi.html', [
            'verification' => $this->tpl->noParse_array(htmlspecialchars_array($verification)),
        ]);
        exit();
    }
}
