<?php

return [
    'name'          =>  'VClaim Request',
    'description'   =>  'Modul vclaim api untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'database',
    'install'       =>  function () use ($core) {

      if (!is_dir(UPLOADS."/qrcode")) {
          mkdir(UPLOADS."/qrcode", 0777);
      }
      if (!is_dir(UPLOADS."/qrcode/sep")) {
          mkdir(UPLOADS."/qrcode/sep", 0777);
      }

    },
    'uninstall'     =>  function() use($core)
    {
    }
];
