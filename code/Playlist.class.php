<?php

include_once("database/Queries.class.php");
include_once("Video.class.php");

class Playlist {

    private $name;
    private $username;
    private $items = [];

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
     * Returns the list of playlist items in their proper order
     */
    function getPlaylistItems() {
        return $this->items;
    }

    /**
     * Clear the local in memory list. This doese not clear from the database
     */
    function clear() {
        $this->items = [];
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
        $item = new PlaylistItem(Playlist::UniqueItemId(), $videoId);
        if ($index == null) {
            $this->items[] = $item;
        } else {
            //insert the videoId into the middle of the playlist
            array_splice($this->items, $index, 0, [$item]);
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

    function indexOf($itemId) {
        foreach ($this->items as $index => $item) {
            if ($item->itemId == $itemId) {
                return $index;
            }
        }
        return -1;
    }

    /**
     * Removes the item with the specified id
     * @param integer $itemId
     */
    function removeItemById($itemId) {
        $index = $this->indexOf($itemId);
        $this->remove($index);
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
        //clear the list of items
        $this->items = [];
        $items = Queries::getPlaylistItems($this->username, $this->name);
        foreach ($items as $itemRow) {
            $item = new PlaylistItem($itemRow->item_id, $itemRow->video_id);
            $this->items[] = $item;
        }
        return true;
    }

    function writeToDb() {
        $this->clearFromDb();
        Queries::AddPlaylistName($this->username, $this->name);
        return Queries::setPlaylistItems($this->username, $this->name, $this->items);
    }

    /**
     * Adds a playlist or overwrites an existing playlist
     * @param string $username - the username of the user who owns the playlist
     * @param string $playlistName - the name of the playlist 
     * @return boolean - success or failure.
     */
    static function AddPlaylist($username, $playlistName, $videoIds) {
        $pl = new Playlist($username, $playlistName);
        $pl->addRange($videoIds);
        return $pl->writeToDb();
    }

    static function GetFirstVideo($username, $playlistName) {
        $p = new Playlist($username, $playlistName);
        $p->loadFromDb();
        $videoIds = $p->getPlaylist();
        $videoId = (isset($videoIds[0]) == true) ? $videoIds[0] : -1;
        $video = Video::GetVideo($videoId);
        return $video;
    }

    static function RemoveItem($username, $playlistName, $playlistItemId) {
        $p = new Playlist($username, $playlistName);
        $success = $p->removeItemById($playlistItemId);
        return $success;
    }

    /**
     * Delete a playlist 
     * @param string $username - the username of the user who owns the playlist
     * @param string $playlistName - the name of the playlist 
     * @return boolean
     */
    static function DeletePlaylist($username, $playlistName) {
        return Queries::DeletePlaylist($username, $playlistName) && Queries::DeletePlaylistName($username, $playlistName);
    }

    static function LoadPlaylistFromDb($username, $name) {
        $p = new Playlist($username, $name);
        $p->loadFromDb();
        return $p;
    }

    function getPlaylistVideos() {
        $playlist = [];
        foreach ($this->videoIds as $videoId) {
            $video = Video::GetVideo($videoId);
            if ($video != false) {
                $video->prepForJsonification();
                $playlist[] = $video;
            }
        }
        return $playlist;
    }

    static function GetPlaylists($username) {
        $names = Playlist::getPlaylistNames($username);
        $lists = [];
        foreach ($names as $name) {
            $p = Playlist::LoadPlaylistFromDb($username, $name);
            $lists[$name] = $p->getPlaylistVideos();
        }
        return $lists;
    }

    static function GetPlaylistNames($username) {
        return Queries::getPlaylistNames($username);
    }

    /**
     * Generates a unique id based on the timestamp
     * @return int
     */
    static function UniqueItemId() {
        //get the current time
        $time = time();
        //wait until we get a NEW time, thus guarenteeing that this will always return a unique value
        while ($time == time()) {
            
        }
        $id = time();
        return $id;
    }

}

class PlaylistItem {

    public $itemId;
    public $videoId;

    function __construct($itemId, $videoId) {
        $this->itemId = $itemId;
        $this->videoId = $videoId;
    }

}

?>
