<?php

namespace Plugins\Icd;

use Systems\Lib\QueryBuilder;

class DB_ICD extends QueryBuilder
{
    /**
     * @var \PDO
     */
    protected static $db_icd;
}

$database = BASE_DIR.'/systems/data/icd.sdb';
DB_ICD::connect("sqlite:{$database}");
