<?php
header("Access-Control-Allow-Origin: *");

require_once (__DIR__."/../config/Config.php");

/*ini_set('error_reporting', E_ALL);*/
ini_set('display_errors', 0);

/**
 * Swagger JSON specification generator.
 * Add the folders containing the specification annotations to the \OpenApi\scan array.
 * By default, routes/ and models/ folders are included, as well as the doc_setup.php setup file.
*/

if (array_key_exists('send', $_GET)) {
    $arr = explode("/", $_GET['send']);
    if ($arr[0] == 'swagger.json') {
        define('SERVER_ROOT', realpath(dirname(__FILE__)));    
        require_once __DIR__ . "/../vendor/autoload.php";
        $openapi = \OpenApi\scan(DOCS_ANNOTATION_LOCATIONS);
        $openapi->servers[0]->url = API_BASE_PATH;
    
        header('Content-Type: application/json');
        echo $openapi->toJson();
    }           
}