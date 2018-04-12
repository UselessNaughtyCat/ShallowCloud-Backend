<?php

require_once '../src/components/UserHandler.php';
require_once '../src/components/HTTPStatus.php';

use Slim\Http\UploadedFile;

/**
* Table Controller
*/
class TableController
{
    public static function handleSelect($className, $id, $token)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;
        if ($id !== "ALL" && !is_numeric($id)) 
            return HTTPStatus::BAD_REQUEST;

        require $path;

        $currentTable = new $className();
        if (TableController::isAvalaible($currentTable->rules["select"], $token)){
            if (is_numeric($id)) {
                return $currentTable->select($id);
            } elseif ($id === "ALL") {
                return $currentTable->selectAll();
            }
        }
        else
            return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleInsert($className, $array, $token, $uploadedFile, $directory)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require $path; 

        $currentTable = new $className();
        if (TableController::isAvalaible($currentTable->rules["insert"], $token)){
            if ($className === "Song"){
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = moveUploadedFile($directory, $uploadedFile);
                    // $response->write('uploaded ' . $filename . '<br/>');
                    $array["Source"] = "$directory.$filename";
                }
            }
            $currentTable->insert($array);
            return HTTPStatus::OK;
        }
        else
            return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleUpdate($className, $array, $id, $token)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require $path; 

        $currentTable = new $className();
        if (TableController::isAvalaible($currentTable->rules["update"], $token)){
            $currentTable->update($id, $array);
            return HTTPStatus::OK;
        }
        else
            return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleDelete($className, $id, $array)
    {
        return HTTPStatus::NOT_IMPLEMENTED;
    }

    private static function isAvalaible($value, $token)
    {
        switch ($value) {
            case 'All':
                return true;
                break;
            
            case 'Authorized':
                return UserHandler::isTokenValid($token);
                break;
            
            case 'Nobody':
                return false;
                break;
        }
    }

    public static function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }
}