<?php

require '../src/components/TableHandler.php';

/**
* Song Table
*/
class Performer extends TableHandler
{
    protected $tableName = "performer";
    protected $tableMainID = "id";
    protected $rules = [
        // "All", "Authorized", "Nobody"
        "select" = "All",
        "insert" = "Authorized",
        "update" = "Authorized",
        "delete" = "Nobody"
    ];
    protected $tableLinks = [
        "USER" => [ 
            TableHandler::LINKS_ENUM_STRING => ["user"],
            "user_id" => "user.id",
        ],
    ];
    protected $tableFormat = [
        "ID" => "id",
        "UserID" => "USER.id", 
        "Name" => "name",
        "FullName" => [ 
            "FirstName" => "USER.first_name", 
            "LastName" => "USER.last_name" 
        ],
    ];
}