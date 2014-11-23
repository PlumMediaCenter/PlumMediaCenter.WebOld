<?php

include_once(dirname(__FILE__) . '/../../MetadataFetcher/TvShowMetadataFetcher.class.php');
include_once(dirname(__FILE__) . '/../../NfoReader/TvShowNfoReader.class.php');

/**
 * Description of FileSystemTvShow
 *
 * @author bplumb
 */
class FSTvShow extends FSVideo {

    function __construct($videoSourceUrl, $videoSourcePath, $fullPath) {
        parent::__construct($videoSourceUrl, $videoSourcePath, $fullPath);
        //the media type of this video is movie
        $this->mediaType = Enumerations\MediaType::TvShow;
        //retrieve the poster path if the video has a poster in its folder with it
        $this->posterPath = $this->getExistingPosterPath();
    }

    /**
     * Returns the filename of the file provided to the video
     * @return string - the filename of the file provided to the video
     */
    protected function getFilename() {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * Returns the full url to the tv show folder
     * @return string - the full url to the tv show folder
     */
    public function getUrl() {
        return $this->getContainingFolderUrl() . '/' . pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * Gets the url to the poster for this video. This will ALWAYS return a url. So if 
     * this video does not have a poster, the url returned will point to the blank poster.
     * @return string - the url to the poster for this video. 
     */
    protected function getPosterUrl() {
        $posterFilename = $posterUrl = null;
        //if no poster exists
        if ($posterFilename === null) {
            $posterUrl = $this->getBlankPosterBaseUrl() . '/' . $this->getBlankPosterName();
        } else {
            $containingFolderUrl = $this->getContainingFolderUrl();
            $posterUrl = $containingFolderUrl . '/' . $posterFilename;
        }
    }

    /**
     * Retrieves the name of the blank poster that will be used if no poster was found for this video
     */
    protected function getBlankPosterName() {
        return "BlankPoster.jpg";
    }

    function getNfoReader() {
        if ($this->nfoReader == null) {
            $this->nfoReader = new TvShowNfoReader();
            $this->nfoReader->loadFromFile($this->getExistingNfoPath());
        }
        return $this->nfoReader;
    }

    /**
     * Returns a Video Metadata Fetcher. Search by title
     * @return MovieMetadataFetcher
     */
    protected function getMetadataFetcher() {
        $metadataFetcher = new MovieMetadataFetcher();
        $foldername = pathinfo(dirname($this->path), PATHINFO_FILENAME);
        $metadataFetcher->searchByTitle($foldername);
        return $metadataFetcher;
    }

}