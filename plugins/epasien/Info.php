<?php
return [
    'name'          =>  'E-Pasien',
    'description'   =>  'Modul epasien',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'heartbeat', 
    'pages'         =>  ['e-Pasien' => 'pasien'],
    'install'       =>  function () use ($core) {
    },
    'uninstall'     =>  function () use ($core) {
    }
];
