<?php

include_once("Video.class.php");

class TvEpisode extends Video {

    const EpisodeSdImageWidth = 140; //140x94
    const EpisodeHdImageWidth = 210; // 210x158

    public $seasonNumber;
    public $episodeNumber;
    public $showName;
    public $showFilePath;

    function __construct($baseUrl, $basePath, $fullPath) {
        parent::__construct($baseUrl, $basePath, $fullPath);
        $this->mediaType = Enumerations::MediaType_TvEpisode;
        $this->seasonNumber = $this->getSeasonNumber();
        $this->episodeNumber = $this->getEpisodeNumber();
        //load all of the information from the metadata file, if it exists
        $this->loadMetadata();
        $this->showName = $this->getShowName();
        $this->showFilePath = "$this->basePath$this->showName/";
    }

    function getShowName() {
        $str = str_replace($this->basePath, "", $this->fullPath);
        $arr = explode("/", $str);
        return $arr[0];
    }
    
    function getTvShowVideoId(){
        return Queries::getVideoIdByPath($this->tvShowFilePath);
    }

    /**
     * Overrides the parent function in order to generate the standard size for tv episode tiles
     */
    function generatePosters() {
        if (isset($_GET["generatePosters"])) {
            $this->generateSdPoster(TvEpisode::EpisodeSdImageWidth);
            $this->generateHdPoster(TvEpisode::EpisodeHdImageWidth);
        }
    }

    function getPosterUrl($imgExt = "jpg") {
        //the poster is located in the same directory as the file, named the same except for the extension
        $Url = $this->getUrl();
        $filename = pathinfo($this->fullPath, PATHINFO_FILENAME);
        $ext = pathinfo($this->fullPath, PATHINFO_EXTENSION);
        $filenameAndExt = "$filename.$ext";
        //replace the 
        return str_replace($filenameAndExt, "$filename.$imgExt", $Url);
    }

    function getPosterPath($imgExt = "jpg") {
        //the poster is located in the same directory as the file, named the same except for the extension
        $filename = pathinfo($this->fullPath, PATHINFO_FILENAME);
        $ext = pathinfo($this->fullPath, PATHINFO_EXTENSION);
        $filenameAndExt = "$filename.$ext";
        //replace the 
        return str_replace($filenameAndExt, "$filename.$imgExt", $this->fullPath);
    }

    function getSdPosterPath() {
        return $this->getPosterPath("sd.jpg");
    }

    function getHdPosterPath() {
        return $this->getPosterPath("hd.jpg");
    }

    function getSdPosterUrl() {
        return $this->getPosterUrl("sd.jpg");
    }

    function getHdPosterUrl() {
        return $this->getPosterUrl("hd.jpg");
    }

    function getEpisodeNumber() {
        $episodeRegexPatterns = array(
            '/(?<=s\d{2}e)\d{2}(?=\.)/', // foo.s01e01.*
            '/(?<=s\d{2}\.e)\d{2}(?=\.)/', //foo.s01.e01.*
            '/(?<=\.s\d{2}_e)\d\d(?=\.)/', //foo.s01_e01.*
            '/(?<=_\[s\d\d\]_\[e)\d\d(?=\]_)/' // foo_[s01]_[e01]_*
        );
        //spin through each of the possible episode regex values. if we find a match using one of them, we are done
        foreach ($episodeRegexPatterns as $pattern) {
            $results = null;
            //preg_match($pattern, $this->pathToVideo, $results);
            preg_match($pattern, $this->fullPath, $results);
            if ($results != null) {
                if (count($results) > 0) {
                    return intval($results[0]);
                }
            }
        }
        return -1;
    }

    /**
     * These are the possible ways of naming the tv episode file
     * foo.s01e01.* 
     * foo.s01.e01.*  
     * foo.s01_e01.*  
     * foo_[s01]_[e01]_*  
     * foo.1x01.* //ignored
     * foo.101.* //ignored
     */
    function getSeasonNumber() {
        $regexPatterns = array(
            '/(?<=\.s)\d{2}(?=e\d{2})/', // foo.s01e01.*
            '/(?<=\.s)\d{2}(?=\.e\d{2})/', //foo.s01.e01.*
            '/(?<=\.s)\d{2}(?=_e\d{2})/', //foo.s01_e01.*
            '/(?<=_\[s)\d{2}(?=\]_\[e\d{2})/' // foo_[s01]_[e01]_*
        );
        //spin through each of the possible season regex values. if we find a match using one of them, we are done
        foreach ($regexPatterns as $pattern) {
            $results = null;
            //preg_match($pattern, $this->pathToVideo, $results);
            preg_match($pattern, $this->fullPath, $results);
            if ($results != null) {
                if (count($results) > 0) {
                    return intval($results[0]);
                }
            }
        }
        return -1;
    }
    
    function writeToDb(){
        parent::writeToDb();
        $videoId = $this->getVideoId();
        $tvShowVideoId = $this->getTvShowVideoId();
        if($tvShowVideoId == -1){
            $k = 1;
        }
        Queries::insertTvEpisode($videoId,$tvShowVideoId , $this->seasonNumber, $this->episodeNumber);
        
    }
}

?>
