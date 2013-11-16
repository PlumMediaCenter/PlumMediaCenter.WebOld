<?php

require_once(dirname(__FILE__) . '/code/Playlist.class.php');

class PlaylistModel extends Model {

    public $title = "Playlist";
    public $playlists;

    function __construct() {
        $this->playlists = Playlist::GetPlaylistNames(Security::GetUsername());
    }

}

?>
