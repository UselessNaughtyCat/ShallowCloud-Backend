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

// require '../src/RouteController.php';
require '../src/routes.php';

$app->run();