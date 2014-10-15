<?php

include_once("database/Queries.class.php");
include_once("Video.class.php");

class Library {

    public $movies = [];
    private $movieCount = 0;
    public $tvShows = [];
    private $tvShowCount = 0;
    public $tvEpisodes = [];
    private $tvEpisodeCount = 0;
    //contains a list of all videos, a combination of movies, tv shows and tv episodes
    private $videos = [];

    public function __construct() {
        //set the time limit for this script to be 10 minutes. If it takes any longer than that, there's something wrong
        set_time_limit(600);
    }

    /**
     * Returns the number of movies in the library
     * @return int - the number of movies in the library
     */
    public function getMovieCount() {
        return $this->movieCount;
    }

    /**
     * Fetches any missing metadata for the videos and fetches any missing posters for the videos and generates posters for
     * any video that doesn't have the posters generated yet.
     */
    public function fetchMissingMetadataAndPosters() {
        /* @var $video Video   */
        foreach ($this->videos as $video) {
            //skip this video if it's not an object
            if(is_object($video) == false){continue;}
            echo $video->title . '<br/>';
            try{
                if ($video->nfoFileExists() == false) {
                    $video->fetchMetadata();
                }
                if ($video->posterExists() == false) {
                    $video->fetchPoster();
                }
                if ($video->sdPosterExists() == false || $video->hdPosterExists() == false) {
                    $video->generatePosters();
                }
            }
            catch(Exception $e){
                
            }
        }
        return true;
    }

    /**
     * Returns the number of tv shows found in the library
     * @return int - the number of tv shows in the library
     */
    public function getTvShowCount() {
        return $this->tvShowCount;
    }

    /**
     * Returns the number of episodes found in the library
     * @return int - the number of episodes found in the library
     */
    public function getTvEpisodeCount() {
        return $this->tvEpisodeCount;
    }

    /**
     * Loads all movies and tv shows from the database
     */
    public function loadFromDatabase() {
        $this->videos = [];
        $success = $this->loadMoviesFromDatabase();
        $success = $success && $this->loadTvShowsFromDatabase();
        return $success;
    }

    /**
     * Populates the movies array with all movies found in the database. All metadata is loaded into the movies. 
     * @return Movie[] - returns the array of movies loaded from the database
     */
    public function loadMoviesFromDatabase() {
        $this->movies = [];
        $this->movieCount = 0;
        $videoIds = Queries::GetVideoIds(Enumerations::MediaType_Movie);
        foreach ($videoIds as $videoId) {
            $movie = Video::GetVideo($videoId);
            $this->movies[] = $movie;
            $this->videos[] = $movie;
            $this->movieCount++;
        }
        return $this->movies;
    }

    /**
     * Loads an array of all tv shows found in the database. All metadata is loaded into the tv show objects. 
     * @return TvShow[] - returns the tv shows in the library that was loaded from the database
     */
    public function loadTvShowsFromDatabase() {
        $this->tvShows = [];
        $this->tvEpisodes = [];
        $this->tvShowCount = 0;
        $this->tvEpisodeCount = 0;
        $videoIds = Queries::GetVideoIds(Enumerations::MediaType_TvShow);
        foreach ($videoIds as $videoId) {
            $tvShow = Video::GetVideo($videoId);
            $this->tvShows[] = $tvShow;
            $this->videos[] = $tvShow;
            $this->tvShowCount++;

            if(is_object($tvShow) == false){continue;}
            //load all of the episodes for this tv show
            $tvShow->loadEpisodesFromDatabase();
            $this->videos = array_merge($this->videos, $tvShow->episodes);
            $this->tvEpisodes = array_merge($this->tvEpisodes, $tvShow->episodes);
            $this->tvEpisodeCount += $tvShow->episodeCount;
        }
        return $this->tvShows;
    }

    /**
     * Forces every video loaded into memory in this library object to be written to the database. 
     * Then any videos that are no longer in this library are removed from the database
     */
    public function writeToDb() {
        //writes every video to the database. If it is a new video, it will automatically be added. If it is an existing
        //video, it will be updated
        $libraryVideoIds = [];
        $totalSuccess = true;
        foreach ($this->videos as $video) {
            $totalSuccess = $totalSuccess && $video->writeToDb();
            $libraryVideoIds[] = $video->getVideoId();
        }

        //delete any videos from the database that are not in this library
        $totalSuccess = $totalSuccess && Queries::DeleteVideosNotInThisList($libraryVideoIds);

        //return success or failure. If at least one item failed, this will be returned as a failure
        return $totalSuccess;
    }

    /**
     * Loads this library object totally from the filesystem. This means scanning each video source directory for 
     * videos. 
     */
    public function loadFromFilesystem() {
        //for each movie
        $success = $this->loadMoviesFromFilesystem();
        $success = $success && $this->loadTvShowsFromFilesystem();
        return $success;
    }

    /**
     * Loads the movies array with movies found in the different video sources marked as movie sources
     */
    public function loadMoviesFromFilesystem() {
        //list of all video sources
        $movieSources = Queries::getVideoSources(Enumerations::MediaType_Movie);
        $this->movies = [];
        $this->movieCount = 0;
        foreach ($movieSources as $source) {
            //get a list of each video in this movies source folder
            $listOfAllFilesInSource = getVideosFromDir($source->location);

            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //writeToLog("New Movie: $fullPathToFile");
                //create a new Movie object
                $video = new Movie($source->base_url, $source->location, $fullPathToFile);
                $this->movies[] = $video;
                $this->videos[] = $video;
                $this->movieCount++;
            }
        }
        return true;
    }
    public function sort() {
        usort($this->movies, array("Video", "CompareTo"));
        usort($this->tvShows, array("Video", "CompareTo"));
    }
    /**
     * loads the libary TvShow object with tv shows found in the source paths marked as tv show sources
     * @return TvShow[] - the array of TvShows found at the source path
     */
    public function loadTvShowsFromFilesystem() {
        $this->tvShows = [];
        $this->tvEpisodes = [];
        $this->tvEpisodeCount = 0;
        $this->tvShowCount = 0;
        $tvShowSources = Queries::getVideoSources(Enumerations::MediaType_TvShow);
        //for every tv show file location, get all tv shows from that location
        foreach ($tvShowSources as $source) {
            //get a list of every folder in the current video source directory, since the required tv show structure is
            //  TvShowsFolder/Name Of Tv Show/files.....
            //get a list of each video in this tv shows folder
            $listOfAllFilesInSource = getFoldersFromDirectory($source->location);

            //spin through every folder in the source location
            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //if the current file is a video file that we can add to our library
                //create a new Movie object
                $tvShow = new TvShow($source->base_url, $source->location, $fullPathToFile);

                //tell the tv show to scan subdirectories for tv episodes
                $tvShow->loadTvEpisodesFromFilesystem();

                //if this tv show has at least one season (which means it has at least one episode), then add it to the list
                if (count($tvShow->seasons) > 0) {
                    $this->tvShows[] = $tvShow;
                    $this->videos[] = $tvShow;
                    $this->tvShowCount++;

                    //include episodes
                    $this->videos = array_merge($this->videos, $tvShow->episodes);
                    $this->tvEpisodes = array_merge($this->tvEpisodes, $tvShow->episodes);
                    $this->tvEpisodeCount+= $tvShow->episodeCount;
                }
            }
        }
        return true;
    }

    /**
     * Writes the entire library to a json file that can be consumed by any application that knows its form.
     * @return boolean - success or failure
     */
    public function writeLibraryJson() {
        //clear out any information in the video objects that doesn't need to be there
        $this->prepareVideosForJsonification();
        $this->sort();
        //save the videos to a new object
        $videoList = [];
        $videoList["movies"] = $this->movies;
        $videoList["tvShows"] = $this->tvShows;
        $videoJson = json_encode($videoList, JSON_PRETTY_PRINT);
        $success = file_put_contents(dirname(__FILE__) . "/../api/library.json", $videoJson);
        return $success;
    }

    /**
     * Removes any information that the video class is storing that will not be helpful for
     * the services consuming the video class outside of this server environment.
     */
    public function prepareVideosForJsonification() {
        /* @var $video Video */
        foreach ($this->videos as $video) {
            $video->prepForJsonification();
        }
    }

    /**
     * Write information to log pertaining to the current status of this library. Logs things like number of new videos
     */
    public function logLibraryStatus() {
        $newMovieCount = 0;
        $newTvShowCount = 0;
        $newTvEpisodeCount = 0;
        foreach ($this->videos as $video) {
            if ($video->isNew()) {
                switch ($video->getMediaType()) {
                    case Enumerations::MediaType_Movie:
                        $newMovieCount++;
                        break;
                    case Enumerations::MediaType_TvShow:
                        $newTvShowCount++;
                        break;
                    case Enumerations::MediaType_TvEpisode:
                        $newTvEpisodeCount++;
                        break;
                }
            }
        }
        writeToLog("Update Library Summary: $newMovieCount new Movies. $newTvShowCount new Tv Shows. $newTvEpisodeCount new Tv Episodes.");
    }

    /**
     * Returns a set of stats (counts) based on the library.
     */
    public static function GetVideoCounts() {
        $stats = (object) [];
        $stats->videoCount = null;
        $stats->movieCount = null;
        $stats->tvShowCount = null;
        $stats->tvEpisodeCount = null;
        $counts = Queries::GetVideoCounts();
        if ($counts != false) {
            $stats->videoCount = $counts->movieCount + $counts->tvEpisodeCount;
            $stats->movieCount = $counts->movieCount;
            $stats->tvShowCount = $counts->tvShowCount;
            $stats->tvEpisodeCount = $counts->tvEpisodeCount;
        }
        return $stats;
    }

}
?>
