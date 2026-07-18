<?php

    return [
        'name'          =>  'MODULE_NAME',
        'description'   =>  'Modul MODULE_DESCRIPTION untuk mLITE',
        'author'        =>  'Basoro',
        'category'      =>  'MODULE_CATEGORY', 
        'version'       =>  '1.0',
        'compatibility' =>  '6.*.*',
        'icon'          =>  'MODULE_ICON',
        'install'       =>  function () use ($core) {
        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
