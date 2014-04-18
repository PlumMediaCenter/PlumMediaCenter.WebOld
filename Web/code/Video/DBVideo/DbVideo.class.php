<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbVideo
 *
 * @author bplumb
 */
class DbVideo {
    
    static function Delete($videoId) {
        //delete every VideoGenre record referencing this video
        \orm\VideoGenre::table()->delete(array('video_id' => array($videoId)));

        //delete every watchVideo record referencing this video
        \orm\WatchVideo::table()->delete(array('video_id' => array($videoId)));

        //finally, delete the video itself
        \orm\Video::table()->delete(array('video_id' => array($videoId)));
    }

}
