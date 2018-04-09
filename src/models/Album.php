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
        "PERFORMER" => [ 
            TableHandler::LINKS_ENUM_STRING => ["performer"],
            "performer_id" => "performer.id",
        ],
        "SONG" => [ 
            TableHandler::LINKS_ENUM_STRING => ["song", "song_in_album"],
            "song_in_album.song_id" => "song.id",
            "song_in_album.album_id" => "album.id",
        ],
    ];
    protected $tableFormat = [
        "ID" => "id",
        "Performer" => "PERFORMER.name",
        "Name" => "name",
        "ReleaseDate" => "release_date",
        "Songs" => [ 
            "ID" => "SONG.id", 
            "Name" => "SONG.name",
        ],
    ];    
}