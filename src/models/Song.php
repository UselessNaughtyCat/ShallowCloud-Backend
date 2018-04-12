<?php

require_once '../src/components/TableHandler.php';

/**
* Song Table
*/
class Song extends TableHandler
{
    public $rules = [
        // "All", "Authorized", "Nobody"
        "select" => "All",
        "insert" => "Authorized",
        "update" => "Authorized",
        "delete" => "Nobody"
    ];
    protected $tableName = "song";
    protected $tableMainID = "id";
    protected $tableLinks = [
        "USER" => [ 
            TableHandler::LINKS_ENUM_STRING => ["user"],
            "user_id" => "user.id",
        ],
        "GENRE" => [ 
            TableHandler::LINKS_ENUM_STRING => ["genre"],
            "genre_id" => "genre.id",
        ],
        "ALBUM" => [ 
            TableHandler::LINKS_ENUM_STRING => ["album", "song_in_album"],
            "song_in_album.song_id" => "song.id",
            "song_in_album.album_id" => "album.id",
        ],
    ];
    protected $tableFormat = [
        "ID" => "id",
        "User" => "USER.nickname",
        "Name" => "name",
        "Album" => "ALBUM.name",/*[ 
            "Name" => "ALBUM.name", 
            "ReleaseDate" => "ALBUM.release_date", 
        ],*/
        "Genre" => "GENRE.name",
        "ReleaseDate" => "release_date",
        "Source" => "src",
    ];
}