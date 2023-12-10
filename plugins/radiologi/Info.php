<?php

return [
    'name'          =>  'Radiologi',
    'description'   =>  'Modul Radiologi untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'film',
    'install'       =>  function () use ($core) {
      if (!is_dir(UPLOADS."/radiologi")) {
          mkdir(UPLOADS."/radiologi", 0777);
      }
    },
    'uninstall'     =>  function() use($core)
    {
    }
];
