<?php

require_once '../src/components/UserHandler.php';
require_once '../src/components/HTTPStatus.php';

/**
* User Controller
*/
class UserController
{
    public static function registration($values)
    {
        $success = UserHandler::registrate($values);
        return $success ? HTTPStatus::OK : HTTPStatus::FORBIDDEN;
    }

    public static function login($values)
    {
        return UserHandler::authenticate($values);
    }
}