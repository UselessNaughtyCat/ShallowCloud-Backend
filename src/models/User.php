<?php

require_once '../src/components/TableHandler.php';

/**
* User Table
*/
class User extends TableHandler
{
    public $rules = [
        // "All", "Authorized", "Nobody"
        "select" => "All",
        "insert" => "Nobody",
        "update" => "Authorized",
        "delete" => "Nobody"
    ];
    protected $tableName = "user";
    protected $tableMainID = "id";
    protected $selfLink = "SUB";
    protected $tableLinks = [
        "SUB" => [ 
            TableHandler::LINKS_ENUM_STRING => ["user", "user_subscriptions"],
            "user_subscriptions.user_id" => "user.id",
            "user_subscriptions.performer_id" => "user.id",
        ],
        "LIKES" => [ 
            TableHandler::LINKS_ENUM_STRING => ["song", "user_favorite_songs"],
            "user_favorite_songs.user_id" => "user.id",
            "user_favorite_songs.song_id" => "song.id",
        ],
    ];
    protected $tableFormat = [
        "id" => "id",
        "e-mail" => "email",
        // "RegistrationDate" => "reg_date",
        "nickname" => "nickname",
        "subscriptions" => [ 
            "id" => "SUB.id", 
            // "Name" => "SUB.name" 
        ],
        "favoriteSongs" => [ 
            "id" => "LIKES.id", 
            // "Name" => "LIKES.name" 
        ],
    ];    
}