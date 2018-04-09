<?php

require_once '../src/components/UserHandler.php';
require_once '../src/components/HTTPStatus.php';

/**
* Table Controller
*/
class TableController
{
    public static function handleSelect($className, $id) //, $array
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;
        if ($id !== "ALL" && !is_numeric($id)) 
            return HTTPStatus::BAD_REQUEST;

        require $path; 

        $currentTable = new $className();
        // $accessed = true;
        // if ($currentTable->rules["select"] != "All"){
        //     $accessed = UserHandler::isAuthenticated($array["Login"], $array["Token"]);
        // }
        // if ($accessed){
            if (is_numeric($id)) {
                return $currentTable->select($id);
            } elseif ($id === "ALL") {
                return $currentTable->selectAll();
            }
        // }
        // else
        //     return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleInsert($className, $array)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require $path; 

        $currentTable = new $className();
        $accessed = true;
        if ($currentTable->rules["insert"] != "All"){
            $accessed = UserHandler::isAuthenticated($array["Login"], $array["Token"]);
        }
        if ($accessed){
            $currentTable->insert($array["Content"]);
            return HTTPStatus::OK;
        }
        else
            return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleUpdate($className, $array, $id)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require $path; 

        $currentTable = new $className();
        $accessed = true;
        if ($currentTable->rules["update"] != "All"){
            // echo "\nkek\n";
            $accessed = UserHandler::isAuthenticated($array["Login"], $array["Token"]);
        }
        if ($accessed){
            $currentTable->update($id, $array["Content"]);
            return HTTPStatus::OK;
        }
        else
            return HTTPStatus::UNAUTHORIZED;
    }

    public static function handleDelete($className, $id, $array)
    {
        return HTTPStatus::NOT_IMPLEMENTED;
    }
}