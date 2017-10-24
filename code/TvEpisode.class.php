<?php

include_once(dirname(__FILE__) . "/Video.class.php");
include_once(dirname(__FILE__) . "/MetadataFetcher/TvEpisodeMetadataFetcher.class.php");
include_once(dirname(__FILE__) . "/NfoReader/TvEpisodeNfoReader.class.php");

class TvEpisode extends Video {

    const EpisodeSdImageWidth = 140; //140x94
    const EpisodeHdImageWidth = 210; // 210x158

    public $seasonNumber;
    public $episodeNumber;
    public $showName;
    public $showFilePath;
    public $tvShow;

    function __construct($videoSourceUrl, $videoSourcePath, $fullPath) {
        parent::__construct($videoSourceUrl, $videoSourcePath, $fullPath);
        $this->mediaType = Enumerations::MediaType_TvEpisode;
        $this->seasonNumber = $this->getSeasonNumber();
        $this->episodeNumber = $this->getEpisodeNumber();
        $this->showName = $this->getShowName();
        $this->showFilePath = str_replace("\\", "/", realpath("$this->videoSourcePath/$this->showName/")) . "/";
    }

    function getBlankPosterName() {
        return "BlankEpisode";
    }

    function getFullEpisodeName() {
        return $this->showName . ' S' . $this->seasonNumber . ':E' . $this->episodeNumber . ' ' . $this->title;
    }

    /**
     * This tv episode cannot provide all important information by itself. It needs the tv show object to do that for it. As such, 
     * this function provides a way to load the tv show from within this tv episode if it has not already been set by the owning tv show
     * when this episode object was loaded
     */
    public function getTvShowObject() {
        $this->tvShow = isset($this->tvShow) ? $this->tvShow : null;
        if ($this->tvShow == null) {
            $this->tvShow = new TvShow($this->videoSourceUrl, $this->videoSourcePath, $this->showFilePath);
        }
        return $this->tvShow;
    }

    protected function getLengthInSecondsFromMetadata()
    {
        if (isset($this->runtime)) {
            return $this->runtime;
        }
        //make sure the metadata has been loaded
        $tvShow = $this->getTvShowObject();
        return $tvShow->_runtime;
    }

    function getShowName() {
       return TvEpisode::GetTvShowName($this->videoSourcePath, $this->fullPath);
    }
    
    static function GetTvShowName($videoSourcePath, $fullPath){
         $str = str_replace($videoSourcePath, "", $fullPath);
        //if the first character is a slash, remove it
        if (strpos($str, "/") === 0) {
            $str = substr($str, 1);
        }
        $arr = explode("/", $str);
        return $arr[0];
    }

    function getTvShowVideoIdFromVideoTable() {
        return Queries::getVideoIdByVideoPath($this->showFilePath);
    }

    function getTvShowVideoIdFromTvEpisodeTable() {
        return Queries::getTvShowVideoIdFromEpisodeTable($this->videoId);
    }

    /**
     * Overrides the parent function in order to generate the standard size for tv episode tiles
     */
    function generatePosters() {
        $this->generateSdPoster(TvEpisode::EpisodeSdImageWidth);
        $this->generateHdPoster(TvEpisode::EpisodeHdImageWidth);
    }

    function getPosterUrl($imgExt = "jpg") {
        //the poster is located in the same directory as the file, named the same except for the extension
        $url = $this->getUrl();
        $filename = pathinfo($this->fullPath, PATHINFO_FILENAME);
        $ext = pathinfo($this->fullPath, PATHINFO_EXTENSION);
        $filenameAndExt = "$filename.$ext";
        //replace the video file name and extension with the image one.
        $url = str_replace($filenameAndExt, "$filename.$imgExt", $url);
        //replace the url encoded filename and extension with the image one
        $url = str_replace(Video::EncodeUrl($filenameAndExt), "$filename.$imgExt", $url);
        return Video::EncodeUrl($url);
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
        return Video::EncodeUrl($this->getPosterUrl("sd.jpg"));
    }

    function getHdPosterUrl() {
        return Video::EncodeUrl($this->getPosterUrl("hd.jpg"));
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

    function prepForJsonification() {
        parent::prepForJsonification();
        unset($this->tvShow);
    }

    function writeToDb() {
        $success = parent::writeToDb();
        $videoId = $this->getVideoId();
        $tvShowVideoId = $this->getTvShowVideoIdFromVideoTable();
        if ($tvShowVideoId == -1) {
            $k = 1;
        }
        $success = $success && Queries::insertTvEpisode($videoId, $tvShowVideoId, $this->seasonNumber, $this->episodeNumber);
        return $success;
    }

    /**
     * Load any TvEpisode specific metadata here. It will be called from the parent loadMetadata function
     */
    protected function loadCustomMetadata() {
        //we are assuming that the reader has already been loaded with the metadata file, since this function should only be called from 
        $reader = $this->getNfoReader();
        $this->year = strlen($reader->aired) >= 4 ? substr($reader->aired, 0, 4) : null;
        return null;
    }

    function getNfoReader() {
        if ($this->nfoReader == null) {
            $this->nfoReader = new TvEpisodeNfoReader();
        }
        return $this->nfoReader;
    }

    /**
     * Returns a new instance of the metadata fetcher for this video type. 
     */
    public function getMetadataFetcherClass() {
        $m = new TvEpisodeMetadataFetcher();
        $m->setEpisodeNumber($this->episodeNumber);
        $m->setSeasonNumber($this->seasonNumber);
        return $m;
    }

    /**
     * Returns a Video Metadata Fetcher. If we have the Online Video Database ID, use that. Otherwise, use the folder name.
     * @param boolean $refresh - if set to true, the metadata fetcher is recreated. otehrwise, a cached one is used if present
     * @param int $onlineVideoDatabaseId - the id of the online video database used to reference this video. 
     *                                     If an id was present, use it. If not, see if there is a global one for the class. if not, search by title
     * @return TvEpisodeMetadataFetcher
     */
    protected function getMetadataFetcher($refresh = false, $onlineVideoDatabaseId = null) {
        //If an id was present, use it. If not, see if there is a global one for the class. if not, search by title
        $id = $this->onlineVideoDatabaseId;
        $id = $onlineVideoDatabaseId != null ? $onlineVideoDatabaseId : $id;
        if ($this->metadataFetcher == null || $refresh == true) {
            include_once(dirname(__FILE__) . "/MetadataFetcher/MovieMetadataFetcher.class.php");
            $this->metadataFetcher = $this->getMetadataFetcherClass();
            if ($id != null) {
                $this->metadataFetcher->searchById($id);
            } else {
                $this->metadataFetcher->searchByTitle($this->getShowName());
            }
        }
        return $this->metadataFetcher;
    }

    /**
     * Determines the nfo file path. Does NOT check to make sure the file exists.
     * Returns the path for an nfo file named the same as the video file. i.e. MyTvEpisode.avi, MyTvEpisode.nfo
     * @return type
     */
    function getNfoPath() {
        $p = $this->fullPath;
        $nfoPath = pathinfo($p, PATHINFO_DIRNAME) . "/" . pathinfo($p, PATHINFO_FILENAME) . ".nfo";
        return $nfoPath;
    }

    /**
     * Goes to TheTvDb and retrieves all available information about this tv episode. 
     * It then stores that information into an .nfo file named the same as the video file name .
     * Deletes any previous metadata files that exist, BEFORE anything else. 
     */
    function fetchMetadata($showTvdbId=null) {
        if($showTvdbId === null){
            $showTvdbId = $this->onlineVideoDatabaseId;
        }
        //this tv episode shouldn't take longer than 3 minutes to run. if it does, then php will cancel the running of this script.
        set_time_limit(180);
        $metadataDestination = $this->getNfoPath();
        //if an old metadata file already exists, delete it.
        if (file_exists($metadataDestination) == true) {
            //delete the file
            unlink($metadataDestination);
        }
        $e = $this->getMetadataFetcher(false, $showTvdbId);

        $title = $e->title();
        $rating = $e->rating();
        $seasonNumber = $e->season();
        $episodeNumber = $e->episode();
        $plot = $e->plot();
        $thumb = $e->posterUrl();
        $mpaa = $e->mpaa();
        $writers = implode(",", $e->writers());
        $directorList = implode(",", $e->directors());
        $actorList = $e->actors();
        $firstAired = $e->firstAired();


        //create the xml nfo doc
        $doc = new DOMDocument();
        $doc->formatOutput = true;
        //  <episodedetails>
        $episodeDetailsNode = $doc->createElement("episodedetails");
        //      <title>
        $l2Node = $doc->createElement("title");
        $l3Node = $doc->createTextNode($title);
        $l2Node->appendChild($l3Node);
        //      </title>
        $episodeDetailsNode->appendChild($l2Node);
        //      <rating>
        $l2Node = $doc->createElement("rating");
        $l3Node = $doc->createTextNode($rating);
        $l2Node->appendChild($l3Node);
        //      </rating>
        $episodeDetailsNode->appendChild($l2Node);
        //      <season>
        $l2Node = $doc->createElement("season");
        $l3Node = $doc->createTextNode($seasonNumber);
        $l2Node->appendChild($l3Node);
        //      </season>
        $episodeDetailsNode->appendChild($l2Node);
        //      <episode>
        $l2Node = $doc->createElement("episode");
        $l3Node = $doc->createTextNode($episodeNumber);
        $l2Node->appendChild($l3Node);
        //      </episode>
        $episodeDetailsNode->appendChild($l2Node);
        //      <plot>
        $l2Node = $doc->createElement("plot");
        $l3Node = $doc->createTextNode($plot);
        $l2Node->appendChild($l3Node);
        //      </plot>
        $episodeDetailsNode->appendChild($l2Node);
        //      <thumb>
        $l2Node = $doc->createElement("thumb");
        $l3Node = $doc->createTextNode($thumb);
        $l2Node->appendChild($l3Node);
        //      </thumb>
        $episodeDetailsNode->appendChild($l2Node);
        //      <playcount>
        $l2Node = $doc->createElement("playcount");
        $l3Node = $doc->createTextNode(0);
        $l2Node->appendChild($l3Node);
        //      </playcount>
        $episodeDetailsNode->appendChild($l2Node);
        //      <lastplayed>
        $l2Node = $doc->createElement("lastplayed");
        $l3Node = $doc->createTextNode("");
        $l2Node->appendChild($l3Node);
        //      </lastplayed>
        $episodeDetailsNode->appendChild($l2Node);
        //      <credits>
        $l2Node = $doc->createElement("credits");
        $l3Node = $doc->createTextNode($writers);
        $l2Node->appendChild($l3Node);
        //      </credits>
        $episodeDetailsNode->appendChild($l2Node);
        //      <director>
        $l2Node = $doc->createElement("director");
        $l3Node = $doc->createTextNode($directorList);
        $l2Node->appendChild($l3Node);
        //      </director>
        $episodeDetailsNode->appendChild($l2Node);
        //      <aired>
        $l2Node = $doc->createElement("aired");
        $l3Node = $doc->createTextNode($firstAired);
        $l2Node->appendChild($l3Node);
        //      </aired>
        $episodeDetailsNode->appendChild($l2Node);
        //      <premiered>
        $l2Node = $doc->createElement("premiered");
        $l3Node = $doc->createTextNode($firstAired);
        $l2Node->appendChild($l3Node);
        //      </premiered>
        $episodeDetailsNode->appendChild($l2Node);
        //      <studio>
        $l2Node = $doc->createElement("studio");
        $l3Node = $doc->createTextNode("");
        $l2Node->appendChild($l3Node);
        //      </studio>
        $episodeDetailsNode->appendChild($l2Node);
        //      <mpaa>
        $l2Node = $doc->createElement("mpaa");
        $l3Node = $doc->createTextNode($mpaa);
        $l2Node->appendChild($l3Node);
        //      </mpaa>
        $episodeDetailsNode->appendChild($l2Node);
        //      <epbookmark>
        $l2Node = $doc->createElement("epbookmark");
        $l3Node = $doc->createTextNode("0");
        $l2Node->appendChild($l3Node);
        //      </epbookmark>
        $episodeDetailsNode->appendChild($l2Node);
        //      <displayseason>
        $l2Node = $doc->createElement("displayseason");
        $l3Node = $doc->createTextNode($seasonNumber);
        $l2Node->appendChild($l3Node);
        //      </displayseason>
        $episodeDetailsNode->appendChild($l2Node);
        //      <displayepisode>
        $l2Node = $doc->createElement("displayepisode");
        $l3Node = $doc->createTextNode($episodeNumber);
        $l2Node->appendChild($l3Node);
        //      </displayepisode>
        $episodeDetailsNode->appendChild($l2Node);
        //      <actor>
        $l2Node = $doc->createElement("actor");
        foreach ($actorList as $actor) {
            //      <name>
            $l3Node = $doc->createElement("name");
            $l4Node = $doc->createTextNode($actor);
            $l3Node->appendChild($l4Node);
            //      </name>
            $l2Node->appendChild($l3Node);
            //      <role>
            $l3Node = $doc->createElement("role");
            $l4Node = $doc->createTextNode("");
            $l3Node->appendChild($l4Node);
            //      </role>
            $l2Node->appendChild($l3Node);
        }
        //if the actor list is empty, add an empty string to the actor node, so we get the whole ending actor tag
        if (count($actorList) < 1) {
            $l3Node = $doc->createTextNode("");
            $l2Node->appendChild($l3Node);
        }
        //      </actor>
        $episodeDetailsNode->appendChild($l2Node);
        //      <fileinfo>
        $l2Node = $doc->createElement("fileinfo");
        //          <streamdetails>
        $l3Node = $doc->createElement("streamdetails");
        //              <audio>
        $l4Node = $doc->createElement("audio");
        //                  <channels>
        $l5Node = $doc->createElement("channels");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </channels>
        $l4Node->appendChild($l5Node);
        //                  <codec>
        $l5Node = $doc->createElement("codec");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </codec>
        $l4Node->appendChild($l5Node);
        //              </audio>
        $l3Node->appendChild($l4Node);
        //              <video>
        $l4Node = $doc->createElement("video");
        //                  <aspect>
        $l5Node = $doc->createElement("aspect");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </aspect>
        $l4Node->appendChild($l5Node);
        //                  <codec>
        $l5Node = $doc->createElement("codec");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </codec>
        $l4Node->appendChild($l5Node);
        //                  <durationinseconds>
        $l5Node = $doc->createElement("durationinseconds");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </durationinseconds>
        $l4Node->appendChild($l5Node);
        //                  <height>
        $l5Node = $doc->createElement("height");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </height>
        $l4Node->appendChild($l5Node);
        //                  <language>
        $l5Node = $doc->createElement("language");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </language>
        $l4Node->appendChild($l5Node);
        //                  <longlanguage>
        $l5Node = $doc->createElement("longlanguage");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </longlanguage>
        $l4Node->appendChild($l5Node);
        //                  <scantype>
        $l5Node = $doc->createElement("scantype");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </scantype>
        $l4Node->appendChild($l5Node);
        //                  <width>
        $l5Node = $doc->createElement("width");
        $l6Node = $doc->createTextNode("");
        $l5Node->appendChild($l6Node);
        //                  </width>
        $l4Node->appendChild($l5Node);
        //              </video>
        $l3Node->appendChild($l4Node);
        //          </streamdetails>
        $l2Node->appendChild($l3Node);
        //      </fileinfo>
        $episodeDetailsNode->appendChild($l2Node);
        //</episodeDetails>
        $doc->appendChild($episodeDetailsNode);
        ob_start();
        echo $doc->saveXML();


        //get the xml file contents
        $contents = ob_get_contents();
        //close the output buffer
        ob_end_clean();
        //write the contents to the destination file
        $bytesWritten = file_put_contents("$metadataDestination", $contents);
        $success = $bytesWritten !== false;
        return $success;
    }

}

?>
