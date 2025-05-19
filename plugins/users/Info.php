<?php

return [
    'name'          =>  'Pengguna',
    'description'   =>  'Pengelolaan pengguna',
    'author'        =>  'Basoro.ID',
    'category'      =>  'main', 
    'version'       =>  '1.1',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'user',
    'pages'         =>  ['Login' => 'login'],

    'install'       =>  function () use ($core) {
        if (!is_dir(UPLOADS."/users")) {
            mkdir(UPLOADS."/users", 0777);
        }
    },
    'uninstall'     =>  function () use ($core) {
    }
];
