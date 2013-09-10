<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../code/functions.php');

class TestFunctions extends UnitTestCase {

    function setUp() {
        
    }

    function testGetBaseUrl() {
        $this->assertEqual(getBaseUrl('index.php', 'http://localhost:8080/PlumVideoPlayer/Web/index.php'), "http://localhost:8080/PlumVideoPlayer/Web/");
        $this->assertEqual(getBaseUrl('test/index.php', 'http://localhost:8080/PlumVideoPlayer/Web/test/index.php'), "http://localhost:8080/PlumVideoPlayer/Web/");
        $this->assertEqual(getBaseUrl('index.php', 'http://localhost/Websites/PlumVideoPlayer/Web/index.php'), "http://localhost/Websites/PlumVideoPlayer/Web/");
        //this last function will fail if you don't have the same web host setup as me.
        $this->assertEqual(getBaseUrl('/test/' . basename(__FILE__)), "http://localhost:8080/PlumVideoPlayer/Web/");
    }

    function testSaveImageFromUrl() {

        $tempFile = dirname(__FILE__) . "/tempfile.jpg";

        //test a successful save with a valid file
        $this->assertTrue(saveImageFromUrl(getBaseUrl('test/TestFunctions.php') . "test/videos/movies/FakeMovie2/folder.jpg", $tempFile));
        //make sure the file is there
        $this->assertTrue(file_exists($tempFile));
        //delete the file
        $this->assertTrue(unlink($tempFile));
        //make sure the file was deleted
        $this->assertFalse(file_exists($tempFile));

        //try an invalid url
        $this->assertFalse(saveImageFromUrl(getBaseUrl('test/TestFunctions.php') . "text/videos/RandomImageThatDoesNotExist.jpg", $tempFile));
        //verify that the file was NOT written to the temp file location
        //make sure the file is NOT there
        $this->assertFalse(file_exists($tempFile));
    }

}

?>
