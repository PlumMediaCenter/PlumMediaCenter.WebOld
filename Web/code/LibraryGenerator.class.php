<?php

include_once("Movie.class.php");
include_once("TvShow.class.php");
include_once("database/Queries.class.php");

class LibraryGenerator {

    private $moviesFilePathList = [];
    private $moviesBaseUrlList = [];
    private $tvShowsFilePathList = [];
    private $tvShowsBaseUrlList = [];
    private $movies = [];
    private $tvShows = [];
    private $movieCount = 0;
    private $tvShowCount = 0;
    private $tvEpisodeCount = 0;

    // function __construct($moviesFilePathList, $moviesBaseUrlList, $tvShowsFilePathList, $tvShowsBaseUrlList) {
//        $this->moviesFilePathList = $moviesFilePathList;
//        $this->moviesBaseUrlList = $moviesBaseUrlList;
//        $this->tvShowsFilePathList = $tvShowsFilePathList;
//        $this->tvShowsBaseUrlList = $tvShowsBaseUrlList;
    function __construct() {
        $movieSources = Queries::getVideoSources(Enumerations::MediaType_Movie);
        foreach ($movieSources as $source) {
            $this->moviesFilePathList[] = $source->location;
            $this->moviesBaseUrlList[] = $source->base_url;
        }

        $tvShowSources = Queries::getVideoSources(Enumerations::MediaType_TvShow);
        foreach ($tvShowSources as $source) {
            $this->tvShowsFilePathList[] = $source->location;
            $this->tvShowsBaseUrlList[] = $source->base_url;
        }
    }

    function generateLibrary() {
        writeToLog("Begin generate library");
        //clear the database of all video references. 
        Queries::truncateTableVideo();
        writeToLog("Begin loading movies");
        $this->generateMovies();
        writeToLog("End loading movies");
        writeToLog("Begin loading tv shows");
        $this->generateTvShows();
        writeToLog("End loading tv shows");
        writeToLog("Begin writing videos to database");
        $this->writeMoviesToDb();
        $this->writeTvShowsToDb();
        writeToLog("End writing videos to database");
        writeToLog("Begin generating videos.json");
        $this->generateVideosJson();
        writeToLog("End generating videos.json");
        writeToLog("Generate Library Summary: $this->movieCount Movies. $this->tvShowCount Tv Shows. $this->tvEpisodeCount Tv Episodes.");
        writeToLog("End Generate Library");
    }

    function updateLibrary() {
        writeToLog("Begin update library");
        /* @var $movie Video   */
        /* @var $tvShow TvShow  */
        /* @var $tvEpisode TvEpisode  */

        //get the list of current videos in the library
        $currentVideosList = Queries::getAllVideoPathsInCurrentLibrary();
        $videosToDelete = array();
        $videosToDelete = array_merge($videosToDelete, $currentVideosList);
        $videosToUpdate = [];
        $newVideos = [];
        $this->generateMovies();
        $this->generateTvShows();
        //for every movie found in sources, remove it from the list of videos found in the db...
        //it has been marked as 'present' in this role call
        foreach ($this->movies as $movie) {
            //if this video needs updated and it is not a new video, add it to the update list
            if ($movie->metadataInDatabaseIsUpToDate() === false && $movie->getVideoId() != -1) {
                $videosToUpdate[] = $movie;
            }
            //if this movie is in the list of videos in the db, remove it from the list of movies from the db...items
            //remaining in the list will be removed from the db after all disc videos have been accounted for
            $key = array_search($movie->fullPath, $videosToDelete);
            //if the path was found, remove it from the list of items to be deleted
            if ($key !== false) {
                $videosToDelete[$key] = null;
            }
            //if the path was not found, this is a NEW video. add it to the new video list
            else {
                $newVideos[] = $movie;
            }
        }

        foreach ($this->tvShows as $tvShow) {
            //if this video needs updated and it is not a new video, add it to the update list
            if ($tvShow->metadataInDatabaseIsUpToDate() === false && $tvShow->getVideoId() != -1) {
                $videosToUpdate[] = $tvShow;
            }
            //if this tv show path is in the list from the db, remove it fro mthe list of videos to delete.
            $key = array_search($tvShow->fullPath, $videosToDelete);
            if ($key != null) {
                $videosToDelete[$key] = null;
            }
            //if the path was not found, this is a NEW video. add it to the new video list
            else {
                $newVideos[] = $tvShow;
            }
            //now process every tv episode in the tv show
            foreach ($tvShow->episodes as $tvEpisode) {
                //if this video needs updated and it is not a new video, add it to the update list
                if ($tvEpisode->metadataInDatabaseIsUpToDate() === false && $tvEpisode->getVideoId() != -1) {
                    $videosToUpdate[] = $tvEpisode;
                }
                $key = array_search($tvEpisode->fullPath, $videosToDelete);
                if ($key != null) {
                    $videosToDelete[$key] = null;
                }
                //if the path was not found, this is a NEW video. add it to the new video list
                else {
                    $newVideos[] = $tvEpisode;
                }
            }
        }

        //remove nulls from the delete list. this will leave us with only the video paths that need deleted
        $videosToDelete = array_filter($videosToDelete);

        //at this point, $videosToDelete should only contain videos that are no longer present in sources. 
        //delete these videos from the database
        Queries::deleteVideosByFilePaths($videosToDelete);

        //log all vides that were just deleted
        foreach ($videosToDelete as $path) {
            writeToLog("Deleted video: '$path' from library");
        }

        //write update videos to database
        foreach ($videosToUpdate as $video) {
            $video->writeToDb();
            writeToLog("Updated $video->mediaType: '$video->fullPath'");
        }

        //log all new videos that were just added
        foreach ($newVideos as $video) {
            //write all new videos to the database
            $video->writeToDb();
            writeToLog("Added new $video->mediaType: '$video->fullPath' to library");
        }
        writeToLog("Begin generating videos.json");
        $this->generateVideosJson();
        writeToLog("End generating videos.json");
        $numDeleted = count($videosToDelete);
        $numUpdated = count($videosToUpdate);
        $numNew = count($newVideos);
        writeToLog("Update Library Summary: Deleted $numDeleted. Updated $numUpdated. Added $numNew new. ");
        writeToLog("Finished update library.");
        return true;
    }

    /**
     * Generates the videos.json file based on the files in the database
     */
    function generateVideosJson() {
        //remove any unnecessary public properties in each video
        foreach ($this->movies as $movie) {
            $movie->prepForJsonification();
        }
        foreach ($this->tvShows as $tvShow) {
            $tvShow->prepForJsonification();
        }

        //save the videos to a new object
        $videoList = [];
        $videoList["movies"] = $this->movies;
        $videoList["tvShows"] = $this->tvShows;
        $videoJson = json_encode($videoList, JSON_PRETTY_PRINT);
        $success = file_put_contents(dirname(__FILE__) . "/../videos.json", $videoJson);
    }

    function writeMoviesToDb() {
        foreach ($this->movies as $movie) {
            $movie->writeToDb();
            writeToLog("Added new movie: '$movie->fullPath'");
        }
    }

    function writeTvShowsToDb() {
        foreach ($this->tvShows as $tvShow) {
            $tvShow->writeToDb();
            writeToLog("Added new tv show: '$tvShow->fullPath'");
            foreach ($tvShow->episodes as $episode) {
                writeToLog("Added new tv episode: '$episode->fullPath'");
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
                $this->movies[] = $video;
                $this->movieCount++;
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
            writeToLog("Added New Movie: $fullPathToFile");
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
                $this->tvShowCount++;
            }

            $this->tvEpisodeCount += $video->episodeCount;
        }
    }

}

?>
