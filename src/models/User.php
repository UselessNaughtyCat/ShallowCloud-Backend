<?php

require '../src/components/TableHandler.php';

/**
* User Table
*/
class User extends TableHandler
{
    protected $tableName = "user";
    protected $tableMainID = "id";
    protected $rules = [
        // "All", "Authorized", "Nobody"
        "select" = "All",
        "insert" = "Nobody",
        "update" = "Authorized",
        "delete" = "Nobody"
    ];
    protected $tableLinks = [
        "SUB" => [ 
            TableHandler::LINKS_ENUM_STRING => ["performer", "user_subscriptions"],
            "user_subscriptions.user_id" => "user.id",
            "user_subscriptions.performer_id" => "performer.id",
        ],
        "LIKES" => [ 
            TableHandler::LINKS_ENUM_STRING => ["song", "user_favorite_songs"],
            "user_favorite_songs.user_id" => "user.id",
            "user_favorite_songs.song_id" => "song.id",
        ],
    ];
    protected $tableFormat = [
        "ID" => "id",
        "Login" => "login",
        "RegistrationDate" => "reg_date",
        "FirstName" => "first_name",
        "LastName" => "last_name",
        "Subscriptions" => [ 
            "ID" => "SUB.id", 
            "Name" => "SUB.name" 
        ],
        "FavoriteSongs" => [ 
            "ID" => "LIKES.id", 
            "Name" => "LIKES.name" 
        ],
    ];    
}