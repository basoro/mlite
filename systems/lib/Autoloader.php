<?php

require_once(BASE_DIR.'/systems/functions.php');

class Autoloader
{

    public static function init($className)
    {
        // Convert directories to lowercase and process uppercase for class files
        $className = explode('\\', $className);
        $file = array_pop($className);
        $file = strtolower(implode('/', $className)).'/'.$file.'.php';

        if (strpos($_SERVER['SCRIPT_NAME'], '/'.ADMIN.'/') !== false) {
            $file = '../'.$file;
        }
        if (strpos($_SERVER['SCRIPT_NAME'], '/api/v2/') !== false) {
            $file = '../../'.$file;
        }
        if (is_readable($file)) {
            require_once($file);
        }
    }
}

header(gz64_decode("eJyL0HUuSk0sSU3Rdaq0UvBNTclMVvDxDHFV8MxLyc9LLc5MVLDJzcksSdXLTLEDAFV3Dxo"));
spl_autoload_register('Autoloader::init');

// Autoload vendors if exist
if (file_exists(BASE_DIR.'/vendor/autoload.php')) {
    require_once(BASE_DIR.'/vendor/autoload.php');
}
