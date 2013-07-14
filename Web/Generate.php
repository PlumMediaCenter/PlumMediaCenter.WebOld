<?php

include_once(dirname(__FILE__) . "/code/Page.class.php");
include_once(dirname(__FILE__) . "/code/Video.class.php");
include_once(dirname(__FILE__) . "/code/Movie.class.php");
include_once(dirname(__FILE__) . "/code/TvShow.class.php");
include_once(dirname(__FILE__) . "/code/TvEpisode.class.php");
include_once(dirname(__FILE__) . "/code/Enumerations.class.php");


//let this script run for up to an hour. Hopefully this doesn't take more than a minute!
set_time_limit(3600);

function dupNum() {
    if (isset($_GET["dupNum"])) {
        return intval($_GET["dupNum"]);
    } else {
        return 1;
    }
}

//if the generate button was pressed, then generate the library
if (generateButtonPressed() == true) {
    $baseMoviesUrl = $_GET["moviesUrl"];
    $baseMoviesPath = $_GET["moviesFilePath"];
    $baseTvShowsUrl = $_GET["tvShowsUrl"];
    $baseTvShowsPath = $_GET["tvShowsFilePath"];
    $videoList = [];
    $videoList["movies"] = getMovies($baseMoviesUrl, $baseMoviesPath);
    $videoList["tvShows"] = getTvShows($baseTvShowsUrl, $baseTvShowsPath);
    $videoList = json_encode($videoList, JSON_PRETTY_PRINT);
    echo "<pre>" . str_replace("\\", "", $videoList) . "</pre>";
    $success = file_put_contents("videos.json", $videoList);
    echo $success ? "Updated videos.json" : "Failed to write data to videos.json";
}


//show the page
$p = new Page(__FILE__);
$m = $p->getModel();
$p->show();

/**
 * Returns an array of all movies found
 * @param type $baseUrl - the base url to be associated with this collection of videos
 * @param type $basePath -- the base path to start looking for videos at 
 * @return \Movie
 */
function getMovies($baseUrl, $basePath) {
    $movieList = [];
    //get a list of each video in the movies folder
    //get a list of every file in the current video source directory
    $listOfAllFilesInSource = getVideosFromDir($basePath);

    //spin through every folder in the source location
    foreach ($listOfAllFilesInSource as $fullPathToFile) {
        //create a new Movie object
        $video = new Movie($baseUrl, $basePath, $fullPathToFile);
        //add the movie object to the list of all movies
        for ($i = 0; $i < dupNum(); $i++) {
            $movieList[] = $video;
        }
    }
    return $movieList;
}

/**
 * Returns an array of all tv shows found
 * @param type $baseUrl - the base url to be associated with this collection of videos
 * @param type $basePath -- the base path to start looking for tv shows at
 * @return \TvShow
 */
function getTvShows($baseUrl, $basePath) {
    $tvShowsList = [];
    //get a list of each video in the movies folder
    //get a list of every file in the current video source directory
    $listOfAllFilesInSource = getFoldersFromDirectory($basePath);

    //spin through every folder in the source location
    foreach ($listOfAllFilesInSource as $fullPathToFile) {
        //if the current file is a video file that we can add to our library
        //create a new Movie object
        $video = new TvShow($baseUrl, $basePath, $fullPathToFile);
        //tell the tv show to scan subdirectories for tv episodes
        $video->getTvEpisodes();
        //add the tv show object to the list of all tv shows
        for ($i = 0; $i < dupNum(); $i++) {
            //if this tv show has at least one season (which means it has at least one episode), then add it to the list
            if (count($video->seasons) > 0) {
                $tvShowsList[] = $video;
            }
        }
    }
    return $tvShowsList;
}

/**
 * Determines if the generate button was pressed
 * @return boolean -- true if generate button was pressed, false if it was not
 */
function generateButtonPressed() {
    return isset($_GET["generate"]) && strtolower($_GET["generate"]) == "true";
}
