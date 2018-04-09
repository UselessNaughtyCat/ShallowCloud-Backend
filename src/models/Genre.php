<?php

require_once '../src/components/TableHandler.php';

/**
* Genre Table
*/
class Genre extends TableHandler
{
    public $rules = [
        // "All", "Authorized", "Nobody"
        "select" => "All",
        "insert" => "Nobody",
        "update" => "Nobody",
        "delete" => "Nobody"
    ];
    protected $tableName = "genre";
    protected $tableMainID = "id";
    protected $tableFormat = [
        "ID" => "id",
        "Name" => "name",
    ];
}