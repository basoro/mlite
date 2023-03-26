<?php

return [
    'name'          =>  'Dashboard',
    'description'   =>  'Akses cepat pada modul dan berita.',
    'author'        =>  'Basoro.ID',
    'version'       =>  '1.1',
    'compatibility' =>  '2023',
    'icon'          =>  'home',
    'pages'         =>  ['Main' => 'main'] ,
    'install'       =>  function () use ($core) {
    },
    'uninstall'     =>  function() use($core)
    {
    }
];
