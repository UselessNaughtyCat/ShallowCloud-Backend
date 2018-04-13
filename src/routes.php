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

$app->post('/upload', function (Request $request, Response $response) {
    $uploadedFiles = $request->getUploadedFiles();
    // print_r($uploadedFiles);
    $directory = $this->get('upload_directory');
    $uploadedFile = $uploadedFiles['musicfile'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);
        $array = [
            "Source" => "$directory"."/"."$filename" //DIRECTORY_SEPARATOR
        ];

        $response->write(json_encode($array));
    }
});

$app->post('/like', function (Request $request, Response $response) {
    $token = $request->getHeader('Authorization')[0];
    $decoded;
    try {
        $decoded = UserHandler::decodeToken($token);
    } catch (Exception $e) {
        return $response->withStatus(403);
    }
    $userId = $decoded['id'];
    $array = TableController::handleSelect("users", $userId, $token);
    $favoriteSongs = [];
    foreach ($array as $k => $v) {
        if ($k === "favoriteSongs"){
            $favoriteSongs[$k] = $v;
        }
    }
    $body = $request->getParsedBody();

    array_push($favoriteSongs["favoriteSongs"], ["id" => $body["songId"]]);
    // $favoriteSongs["favoriteSongs"][count($favoriteSongs)+1] = ["ID" => $body["songId"]];

    // print_r($favoriteSongs);

    return $response->withStatus(TableController::handleUpdate("users", $favoriteSongs, $userId, $token));
});

$app->post('/dislike', function (Request $request, Response $response) {
    $token = $request->getHeader('Authorization')[0];
    $decoded;
    try {
        $decoded = UserHandler::decodeToken($token);
    } catch (Exception $e) {
        return $response->withStatus(403);
    }
    $userId = $decoded['id'];
    $array = TableController::handleSelect("users", $userId, $token);
    $favoriteSongs = [];
    foreach ($array as $k => $v) {
        if ($k === "favoriteSongs"){
            $favoriteSongs[$k] = $v;
        }
    }
    $body = $request->getParsedBody();

    for ($i=0; $i < count($favoriteSongs["favoriteSongs"]); $i++) {
        if ($favoriteSongs["favoriteSongs"][$i]["id"] === $body["songId"]){
            unset($favoriteSongs["favoriteSongs"][$i]);
        }
    }

    // print_r($favoriteSongs);

    return $response->withStatus(TableController::handleUpdate("users", $favoriteSongs, $userId, $token));
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
    return $response->withStatus(TableController::handleInsert($className, $body, $request->getHeader('Authorization')[0]));
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


function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = pathinfo($uploadedFile->getClientFilename(),  PATHINFO_FILENAME); // bin2hex(random_bytes(8)) see http://php.net/manual/en/function.random-bytes.php

    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}