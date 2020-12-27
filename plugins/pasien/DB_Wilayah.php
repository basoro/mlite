<?php

namespace Plugins\Pasien;

use Systems\Lib\QueryBuilder;

class DB_Wilayah extends QueryBuilder
{
    protected static $db_wilayah;
}

$database = BASE_DIR.'/systems/data/wilayah.sdb';
DB_Wilayah::connect("sqlite:{$database}");
