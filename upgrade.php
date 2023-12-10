<?php

if (!defined("UPGRADABLE")) {
    exit();
}

function rrmdir($dir)
{
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        if (is_dir("$dir/$file")) {
            rrmdir("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    return rmdir($dir);
}

switch ($version) {
    case '4.0.0':
        // Upgrade version
        $return = '4.0.1';
    case '4.0.1':
        // Upgrade version
        $return = "4.0.2";
}

return $return;