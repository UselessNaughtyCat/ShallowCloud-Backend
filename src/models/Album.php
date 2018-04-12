<?php

require_once '../src/components/TableHandler.php';

/**
* Album Table
*/
class Album extends TableHandler
{
    public $rules = [
        // "All", "Authorized", "Nobody"
        "select" => "All",
        "insert" => "Authorized",
        "update" => "Authorized",
        "delete" => "Nobody"
    ];
    protected $tableName = "album";
    protected $tableMainID = "id";
    protected $tableStrongLinks = true;
    protected $tableLinks = [
        "USER" => [ 
            TableHandler::LINKS_ENUM_STRING => ["user"],
            "user_id" => "user.id",
        ],
        "SONG" => [ 
            TableHandler::LINKS_ENUM_STRING => ["song", "song_in_album"],
            "song_in_album.song_id" => "song.id",
            "song_in_album.album_id" => "album.id",
        ],
    ];
    protected $tableFormat = [
        "ID" => "id",
        "User" => "USER.nickname",
        "Name" => "name",
        "ReleaseDate" => "release_date",
        "Songs" => [ 
            "ID" => "SONG.id", 
            // "Name" => "SONG.name",
        ],
    ];    
}