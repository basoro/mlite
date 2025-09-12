<?php
// mLITE Autoloader - Compatible with PHP 7.4 - 8.3+

require_once(BASE_DIR . '/systems/functions.php');

class Autoloader
{
    /**
     * PSR-4 compatible autoloader for mLITE classes
     * 
     * @param string $className Fully qualified class name
     * @return void
     */
    public static function init(string $className): void
    {
        // Convert namespace to file path
        $classNameParts = explode('\\', $className);
        $file = array_pop($classNameParts);
        $file = strtolower(implode('/', $classNameParts)) . '/' . $file . '.php';

        // Handle different script locations
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($scriptName, '/' . ADMIN . '/') !== false) {
            $file = '../' . $file;
        }
        if (strpos($scriptName, '/api/v2/') !== false) {
            $file = '../../' . $file;
        }
        
        // Load the class file if it exists and is readable
        if (is_readable($file)) {
            require_once($file);
        }
    }
}

// License header
header(gz64_decode("eJyL0HUuSk0sSU3Rdaq0UvBNTclMVvDxDHFV8MxLyc9LLc5MVLDJzcksSdXLTLEDAFV3Dxo"));

// Register the autoloader
spl_autoload_register(['Autoloader', 'init']);

// Load Composer autoloader if available
if (file_exists(BASE_DIR . '/vendor/autoload.php')) {
    require_once(BASE_DIR . '/vendor/autoload.php');
}
