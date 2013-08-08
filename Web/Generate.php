<?php

include_once(dirname(__FILE__) . "/code/Page.class.php");
include_once(dirname(__FILE__) . "/code/Video.class.php");
include_once(dirname(__FILE__) . "/code/Movie.class.php");
include_once(dirname(__FILE__) . "/code/TvShow.class.php");
include_once(dirname(__FILE__) . "/code/TvEpisode.class.php");
include_once(dirname(__FILE__) . "/code/Enumerations.class.php");
include_once(dirname(__FILE__) . "/code/FileScanner.class.php");
include_once(dirname(__FILE__) . "/code/database/Queries.class.php");

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
    Queries::truncateTableVideo();
    $videoList = [];
    $videoList["movies"] = getMovies($baseMoviesUrl, $baseMoviesPath);
    $videoList["tvShows"] = getTvShows($baseTvShowsUrl, $baseTvShowsPath);
    $videoList = json_encode($videoList, JSON_PRETTY_PRINT);

    $success = file_put_contents("videos.json", $videoList);
    echo "<div style='padding-top:50px;'>";
    echo $success ? color("Generated videos.json", "green") : color("Failed to write data to videos.json", "red");
    echo "</div>";
}


//show the page
$p = new Page(__FILE__);
$m = $p->getModel();
$m->title = "Generate Library - Plum Video Player";
$p->show();

/**
 * Determines if the generate button was pressed
 * @return boolean -- true if generate button was pressed, false if it was not
 */
function generateButtonPressed() {
    return isset($_GET["generate"]);
}
