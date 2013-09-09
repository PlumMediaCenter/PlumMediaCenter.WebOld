<?php
include_once(dirname(__FILE__) . "/code/Video.class.php");
class PlayModel extends Model{
    public $videoUrl;
    public $posterUrl;
    public $videoId;
    function init($videoId){
        $v = Video::loadFromDb($videoId);
        $this->videoUrl = $v->url;
        $this->posterUrl = $v->sdPosterUrl;
        $this->videoId = $v->videoId;
    }
}

?>
