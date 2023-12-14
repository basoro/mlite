<?php

return [
    'name'          =>  'Pengguna',
    'description'   =>  'Pengelolaan pengguna',
    'author'        =>  'Basoro.ID',
    'version'       =>  '1.1',
    'compatibility' =>  '4.0.*',
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
