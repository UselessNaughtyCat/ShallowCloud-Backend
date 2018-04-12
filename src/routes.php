<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

require_once '../src/controllers/UserController.php';
require_once '../src/components/UserHandler.php';
require_once '../src/controllers/TableController.php';

$app->post('/registration', function (Request $request, Response $response) {
    $body      = $request->getParsedBody();
    return $response->withStatus(UserController::registration($body));
});

$app->post('/login', function (Request $request, Response $response) {
    $body      = $request->getParsedBody();
    $object = UserController::login($body);
    return !is_numeric($object) ? $response->withJson($object) : $response->withStatus($object);
});

$app->get('/{className}[/[{id}]]', function (Request $request, Response $response) {
    $className = $request->getAttribute('className');
    $id        = $request->getAttribute('id') ? $request->getAttribute('id') : "ALL";
    $object = TableController::handleSelect($className, $id, $request->getHeader('Authorization')[0]);
    return !is_numeric($object) ? $response->withJson($object) : $response->withStatus($object);
});

$app->post('/{className}/add', function (Request $request, Response $response) {
    $className = $request->getAttribute('className');
    $body      = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    $directory = $this->get('upload_directory');
    $uploadedFile = $uploadedFiles['musicfile'];
    return $response->withStatus(TableController::handleInsert($className, $body, $request->getHeader('Authorization')[0]), $uploadedFile, $directory);
});

$app->put('/{className}/update/{id}', function (Request $request, Response $response) {
    $className = $request->getAttribute('className');
    $body      = $request->getParsedBody();
    $id        = $request->getAttribute('id');
    return $response->withStatus(TableController::handleUpdate($className, $body, $id, $request->getHeader('Authorization')[0]));
});

$app->delete('/{className}/delete/{id}', function (Request $request, Response $response) {
    $className = $request->getAttribute('className');
    $id        = $request->getAttribute('id');
    return $response->withStatus(TableController::handleDelete($className, $id));
});