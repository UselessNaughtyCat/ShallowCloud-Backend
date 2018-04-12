<?php

require_once '../src/components/DB.php';

use \Firebase\JWT\JWT;

/**
* User Controller
*/
class UserHandler
{
    private const KEY = "kek";

    public static function registrate($values)
    {
        /*
        {
            "E-mail": "login",
            "Password": "password",
            "FirstName": "first_name",
            "LastName": "last_name"
        }
        */
        $login = $values["E-mail"];
        $password = $values["Password"];
        $firstName = $values["FirstName"];
        $lastName = $values["LastName"];

        $db = new DB();
        $dbConn = $db->getDBConnection();
        $result = $dbConn->query("SELECT email FROM user WHERE email = '$login'");
        $existedLogin = $result->fetchAll(PDO::FETCH_NUM)[0][0];

        if ($login !== $existedLogin) {
            $currentDate = date("Y-m-d H:i:s");
            $dbConn->query("INSERT INTO user(email, password, reg_date, first_name, last_name) VALUES ('$login','$password','$currentDate','$firstName','$lastName')");
            return true;
        } else {
            return false;
        }
    }

    public static function authenticate($values)
    {
        /*
        {
            "E-mail": "login",
            "Password": "qwerty"
        }
        */
        $login = $values["E-mail"];
        $password = $values["Password"];

        $db = new DB();
        $dbConn = $db->getDBConnection();
        $result = $dbConn->query("SELECT id FROM user WHERE email='$login' AND password='$password'");
        $userId = $result->fetchAll(PDO::FETCH_NUM)[0][0];

        if ($userId !== "" || $userId !== NULL){
            $token = UserHandler::generateToken($userId);
            return array("Token" => $token);
        } else {
            return false;
        }
    }

    public static function isTokenValid($token) 
    {
        /*
        {
            "Login": "qwerty",
            "Token": "something",
            "Content": {}
        }
        */
        try {
            $decoded = JWT::decode($token, UserHandler::KEY, array('HS256'));
            // return (array) $decoded;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function generateToken($userId)
    {
        $key = "kek";
        $token = array(
            "user_id" => "$userId"
        );
        return JWT::encode($token, UserHandler::KEY);
    }
}