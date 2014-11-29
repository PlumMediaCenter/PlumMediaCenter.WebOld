<?php

require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../code/functions.php');

class TestFunctions extends UnitTestCase {

    function setUp() {
        
    }

    function testSaveImageFromUrl() {

        $tempFile = dirname(__FILE__) . "/tempfile.jpg";

        //test a successful save with a valid file
        $this->assertTrue(saveImageFromUrl(getBaseUrl() . "test/videos/movies/FakeMovie2/folder.jpg", $tempFile));
        //make sure the file is there
        $this->assertTrue(file_exists($tempFile));
        //delete the file
        $this->assertTrue(unlink($tempFile));
        //make sure the file was deleted
        $this->assertFalse(file_exists($tempFile));

        //try an invalid url
        $this->assertFalse(saveImageFromUrl(getBaseUrl() . "text/videos/RandomImageThatDoesNotExist.jpg", $tempFile));
        //verify that the file was NOT written to the temp file location
        //make sure the file is NOT there
        $this->assertFalse(file_exists($tempFile));
    }

}

?>
