<?php

include_once(dirname(__FILE__) . "/code/Video.class.php");
include_once(dirname(__FILE__) . "/code/Playlist.class.php");

class PlayModel extends Model {

    public $playType = Enumerations::PlayType_Single;
    public $playlistName = "";
    public $video = null;
    public $startSeconds = null;

    function initPlaylist($playlistName) {
        $this->playType = Enumerations::PlayType_Playlist;

        //if a playlist was specified, load all videos from that playlist onto the page
        if ($playlistName != null) {
            $this->playlistName = $playlistName;
            $v = Playlist::GetFirstVideo(Security::GetUsername(), $playlistName);
            if ($v->mediaType == Enumerations::MediaType_TvShow) {
                //grab the next tv episode from this tv show
                $this->video = TvShow::GetNextEpisodeToWatch($v->videoId);
            } else {
                $this->video = $v;
            }
        }
    }

    function init($pVideoId) {
        $v = Video::GetVideo($pVideoId);
        //if this is a tv show, we want to watch the next episode. get the next episode
        if ($v->getMediaType() == Enumerations::MediaType_TvShow) {
            $v = $v->nextEpisode();
            $this->playType = Enumerations::PlayType_TvShow;
        } else if ($v->getMediaType() == Enumerations::MediaType_TvEpisode) {
            $this->playType = Enumerations::PlayType_TvShow;
        }

        $v->getVideoId();
        $this->startSeconds = $v->videoStartSeconds();
        $this->video = $v;
    }

}

?>
