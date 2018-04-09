<?php

/**
* Table Controller
*/
class TableController
{
    public static function handleSelect($className, $id)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;
        if ($id !== "ALL" && !is_numeric($id)) 
            return HTTPStatus::BAD_REQUEST;

        require $path; 

        $currentTable = new $className();
        if (is_numeric($id)) {
            return $currentTable->select($id);
        } elseif ($id === "ALL") {
            return $currentTable->selectAll();
        }
    }

    public static function handleInsert($className, $array)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require $path; 

        $currentTable = new $className();
        $currentTable->insert($array);

        return HTTPStatus::OK;
    }

    public static function handleUpdate($className, $array, $id)
    {
        $className = ucfirst(substr($className, 0, -1));
        $path = '../src/models/'.$className.'.php';

        if (!file_exists($path)) 
            return HTTPStatus::NOT_FOUND;

        require $path; 

        $currentTable = new $className();
        $currentTable->update($id, $array);

        return HTTPStatus::OK;
    }

    public static function handleDelete($className, $id)
    {
        return HTTPStatus::NOT_IMPLEMENTED;
    }
}

class HTTPStatus
{
    const OK = 200;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const NOT_IMPLEMENTED = 501;
}