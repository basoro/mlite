<?php
return [
    'name'          =>  'E-Pasien',
    'description'   =>  'Modul epasien',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'code', // Icon from https://fontawesome.com/v4.7.0/icons/
    'pages'         =>  ['e-Pasien' => 'pasien'],
    'install'       =>  function () use ($core) {
    },
    'uninstall'     =>  function () use ($core) {
    }
];
