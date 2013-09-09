<?php

include_once(dirname(__FILE__) . "/MetadataFetcher.class.php");
include_once(dirname(__FILE__) . "/TvShowMetadataFetcher.class.php");
include_once(dirname(__FILE__) . "/../TVDB/TVDB.class.php");

class TvEpisodeMetadataFetcher extends MetadataFetcher {

    private $tvShowObject = null;
    private $episodeObject = null;

    public function searchByShowNameAndEpisodeId($showName, $id) {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchByTitle($showName);
        $this->episodeObject = $this->tvShowObject->getEpisodeById($id);
    }

    public function searchByShowNameAndSeasonAndEpisodeNumber($showName, $seasonNumber, $episodeNumber) {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchByTitle($showName);
        $this->episodeObject = $this->tvShowObject->getEpisode($seasonNumber, $episodeNumber);
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
