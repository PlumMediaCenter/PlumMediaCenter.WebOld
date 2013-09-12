<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../code/NfoReader/TvEpisodeNfoReader.class.php');

class TestTvEpisodeNfoReader extends UnitTestCase {

    /**
     * Test that the movie reader correctly loads an entirely correct nfo file
     */
    function testLoadsValidFile() {
        $m = new TvEpisodeNfoReader();
        $m->loadFromFile(dirname(__file__) . "/videos/tv shows/FakeShow1/Season1/s01.e01.nfo");
        $this->assertEqual($m->title, "My TV Episode");
        $this->assertEqual($m->rating, "10.00");
        $this->assertEqual($m->season, "1");
        $this->assertEqual($m->episode, "1");
        $this->assertEqual($m->plot, "The best episode in the world");
        $this->assertEqual($m->thumb, "http://thetvdb.com/banners/episodes/164981/2528821.jpg");
        $this->assertEqual($m->playCount, "0");
        $this->assertEqual($m->lastPlayed, "2010-01-01");
        $this->assertEqual($m->credits, "Writer");
        $this->assertEqual(count($m->directors), 2);
        $this->assertEqual($m->directors[0], "Mr. Vision");
        $this->assertEqual($m->directors[1], "Mr. Vision's Son");
        $this->assertEqual($m->aired, "2000-12-31");
        $this->assertEqual($m->premiered, "2010-09-24");
        $this->assertEqual($m->studio, "Production studio or channel");
        $this->assertEqual($m->mpaa, "TV-PG");
        $this->assertEqual($m->epbookmark, "200");
        $this->assertEqual($m->displaySeason, "3");
        $this->assertEqual($m->displayEpisode, "4096");
        $this->assertEqual(count($m->actors), 2);
        $this->assertEqual($m->actors[0]->name, "Little Suzie");
        $this->assertEqual($m->actors[0]->role, "Pole Jumper/Dancer");
        $this->assertEqual($m->actors[1]->name, "Famous Actor");
        $this->assertEqual($m->actors[1]->role, "Super friend");



        //test the fileInfo item
        $this->assertNotNull($m->fileInfo);
        //fileinfo audio 1
        $this->assertEqual($m->fileInfo->streamDetails->audio[0]->codec, "ac3");
        $this->assertEqual($m->fileInfo->streamDetails->audio[0]->channels, "6");
        //fileinfo audio 2
        $this->assertEqual($m->fileInfo->streamDetails->audio[1]->codec, "mp3");
        $this->assertEqual($m->fileInfo->streamDetails->audio[1]->channels, "2");
        //fileinfo video
        $this->assertEqual($m->fileInfo->streamDetails->video->aspect, "1.778");
        $this->assertEqual($m->fileInfo->streamDetails->video->codec, "h264");
        $this->assertEqual($m->fileInfo->streamDetails->video->durationInSeconds, "587");
        $this->assertEqual($m->fileInfo->streamDetails->video->height, "720");
        $this->assertEqual($m->fileInfo->streamDetails->video->language, "eng");
        $this->assertEqual($m->fileInfo->streamDetails->video->longLanguage, "English");
        $this->assertEqual($m->fileInfo->streamDetails->video->scanType, "Progressive");
        $this->assertEqual($m->fileInfo->streamDetails->video->width, "1280");
    }

    /**
     * Test that the nfo reader correctly handles a file that only has the <episodedetails></episodedetails> tags. It should fetch null values for every item the reader is expecting
     */
    function testEmptyXmlFile() {
       $m = new TvEpisodeNfoReader();
        $m->loadFromFile(dirname(__file__) . "/videos/tv shows/FakeShow1/Season1/s01.e02.nfo");
        $this->assertEqual($m->title, null);
        $this->assertEqual($m->rating,null);
        $this->assertEqual($m->season, null);
        $this->assertEqual($m->episode, null);
        $this->assertEqual($m->plot, null);
        $this->assertEqual($m->thumb,null);
        $this->assertEqual($m->playCount,null);
        $this->assertEqual($m->lastPlayed, null);
        $this->assertEqual($m->credits, null);
        $this->assertEqual(count($m->directors), 0);
        $this->assertEqual($m->aired, null);
        $this->assertEqual($m->premiered, null);
        $this->assertEqual($m->studio,null);
        $this->assertEqual($m->mpaa, null);
        $this->assertEqual($m->epbookmark, null);
        $this->assertEqual($m->displaySeason, null);
        $this->assertEqual($m->displayEpisode, null);
        $this->assertEqual(count($m->actors), 0);
        //test the fileInfo item
        $this->assertNotNull($m->fileInfo);
        $this->assertEqual(count($m->fileInfo->streamDetails->audio), 0);
        $this->assertEqual($m->fileInfo->streamDetails->video->aspect,null);
        $this->assertEqual($m->fileInfo->streamDetails->video->codec,null);
        $this->assertEqual($m->fileInfo->streamDetails->video->durationInSeconds, null);
        $this->assertEqual($m->fileInfo->streamDetails->video->height, null);
        $this->assertEqual($m->fileInfo->streamDetails->video->language, null);
        $this->assertEqual($m->fileInfo->streamDetails->video->longLanguage, null);
        $this->assertEqual($m->fileInfo->streamDetails->video->scanType,null);
        $this->assertEqual($m->fileInfo->streamDetails->video->width, null);
    }

}

?>
