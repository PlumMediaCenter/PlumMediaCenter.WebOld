<?php

include_once("SimpleImage.class.php");
include_once("Enumerations.class.php");

class Video {

    const SdImageWidth = 110; //110x150
    const HdImageWidth = 210; // 210x270

    public $baseUrl;
    public $basePath;
    public $fullPath;
    protected $mediaType;
    protected $metadata;
    public $title;
    public $plot = "";
    public $year;
    public $url;
    public $sdPosterUrl;
    public $hdPosterUrl;
    public $mpaa = "N/A";
    public $actorList = [];
    public $generatePosterMethod;
    
    function __construct($baseUrl, $basePath, $fullPath) {
        //save the important stuff
        $this->baseUrl = $baseUrl;
        $this->basePath = $basePath;
        $this->fullPath = $fullPath;

        //calculate anything extra that is needed
        $this->url = $this->encodeUrl($this->getUrl());
        $this->sdPosterUrl = $this->encodeUrl($this->getSdPosterUrl());
        $this->hdPosterUrl = $this->encodeUrl($this->getHdPosterUrl());

        $this->title = $this->getVideoName();
        $this->generatePosterMethod = $this->getGeneratePosterMethod();
        $this->generatePosters();
    }

    public function update() {
        //__construct($this->baseUrl, $this->basePath, $this->fullPath);
    }

    function getGeneratePosterMethod() {
        if (isset($_GET["generatePosters"])) {
            return $_GET["generatePosters"];
        } else {
            return Enumerations::GeneratePosters_None;
        }
    }

    function getMediaType() {
        return $this->mediaType;
    }

    /**
     * Generates the sd and hd images for this video's poster based on the generate posters method. (if none, no poster is generated, if missing, only missing posters
     * are generated. if all, then all posters are re-generated
     */
    function generatePosters() {
        switch ($this->generatePosterMethod) {
            case Enumerations::GeneratePosters_None:
                break;
            case Enumerations::GeneratePosters_Missing:
                //if the SD poster does not exist, generate it
                if (!file_exists($this->getSdPosterPath())) {
                    $this->generateSdPoster();
                }
                //if the HD poster does not exist, generate it
                if (!file_exists($this->getHdPosterPath())) {
                    $this->generateHdPoster();
                }
                break;
            case Enumerations::GeneratePosters_All:
                $this->generateSdPoster();
                $this->generateHdPoster();
                break;
        }
    }

    /**
     * Determine if there is a poster for this video
     * @return boolean - true if the poster exists, false if it does not
     */
    public function posterExists() {
        return file_exists($this->getPosterPath());
    }

    private function getVideoName() {
        //For now, just return the filename without the extension.
        return pathinfo($this->fullPath, PATHINFO_FILENAME);
    }

    protected function getUrl() {
        $relativePath = str_replace($this->basePath, "", $this->fullPath);
        $url = $this->baseUrl . $relativePath;
        //encode the url and then restore the forward slashes and colons
        return $url;
    }

    protected function encodeUrl($url) {
        return str_replace(" ", "%20", $url);
    }

    protected function getFullPathToContainingFolder() {
        return pathinfo($this->fullPath, PATHINFO_DIRNAME) . "/";
    }

    protected function getFullUrlToContainingFolder() {
        $dirname = pathinfo($this->url, PATHINFO_DIRNAME);
        return $dirname . "/";
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

    public function hasMetadata() {
        //get the path to the nfo file
        $nfoPath = $this->getNfoPath();
        //verify that the file exists
        if (file_exists($nfoPath) === false) {
            return false;
        } else {
            return true;
        }
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
        if ($this->mediaType == Enumerations::MediaType_Movie) {
            $this->year = $m->getElementsByTagName("year")->item(0)->nodeValue;
        } else {
            $y = $m->getElementsByTagName("premiered")->item(0);
            if ($y != null) {
                $this->year = $y->nodeValue;
            }
        }
        $this->mpaa = $m->getElementsByTagName("mpaa")->item(0)->nodeValue;

        //specify a maximum number of actors to include
        $maxActorNumber = 4;
        $actorNodeList = $m->getElementsByTagName("actor");
        foreach ($actorNodeList as $actorNode) {
            if (count($this->actorList) > $maxActorNumber) {
                break;
            }
            $actor = [];
            $nameItem = $actorNode->getElementsByTagName("name")->item(0);
            $actor["name"] = $nameItem != null ? $nameItem->nodeValue : "";
            $roleItem = $actorNode->getElementsByTagName("role")->item(0);
            $actor["role"] = $roleItem != null ? $roleItem->nodeValue : "";
            //if we have either an actor name or role, add this actor
            if ($actor["name"] != "" || $actor["role"] != "") {
                $this->actorList[] = $actor;
            }
        }
        //error_reporting($current_error_reporting);
    }

    function getPosterPath() {
        return $this->getFullPathToContainingFolder() . "folder.jpg";
    }

    /**
     * Determines whether or not the SD poster exists on disk
     */
    function getSdPosterExists() {
        if (file_exists($this->getSdPosterPath())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determines whether or not the HD poster exists on disk
     */
    function getHdPosterExists() {
        if (file_exists($this->getHdPosterPath())) {
            return true;
        } else {
            return false;
        }
    }

    function getSdPosterPath() {
        return $this->getFullPathToContainingFolder() . "folder.sd.jpg";
    }

    function getHdPosterPath() {
        return $this->getFullPathToContainingFolder() . "folder.hd.jpg";
    }

    function getPosterUrl() {
        return $this->getFullUrlToContainingFolder() . "folder.jpg";
    }

    function getSdPosterUrl() {
        return $this->getFullUrlToContainingFolder() . "folder.sd.jpg";
    }

    function getHdPosterUrl() {
        return $this->getFullUrlToContainingFolder() . "folder.hd.jpg";
    }

    /**
     * Generates an poster that is sized to the SD image specifications for the roku standard movie grid layout
     * The existing aspect ratio is retained
     * @param type $width
     * @return boolean - true if successful, false if file doesn't exist or failure

     */
    public function generateSdPoster($width = Video::SdImageWidth) {
        $posterPath = $this->getPosterPath();
        if (file_exists($posterPath)) {
            $image = new SimpleImage();
            if (strpos($posterPath, "Jwoww") > 0) {
                $i = 0;
            }
            //load the image
            try {
                $success = $image->load($posterPath);

                //resize the image
                $image->resizeToWidth($width);
                //re-save the image as folder-small.jpg
                $image->save($this->getSdPosterPath());
            } catch (ErrorException $e) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Generates an poster that is set to the HD image specifications for the roku standard movie grid layout. 
     * The existing aspect ratio is retained
     * @param type $width - optional width to override the standard. 
     * @return boolean - true if successful, false if file doesn't exist or failure
     */
    function generateHdPoster($width = Video::HdImageWidth) {
        $posterPath = $this->getPosterPath();
        if (file_exists($posterPath)) {
            $image = new SimpleImage();

            try {
                //load the image
                $image->load($this->getPosterPath());
                //resize the image
                $image->resizeToWidth($width);
                //re-save the image as folder-small.jpg
                $image->save($this->getHdPosterPath());
            } catch (ErrorException $e) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

}

?>
