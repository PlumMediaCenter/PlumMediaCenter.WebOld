<?php

class TvShow extends Video {

    public $seasons;
    public $episodeCount = 0;

    function __construct($baseUrl, $basePath, $fullPath) {
        parent::__construct($baseUrl, $basePath, $fullPath);
        $this->mediaType = Video::MediaType_TvSeries;
        //generate all tv episode items
        $this->seasons = $this->getSeasonList();


        //load all of the information from the metadata file, if it exists
        $this->loadMetadata();
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

    function getSeasonList() {
        $seasonList = [];
        //get the list of videos from this tv series 
        $videosList = getVideosFromDir($this->fullPath);
        $this->episodeCount = count($videosList);
        //spin through every folder in the source location
        foreach ($videosList as $fullPathToFile) {
            //create a new Episode object
            $episode = new TvEpisode($this->baseUrl, $this->basePath, $fullPathToFile, Video::MediaType_Movie);

            //if the season that this episode is in does not yet exist, create it
            if (isset($seasonList[$episode->seasonNumber]) == false) {
                $seasonList[$episode->seasonNumber] = [];
            }
            //add this episode to the season array 
            $seasonList[$episode->seasonNumber][$episode->episodeNumber] = $episode;
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
        return $newSeasonList;
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

    protected function loadMetadata() {
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

?>
