<?php

include_once('MetadataFetcher.class.php');
include_once('TvShowMetadataFetcher.class.php');
include_once(basePath() . '/Code/lib/TVDB/TVDB.class.php');

class TvEpisodeMetadataFetcher extends MetadataFetcher {

    private $tvShowObject = null;
    private $episodeObject = null;
    private $episodeNumber = null;
    private $seasonNumber = null;

    /**
     * Search by season name and by preset season and episode numbers. you MUST set the episode and season numbers before calling this
     * @param string $title - the show title
     */
    function searchByTitle($title) {
        $this->searchByShowNameAndSeasonAndEpisodeNumber($title, $this->seasonNumber, $this->episodeNumber);
    }

    /**
     * Search by show id and by preset season and episode numbers. you MUST set the episode and season numbers before calling this
     * @param string $id - the id of the show that this episode belongs to
     */
    function searchById($id) {
        $this->searchByShowIdAndSeasonAndEpisodeNumber($id, $this->seasonNumber, $this->episodeNumber);
    }

    public function searchByShowNameAndEpisodeId($showName, $id) {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchByTitle($showName);
        $this->episodeObject = $this->tvShowObject->getEpisodeById($id);
    }

    public function searchByShowNameAndSeasonAndEpisodeNumber($showName, $seasonNumber, $episodeNumber) {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchByTitle($showName);
        if ($this->tvShowObject == null) {
            $this->episodeObject = null;
        } else {
            $this->episodeObject = $this->tvShowObject->getEpisode($seasonNumber, $episodeNumber);
        }
    }

    public function searchByShowIdAndEpisodeId($showId, $id) {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchById($showId);
        $this->episodeObject = $this->tvShowObject->getEpisodeById($id);
    }

    public function searchByShowIdAndSeasonAndEpisodeNumber($showId, $seasonNumber, $episodeNumber) {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchById($showId);
        $this->episodeObject = $this->tvShowObject->getEpisode($seasonNumber, $episodeNumber);
    }

    /**
     * Set the episode number of the episode to be fetched
     * @param int $eNum
     */
    public function setEpisodeNumber($eNum) {
        $this->episodeNumber = $eNum;
    }

    /**
     * Set the season number of the episode to be fetched
     * @param int $sNum
     */
    public function setSeasonNumber($sNum) {
        $this->seasonNumber = $sNum;
    }

    public function actors() {
        return $this->tvShowObject->actors;
    }

    public function directors() {
        return $this->episodeObject->directors;
    }

    public function dayOfTheWeek() {
        return $this->tvShowObject->dayOfWeek;
    }

    public function episode() {
        return $this->episodeObject->episode;
    }

    public function firstAired() {
        return $this->episodeObject->firstAired;
    }

    public function genres() {
        return $this->tvShowObject->genres;
    }

    public function guestStars() {
        return $this->episodeObject->guestStars;
    }

    public function id() {
        return $this->episodeObject->id;
    }

    public function imdbId() {
        return $this->episodeObject->imdbId;
    }

    public function mpaa() {
        return $this->tvShowObject->contentRating;
    }

    public function plot() {
        return $this->episodeObject->overview;
    }

    public function posterUrl() {
        return $this->episodeObject->thumbnail;
    }

    public function rating() {
        return $this->tvShowObject->rating;
    }

    public function season() {
        return $this->episodeObject->season;
    }

    public function showName() {
        return $this->tvShowObject->seriesName;
    }

    public function showId() {
        return $this->tvShowObject->id;
    }

    public function title() {
        return $this->episodeObject->name;
    }

    public function writers() {
        return $this->episodeObject->writers;
    }

}

?>
