<?php

namespace Plugins\Pasien;

use Systems\Lib\QueryWrapper;

class DB_Wilayah extends QueryWrapper
{
    protected static $db_wilayah;
}

$database = BASE_DIR.'/systems/data/wilayah.sdb';
DB_Wilayah::connect("sqlite:{$database}");
