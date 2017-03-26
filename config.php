<?php

define("BASE_URL", "http://localhost:8080/PlumMediaCenter/");

class config {

    public static $connectionString = "mysql:host=localhost:3306;dbname=plummediacenter";
    public static $dbUsername = "plummediacenter";
    public static $dbPassword = "plummediacenter";
    public static $language = "en";
    public static $tvdbUrl = 'http://thetvdb.com';
    public static $tvdbApiKey = '21974976C0C3A041';
    public static $tmdbApiKey = '90dbc17887e30eae3095d213fa803190';
    public static $tmdbUrl = 'http://api.themoviedb.org/3';
    public static $repoOwner = 'TwitchBronBron';
    public static $repoName = 'PlumMediaCenter';
    public static $globalUsername= 'plumuser';
    //if a video is within this amount of time of the next video, play the next one instead (or restart it)
    public static $playNextVideoBufferInSeconds = 90;

}

?>
