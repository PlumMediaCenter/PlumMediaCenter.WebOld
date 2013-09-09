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
            $episode = new TvEpisode($this->videoSourceUrl, $this->videoSourcePath, $fullPathToFile, Enumerations::MediaType_Movie);
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
        $result = Queries::getLastEpisodeWatched('user', $tvSeriesVideoId);
        $lastVideoIdWatched = $result === false ? -1 : $result->video_id;
        $lastWatchedSecondsProgress = $result === false ? 0 : $result->time_in_seconds;

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
        $thisSeriesEpisodeList = Queries::getEpisodesInTvShow($tvSeriesVideoId);
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

            //we didn't find a valid video to play. this  that we must be at the end of the season. spin through and find the first episode.
            $resultVideoId = -1;
            //any valid value should be smaller than this initial value
            $resultSeasonEpisodeString = "000.0000";
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
            //return the results. if no video was found, this number is -1. 
            return $resultVideoId;
        }
        //shouldn't make it here, but if we do, return false for failure
        return false;
    }

    function getFolderName() {
        return pathinfo($this->fullPath, PATHINFO_FILENAME);
    }

    function getMetadataFetcher() {
        $t = new TvShowMetadataFetcher();
        $t->searchByTitle($this->getFolderName());
        return $t;
    }

    /**
     * Goes to TheTvDb and retrieves all available information about this tv episode. 
     * It then stores that information into an .nfo file named the same as the video file name .
     * Deletes any previous metadata files that exist, BEFORE anything else. 
     */
    function fetchMetadata() {

        $metadataDestination = $this->getNfoPath();
        //if an old metadata file already exists, delete it.
        if (file_exists($metadataDestination) == true) {
            //delete the file
            unlink($metadataDestination);
        }
        $s = $this->getMetadataFetcher();


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
        file_put_contents("$metadataDestination", $contents);
        return true;
    }

}

?>
