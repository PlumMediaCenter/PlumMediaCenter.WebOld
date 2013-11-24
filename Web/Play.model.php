<?php

include_once(dirname(__FILE__) . "/code/Video.class.php");
include_once(dirname(__FILE__) . "/code/Playlist.class.php");

class PlayModel extends Model {

    public $videoList = [];
    public $posterUrl;
    public $videoId;

    function init($pVideoId, $playlistName) {
        $v = Video::GetVideo($pVideoId);
        $videoId = $v->getVideoId();
        //if this is a tv show, we want to watch the next episode. get the next episode
        if ($v->getMediaType() == Enumerations::MediaType_TvShow) {
            $v = $v->nextEpisode();
            $videoId = $v->getVideoId();
        }
        $this->videoUrl = $v->url;
        $this->posterUrl = $v->sdPosterUrl;
        $this->videoId = $v->videoId;
        $this->startSeconds = Video::GetVideoStartSeconds($videoId);

        //if a playlist was specified, load all videos from that playlist onto the page
        if ($playlistName != null) {
            $p = Playlist::LoadPlaylistFromDb(Security::GetUsername(), $playlistName);
            $this->videoList = $p->getPlaylistVideos();
        }
        //if this is a tv episode or tv show and we are NOT in playlist mode, get the list of remaining 
        //episodes in the show
        else if ($v->getMediaType() == Enumerations::MediaType_TvEpisode) {
            $tvShow = $v->getTvShowObject();
            $this->videoList = $tvShow->remainingEpisodes($v);
        } else {
            $this->videoList[] = $v;
        }
    }

}

?>
