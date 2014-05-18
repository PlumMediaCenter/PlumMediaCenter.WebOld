<?php

require_once(dirname(__FILE__) . '/../../../../code/NfoReader/MovieNfoReader.class.php');

class TestMovieNfoReader extends UnitTestCase {

    /**
     * Test that the movie reader correctly loads an entirely correct nfo file
     */
    function testLoadsValidFile() {
        $m = new MovieNfoReader();
        $m->loadFromFile(dirname(__file__) . "/../../../videos/movies/FakeMovie1/FakeMovie1.nfo");
        $this->assertEqual($m->title, "Fake Movie 1");
        $this->assertEqual($m->originalTitle, "Fake Movie 1 Original Title");
        $this->assertEqual($m->sortTitle, "Fake Movie 1 Sort Title");
        $this->assertEqual($m->set, "Fake Movie Set");
        $this->assertEqual($m->rating, "6.100000");
        $dateTime = new DateTime();
        $dateTime->setDate(1992, 1, 1);
        $dateTime->setTime(0, 0, 0);
        $this->assertEqual($m->releaseDate(), $dateTime);
        $this->assertEqual($m->top250, "12");
        $this->assertEqual($m->votes, "9999");
        $this->assertEqual($m->outline, "Short outline about Fake Movie 1");
        $this->assertEqual($m->plot, "This is the plot for the fake movie 1.");
        $this->assertEqual($m->tagline, "What a fake movie!");
        $this->assertEqual($m->runtime, "120");
        $this->assertEqual($m->thumb, "http://www.google.com?q=someThumbPath.jpg");
        $this->assertEqual($m->mpaa, "PG");
        $this->assertEqual($m->playCount, "4");
        $this->assertEqual($m->id, "123456789");
        $this->assertEqual($m->filenameAndPath, "file.path");
        $this->assertEqual($m->trailer, "http://www.youtube.com/123456789");
        //test each genre
        $this->assertEqual($m->genres[0], "Fake");
        $this->assertEqual($m->genres[1], "False");
        $this->assertEqual($m->genres[2], "Bogus");

        $this->assertEqual($m->credits, "Johnny Fake");
        //test the fileInfo item
        $this->assertNotNull($m->fileInfo);
        //fileinfo video
        $this->assertEqual($m->fileInfo->streamDetails->video->codec, "h264");
        $this->assertEqual($m->fileInfo->streamDetails->video->aspect, "4x3");
        $this->assertEqual($m->fileInfo->streamDetails->video->width, "800");
        $this->assertEqual($m->fileInfo->streamDetails->video->height, "600");
        //fileinfo audio 1
        $this->assertEqual($m->fileInfo->streamDetails->audio[0]->codec, "avi");
        $this->assertEqual($m->fileInfo->streamDetails->audio[0]->language, "sp");
        $this->assertEqual($m->fileInfo->streamDetails->audio[0]->channels, "6");
        //fileinfo audio 2
        $this->assertEqual($m->fileInfo->streamDetails->audio[1]->codec, "mp4");
        $this->assertEqual($m->fileInfo->streamDetails->audio[1]->language, "en");
        $this->assertEqual($m->fileInfo->streamDetails->audio[1]->channels, "2");
        //fileinfo subtitle
        $this->assertEqual($m->fileInfo->streamDetails->subtitle->language, "en");
        $this->assertEqual($m->directors[0], "Fake Director 1");
        $this->assertEqual($m->directors[1], "Fake Director 2");

        $actor1 = $m->actors[0];
        $this->assertEqual($actor1->name, "Fake Actor A");
        $this->assertEqual($actor1->role, "Fake Role A");
        $actor1 = $m->actors[1];
        $this->assertEqual($actor1->name, "Fake Actor B");
        $this->assertEqual($actor1->role, "Fake Role B");
        $actor1 = $m->actors[2];
        $this->assertEqual($actor1->name, "Fake Actor C");
        $this->assertEqual($actor1->role, "Fake Role C");
    }

    /**
     * Test that the nfo reader correctly handles a file that only has the <movie></movie> tags. It should fetch null values for every item the reader is expecting
     */
    function testEmptyXmlFile() {
        $m = new MovieNfoReader();
        $m->loadFromFile(dirname(__file__) . "/../videos/EmptyMovie.nfo");
        $this->assertEqual($m->title, null);
        $this->assertEqual($m->originalTitle, null);
        $this->assertEqual($m->sortTitle, null);
        $this->assertEqual($m->set, null);
        $this->assertEqual($m->rating, null);
        $this->assertEqual($m->year, null);
        $this->assertEqual($m->top250, null);
        $this->assertEqual($m->votes, null);
        $this->assertEqual($m->outline, null);
        $this->assertEqual($m->plot, null);
        $this->assertEqual($m->tagline, null);
        $this->assertEqual($m->runtime, null);
        $this->assertEqual($m->thumb, null);
        $this->assertEqual($m->mpaa, null);
        $this->assertEqual($m->playCount, null);
        $this->assertEqual($m->id, null);
        $this->assertEqual($m->filenameAndPath, null);
        $this->assertEqual($m->trailer, null);
        //test each genre
        $this->assertEqual(count($m->genres), 0);

        $this->assertEqual($m->credits, null);
        //test the fileInfo item. Should be null since the file never loaded
        $this->assertEqual($m->fileInfo, null);
    }

    /**
     * Test that the reader properly handles invalid or missing nfo files
     */
    function testInvalidFileLoads() {
        $m = new MovieNfoReader();
        //test that it handles empty files
        $this->assertFalse($m->loadFromFile(dirname(__file__) . "/../videos/Empty.nfo"));
        $m->loadFromFile(dirname(__file__) . "/../videos/EmptyXml.nfo");

        //test that it handles invalid file paths
        $this->assertFalse($m->loadFromFile(dirname(__file__) . "/../RandomFileThatDoesntExist.txt"));
        //test that it handles empty file paths and other random variables passed in as the
        $this->assertFalse($m->loadFromFile(""));
        $this->assertFalse($m->loadFromFile(null));
        $this->assertFalse($m->loadFromFile([]));
        $this->assertFalse($m->loadFromFile(["hello" => "world"]));
        $this->assertFalse($m->loadFromFile((object) ["hello" => "world"]));
    }

}

?>
