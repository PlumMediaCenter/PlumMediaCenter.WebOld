<?php

include_once(dirname(__FILE__) . '/DbVideo.php');

/**
 * This class wraps a database movie object. 
 * @author bplumb
 */
class DbMovie extends DbVideo {

    public function __construct($videoId) {
        parent::__construct($videoId);
    }

    static function Delete($videoId) {
        //delete every VideoGenre record referencing this video
        \orm\VideoGenre::table()->delete(array('video_id' => array($videoId)));

        //delete every watchVideo record referencing this video
        \orm\WatchVideo::table()->delete(array('video_id' => array($videoId)));

        //finally, delete the video itself
        \orm\Video::table()->delete(array('video_id' => array($videoId)));
    }

}
