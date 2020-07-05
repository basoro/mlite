<?php
return [
    'name'          =>  'JKN Mobile',
    'description'   =>  'Modul Khanza JKN Mobile API',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'code',                                 // Icon from http://fontawesome.io/icons/

    // Registering page for possible use as a homepage
    'pages'            =>  ['JKN Mobile' => 'jknmobile'],

    'install'       =>  function () use ($core) {
    },
    'uninstall'     =>  function () use ($core) {
    }
];
