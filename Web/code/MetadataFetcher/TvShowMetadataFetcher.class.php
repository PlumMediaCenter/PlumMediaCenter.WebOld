<?php

include_once(dirname(__FILE__) . '/MetadataFetcher.class.php');
include_once(dirname(__FILE__) . '/../lib/TVDB/TVDB.class.php');

class TvShowMetadataFetcher extends MetadataFetcher {

    private $tvShowObject;

    function searchByTitle($title, $year = null) {
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchByTitle($title);
        $this->fetchSuccess = $this->tvShowObject != null;
        return $this->fetchSuccess;
    }

    function searchById($id) {
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchById($id);
        $this->fetchSuccess = $this->tvShowObject != null;
        return $this->fetchSuccess;
    }

    static function GetSearchByTitle($title) {
        //query the TvDb to find a tv show that matches this folder's title. 
        $tvShowsList = TV_Shows::search($title);
        if (count($tvShowsList) < 1) {
            //throw new Exception("No tv shows found for '$title'");
            return null;
        } else {
            //just assume that the first search result is the one that we want.
            return $tvShowsList[0];
        }
    }

    static function GetSearchById($id) {
        $tvShow = TV_Shows::findById($id);
        //if we found the tv show l
        if ($tvShow == false) {
            //echo "No TV show found using TvDB ID '" . $this->onlineMovieDatabaseId . "'<br/>";
            // throw new Exception("No tv shows found with id of $id");
        } else {
            return $tvShow;
        }
    }

    function actors() {
        return $this->fetchSuccess ? $this->tvShowObject->actors : null;
    }

    function bannerUrl() {
        return $this->fetchSuccess ? $this->tvShowObject->bannerUrl : null;
    }

    function airTime() {
        return $this->fetchSuccess ? $this->tvShowObject->airTime : null;
    }

    function dayOfWeek() {
        return $this->fetchSuccess ? $this->tvShowObject->dayOfWeek : null;
    }

    function firstAired() {
        return $this->fetchSuccess ? $this->tvShowObject->firstAired : null;
    }

    function releaseDate() {
        $firstAired = $this->firstAired();
        return $firstAired === null ? null : new DateTime($firstAired);
    }

    function genres() {
        return $this->fetchSuccess ? $this->tvShowObject->genres : null;
    }

    function imdbId() {
        return $this->fetchSuccess ? $this->tvShowObject->imdbId : null;
    }

    function title() {
        return $this->fetchSuccess ? $this->tvShowObject->seriesName : null;
    }

    function mpaa() {
        return $this->fetchSuccess ? $this->tvShowObject->contentRating : null;
    }

    function network() {
        return $this->fetchSuccess ? $this->tvShowObject->network : null;
    }

    function posterUrl() {
        return $this->fetchSuccess ? $this->tvShowObject->posterUrl : null;
    }

    function plot() {
        return $this->fetchSuccess ? $this->tvShowObject->overview : null;
    }

    function rating() {
        return $this->fetchSuccess ? $this->tvShowObject->rating : null;
    }

    function runtime() {
        return $this->fetchSuccess ? $this->tvShowObject->runtime : null;
    }
    
    function runningTimeSeconds(){
        $runtime = $this->runtime();
        return $runtime === null? null: $runtime * 60;
    }

    function seriesName() {
        return $this->fetchSuccess ? $this->tvShowObject->seriesName : null;
    }

    function status() {
        return $this->fetchSuccess ? $this->tvShowObject->status : null;
    }

    function tmdbId() {
        return $this->fetchSuccess ? $this->tvShowObject->id : null;
    }

}

?>
