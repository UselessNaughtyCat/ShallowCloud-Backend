<?php

require '../src/components/TableHandler.php';

/**
* Genre Table
*/
class Genre extends TableHandler
{
    protected $tableName = "genre";
    protected $tableMainID = "id";
    protected $rules = [
        // "All", "Authorized", "Nobody"
        "select" = "All",
        "insert" = "Nobody",
        "update" = "Nobody",
        "delete" = "Nobody"
    ];
    protected $tableFormat = [
        "ID" => "id",
        "Name" => "name",
    ];
}