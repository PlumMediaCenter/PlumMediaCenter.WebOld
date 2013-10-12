<?php

require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../code/Video.class.php');

class TestMovie extends UnitTestCase {

    private $video;
    private $videoSourceUrl;
    private $videoSourcePath;
    private $fullPath;

    function setUp() {
        
    }

    function loadVideo() {
        $this->video = new Movie($this->videoSourceUrl, $this->videoSourcePath, $this->fullPath);
    }

    function loadMovie($halfPath) {
        //this url doesn't have to exist right now.
        $this->videoSourceUrl = "http://localhost/videos/movies/";
        $this->videoSourcePath = str_replace("\\", "/", realpath(dirname(__FILE__) . "/../videos/movies/")) . "/";
        $this->fullPath = "$this->videoSourcePath$halfPath";
        $this->loadVideo();
        return $this->video;
    }

    function testConstruct() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        //make sure that the constructor loaded everything correctly
        $this->assertEqual($v->videoSourceUrl, $this->videoSourceUrl);
        $this->assertEqual($v->videoSourcePath, $this->videoSourcePath);
        $this->assertEqual($v->fullPath, $this->fullPath);
        $this->assertEqual($v->url, "http://localhost/videos/movies/FakeMovie1/FakeMovie1.mp4");
        //make sure the sdPoster url was generated
        $this->assertNotNull($v->sdPosterUrl);
        $this->assertNotNull($v->hdPosterUrl);
        $this->assertNotNull($v->title);
    }

    function testMediaType() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $this->assertEqual($v->getMediaType(), Enumerations::MediaType_Movie);
    }

    /**
     * Test that the url gets encoded with the needed characters in order to make it work
     */
    function testEncodeUrl() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $this->assertEqual(Video::EncodeUrl("http://domain.com/Hello World"), "http://domain.com/Hello%20World");
    }

    function testPosters() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $folder = $this->videoSourcePath . "FakeMovie1";
        $folderUrl = $this->videoSourceUrl . "FakeMovie1";
        //test poster paths
        $this->assertEqual($v->getPosterPath(), "$folder/folder.jpg");
        $this->assertEqual($v->getSdPosterPath(), "$folder/folder.sd.jpg");
        $this->assertEqual($v->getHdPosterPath(), "$folder/folder.hd.jpg");
        //test poster urls
        $this->assertEqual($v->getPosterUrl(), "$folderUrl/folder.jpg");
        $this->assertEqual($v->getSdPosterUrl(), "$folderUrl/folder.sd.jpg");
        $this->assertEqual($v->getHdPosterUrl(), "$folderUrl/folder.hd.jpg");
    }

    /**
     * Test things like:
     *      Does this video has a poster in the same folder as the video, 
     *      Detect sd and hd posters
     *      Create and destroy sd and hd posters
     *      
     */
    function testPosterFile() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");

        //rename the poster file temorarily so we can test that the video knows it doesn't have a poster
        $posterPath = $v->getPosterPath();
        rename($posterPath, "$posterPath.tmp");
        //the video shound no longer think it has a poster
        $this->assertFalse($v->posterExists());
        //rename the poster back
        rename("$posterPath.tmp", $posterPath);
        //the video should know it has a poster again
        $this->assertTrue($v->posterExists());

        //
        //Test the sd and hd poster presence and creation
        //there should be no sd poster
        $this->assertFalse($v->sdPosterExists());
        //create the sd poster
        $this->assertTrue($v->generateSdPoster());
        //the sd poster should exist now
        $this->assertTrue($v->sdPosterExists());
        //destroy the sd poster
        unlink($v->getSdPosterPath());

        //there should be no hd poster
        $this->assertFalse($v->hdPosterExists());
        //create the hd poster
        $this->assertTrue($v->generateHdPoster());
        //the sd poster should exist now
        $this->assertTrue($v->hdPosterExists());
        //destroy the sd poster
        unlink($v->getHdPosterPath());
    }

    /**
     * Test that an image from the web is correctly downloaded
     */
    function testDowloadPoster() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $posterPath = $v->getPosterPath();
        rename($posterPath, "$posterPath.tmp");
        //download an image from a web server (this web server) and save it as the poster
        $this->assertTrue($v->downloadPoster(getBaseUrl() . "test/videos/movies/FakeMovie2/folder.jpg"));

        //does the poster exist?
        $this->assertTrue($v->posterExists());

        //delete the bogus picture
        unlink($posterPath);
        //move the original picture back 
        rename("$posterPath.tmp", $posterPath);
    }

    function testGetNfoPath() {
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $nfoPath = $this->videoSourcePath . "FakeMovie1/FakeMovie1.nfo";
        //if the nfo file is in the format filename.nfo, the getNfoPath function should pick that up
        $this->assertEqual($v->getNfoPath(), $this->videoSourcePath . "FakeMovie1/FakeMovie1.nfo");
        //verify that the Movie class knows it has an nfo file
        $this->assertTrue($v->nfoFileExists());

        //move the nfo file
        rename($nfoPath, "$nfoPath.tmp");
        //if no nfo file exists, the Movie class should default to filename.nfo
        $this->assertEqual($v->getNfoPath(), $this->videoSourcePath . "FakeMovie1/FakeMovie1.nfo");
        //verify that the Movie class knows it does NOT have an nfo file
        $this->assertFalse($v->nfoFileExists());

        $movieNfoPath = $this->videoSourcePath . "FakeMovie1/movie.nfo";
        //if the nfo file is in the format of movie.nfo, the getNfoPath function should pick that up
        rename("$nfoPath.tmp", $movieNfoPath);
        //make sure the video returns the correct nfo file
        $this->assertEqual($v->getNfoPath(), $movieNfoPath);
        //verify that the Movie knows it has an nfo file in the movie.nfo format
        $this->assertTrue($v->nfoFileExists());

        //rename the nfo file back to filename.nfo
        rename($movieNfoPath, $nfoPath);
    }

    function testFetchMetadata() {

        //
        //check that the fetcher correctly fetches a video by folder name
        //
        //delete any metadata that exists in the folder
        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/movie.nfo");
        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/Night of the Living Dead.nfo");
        $v = $this->loadMovie("Night of the Living Dead/Night of the Living Dead.mp4");

        $this->assertFalse(is_file($v->getNfoPath()));
        $v->fetchMetadata();
        $this->assertTrue(is_file($v->getNfoPath()));

        //
        //check that the video fails gracefully when fetching a video that doesn't exist
        //
        //delete any metadata that exists in the folder
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
        $v = $this->loadMovie("Movie that is not real/Movie that is not real.mp4");
        $this->assertFalse(is_file($v->getNfoPath()));
        $this->assertFalse($v->fetchMetadata());
        //no nfo file should have been created since no metadata will be found for this video
        $this->assertFalse(is_file($v->getNfoPath()));

        //
        //Check that the video can fetch metadata based on a videoId, and that it overrides the foldername option
        //
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
        $v = $this->loadMovie("Movie that is not real/Movie that is not real.mp4");
        $this->assertFalse(is_file($v->getNfoPath()));
        $v->setOnlineVideoDatabaseId(10331);
        $this->assertTrue($v->fetchMetadata());
        $this->assertTrue(is_file($v->getNfoPath()));

        //
        //Check that the video fetches metadata based on the videoId provided in the fetchCall and that it overrides the property id
        //
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
        $v = $this->loadMovie("Movie that is not real/Movie that is not real.mp4");
        $this->assertFalse(is_file($v->getNfoPath()));
        //set an invalid videoId
        $v->setOnlineVideoDatabaseId(0);
        //load metadata, it should fail
        $this->assertFalse($v->fetchMetadata());
        $this->assertFalse(is_file($v->getNfoPath()));
        //load metadata again, this time using a provided videoId
        $this->assertTrue($v->fetchMetadata(10331));
        $this->assertTrue(is_file($v->getNfoPath()));

        //clean up the directory
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/movie.nfo");
        @unlink(dirname(__FILE__) . "/../videos/movies/Movie that is not real/Movie that is not real.nfo");
        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/movie.nfo");
        @unlink(dirname(__FILE__) . "/../videos/movies/Night of the Living Dead/Night of the Living Dead.nfo");
    }

    function testLoadMetadata() {
        //load a video with full metadata
        $v = $this->loadMovie("FakeMovie1/FakeMovie1.mp4");
        $v->loadMetadata(true);

        $this->assertEqual($v->title, "Fake Movie 1");
        $this->assertEqual($v->plot, "This is the plot for the fake movie 1.");
        $this->assertEqual($v->year, "1992-11-25");
        $this->assertEqual($v->mpaa, "PG");
        $this->assertEqual(count($v->actorList), 3);

        $v = $this->loadMovie("BarrenMovie/BarrenMovie.mp4");
        $this->assertEqual($v->title, "BarrenMovie");
        $this->assertEqual($v->plot, "");
        $this->assertEqual($v->year, "");
        $this->assertEqual($v->mpaa, "N/A");
        $this->assertEqual(count($v->actorList), 0);
    }

}

?>
