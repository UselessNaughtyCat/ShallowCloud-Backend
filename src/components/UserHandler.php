<?php

require_once '../src/components/CryptoLib.php';
require_once '../src/components/DB.php';

/**
* User Controller
*/
class UserHandler
{
    public static function registrate($values)
    {
        /*
        {
            "Login": "login",
            "Password": "password",
            "FirstName": "first_name",
            "LastName": "last_name"
        }
        */
        $login = $values["Login"];
        $password = $values["Password"];
        $firstName = $values["FirstName"];
        $lastName = $values["LastName"];

        $db = new DB();
        $dbConn = $db->getDBConnection();
        $result = $dbConn->query("SELECT login FROM user WHERE login = '$login'");
        $existedLogin = $result->fetchAll(PDO::FETCH_NUM)[0][0];

        if ($login !== $existedLogin) {
            $currentDate = date("Y-m-d H:i:s");
            $dbConn->query("INSERT INTO user(login, password, reg_date, first_name, last_name) VALUES ('$login','$password','$currentDate','$firstName','$lastName')");
            $result = $dbConn->query("SELECT id FROM user WHERE login='$login'");
            $userId = $result->fetchAll(PDO::FETCH_NUM)[0][0];
            $dbConn->query("INSERT INTO user_auth (user_id, salt, hash) VALUES ('$userId', null, null)");
            return true;
        } else {
            return false;
        }
    }

    public static function authenticate($values)
    {
        /*
        {
            "Login": "qwerty",
            "Password": "qwerty"
        }
        */
        $login = $values["Login"];
        $password = $values["Password"];

        $db = new DB();
        $dbConn = $db->getDBConnection();
        $result = $dbConn->query("SELECT id FROM user WHERE login='$login' AND password='$password'");
        $userId = $result->fetchAll(PDO::FETCH_NUM)[0][0];

        $token = UserHandler::generateToken($userId);
        return array("Token" => $token);
    }

    public static function isAuthenticated($login, $token) 
    {
        /*
        {
            "Login": "qwerty",
            "Token": "something",
            "Content": {}
        }
        */
        $db = new DB();
        $dbConn = $db->getDBConnection();
        $result = $dbConn->query("SELECT id FROM user WHERE login='$login'");
        $userId = $result->fetchAll(PDO::FETCH_NUM)[0][0];

        $result = $dbConn->query("SELECT hash, salt FROM user_auth WHERE user_id='$userId'");
        $array = $result->fetchAll(PDO::FETCH_NUM);
        $hash = $array[0][0];
        $salt = $array[0][1];

        return UserHandler::isTokenCorrect($hash, $token);
    }

    public static function generateToken($userId)
    {
        $db = new DB();
        $dbConn = $db->getDBConnection();

        $token = CryptoLib::randomString(16);

        // Generate a salt with the library by calling the generateSalt method
        $salt = CryptoLib::generateSalt();
        // Hash the token with the salt that was generated
        $hash = CryptoLib::hash($token, $salt);
        // Salt and hash are then stored in the database.
        $dbConn->query("UPDATE user_auth SET salt='$salt', hash='$hash' WHERE user_id='$userId'");

        return $token;
    }

    public static function isTokenCorrect($hash, $token)
    {
        // $hash and $salt are gotten later from the database, and the token is provided via a POST variable by the user
        // If isHashCorrect is true, the user has provided the correct token.
        return CryptoLib::validateHash($hash, $token);
    }
}