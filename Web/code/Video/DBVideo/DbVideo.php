<?php

include_once(dirname(__file__) . '/../../Interfaces/iVideo.php');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This class wraps a database video object. 
 * @author bplumb
 */
abstract class DbVideo implements iVideo {

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
    public function getGenreRecords(){
        if($this->genreRecords === null){
            $this->genreRecords = \orm\VideoGenre::all(array('conditions'=> "video_id = $this->videoId"));
        }
        return $this->genreRecords;
    }

    static function Delete($videoId) {
        //delete every VideoGenre record referencing this video
        \orm\VideoGenre::table()->delete(array('video_id' => array($videoId)));

        //delete every watchVideo record referencing this video
        \orm\WatchVideo::table()->delete(array('video_id' => array($videoId)));

        //finally, delete the video itself
        \orm\Video::table()->delete(array('video_id' => array($videoId)));
        
    }

    /* iVideo functions */

    public function videoId() {
        return $this->videoId;
    }

    function title() {
        return $this->getVideoRecord()->title;
    }

    function plot() {
        return $this->getVideoRecord()->plot;
    }

    function mpaa() {
        return $this->getVideoRecord()->mpaa;
    }

    function path() {
        return $this->getVideoRecord()->path;
    }

    function sourcePath() {
        return $this->getVideoRecord()->videoSourcePath;
    }
    
    function sourceUrl(){
        return $this->getVideoRecord()->videoSourceUrl;
    }
    
    function metadataLoadedFromNfo(){
        return $this->getVideoRecord()->metadataLoadedFromNfo;
    }
    
    function poster(){
        return $this->getVideoRecord();
    }

    /**
     * The list of genres for this video
     * @return string[] - a list of the genres for this video
     */
    function genres() {
        $genreRecords = $this->getGenreRecords();
        $genres = [];
        foreach($genreRecords as $genreRecord){
            $genres[] = $genreRecord->name;
        }
        return $genres;
    }

    /**
     * The date the video was originally released
     * @return \DateTime
     */
    function releaseDate() {
        return $this->getVideoRecord()->releaseDate;
    }

    /**
     * The running time of the video in seconds
     */
    function runningTimeSeconds() {
        return $this->getVideoRecord()->runningTimeSeconds;
    }

    /* End iVideo functions */
}
