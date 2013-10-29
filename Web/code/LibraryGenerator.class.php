<?php

include_once("Movie.class.php");
include_once("TvShow.class.php");
include_once("database/Queries.class.php");

class LibraryGenerator {

    private $movieSources;
    private $tvShowSources;
    private $movies = [];
    private $tvShows = [];
    private $movieCount = 0;
    private $tvShowCount = 0;
    private $tvEpisodeCount = 0;
    private $loadFromDatabase = false;

    function __construct() {
        $movieSources = Queries::getVideoSources(Enumerations::MediaType_Movie);
        $this->movieSources = $movieSources;

        $tvShowSources = Queries::getVideoSources(Enumerations::MediaType_TvShow);
        $this->tvShowSources = $tvShowSources;
        //set the time limit for this script to be 10 minutes. If it takes any longer than that, there's something wrong
        set_time_limit(600);
    }

    /**
     * Loads the library from the database instead of loading from disk
     */
    function loadFromDatabase() {
        $this->loadFromDatabase = true;
        $this->generateMovies();
        $this->generateTvShows();
    }

    /**
     * After the library has been loaded, call this to force each video to retrieve its video id from the database
     */
    function retrieveVideoIds() {
        /** @var $movie Movie  */
        foreach ($this->movies as $movie) {
            $movie->getVideoId();
        }
        foreach ($this->tvShows as $show) {
            $show->getVideoId();
            foreach ($show->episodes as $episode) {
                $episode->getVideoId();
            }
        }
    }
    
    /**
     * Processes the video sources directories and updates the database and library.json to have all videos found in the sources.
     * @return true if total success, false if at least partial failure
     */
    function updateLibrary() {
        writeToLog("Begin update library");
        /* @var $movie Video   */
        /* @var $tvShow TvShow  */
        /* @var $tvEpisode TvEpisode  */

        $videosToUpdate = [];
        $newVideos = [];
        $unchangedVideos = [];
        $this->generateMovies();
        $this->generateTvShows();
        //for every movie found in sources, remove it from the list of videos found in the db...
        //it has been marked as 'present' in this role call
        foreach ($this->movies as $movie) {
            //if this video needs updated and it is not a new video, add it to the update list
            if (LibraryGenerator::VideoIsModified($movie)) {
                $videosToUpdate[] = $movie;
            } else if (LibraryGenerator::VideoIsNew($movie)) {
                $newVideos[] = $movie;
            } else {
                $unchangedVideos[] = $movie;
            }
        }

        foreach ($this->tvShows as $tvShow) {
            //if this video needs updated and it is not a new video, add it to the update list
            if (LibraryGenerator::VideoIsModified($tvShow)) {
                $videosToUpdate[] = $tvShow;
            }
            //if this video is a new video, add it to the new videos list
            else if (LibraryGenerator::VideoIsNew($tvShow)) {
                $newVideos[] = $tvShow;
            } else {
                $unchangedVideos[] = $tvShow;
            }
            //now process every tv episode in the tv show
            foreach ($tvShow->episodes as $tvEpisode) {
                //if this video needs updated and it is not a new video, add it to the update list
                if (LibraryGenerator::VideoIsModified($tvEpisode)) {
                    $videosToUpdate[] = $tvEpisode;
                }
                //if this is a new video, add it to the list of new videos
                else if (LibraryGenerator::VideoIsNew($tvEpisode)) {
                    $newVideos[] = $tvEpisode;
                } else {
                    $unchangedVideos[] = $tvEpisode;
                }
            }
        }

        //get the list of videos currently in the database
        $videosFromDb = Queries::getAllVideoPathsInCurrentLibrary();

        //remove all modified videos from the list from the db
        foreach ($videosToUpdate as $videoToUpdate) {
            $key = array_search($videoToUpdate->fullPath, $videosFromDb);
            if ($key != null) {
                $videosFromDb[$key] = null;
            }
        }

        //remove all unchanged videos from the list from the db
        foreach ($unchangedVideos as $unchangedVideo) {
            $key = array_search($unchangedVideo->fullPath, $videosFromDb);
            if ($key != null) {
                $videosFromDb[$key] = null;
            }
        }

        //remove nulls from the list. this will leave us with only the video paths that need deleted
        $videosToDelete = array_filter($videosFromDb);

        //at this point, $videosToDelete should only contain videos that are no longer present in sources. 
        //delete these videos from the database
        Queries::deleteVideosByVideoPaths($videosToDelete);

        //log all vides that were just deleted
        foreach ($videosToDelete as $path) {
            writeToLog("Deleted video: '$path' from library");
        }

        //update videos in the db
        foreach ($videosToUpdate as $video) {
            $video->writeToDb();
            writeToLog("Updated " . $video->getMediaType() . ": '$video->fullPath'");
        }

        //add new videos to db
        foreach ($newVideos as $video) {
            //write all new videos to the database
            $video->writeToDb();
            writeToLog("Added new " . $video->getMediaType() . ": '$video->fullPath' to library");
        }
        writeToLog("Begin generating library.json");
        $this->generateVideosJson();
        writeToLog("End generating library.json");
        $numDeleted = count($videosToDelete);
        $numUpdated = count($videosToUpdate);
        $numNew = count($newVideos);
        $numUnchanged = count($unchangedVideos);
        writeToLog("Update Library Summary: $numNew Added. $numUpdated Updated. $numDeleted Deleted. $numUnchanged Unchanged.");
        writeToLog("Finished update library.");
        //the update has finished. update the video_source table to indicate that its changes have been flushed to each video
        Queries::updateVideoSourceRefreshVideos();
        return true;
    }

    /**
     * Generates the library.json file based on the files in the database
     */
    function generateVideosJson() {
        //remove any unnecessary public properties in each video
        foreach ($this->movies as $movie) {
            $movie->prepForJsonification();
        }
        foreach ($this->tvShows as $tvShow) {
            $tvShow->prepForJsonification();
        }

      
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

    /**
     * Fetches posters for all videos missing posters. Generates sd and hd posters for all videos missing them.
     * @return boolean - success or at least one video failure
     */
    function generateAndFetchPosters() {
        $fetchCount = 0;
        $generateCount = 0;
        $this->loadFromDatabase();
        /* @var $movie Movie */
        foreach ($this->movies as $movie) {
            if ($movie->posterExists() === false) {
                $fetchCount++;
                $success = $movie->fetchPoster();
                $success = $success ? "Success" : "Failure";
                writeToLog("$success fetching poster for '$movie->fullPath'");
            }
            if ($movie->sdPosterExists() === false) {
                $generateCount++;
                $success = $movie->generatePosters();
                $success = $success ? "Success" : "Failure";
                writeToLog("$success generating sd and hd posters for '$movie->fullPath'");
            }
        }
        /* @var $show TvShow */
        foreach ($this->tvShows as $show) {
            if ($show->posterExists() === false) {
                $fetchCount++;
                $success = $show->fetchPoster();
                $success = $success ? "Success" : "Failure";
                writeToLog("$success fetching poster for '$show->fullPath'");
            }
            if ($show->sdPosterExists() == false) {
                $generateCount++;
                $success = $show->generatePosters();
                $success = $success ? "Success" : "Failure";

                writeToLog("$success generating sd and hd posters for '$show->fullPath'");
            }
            //each episode in this show
            foreach ($show->episodes as $episode) {
                if ($episode->posterExists() === false) {
                    $fetchCount++;
                    $success = $episode->fetchPoster();
                    $success = $success ? "Success" : "Failure";

                    writeToLog("$success fetching poster for '$episode->fullPath'");
                }
                if ($episode->sdPosterExists() == false) {
                    $generateCount++;
                    $success = $episode->generatePosters();
                    $success = $success ? "Success" : "Failure";

                    writeToLog("$success generating sd and hd posters for '$episode->fullPath'");
                }
            }
        }
        return array($fetchCount, $generateCount);
    }
    
    function generateMovies() {
        //for every movie file location, get all movies from that location
        foreach ($this->movieSources as $source) {
            $basePath = $source->location;
            $baseUrl = $source->base_url;
            $refreshVideos = $source->refresh_videos;

            //if the flag says to load from database, load the videos from the database instead of from disc
            if ($this->loadFromDatabase === true) {
                $listOfAllFilesInSource = Queries::getVideoPathsBySourcePath($source->location, Enumerations::MediaType_Movie);
            } else {
                //get a list of each video in this movies folder
                $listOfAllFilesInSource = getVideosFromDir($basePath);
            }

            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //writeToLog("New Movie: $fullPathToFile");
                //create a new Movie object
                $video = new Movie($baseUrl, $basePath, $fullPathToFile);
                $video->refreshVideo = $refreshVideos;
                $this->movies[] = $video;
                $this->movieCount++;
            }
        }
    }

    public function generateTvShows() {
        //for every tv show file location, get all tv shows from that location
        foreach ($this->tvShowSources as $source) {
            $basePath = $source->location;
            $baseUrl = $source->base_url;
            $refreshVideos = $source->refresh_videos;
            //if the flag says to load from database, load the videos from the database instead of from disc
            if ($this->loadFromDatabase === true) {
                $listOfAllFilesInSource = Queries::getVideoPathsBySourcePath($basePath, Enumerations::MediaType_TvShow);
            } else {
                //get a list of every folder in the current video source directory, since the required tv show structure is
                //  TvShowsFolder/Name Of Tv Show/files.....
                //get a list of each video in this tv shows folder
                $listOfAllFilesInSource = getFoldersFromDirectory($basePath);
            }


            //spin through every folder in the source location
            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //if the current file is a video file that we can add to our library
                //create a new Movie object
                $video = new TvShow($baseUrl, $basePath, $fullPathToFile);
                $video->refreshVideo = $refreshVideos;
                $video->setLoadEpisodesFromDatabase($this->loadFromDatabase);

                //tell the tv show to scan subdirectories for tv episodes
                $video->generateTvEpisodes();

                //if this tv show has at least one season (which means it has at least one episode), then add it to the list
                if (count($video->seasons) > 0) {
                    $this->tvShows[] = $video;
                    $this->tvShowCount++;
                }

                $this->tvEpisodeCount += $video->episodeCount;
            }
        }
    }

    function getTvShows() {
        return $this->tvShows;
    }

    function getMovies() {
        return $this->movies;
    }

    /**
     * Determines if the provided video is a new video for this library
     * @param Video $video
     */
    static function VideoIsNew($video) {
        //if this video does NOT have a video id, then it does not exist in the database. It is new
        if ($video->getVideoId() === -1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determines if the video is of 'modified' status. This means that the metadata is out of date in the db
     * OR the video was set to be refreshed. 
     * AND the videoId of this video MUST exist. If the videoId does not exist, then this is a new video.
     * @param Video $video - the video in question
     * @return boolean - true if the video is modified and not new, false if the video is not modified or is new
     */
    static function VideoIsModified($video) {
        //if the video NOT new, and (metadata is old OR video flagged to be refreshed)
        $isModified = LibraryGenerator::VideoIsNew($video) == false && ($video->metadataInDatabaseIsUpToDate() == false || $video->refreshVideo == true);
        //if this is a tv episode
        if ($video->getMediaType() === Enumerations::MediaType_TvEpisode) {
            //if the tv show ids don't match up, this video needs to be refreshed.
            if ($video->getTvShowVideoIdFromVideoTable() != $video->getTvShowVideoIdFromTvEpisodeTable()) {
                return true;
            }
        }
        return $isModified;
    }

}

?>
