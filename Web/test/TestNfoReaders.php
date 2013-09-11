<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../code/NfoReader/MovieNfoReader.class.php');

class TestMovie extends UnitTestCase {

    function setUp() {
        
    }

    function testMovieNfoReader() {
        $m = new MovieNfoReader();
        $m->loadFromFile(dirname(__file__) . "/videos/movies/FakeMovie1/FakeMovie1.nfo");
        $this->assertEqual($m->title, "Fake Movie 1");
        $this->assertEqual($m->originalTitle, "Fake Movie 1 Original Title");
        $this->assertEqual($m->sortTitle, "Fake Movie 1 Sort Title");
        $this->assertEqual($m->set, "Fake Movie Set");
        $this->assertEqual($m->rating, "6.100000");
        $this->assertEqual($m->year, "1992-11-25");
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
        $this->assertEqual($m->fileInfo->streamDetails->video->codec, "h.264");
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

}

?>
