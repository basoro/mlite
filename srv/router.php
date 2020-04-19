<?php

require(dirname(__FILE__) . "/router.class.php");

/**
 * Enable errors
 */
$_SERVER["CI_ENV"] = "development";
error_reporting(E_ALL);

$php_web_server = new PHP_Webserver_Router();

/************************************************
 * Uncomment to Disable http output in console:
 ***********************************************/
//$php_web_server->log_enable = FALSE;

/************************************************
 * Change this if your "index.php" has another name.
 ***********************************************/
//$php_web_server->indexPath = "my_new_index_file.php";

/************************************************
 * Listen for requests
 ***********************************************/
return $php_web_server->listen();

