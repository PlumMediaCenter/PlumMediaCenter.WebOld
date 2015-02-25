<?php

include_once(dirname(__FILE__) . "/../config.php");

include_once("MetadataFetcher/TvShowMetadataFetcher.class.php");
include_once("NfoReader/TvShowNfoReader.class.php");

include_once(dirname(__FILE__) . "/../lib/php-mp4info/MP4Info.php");


include_once("Video.class.php");
include_once("TvEpisode.class.php");

class TvShow extends Video {

    public $seasons = [];
    //holds each episode in a list instead of grouped by seasons
    public $episodes = [];
    public $episodeCount = 0;

    function __construct($baseUrl, $basePath, $fullPath) {
        parent::__construct($baseUrl, $basePath, $fullPath);
        //the full path for this video needs to have a slash at the end of it. if it doesn't, then append it
        if (substr($this->fullPath, -1) != "/") {
            $this->fullPath .= "/";
        }
        $this->mediaType = Enumerations::MediaType_TvShow;
    }

    function getUrl() {
        $url = parent::getUrl();
        //the url path for this video needs to have a slash at the end of it. if it doesn't, then append it
        if (substr($url, -1) != "/") {
            $url .= "/";
        }
        return Video::encodeUrl($url);
    }

    /**
     * Forces this show and every episode to retrieve its videoId from the database
     */
    function retrieveVideoIds() {
        $this->getVideoId();
        foreach ($this->episodes as $episode) {
            $episode->getVideoId();
        }
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
                $e = new TvEpisode($episode->videoSourceUrl, $episode->videoSourcePath, $episode->fullPath);
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
        $slash = "";
        //the full path for this video needs to have a slash at the end of it. if it doesn't, then append it
        if (substr($this->fullPath, -1) != "/") {
            $slash = "/";
        }
        $path = $this->fullPath . $slash;
        return $path;
    }

    protected function getFullUrlToContainingFolder() {
        $url = $this->getUrl();
        return Video::encodeUrl($url);
    }

    /**
     * Override this function since no tv show will ever have a file to scan.
     * @return boolean - always will be false for tv shows
     */
    public function getLengthInSecondsFromFile() {
        return false;
    }

    protected function getLengthInSecondsFromMetadata() {
        //make sure the metadata has been loaded
        $this->loadMetadata();
        if ($this->runtime != null) {
            $intMinutes = intval($this->runtime);
            $intSeconds = $intMinutes * 60;
            $this->runtime = $intSeconds;
        }
        return $this->runtime;
    }

    /**
     * Loads the episodes associated with this tv show into this tv show based on information found in the db
     */
    function loadEpisodesFromDatabase() {
        $this->seasons = [];
        $episodeInfoList = Queries::GetTvEpisodeVideoIdsForShow($this->getVideoId());
        foreach ($episodeInfoList as $info) {
            $episode = Video::GetVideo($info->video_id);

            //if no episode was able to be loaded, move on to the next item.
            if ($episode == false) {
                continue;
            }
            //if this season does not exist, create it
            if (isset($this->seasons[$episode->seasonNumber]) == false) {
                $this->seasons[$episode->seasonNumber] = [];
            }
            $this->seasons[$episode->seasonNumber][] = $episode;
            $this->episodes[] = $episode;
        }
    }

    /**
     * Returns an array of all tv episodes AFTER and INCLUDING the current episode
     * @param TvEpisode $tvEpisode
     * @return TvEpisode
     */
    function remainingEpisodes($tvEpisode) {
        //load all tv episodes
        if (count($this->episodes) == 0) {
                $this->loadEpisodesFromDatabase();
        }
        $remainingEpisodes = [];
        $sNum = $tvEpisode->seasonNumber;
        $eNum = $tvEpisode->episodeNumber;
        foreach ($this->episodes as $e) {
            //if episode is in same season and is greater episode number, add it to list
            if ($e->seasonNumber == $sNum && $e->episodeNumber >= $eNum) {
                $remainingEpisodes[] = $e;
            }
            // if episode is greater season, add it to the list
            else if ($e->seasonNumber > $sNum) {
                $remainingEpisodes[] = $e;
            }
        }
        return $remainingEpisodes;
    }

    /**
     * Loads episodes for this tv show from within the folder designated as this tv show's root path
     */
    function loadTvEpisodesFromFilesystem() {
        $seasonList = [];
        //get the list of videos from this tv series 
        $videosList = getVideosFromDir($this->fullPath);

        $this->episodeCount = count($videosList);
        //spin through every folder in the source location
        foreach ($videosList as $fullPathToFile) {
            //create a new Episode object
            $episode = new TvEpisode($this->videoSourceUrl, $this->videoSourcePath, $fullPathToFile, Enumerations::MediaType_Movie);

            $episode->runtime = $this->getLengthInSeconds();
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
     * Load any TvShow specific metadata here. It will be called from the parent loadMetadata function
     * ***DO NOT CALL THIS FUNCTION UNLESS YOU PRELOAD THE NfoReader object with metadata
     */
    protected function loadCustomMetadata() {
        //we are assuming that the reader has already been loaded with the metadata file, since this function should only be called from 
        $reader = $this->getNfoReader();
        $this->year = $reader->year !== null ? $reader->year : "";
        $this->runtime = $reader->runtime;
    }

    /**
     * Determines the nfo file path. Does NOT check to make sure the file exists.
     * @return type
     */
    function getNfoPath() {
        $nfoPath = "$this->fullPath/tvshow.nfo";
        return $nfoPath;
    }

    function getNfoReader() {
        if ($this->nfoReader == null) {
            $this->nfoReader = new TvShowNfoReader();
        }
        return $this->nfoReader;
    }

    function prepForJsonification() {
        parent::prepForJsonification();
        foreach ($this->episodes as $episode) {
            $episode->prepForJsonification();
        }
    }

    /**
     * Determines the next episode to watch based on the watch_video table. 
     * @param int $videoId - the videoId of the tv show that you wish to get the next video
     * @param int $finishedBuffer - the number of seconds that a video must be near to the end of the video in order to retrieve the next episode
     * @return video - false if failure, the next episode if success
     */
    function nextEpisode($finishedBuffer = 45) {
        $episode = TvShow::GetNextEpisodeToWatch($this->videoId);
        return $episode;
    }

    /**
     * Determines the next episode to watch based on the watch_video table. 
     * @param int $videoId - the videoId of the tv show that you wish to get the next video
     * @param int $finishedBuffer - the number of seconds that a video must be near to the end of the video in order to retrieve the next episode
     * @return video - false if failure, the next episode if success
     */
    static function GetNextEpisodeToWatch($videoId, $finishedBuffer = 45) {
        $videoId = TvShow::GetNextEpisodeIdToWatch($videoId, $finishedBuffer);
        return Video::GetVideo($videoId);
    }

    /**
     * Determines the next episode to watch based on the watch_video table. 
     * @param int $videoId - the videoId of the tv show that you wish to get the next video
     * @param int $finishedBuffer - the number of seconds that a video must be near to the end of the video in order to retrieve the next episode
     * @return int  - negative 1 if failure, the video id of the next video if success
     */
    static function GetNextEpisodeIdToWatch($videoId, $finishedBuffer = 45) {
        //load this video
        $v = Video::GetVideo($videoId);
        //the video is a tv episode, get the tv show for that episode
        if ($v->mediaType == Enumerations::MediaType_TvEpisode) {
            $tvShowVideoId = $v->getTvShowVideoIdFromVideoTable();
        } else
        //the video is a tv show. use this video id
        if ($v->mediaType == Enumerations::MediaType_TvShow) {
            $tvShowVideoId = $videoId;
        } else {
            //the video associated with the videoId provided is not a tv episode or tv show, nothing more can be done
            return -1;
        }
        $result = Queries::getLastEpisodeWatched(config::$globalUsername, $tvShowVideoId);
        $lastVideoIdWatched = $result === false ? -1 : $result->video_id;
        $lastWatchedSecondsProgress = $result === false ? 0 : $result->time_in_seconds;
        //if there IS a last video watched, then see if the user hadn't finished it yet
        if ($lastVideoIdWatched !== -1) {

            $lastEpisodeWatched = Video::GetVideo($lastVideoIdWatched);
            //if the last episode watched is 
            if ($lastEpisodeWatched === false) {
                return -1;
            }
            //if the video progress seconds is more than $finishedBuffer seconds away from the end of the video, THIS video isn't finished yet. 
            $videoLengthInSeconds = $lastEpisodeWatched->getLengthInSeconds();
            if ($videoLengthInSeconds === false) {
                //we couldn't determine the lengh of the video from its metadata. 
            } else {
                //if the $lastWatchedSecondsProgress is farther than $finishedBuffer away from the end, return THIS videoId
                if ($lastWatchedSecondsProgress + $finishedBuffer < $videoLengthInSeconds) {
                    return $lastVideoIdWatched;
                }
            }
        }
        //if we didn't find anything from the query above, that means we haven't watched this series before. 
        //we need to set the last watched season episode string to 000.0000 so that the next episode we will find should be the first episode in the series.
        if ($lastVideoIdWatched === null || $lastVideoIdWatched === -1) {
            $lastWatchedSeasonEpisodeString = "000.0000";
        } else {

            //now get the season and episode number of the last watched episode
            $lastWatchedEpisodeInfo = Queries::getTvEpisode($lastVideoIdWatched);
            $lastWatchedEpisodeNumber = $lastWatchedEpisodeInfo->episode_number;
            $lastWatchedSeasonNumber = $lastWatchedEpisodeInfo->season_number;
            //smash the season and episode numbers together as a string, so we can just do a string comparison
            $lastWatchedSeasonEpisodeString = str_pad($lastWatchedSeasonNumber, 3, "0", STR_PAD_LEFT) . "." . str_pad($lastWatchedEpisodeNumber, 4, "0", STR_PAD_LEFT);
        }
        //now that we have the info about the last episode watched, we need to get the list of all tv episodes that this series has 
        $thisSeriesEpisodeList = Queries::GetEpisodesInTvShow($tvShowVideoId);
        //if there are no episodes associated with this series, nothing more can be done.
        if ($thisSeriesEpisodeList === false) {
            return -1;
        }
        $resultVideoId = -1;
        //any valid value should be smaller than this initial value
        $resultSeasonEpisodeString = "999.9999";
        //spin through the list of all episodes in this series and find the next episode
        foreach ($thisSeriesEpisodeList as $episode) {
            //smash the season and episode numbers together as a string, so we can just do a string comparison
            $sNum = $episode->season_number;
            $eNum = $episode->episode_number;
            $seasonEpisodeString = str_pad($sNum, 3, "0", STR_PAD_LEFT) . "." . str_pad($eNum, 4, "0", STR_PAD_LEFT);
            //if the current string is greater than the last watched episode
            if (strcmp($lastWatchedSeasonEpisodeString, $seasonEpisodeString) < 0) {
                //now that we have determined that the current string is larger than the last watched episode, 
                //find the smallest one of those
                if (strcmp($seasonEpisodeString, $resultSeasonEpisodeString) < 0) {
                    //we have found a string that is smaller than our previous result, so keep that as the new next episode
                    $resultVideoId = $episode->video_id;
                    $resultSeasonEpisodeString = $seasonEpisodeString;
                }
            }
        }

        //if we have a valid video id, then we were successful in finding the next episode to play.
        if ($resultVideoId != -1) {
            return $resultVideoId;
        } else {
            //return the LAST episode. This means they have finished the series.
            $v = TvShow::GetLastEpisode($tvShowVideoId);
            return ($v != false) ? $v->videoId : -1;
        }
        //shouldn't make it here, but if we do, return false for failure
        return false;
    }

    /**
     * Returns the first tv episode in this series
     * @param int $tvShowVideoId - the videoId of the tv show
     * @return Video
     */
    public static function GetFirstEpisode($tvShowVideoId) {
        //get the list of all tv episodes in this tv show.
        $e = Queries::GetEpisodesInTvShow($tvShowVideoId);
        $videoId = ($e != false) ? $e[0]->video_id : -1;
        return Video::GetVideo($videoId);
    }

    /**
     * Returns the last tv episode in this series
     * @param int $tvShowVideoId - the videoId of the tv show
     * @return Video
     */
    public static function GetLastEpisode($tvShowVideoId) {
        //get the list of all tv episodes in this tv show.
        $e = Queries::GetEpisodesInTvShow($tvShowVideoId);
        $idx = count($e);
        $videoId = ($e != false) ? $e[$idx - 1]->video_id : -1;
        return Video::GetVideo($videoId);
    }

    function getFolderName() {
        return pathinfo($this->fullPath, PATHINFO_FILENAME);
    }

    /**
     * Returns a new instance of the metadata fetcher for this video type. 
     */
    public function getMetadataFetcherClass() {
        return new TvShowMetadataFetcher();
    }

    /**
     * Goes to TheTvDb and retrieves all available information about this tv episode. 
     * It then stores that information into an .nfo file named the same as the video file name .
     * Deletes any previous metadata files that exist, BEFORE anything else. 
     * @param int $onlineVideoDatabaseId - the id of the online video database used to reference this video. 
     * @return boolean - true if totally successful, false if unsuccessful
     */
    function fetchMetadata($onlineVideoDatabaseId = null) {

        //delete the existing metadata file before starting
        $this->deleteMetadata();

        $s = $this->getMetadataFetcher(true, $onlineVideoDatabaseId);

        //if the metadata fetcher was not able to find a show, nothing more can be done. quit.
        if ($s->getFetchSuccess() == false) {
            return false;
        }

        $title = $s->title();
        $rating = $s->rating();
        $year = $s->firstAired();
        $year = $year->format("Y");
        $premiered = $s->firstAired()->format("Y-m-d");
        $aired = $s->firstAired()->format("Y-m-d");
        $studio = $s->network();
        $plot = $s->plot();
        $runtime = $s->runtime();
        $thumb = $s->posterUrl();
        $mpaa = $s->mpaa();
        $genreList = $s->genres();
        $status = $s->status();
        $trailer = "";
        $actorList = $s->actors();


        ob_start();
        //create the xml nfo doc
        $doc = new DOMDocument();
        $doc->formatOutput = true;
        //<tvshow>
        $tvShowNode = $doc->createElement("tvshow");
        //  <title>
        $titleNode = $doc->createElement("title");
        $titleTextNode = $doc->createTextNode($title);
        $titleNode->appendChild($titleTextNode);
        //  </title>
        $tvShowNode->appendChild($titleNode);
        //  <showtitle>
        $showTitleNode = $doc->createElement("showtitle");
        $showTitleTextNode = $doc->createTextNode($title);
        $showTitleNode->appendChild($showTitleTextNode);
        //  </showtitle>
        $tvShowNode->appendChild($showTitleNode);
        //  <rating>
        $ratingNode = $doc->createElement("rating");
        $ratingTextNode = $doc->createTextNode($rating);
        $ratingNode->appendChild($ratingTextNode);
        //  </rating>
        $tvShowNode->appendChild($titleNode);
        //  <epbookmark>
        $epBookmarkNode = $doc->createElement("epbookmark");
        $epBookmarkTextNode = $doc->createTextNode("");
        $epBookmarkNode->appendChild($epBookmarkTextNode);
        //  </epbookmark>
        $tvShowNode->appendChild($epBookmarkNode);
        //  <year>
        $yearNode = $doc->createElement("year");
        $yearTextNode = $doc->createTextNode($year);
        $yearNode->appendChild($yearTextNode);
        //  </year>
        $tvShowNode->appendChild($yearNode);
        //  <top250>
        $top250Node = $doc->createElement("top250");
        $top250TextNode = $doc->createTextNode("");
        $top250Node->appendChild($top250TextNode);
        //  </top250>
        $tvShowNode->appendChild($top250Node);
        //  <season>
        $seasonNode = $doc->createElement("season");
        $seasonTextNode = $doc->createTextNode("");
        $seasonNode->appendChild($seasonTextNode);
        //  </season>
        $tvShowNode->appendChild($seasonNode);
        //  <episode>
        $episodeNode = $doc->createElement("episode");
        $episodeTextNode = $doc->createTextNode("");
        $episodeNode->appendChild($episodeTextNode);
        //  </episode>
        $tvShowNode->appendChild($episodeNode);
        //  <uniqueid>
        $uniqueidNode = $doc->createElement("uniqueid");
        $uniqueidTextNode = $doc->createTextNode("");
        $uniqueidNode->appendChild($uniqueidTextNode);
        //  </uniqueid>
        $tvShowNode->appendChild($uniqueidNode);
        //  <displayseason>
        $displaySeasonNode = $doc->createElement("displayseason");
        $displaySeasonTextNode = $doc->createTextNode("");
        $displaySeasonNode->appendChild($displaySeasonTextNode);
        //  </season>
        $tvShowNode->appendChild($displaySeasonNode);
        //  <displayepisode>
        $displayEpisodeNode = $doc->createElement("displayepisode");
        $displayEpisodeTextNode = $doc->createTextNode("");
        $displayEpisodeNode->appendChild($displayEpisodeTextNode);
        //  </displayepisode>
        $tvShowNode->appendChild($displayEpisodeNode);
        //  <votes>
        $votesNode = $doc->createElement("votes");
        $votesTextNode = $doc->createTextNode("");
        $votesNode->appendChild($votesTextNode);
        //  </votes>
        $tvShowNode->appendChild($votesNode);
        //  <outline>
        $outlineNode = $doc->createElement("outline");
        $outlineTextNode = $doc->createTextNode("");
        $outlineNode->appendChild($outlineTextNode);
        //  </outline>
        $tvShowNode->appendChild($outlineNode);
        //  <plot>
        $plotNode = $doc->createElement("plot");
        $plotTextNode = $doc->createTextNode($plot);
        $plotNode->appendChild($plotTextNode);
        //  </plot>
        $tvShowNode->appendChild($plotNode);
        //  <tagline>
        $taglineNode = $doc->createElement("tagline");
        $taglineTextNode = $doc->createTextNode("");
        $taglineNode->appendChild($taglineTextNode);
        //  </tagline>
        $tvShowNode->appendChild($taglineNode);
        //  <runtime>
        $runtimeNode = $doc->createElement("runtime");
        $runtimeTextNode = $doc->createTextNode($runtime);
        $runtimeNode->appendChild($runtimeTextNode);
        //  </runtime>
        $tvShowNode->appendChild($runtimeNode);
        //  <mpaa>
        $mpaaNode = $doc->createElement("mpaa");
        $mpaaTextNode = $doc->createTextNode($mpaa);
        $mpaaNode->appendChild($mpaaTextNode);
        //  </thumb>
        $tvShowNode->appendChild($mpaaNode);
        //  <playcount>
        $playcountNode = $doc->createElement("playcount");
        $playcountTextNode = $doc->createTextNode("0");
        $playcountNode->appendChild($playcountTextNode);
        //  </playcount>
        $tvShowNode->appendChild($playcountNode);
        //  <lastplayed>
        $lastplayedNode = $doc->createElement("lastplayed");
        $lastplayedTextNode = $doc->createTextNode("");
        $lastplayedNode->appendChild($lastplayedTextNode);
        //  </lastplayed>
        $tvShowNode->appendChild($lastplayedNode);
        //  <episodeguide>
        $episodeGuideNode = $doc->createElement("episodeguide");
        //      <url>
        $urlNode = $doc->createElement("url");
        $urlNode->setAttribute("cache", "");
        $urlNode->appendChild($doc->createTextNode(""));
        //      </url>
        $episodeGuideNode->appendChild($urlNode);
        //  </episodeguide>
        $tvShowNode->appendChild($episodeGuideNode);
        //  <id>
        $idNode = $doc->createElement("id");
        $idTextNode = $doc->createTextNode("");
        $idNode->appendChild($idTextNode);
        //  </id>
        $tvShowNode->appendChild($idNode);
        //  <genre>
        foreach ($genreList as $genre) {
            //get rid of extra space
            $genre = trim($genre);
            $genreNode = $doc->createElement("genre");
            $genreTextNode = $doc->createTextNode($genre);
            $genreNode->appendChild($genreTextNode);
        }
        //  </genre>
        //  <set>
        $setNode = $doc->createElement("set");
        $setTextNode = $doc->createTextNode("");
        $setNode->appendChild($setTextNode);
        //  </set>
        $tvShowNode->appendChild($setNode);
        //  < premiered>
        $premieredNode = $doc->createElement("premiered");
        $premieredTextNode = $doc->createTextNode($premiered);
        $premieredNode->appendChild($premieredTextNode);
        //  </premiered>
        $tvShowNode->appendChild($premieredNode);
        //  <status>
        $statusNode = $doc->createElement("status");
        $statusTextNode = $doc->createTextNode($status);
        $statusNode->appendChild($statusTextNode);
        //  </status>
        $tvShowNode->appendChild($statusNode);
        //  <code>
        $codeNode = $doc->createElement("code");
        $codeTextNode = $doc->createTextNode("");
        $codeNode->appendChild($codeTextNode);
        //  </code>
        $tvShowNode->appendChild($codeNode);
        //  <aired>
        $airedNode = $doc->createElement("aired");
        $airedTextNode = $doc->createTextNode($aired);
        $airedNode->appendChild($airedTextNode);
        //  </aired>
        $tvShowNode->appendChild($airedNode);
        //  <studio>
        $studioNode = $doc->createElement("studio");
        $studioTextNode = $doc->createTextNode($studio);
        $studioNode->appendChild($studioTextNode);
        //  </studio>
        $tvShowNode->appendChild($studioNode);
        //  <trailer>
        $trailerNode = $doc->createElement("trailer");
        $trailerTextNode = $doc->createTextNode($trailer);
        $trailerNode->appendChild($trailerTextNode);
        //  </trailer>
        $tvShowNode->appendChild($trailerNode);
        //if the actor list is empty, add the actor tag anyway, so media centers don't freak out when they can't find it
        if (count($actorList) === 0) {
            //  <>
            $actorNode = $doc->createElement("actor");
            $actorTextNode = $doc->createTextNode("");
            $actorNode->appendChild($actorTextNode);
            //  </actor>
            $tvShowNode->appendChild($actorNode);
        }
        foreach ($actorList as $actor) {
            //get rid of extra space
            $name = trim($actor);
            //<actor>
            $actorNode = $doc->createElement("actor");
            //  <name>
            $nameNode = $doc->createElement("name");
            $nameTextNode = $doc->createTextNode($name);
            $nameNode->appendChild($nameTextNode);
            //  </name>
            $actorNode->appendChild($nameNode);
            //  <role>
            $roleNode = $doc->createElement("role");
            $roleTextNode = $doc->createTextNode("");
            $roleNode->appendChild($roleTextNode);
            //  </role>
            $actorNode->appendChild($roleNode);
            //  <thumb>
            $thumbNode = $doc->createElement("thumb");
            $thumbTextNode = $doc->createTextNode("");
            $thumbNode->appendChild($thumbTextNode);
            //  </thumb>
            $actorNode->appendChild($thumbNode);
            //</actor>
            $tvShowNode->appendChild($actorNode);
        }
        //  <resume>
        $resumeNode = $doc->createElement("resume");
        //      <position>
        $positionNode = $doc->createElement("position");
        $positionTextNode = $doc->createTextNode("0.000000");
        $positionNode->appendChild($positionTextNode);
        //      </position>
        $resumeNode->appendChild($positionNode);
        //      <total>
        $totalNode = $doc->createElement("total");
        $totalTextNode = $doc->createTextNode("");
        $totalNode->appendChild($totalTextNode);
        $resumeNode->appendChild($totalNode);
        //  </resume>
        $tvShowNode->appendChild($resumeNode);
        //  <dateadded>
        $dateAddedNode = $doc->createElement("dateadded");
        $dateAddedTextNode = $doc->createTextNode("");
        $dateAddedNode->appendChild($dateAddedTextNode);
        //  </dateadded>
        $tvShowNode->appendChild($dateAddedNode);
        //  <thumb>
        $thumbNode = $doc->createElement("thumb");
        $thumbTextNode = $doc->createTextNode($thumb);
        $thumbNode->appendChild($thumbTextNode);
        //  </thumb>
        $tvShowNode->appendChild($thumbNode);
        //</tvshow>
        $doc->appendChild($tvShowNode);
        ob_start();
        echo $doc->saveXML();
        //get the xml file contents
        $contents = ob_get_contents();
        //close the output buffer
        ob_end_clean();

        //write the contents to the destination file
        $bytesWritten = file_put_contents($this->getNfoPath(), $contents);
        $success = $bytesWritten !== false;
        return $success;
    }

}

?>
