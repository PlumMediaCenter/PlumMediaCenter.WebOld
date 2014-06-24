<?php

include_once(dirname(__file__) . '/../../Interfaces/iVideo.php');
include_once(dirname(__file__) . '/../FileSystemVideo/FileSystemVideo.php');


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This class wraps a database video object. 
 * @author bplumb
 */
class DbVideo implements iVideo {

    private $videoId;

    /* ORM objects */
    private $videoRecord;
    private $genreRecords;

    /* End ORM Objects */

    public function __construct($videoId) {
        $this->videoId = $videoId;
    }

    /**
     * Setter for the Orm video record
     * @param \orm\Video $videoRecord
     */
    public function setVideoRecord($videoRecord) {
        $this->videoRecord = $videoRecord;
    }

    /**
     * Getter for the orm video record
     * @return \orm\Video
     */
    public function getVideoRecord() {
        if ($this->videoRecord === null) {
            $this->videoRecord = \orm\Video::find($this->videoId);
        }
        return $this->videoRecord;
    }

    /**
     * Returns the list of orm genre records
     * @return \orm\Genre[]
     */
    public function getGenreRecords() {
        if ($this->genreRecords === null) {
            $this->genreRecords = \orm\VideoGenre::all(array('conditions' => "video_id = $this->videoId"));
        }
        return $this->genreRecords;
    }

    public function posterLastModifiedDate() {
        return $this->getVideoRecord()->posterLastModifiedDate;
    }

    public function metadataLastModifiedDate() {
        return $this->getVideoRecord()->metadataLastModifiedDate;
    }

    /* iVideo functions */

    public function getMediaType() {
        return $this->getVideoRecord()->mediaType;
    }

    public function setMediaType($mediaType) {
        $this->getVideoRecord()->media_type = $mediaType;
    }

    public function getVideoId() {
        return $this->videoId;
    }

    public function setVideoId($value) {
        $this->videoId = $value;
        $vr = $this->getVideoRecord();
        $vr->video_id = $value;
        $this->setVideoRecord($vr);
    }

    function getTitle() {
        return $this->getVideoRecord()->title;
    }

    function setTitle($value) {
        $vr = $this->getVideoRecord();
        $vr->title = $value;
        $this->setVideoRecord($vr);
    }

    function getPlot() {
        return $this->getVideoRecord()->plot;
    }

    function setPlot($value) {
        $vr = $this->getVideoRecord();
        $vr->plot = $value;
        $this->setVideoRecord($vr);
    }

    function getMpaa() {
        return $this->getVideoRecord()->mpaa;
    }

    function setMpaa($value) {
        $vr = $this->getVideoRecord();
        $vr->mpaa = $value;
        $this->setVideoRecord($vr);
    }

    function getPath() {
        return $this->getVideoRecord()->path;
    }

    function setPath($value) {
        $vr = $this->getVideoRecord();
        $vr->path = $value;
        $this->setVideoRecord($vr);
    }

    function getSourcePath() {
        return $this->getVideoRecord()->video_source_path;
    }

    function setSourcePath($value) {
        $vr = $this->getVideoRecord();
        $vr->video_source_path = $value;
        $this->setVideoRecord($vr);
    }

    function getSourceUrl() {
        return $this->getVideoRecord()->video_source_url;
    }

    function setSourceUrl($value) {
        $vr = $this->getVideoRecord();
        $vr->video_source_url = $value;
        $this->setVideoRecord($vr);
    }

    function getPosterUrl() {
        return FileSystemVideo::posterDestinationUrl() . "/$this->video_id.jpg";
    }

    /**
     * The list of genres for this video
     * @return string[] - a list of the genres for this video
     */
    function getGenres() {
        $genreRecords = $this->getGenreRecords();
        $genres = [];
        foreach ($genreRecords as $genreRecord) {
            $genres[] = $genreRecord->name;
        }
        return $genres;
    }

    /**
     * Sets the list of genres associated with this video
     * @param string[] $genres
     */
    function setGenres($genres) {
        $genreRecords = $this->getGenreRecords();

        //delete any old genres associated with this video
        /* @var $genreRecord \orm\VideoGenre  */
        foreach ($genreRecords as $genreRecord) {
            $genreRecord->delete();
        }
        $this->genreRecords = [];
        foreach ($genres as $genre) {
            $g = new \orm\VideoGenre();
            $g->genre_name = $genre;
            $g->video_id = $this->getVideoId();
            $g->save();
            $this->genreRecords[] = $g;
        }
    }

    /**
     * The date the video was originally released
     * @return \DateTime
     */
    function getReleaseDate() {
        return $this->getVideoRecord()->release_date;
    }

    function setReleaseDate($value) {
        $vr = $this->getVideoRecord();
        $vr->release_date = $value;
        $this->setVideoRecord($vr);
    }

    /**
     * The running time of the video in seconds
     */
    function getRunningTimeSeconds() {
        return $this->getVideoRecord()->running_time_seconds;
    }

    function setRunningTimeSeconds($value) {
        $vr = $this->getVideoRecord();
        $vr->running_time_seconds = $value;
        $this->setVideoRecord($vr);
    }

    /* End iVideo functions */
}
