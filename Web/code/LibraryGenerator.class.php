<?php

include_once("Movie.class.php");
include_once("TvShow.class.php");
include_once("database/Queries.class.php");

class LibraryGenerator {

    private $moviesFilePathList;
    private $moviesBaseUrlList;
    private $tvShowsFilePathList;
    private $tvShowsBaseUrlList;
    private $movies = [];
    private $tvShows = [];

    function __construct($moviesFilePathList, $moviesBaseUrlList, $tvShowsFilePathList, $tvShowsBaseUrlList) {
        $this->moviesFilePathList = $moviesFilePathList;
        $this->moviesBaseUrlList = $moviesBaseUrlList;
        $this->tvShowsFilePathList = $tvShowsFilePathList;
        $this->tvShowsBaseUrlList = $tvShowsBaseUrlList;
    }

    function generateLibrary() {
        //clear the database of all video references. 
        Queries::truncateTableVideo();
        $this->generateMovies();
        $this->generateTvShows();
        $this->writeMoviesToDb();
        $this->writeTvShowsToDb();
    }

    function writeMoviesToDb() {
        foreach ($this->movies as $movie) {
            $movie->writeToDb();
        }
    }

    function writeTvShowsToDb() {
        foreach ($this->tvShows as $tvShow) {
            $tvShow->writeToDb();
            foreach ($tvShow->episodes as $episode) {
                $episode->writeToDb();
            }
        }
    }

    function generateMovies() {
        //for every movie file location, get all movies from that location
        for ($i = 0; $i < count($this->moviesFilePathList); $i++) {
            $basePath = $this->moviesFilePathList[$i];
            $baseUrl = $this->moviesBaseUrlList[$i];

            //get a list of each video in this movies folder
            $listOfAllFilesInSource = getVideosFromDir($basePath);

            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //writeToLog("New Movie: $fullPathToFile");
                //create a new Movie object
                $video = new Movie($baseUrl, $basePath, $fullPathToFile);
                $video->writeToDb();
                $this->movies[] = $video;
                //writeToLog("Finished $fullPathToFile");
            }
        }
    }

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
            writeToLog("New Movie: $fullPathToFile");
            $video = new Movie($baseUrl, $basePath, $fullPathToFile);
            $video->writeToDb();
            //add the movie object to the list of all movies
            for ($i = 0; $i < dupNum(); $i++) {
                $movieList[] = $video;
            }
            writeToLog("Finished $fullPathToFile");
        }
        return $movieList;
    }

    function generateTvShows() {
        //for every movie file location, get all movies from that location
        for ($i = 0; $i < count($this->tvShowsFilePathList); $i++) {
            $basePath = $this->tvShowsFilePathList[$i];
            $baseUrl = $this->tvShowsBaseUrlList[$i];
            $this->getTvShows($baseUrl, $basePath);
        }
    }

    function getTvShows($baseUrl, $basePath) {
        //get a list of every folder in the current video source directory, since the required tv show structure is
        //  TvShowsFolder/Name Of Tv Show/files.....
        $listOfAllFilesInSource = getFoldersFromDirectory($basePath);

        //spin through every folder in the source location
        foreach ($listOfAllFilesInSource as $fullPathToFile) {
            //if the current file is a video file that we can add to our library
            //create a new Movie object
            $video = new TvShow($baseUrl, $basePath, $fullPathToFile);

            //tell the tv show to scan subdirectories for tv episodes
            $video->getTvEpisodes();

            //if this tv show has at least one season (which means it has at least one episode), then add it to the list
            if (count($video->seasons) > 0) {
                $this->tvShows[] = $video;
            }
        }
    }

    function updateLibrary() {
        
    }

}
?>
