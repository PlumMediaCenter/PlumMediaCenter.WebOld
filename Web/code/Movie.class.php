<?php
include_once("Video.class.php");
class Movie extends Video {

    function __construct($baseUrl, $basePath, $fullPath) {
        parent::__construct($baseUrl, $basePath, $fullPath);
        $this->mediaType = Enumerations::MediaType_Movie;
        $this->loadMetadata();
    }

    /**
     * Determiens the nfo path for this video. If movie.nfo is present, that file will be used. If not, then filename.nfo will be used.
     * @return string
     */
    function getNfoPath() {

        $movieNfoPath = $this->getFullPathToContainingFolder() . "movie.nfo";
        if (file_exists($movieNfoPath) === true) {
            return $movieNfoPath;
        } else {
            return parent::getNfoPath();
        }
    }

}

?>
