<?php

namespace Plugins\Icd;

use Systems\Lib\QueryWrapper;

class DB_ICD extends QueryWrapper
{
    /**
     * @var \PDO
     */
    protected static $db_icd;
}

$database = BASE_DIR.'/systems/data/icd.sdb';
DB_ICD::connect("sqlite:{$database}");
