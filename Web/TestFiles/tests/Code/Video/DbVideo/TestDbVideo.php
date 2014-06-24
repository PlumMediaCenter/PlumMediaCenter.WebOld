<?php

include_once(dirname(__FILE__) . '/../../../../../Code/Video/DbVideo/DbVideo.php');

class TestDbVideo extends UnitTestCase {

    function setUp() {
        
    }

    function testWriteToDatabase(){
        $v = new DbVideo(1);
    }

}
