<?php

/**
* Data Base Connection
*/
class DB
{
    private $dbConn;
    private $dbHost = 'localhost';
    private $dbName = 'music';
    private $dbUser = 'root';
    private $dbPass = '';

    function __construct()
    {
        $mysql_connect_str = "mysql:host=$this->dbHost;dbname=$this->dbName";
        $dbConnection = new PDO($mysql_connect_str, $this->dbUser, $this->dbPass);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dbConn = $dbConnection;
    }

    public function getDBConnection()
    {
        return $this->dbConn;
    }
}