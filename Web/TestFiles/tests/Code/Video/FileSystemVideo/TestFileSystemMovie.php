<?php

include_once(dirname(__FILE__) . '/../../../../../Code/Video/FileSystemVideo/FileSystemMovie.php');

class TestFileSystemMovie extends UnitTestCase {

    private $video;
    private $sourceUrl;
    private $sourcePath;
    private $fullPath;

    function setUp() {
        
    }

    function loadVideo() {
        $this->video = new FileSystemMovie($this->fullPath, $this->sourcePath, $this->sourceUrl);
    }

    function loadMovie($halfPath) {
        //this url doesn't have to exist right now.
        $this->sourceUrl = "http://localhost/videos/movies/";
        $sourcePath = dirname(__FILE__) . "/../../../../videos/movies/";
        $this->sourcePath = str_replace("\\", "/", realpath($sourcePath)) . "/";
        $this->fullPath = "$this->sourcePath$halfPath";
        $this->loadVideo();
        return $this->video;
    }

    /**
     * Make sure that movie files that do not exist can be detected 
     */
    function testPathNotFoundMovie() {
        $v = $this->loadMovie("path_that_doesnt_exist/movie.mp4");
        $this->assertFalse($v->videoExists());
    }

    function testFindVideoIdFromDatabase() {
        $this->video = new FileSystemMovie('http://localhost:8080/video/Movies/', 'C:/Videos/Movies/', 'C:/Videos/Movies/Batman/movie.mp4');
    }

    function testConstruct() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        //make sure that the constructor loaded everything correctly
        $this->assertEqual($v->sourceUrl(), $this->sourceUrl);
        $this->assertEqual($v->sourcePath(), $this->sourcePath);
        $this->assertEqual($v->path(), $this->fullPath);
        $this->assertEqual($v->getUrl(), "http://localhost/videos/movies/FakeMovie1/FakeMovie1.mp4");
        $this->assertNotNull($v->title());
    }

    function testMediaType() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $this->assertEqual($v->mediaType(), Enumerations\MediaType::Movie);
    }

    /**
     * Test that the url gets encoded with the needed characters in order to make it work
     */
    function testEncodeUrl() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $this->assertEqual(FileSystemVideo::EncodeUrl("http://domain.com/Hello World"), "http://domain.com/Hello%20World");
    }

    function testFetchMetadata() {

//        //
//        //check that the fetcher correctly fetches a video by folder name
//        //
//        //delete any metadata that exists in the folder
//        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/movie.nfo");
//        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/Night of the Living Dead.nfo");
//        $v = $this->loadMovie("Night of the Living Dead/Night of the Living Dead.mp4");
//
//        $this->assertFalse(is_file($v->getNfoPath()));
//        $v->fetchMetadata();
//        $this->assertTrue(is_file($v->getNfoPath()));
//
//        //
//        //check that the video fails gracefully when fetching a video that doesn't exist
//        //
//        //delete any metadata that exists in the folder
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
//        $v = $this->loadMovie("Movie that is not real/Movie that is not real.mp4");
//        $this->assertFalse(is_file($v->getNfoPath()));
//        $this->assertFalse($v->fetchMetadata());
//        //no nfo file should have been created since no metadata will be found for this video
//        $this->assertFalse(is_file($v->getNfoPath()));
//
//        //
//        //Check that the video can fetch metadata based on a videoId, and that it overrides the foldername option
//        //
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
//        $v = $this->loadMovie("Movie that is not real/Movie that is not real.mp4");
//        $this->assertFalse(is_file($v->getNfoPath()));
//        $v->setOnlineVideoDatabaseId(10331);
//        $this->assertTrue($v->fetchMetadata());
//        $this->assertTrue(is_file($v->getNfoPath()));
//
//        //
//        //Check that the video fetches metadata based on the videoId provided in the fetchCall and that it overrides the property id
//        //
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
//        $v = $this->loadMovie("Movie that is not real/Movie that is not real.mp4");
//        $this->assertFalse(is_file($v->getNfoPath()));
//        //set an invalid videoId
//        $v->setOnlineVideoDatabaseId(0);
//        //load metadata, it should fail
//        $this->assertFalse($v->fetchMetadata());
//        $this->assertFalse(is_file($v->getNfoPath()));
//        //load metadata again, this time using a provided videoId
//        $this->assertTrue($v->fetchMetadata(10331));
//        $this->assertTrue(is_file($v->getNfoPath()));
//
//        //clean up the directory
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
//        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
//        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/movie.nfo");
//        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/Night of the Living Dead.nfo");
    }

    function testLoadMetadata() {
        //load a video with full metadata
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $v->loadMetadata(true);

        $this->assertEqual($v->title(), "Fake Movie 1");
        $this->assertEqual($v->plot(), "This is the plot for the fake movie 1.");
        $date = new DateTime();
        $date->setDate(1992, 1, 1);
        $date->setTime(0, 0, 0);
        $this->assertEqual($v->releaseDate(), $date);
        $this->assertEqual($v->mpaa(), "PG");
        // $this->assertEqual(count($v->actorList), 3);

        $v = $this->loadMovie("BarrenMovie/BarrenMovie.mp4");
        $this->assertEqual($v->title(), "BarrenMovie");
        $this->assertEqual($v->plot(), "");
        $this->assertEqual($v->releaseDate(), null);
        $this->assertEqual($v->mpaa(), "N/A");
        //$this->assertEqual(count($v->actorList), 0);
    }

    function testGetVideoName() {
        $v = $this->loadMovie("FakeMovie (2001)/movie.mp4");
        $this->assertEqual($v->title(), "movie");
    }
    
    function testWriteToDatabase(){
        $v = $this->loadMovie("Fakemovie1/FakeMovie1.mp4");
        $v->save();
    }

}
