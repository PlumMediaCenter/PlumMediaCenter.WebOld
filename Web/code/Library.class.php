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
    private $genres = [];
    //contains a list of all videos, a combination of movies, tv shows and tv episodes
    private $videos = [];
    public $moviesAndTvShows = [];
    public $invalidVideos = [];

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

    private function clear() {
        $this->moviesAndTvShows = [];
        $this->videos = [];
        $this->movies = [];
        $this->tvShows = [];
        $this->tvEpisodes = [];
        $this->movieCount = 0;
        $this->tvShowCount = 0;
        $this->tvEpisodeCount = 0;
    }

    /**
     * Fetches any missing metadata for the videos and fetches any missing posters for the videos and generates posters for
     * any video that doesn't have the posters generated yet.
     */
    public function fetchMissingMetadataAndPosters() {
        /* @var $video Video   */
        foreach ($this->videos as $video) {
            //wrap each fetch in a try/catch so that a bad video doesn't break the process for the rest
            try {
                if ($video->nfoFileExists() == false) {
                    $video->fetchMetadata();
                }
                if ($video->posterExists() == false) {
                    $video->fetchPoster();
                }
                if ($video->sdPosterExists() == false || $video->hdPosterExists() == false) {
                    $video->generatePosters();
                }
            } catch (Exception $e) {
                writeToLog("Unable to fetch metadata for video at '" . $video->getFullPath() . "'");
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
        $this->clear();
        $videoIds = Queries::GetMovieAndTvShowVideoIds(Enumerations\MediaType::Movie);
        foreach ($videoIds as $videoId) {
            //if we were able to successfully load a video, then continue with this video. otherwise,
            //skip this video and try the next one.
            try {
                $video = Video::GetVideo($videoId);
            } catch (Exception $e) {
                $this->invalidVideoIds[] = $videoId;
                continue;
            }

            $this->moviesAndTvShows[] = $video;

            if ($video->mediaType == Enumerations\MediaType::Movie) {
                $this->movies[] = $video;
                $this->videos[] = $video;
                $this->movieCount++;
            } else {
                //tell the tv show to scan subdirectories for tv episodes
                $video->loadTvEpisodesFromFilesystem();

                //if this tv show has at least one season (which means it has at least one episode), then add it to the list
                if (count($video->episodes) > 0) {
                    $this->tvShows[] = $video;
                    $this->videos[] = $video;
                    $this->tvShowCount++;

                    //include episodes
                    $this->videos = array_merge($this->videos, $video->episodes);
                    $this->tvEpisodes = array_merge($this->tvEpisodes, $video->episodes);
                    $this->tvEpisodeCount+= $video->episodeCount;
                }
            }
        }
        return true;
    }

    /**
     * Loads this library object totally from the filesystem. This means scanning each video source directory for 
     * videos. 
     */
    public function loadFromFilesystem() {
        $this->clear();

        //for each movie
        $loadMoviesSuccess = $this->loadMoviesFromFilesystem();
        $loadTvShowsSuccess = $this->loadTvShowsFromFilesystem();
        $success = $loadMoviesSuccess && $loadTvShowsSuccess;
        return $success;
    }

    /**
     * Loads the movies array with movies found in the different video sources marked as movie sources
     */
    public function loadMoviesFromFilesystem() {
        //list of all video sources
        $movieSources = Queries::getVideoSources(Enumerations\MediaType::Movie);
        $this->movies = [];
        $this->movieCount = 0;
        foreach ($movieSources as $source) {
            //get a list of each video in this movies source folder
            $listOfAllFilesInSource = getVideosFromDir($source->location);
            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //create a new Movie object
                $video = new Movie($source->base_url, $source->location, $fullPathToFile);
                $this->movies[] = $video;
                $this->videos[] = $video;
                $this->moviesAndTvShows[] = $video;
                $this->movieCount++;
            }
        }
        return true;
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
        $tvShowSources = Queries::getVideoSources(Enumerations\MediaType::TvShow);
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
                $video = new TvShow($source->base_url, $source->location, $fullPathToFile);
                $this->moviesAndTvShows[] = $video;
                //tell the tv show to scan subdirectories for tv episodes
                $video->loadTvEpisodesFromFilesystem();

                //if this tv show has at least one season (which means it has at least one episode), then add it to the list
                if (count($video->episodes) > 0) {
                    $this->tvShows[] = $video;
                    $this->videos[] = $video;
                    $this->tvShowCount++;

                    //include episodes
                    $this->videos = array_merge($this->videos, $video->episodes);
                    $this->tvEpisodes = array_merge($this->tvEpisodes, $video->episodes);
                    $this->tvEpisodeCount+= $video->episodeCount;
                }
            }
        }
        return true;
    }

    public function sort() {
        usort($this->movies, array("Video", "CompareTo"));
        usort($this->tvShows, array("Video", "CompareTo"));
    }

    /**
     * Writes the entire library to a json file that can be consumed by any application that knows its form.
     * @return boolean - success or failure
     */
    public function writeLibraryJson() {
        //clear out any information in the video objects that doesn't need to be there
        $this->prepareVideosForJsonification();

        //save the videos to a new object
        $videoList = [];
        $videoList["movies"] = $this->movies;
        $videoList["tvShows"] = $this->tvShows;
        $videoJson = json_encode($videoList, JSON_PRETTY_PRINT);
        $success = file_put_contents(dirname(__FILE__) . "/../api/library.json", $videoJson);
        return $success !== false;
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
                    case Enumerations\MediaType::Movie:
                        $newMovieCount++;
                        break;
                    case Enumerations\MediaType::TvShow:
                        $newTvShowCount++;
                        break;
                    case Enumerations\MediaType::TvEpisode:
                        $newTvEpisodeCount++;
                        break;
                }
            }
        }
        writeToLog("Update Library Summary: $newMovieCount new Movies. $newTvShowCount new Tv Shows. $newTvEpisodeCount new Tv Episodes.");
    }

    /**
     * Forces every video loaded into memory in this library object to be written to the database. 
     * Then any videos that are no longer in this library are removed from the database
     */
    public function writeToDb() {
        //writes every video to the database. If it is a new video, it will automatically be added. If it is an existing
        //video, it will be updated. if a video is in the db but is no longer present in the filesystem, it will be deleted.
        $libraryVideoIds = [];
        $totalSuccess = true;
        foreach ($this->videos as $video) {
            $totalSuccess = $totalSuccess && $video->writeToDb();
            $libraryVideoIds[] = $video->getVideoId();
        }
        //get a list of all videoIds from the database
        $allIds = Queries::GetAllVideoIds();
        $deleteIds = array_diff($allIds, $libraryVideoIds);
        //we can assume that all tv shows were added before tv episodes, so sorting this array in descending order will
        //force all tv episodes to be deleted before their tv show, thus allowing us to spin through the whole list
        //without worrying about referencial integrity.
        arsort($deleteIds);
        foreach ($deleteIds as $videoId) {
            Video::DeleteVideo($videoId);
        }

        //delete any videos from the database that are not in this library
        //$totalSuccess = $totalSuccess && Queries::DeleteVideosNotInThisList($libraryVideoIds);
        //return success or failure. If at least one item failed, this will be returned as a failure
        //delete any orphaned genres

        return $totalSuccess;
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

    public static function SearchByTitle($searchString, $caseSensitiveSearch = true) {
        //split the search string by spaces
        $searchTerms = explode(" ", $searchString);
        $cleanedTerms = [];


        foreach ($searchTerms as $term) {
            $t = trim($term);
            //if the term is not empty, add it to the list
            if (strlen($t) > 0) {
                $cleanedTerms[] = $t;
            }
        }
        $colname = "v.title";
        $colname = ($caseSensitiveSearch) ? $colname : "lower($colname)";
        //create an in statement with the terms
        $inStmt = DbManager::GenerateLikeStatement($cleanedTerms, $colname, "or");
        $inStmt = ($caseSensitiveSearch) ? $inStmt : strtolower($inStmt);
        $q = "select * from video v " .
                " where media_type in('" . Enumerations\MediaType::Movie . "', '" . Enumerations\MediaType::TvShow . "') and " .
                "($inStmt)";
        $results = DbManager::Query($q);
        //create video objects out of the results
        $videos = [];
        foreach ($results as $row) {
            $videos[] = Video::GetVideoFromDataRow($row);
        }
        return $videos;
    }

}

?>
