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

        require_once $path;

        $currentTable = new $className();
        if (TableController::isAvalaible($currentTable->rules["select"], $token)){
            if (is_numeric($id)) {
                return $currentTable->select($id);
            } elseif ($id === "ALL") {
                return $currentTable->selectAll();
            }
        }
        elseif ($isAvalaible === -1)
            return HTTPStatus::FORBIDDEN;
        else
            return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleInsert($className, $array, $token)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require_once $path; 

        $currentTable = new $className();
        $isAvalaible = TableController::isAvalaible($currentTable->rules["insert"], $token);
        if ($isAvalaible === true){
            $currentTable->insert($array);
            return HTTPStatus::OK;
        }
        elseif ($isAvalaible === -1)
            return HTTPStatus::FORBIDDEN;
        else
            return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleUpdate($className, $array, $id, $token)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require_once $path; 

        $currentTable = new $className();
        $isAvalaible = TableController::isAvalaible($currentTable->rules["update"], $token);
        if ($isAvalaible === true){
            $currentTable->update($id, $array);
            return HTTPStatus::OK;
        }
        elseif ($isAvalaible === -1)
            return HTTPStatus::FORBIDDEN;
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
                return -1;
                break;
        }
    }
}