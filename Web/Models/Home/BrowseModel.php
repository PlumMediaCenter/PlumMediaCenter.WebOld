<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Home;

class BrowseModel extends \Models\BaseModel {

    public $videos;

    function __construct() {
        $this->videos = array();
    }

    public function process() {
        include_once(basePath() . '/Code/Video/DbVideo/DbMovie.php');
        $mediaType = \Enumerations\MediaType::Movie;
        $videos = \orm\Video::find('all', array('conditions' => array('media_type in(?)', $mediaType)));
        foreach ($videos as $video) {
            $vid  = new \DbMovie($video->videoId);
            $vid->setVideoRecord($video);
            $this->videos[] = $vid;
            
        }
    }

}
