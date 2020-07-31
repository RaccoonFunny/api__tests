<?php



use Application\Controllers\Controller;

session_start();

require 'vendor/autoload.php';
require "vendor/amocrm/amocrm-api-library/examples/error_printer.php";

spl_autoload_register(function ($className) {
    include __DIR__ . "/" . str_replace('\\', '/', $className) . '.php';
});


$controller = new Controller();
$controller->auth();
$controller->testApi();
