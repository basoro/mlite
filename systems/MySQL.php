<?php

namespace Systems;

use Systems\Lib\QueryWrapper;

class MySQL extends QueryWrapper
{
    protected static $db;
}

MySQL::connect("mysql:host=".DBHOST.";port=".DBPORT.";dbname=".DBNAME."", DBUSER, DBPASS);
