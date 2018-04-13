<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'debug' => true,
    ],
];
$app = new \Slim\App($config);

$container = $app->getContainer();
$container['upload_directory'] = "uploads";  //realpath(__DIR__ . '/..')."\\uploads\\";

// require '../src/RouteController.php';
// require '../public/fileTest.php';
require '../src/routes.php';

$app->run();