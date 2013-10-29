<?php

include_once(dirname(__FILE__) . "/code/Video.class.php");

class PlayModel extends Model {

    public $videoUrl;
    public $posterUrl;
    public $videoId;

    function init($videoId) {
        $v = Video::GetVideo($videoId);
        //if this is a tv show, we want to watch the next episode. get the next episode
        if($v->getMediaType() == Enumerations::MediaType_TvShow){
            $v = $v->nextEpisode();
        }
        $this->videoUrl = $v->url;
        $this->posterUrl = $v->sdPosterUrl;
        $this->videoId = $v->videoId;
        $this->startSeconds = Video::GetVideoStartSeconds($videoId);
    }

}

?>
