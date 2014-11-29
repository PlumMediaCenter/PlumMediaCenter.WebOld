<?php

define("BASE_URL", "http://localhost:8080/PlumMediaCenter/");

class config {

    public static $dbHost = "127.0.0.1";
    public static $dbName = "plummediacenter";
    public static $dbUsername = "plummediacenter";
    public static $dbPassword = "plummediacenter";
    public static $logQueries = true;
    public static $tvdbUrl = 'http://thetvdb.com';
    public static $tvdbApiKey = '3352E255A2DE009D';
    public static $tmdbApiKey = '90dbc17887e30eae3095d213fa803190';
    public static $tmdbUrl = 'http://api.themoviedb.org/3';
    public static $repoName = 'TwitchBronBron/PlumMediaCenter';
    public static $globalUsername = 'plumuser';
    //if a video is within this amount of time of the next video, play the next one instead (or restart it)
    public static $playNextVideoBufferInSeconds = 90;

}

?>
