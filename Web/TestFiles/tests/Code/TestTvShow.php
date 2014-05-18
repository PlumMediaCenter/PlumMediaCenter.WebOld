<?php

require_once(dirname(__FILE__) . '/../../../code/Video.class.php');

class TestTvShow extends UnitTestCase {

    private $video;
    private $videoSourceUrl;
    private $videoSourcePath;
    private $fullPath;

    function setUp() {
        
    }

    function loadVideo() {
        $this->video = new TvShow($this->videoSourceUrl, $this->videoSourcePath, $this->fullPath);
    }

    function loadTvShow($halfPath) {
        $this->videoSourceUrl = "http://localhost/videos/tv shows/";
        $this->videoSourcePath = dirname(__FILE__) . "/../videos/tv shows/";
        $this->fullPath = dirname(__FILE__) . "/../videos/tv shows/$halfPath";
        $this->loadVideo();
        return $this->video;
    }

    function testFetchMetadata() {

        //
        //check that the fetcher correctly fetches a video by folder name
        //
        //delete any metadata that exists in the folder
        @unlink(dirname(__FILE__) . "/../videos/tv shows/The Dick Van Dyke Show/tvshow.nfo");
        $v = $this->loadTvShow("/The Dick Van Dyke Show/");

        $this->assertFalse(is_file($v->getNfoPath()));
        $v->fetchMetadata();
        $this->assertTrue(is_file($v->getNfoPath()));

        //
        //check that the video fails gracefully when fetching a video that doesn't exist
        //
        //delete any metadata that exists in the folder
        @unlink(dirname(__FILE__) . "/../videos/tv shows/Show That Does Not Exist/tvshow.nfo");
        $v = $this->loadTvShow("Show That Does Not Exist/");
        $this->assertFalse(is_file($v->getNfoPath()));
        $this->assertFalse($v->fetchMetadata());
        //no nfo file should have been created since no metadata will be found for this video
        $this->assertFalse(is_file($v->getNfoPath()));

        //
        //Check that the video can fetch metadata based on a videoId, and that it overrides the foldername option
        //
        @unlink(dirname(__FILE__) . "/../videos/tv shows/Show That Does Not Exist/tvshow.nfo");
        $v = $this->loadTvShow("Show That Does Not Exist/");
        $this->assertFalse(is_file($v->getNfoPath()));
        $v->setOnlineVideoDatabaseId(77041);
        $this->assertTrue($v->fetchMetadata());
        $this->assertTrue(is_file($v->getNfoPath()));

        //
        //Check that the video fetches metadata based on the videoId provided in the fetchCall and that it overrides the property id
        //
        @unlink(dirname(__FILE__) . "/../videos/tv shows/Show That Does Not Exist/tvshow.nfo");
        $v = $this->loadTvShow("Show That Does Not Exist/");
        $this->assertFalse(is_file($v->getNfoPath()));
        //set an invalid videoId
        $v->setOnlineVideoDatabaseId(-1);
        //load metadata, it should fail
        $this->assertFalse($v->fetchMetadata());
        $this->assertFalse(is_file($v->getNfoPath()));
        //load metadata again, this time using a provided videoId
        $this->assertTrue($v->fetchMetadata(77041));
        $this->assertTrue(is_file($v->getNfoPath()));

        //clean up the directory
        @unlink(dirname(__FILE__) . "/../videos/tv shows/Show That Does Not Exist/tvshow.nfo");
        @unlink(dirname(__FILE__) . "/../videos/tv shows/The Dick Van Dyke Show/tvshow.nfo");
    }

}

?>
