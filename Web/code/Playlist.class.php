<?php

include_once("database/Queries.class.php");
include_once("Video.class.php");


class Playlist {

    private $name;
    private $username;
    private $videoIds = [];

    function __construct($username, $name) {
        $this->name = $name;
        $this->username = $username;
    }

    function getUsername() {
        return $this->username;
    }

    function getPlaylistName() {
        return $this->name;
    }

    /**
     * Returns the list of videoIds in their proper order
     */
    function getPlaylist() {
        return $this->videoIds;
    }

    /**
     * Clear the local in memory list. This doese not clear from the database
     */
    function clear() {
        $this->videoIds = [];
    }

    /**
     * Clear the list of playlist items from the database
     */
    function clearFromDb() {
        Queries::clearPlaylist($this->username, $this->name);
    }

    /**
     * Adds a videoId to the playlist. If an index is provided, the videoId is added at the specified index and the other items are shifted.
     * @param int $videoId - the videoId to be added to the playlist
     * @param int $index - the index at which the videoId should be added. Zero based. Other items will be shifted
     */
    function add($videoId, $index = null) {
        if ($index == null) {
            $this->videoIds[] = $videoId;
        } else {
            //insert the videoId into the middle of the playlist
            array_splice($this->videoIds, $index, 0, $videoId);
        }
    }

    /**
     * Add an array of videoIds
     * @param int[] $videoIdList
     */
    function addRange($videoIdList) {
        foreach ($videoIdList as $videoId) {
            $this->add($videoId);
        }
    }

    /**
     * Remove the video at the specified index
     * @param int $index
     */
    function remove($index) {
        //if an item at that index exists, remove it.
        if (isset($this->videoIds[$index])) {
            unset($this->videoIds[$index]);
            $this->videoIds = array_values($this->videoIds);
            return true;
        } else {
            return false;
        }
    }

    function loadFromDb() {
        //clear the list of video ids
        $this->videoIds = [];
        $this->videoIds = Queries::getPlaylistVideoIds($this->username, $this->name);
    }

    function writeToDb() {
        $this->clearFromDb();
        return Queries::setPlaylistItems($this->username, $this->name, $this->videoIds);
    }

    static function LoadPlaylistFromDb($username, $name) {
        Queries::getPlaylist($username, $name);
        $p = new Playlist($username, $name);
        $p->loadFromDb();
        return $p;
    }

    function getPlaylistVideos() {
        $playlist = [];
        foreach ($this->videoIds as $videoId) {
            $video = Video::loadFromDb($videoId);
            $playlist[] = $video;
        }
        return $playlist;
    }

}

?>
