<?php
/**
 * Entry point for the FlightPHP project, bundled with Swagger OpenAPI documentation generator.
 */

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/docs/swagger.php";

//ini_set('error_reporting', E_ALL); 
ini_set('display_errors', 0);

/**
 * Required files, modules & libraries.
*/

require_once __DIR__."/config/Config.php";

foreach (glob(__DIR__."/app/utils/*.php") as $util) {
    require_once $util;
}

foreach (glob(__DIR__."/app/dao/*.php") as $dao) {
    require_once $dao;
}

foreach (glob(__DIR__."/app/routes/*.php") as $route) {
    require_once $route;
}

foreach (glob(__DIR__."/app/models/*.php") as $model) {
    require_once $model;
}

/**
 * Start the Flight framework.
 */
Flight::start();
