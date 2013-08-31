<?php
include_once("MetadataFetcher/TvShowMetadataFetcher.class.php");

include_once("Video.class.php");
include_once("TvEpisode.class.php");

class TvShow extends Video {

    public $seasons = [];
    //holds each episode in a list instead of grouped by seasons
    public $episodes = [];
    public $episodeCount = 0;
    private $loadEpisodesFromDatabase = false;

    function __construct($baseUrl, $basePath, $fullPath) {
        parent::__construct($baseUrl, $basePath, $fullPath);
        $this->mediaType = Enumerations::MediaType_TvShow;

        //load all of the information from the metadata file, if it exists
        $this->loadMetadata();
    }

    function setLoadEpisodesFromDatabase($loadEpisodesFromDatabase) {
        $this->loadEpisodesFromDatabase = $loadEpisodesFromDatabase;
    }

    /**
     *  Get the array of all tv episodes
     * @return type
     */
    function getEpisodes() {
        return $this->episodes;
    }

    /**
     * Set the list of tv episodes for this show based on
     * @param array of objects - $seasons - arrays of episodes grouped  into seasons
     */
    function setSeasons($seasons) {
        foreach ($seasons as $season) {
            $s = [];
            //look at each episode in this season
            foreach ($season as $episode) {
                $e = new TvEpisode($episode->baseUrl, $episode->basePath, $episode->fullPath);
                $s[$episode->episodeNumber] = $e;
                $this->episodes[] = $e;
            }
            $this->seasons[$episode->seasonNumber] = $s;
        }
    }

    /**
     * For tv Series, there is no file, it's just the folder itself. So for this class, return the folder to itself as the containing folder.
     * @return type
     */
    protected function getFullPathToContainingFolder() {
        return $this->fullPath . "/";
    }

    protected function getFullUrlToContainingFolder() {
        return $this->getUrl();
    }

    function generateTvEpisodes() {
        $seasonList = [];
        //if the flag says to load from database, load the videos from the database instead of from disc
        if ($this->loadEpisodesFromDatabase === true) {
            $videosList = Queries::getEpisodePathsByShowPath($this->fullPath);
        } else {
            //get the list of videos from this tv series 
            $videosList = getVideosFromDir($this->fullPath);
        }

        $this->episodeCount = count($videosList);
        //spin through every folder in the source location
        foreach ($videosList as $fullPathToFile) {
            //create a new Episode object
            $episode = new TvEpisode($this->baseUrl, $this->basePath, $fullPathToFile, Enumerations::MediaType_Movie);
            //pass on to the episode if it needs to be refreshed or not
            $episode->refreshVideo = $this->refreshVideo;
            //give the video the show's file path
            $episode->tvShowFilePath = $this->fullPath;
            //if the season that this episode is in does not yet exist, create it
            if (isset($seasonList[$episode->seasonNumber]) == false) {
                $seasonList[$episode->seasonNumber] = [];
            }
            //add this episode to the season array 
            $seasonList[$episode->seasonNumber][$episode->episodeNumber] = $episode;
            //add this episode to the episode list array
            $this->episodes[] = $episode;
        }

        //short the season list ascending
        ksort($seasonList);

        //sort the episode lists in each season ascending
        foreach ($seasonList as &$episodeList) {
            ksort($episodeList);
        }
        //now that the episodes and seasons are in order, add them to arrays instead of 
        //associative arrays so they will display correctly on the roku
        $newSeasonList = [];
        foreach ($seasonList as $season) {
            $newSeason = [];
            foreach ($season as $episode) {
                $newSeason[] = $episode;
            }
            $newSeasonList[] = $newSeason;
        }
        $this->seasons = $newSeasonList;
    }

    /**
     * Checks for 
     * @return type
     */
    function getThumbnailUrl() {
        return $this->getUrl() . "folder.jpg";
    }

    /**
     * Determines the nfo file path. Does NOT check to make sure the file exists.
     * @return type
     */
    function getNfoPath() {
        $nfoPath = "$this->fullPath/tvshow.nfo";
        return $nfoPath;
    }

    protected function loadMetadata($force = false) {
        if ($this->metadataLoaded === false || $force === true) {
            //get the path to the nfo file
            $nfoPath = $this->getNfoPath();
            //verify that the file exists
            if (file_exists($nfoPath) === false) {
                return false;
            }
            //load the nfo file as an xml file 
            //hide any xml errors that may pop up
            // $current_error_reporting = error_reporting();
            // error_reporting(0);
            //open the nfo file
            $m = new DOMDocument();
            $success = $m->load($nfoPath);
            if ($success == false) {
                //fail gracefully, since we will just use dummy information
                return false;
            }

            //parse the nfo file
            $t = $m->getElementsByTagName("title")->item(0)->nodeValue;
            //if the title is empty, use the filename like defined in the constructor
            $this->title = strlen($t) > 0 ? $t : $this->title;
            $this->plot = $m->getElementsByTagName("plot")->item(0)->nodeValue;
            $this->year = $m->getElementsByTagName("year")->item(0)->nodeValue;
            $this->mpaa = $m->getElementsByTagName("mpaa")->item(0)->nodeValue;

            //error_reporting($current_error_reporting);
        }
    }

    function prepForJsonification() {
        parent::prepForJsonification();
        foreach ($this->episodes as $episode) {
            $episode->prepForJsonification();
        }
    }

    static function getNextEpisodeToWatch($tvSeriesVideoId) {
        //this function is not operational
        //get the list of all tv episodes for this series
        //get the video id of the last episode watched. 

        $dbMan = DatabaseManager::getInstance();
        $query = "SELECT VIDEO_ID, ID FROM USER_WATCHES_VIDEO WHERE TV_SERIES_VIDEO_ID=$tvSeriesVideoId AND DATE_WATCHED = (SELECT MAX(DATE_WATCHED) FROM USER_WATCHES_VIDEO WHERE TV_SERIES_VIDEO_ID= $tvSeriesVideoId)";
        $lastVideoIdWatched = $dbMan->getSingleItemQuery($query);

        //if we didn't find anything from the query above, that means we haven't watched this series before. 
        //we need to set the last watched season episode string to 000.0000 so that the next episode we will find should be the first episode in the series.
        if ($lastVideoIdWatched == null || $lastVideoIdWatched == -1) {
            $lastWatchedSeasonEpisodeString = "000.0000";
        } else {

            //now get the season and episode number of the last watched episode
            $lastWatchedEpisodeInfo = $dbMan->getData("SELECT SEASON_NUMBER, EPISODE_NUMBER FROM TV_EPISODE WHERE VIDEO_ID = $lastVideoIdWatched");
            $lastWatchedEpisodeNumber = $lastWatchedEpisodeInfo[0]["EPISODE_NUMBER"];
            $lastWatchedSeasonNumber = $lastWatchedEpisodeInfo[0]["SEASON_NUMBER"];
            //smash the season and episode numbers together as a string, so we can just do a string comparison
            $lastWatchedSeasonEpisodeString = str_pad($lastWatchedSeasonNumber, 3, "0", STR_PAD_LEFT) . "." . str_pad($lastWatchedEpisodeNumber, 4, "0", STR_PAD_LEFT);
        }
        //now that we have the info about the last episode watched, we need to get the list of all tv episodes that this series has 
        $query = "SELECT VIDEO_ID, SEASON_NUMBER, EPISODE_NUMBER FROM TV_EPISODE WHERE TV_SERIES_VIDEO_ID = $tvSeriesVideoId ";
        $thisSeriesEpisodeList = $dbMan->getData($query);
        //if there are no episodes associated with this series, nothing more can be done.
        if (count($thisSeriesEpisodeList) < 1) {
            return -1;
        }
        $resultVideoId = -1;
        //any valid value should be smaller than this initial value
        $resultSeasonEpisodeString = "999.9999";
        //spin through the list of all episodes in this series and find the next episode
        foreach ($thisSeriesEpisodeList as $episode) {
            //smash the season and episode numbers together as a string, so we can just do a string comparison
            $sNum = $episode["SEASON_NUMBER"];
            $eNum = $episode["EPISODE_NUMBER"];
            $seasonEpisodeString = str_pad($sNum, 3, "0", STR_PAD_LEFT) . "." . str_pad($eNum, 4, "0", STR_PAD_LEFT);
            //if the current string is greater than the last watched episode
            if (strcmp($lastWatchedSeasonEpisodeString, $seasonEpisodeString) < 0) {
                //now that we have determined that the current string is larger than the last watched episode, 
                //find the smallest one of those
                if (strcmp($seasonEpisodeString, $resultSeasonEpisodeString) < 0) {
                    //we have found a string that is smaller than our previous result, so keep that as the new next episode
                    $resultVideoId = $episode["VIDEO_ID"];
                    $resultSeasonEpisodeString = $seasonEpisodeString;
                }
            }
        }

        //if we have a valid video id, then we were successful in finding the next episode to play.
        if ($resultVideoId != -1) {
            return $resultVideoId;
        } else {

            //we didn't find a valid video to play. this  that we must be at the end of the season. spin through and find the first episode.
            $resultVideoId = -1;
            //any valid value should be smaller than this initial value
            $resultSeasonEpisodeString = "000.0000";
            //spin through the list of all episodes in this series and find the next episode
            foreach ($thisSeriesEpisodeList as $episode) {
                //smash the season and episode numbers together as a string, so we can just do a string comparison
                $sNum = $episode["SEASON_NUMBER"];
                $eNum = $episode["EPISODE_NUMBER"];
                $seasonEpisodeString = str_pad($sNum, 3, "0", STR_PAD_LEFT) . "." . str_pad($eNum, 4, "0", STR_PAD_LEFT);
                //if the current string is greater than the last watched episode
                if (strcmp($lastWatchedSeasonEpisodeString, $seasonEpisodeString) < 0) {
                    //now that we have determined that the current string is larger than the last watched episode, 
                    //find the smallest one of those
                    if (strcmp($seasonEpisodeString, $resultSeasonEpisodeString) < 0) {
                        //we have found a string that is smaller than our previous result, so keep that as the new next episode
                        $resultVideoId = $episode["VIDEO_ID"];
                        $resultSeasonEpisodeString = $seasonEpisodeString;
                    }
                }
            }
            //return the results. if no video was found, this number is -1. 
            return $resultVideoId;
        }
    }

    function getFolderName(){
        return pathinfo($this->fullPath, PATHINFO_FILENAME);
    }
    function getMetadataFetcher() {
        $t = new TvShowMetadataFetcher();
        $t->searchByTitle($this->getFolderName());
        return $t;
    }

}
?>
