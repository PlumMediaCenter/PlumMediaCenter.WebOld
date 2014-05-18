<?php

require_once(dirname(__FILE__) . '/../../../../code/NfoReader/TvShowNfoReader.class.php');

class TestTvShowNfoReader extends UnitTestCase {

    /**
     * Test that the movie reader correctly loads an entirely correct nfo file
     */
    function testLoadsValidFile() {
        $m = new TvShowNfoReader();
        $m->loadFromFile(dirname(__file__) . "/../../../videos/tv shows/FakeShow1/tvshow.nfo");
        $this->assertEqual($m->title, "FakeShow1");
        $this->assertEqual($m->showTitle, "FakeShow1Title");
        $this->assertEqual($m->rating, "9.98");
        $this->assertEqual($m->votes, "6");
        $this->assertEqual($m->epBookmark, "0.000000");
        $this->assertEqual($m->year, "1864");
        $this->assertEqual($m->top250, "0");
        $this->assertEqual($m->season, "-1");
        $this->assertEqual($m->episode, "3");
        $this->assertEqual($m->uniqueId, "314");
        $this->assertEqual($m->displaySeason, "-1");
        $this->assertEqual($m->displayEpisode, "2");
        $this->assertEqual($m->outline, "outline of fake show");
        $this->assertEqual($m->plot, "plot of fake show");
        $this->assertEqual($m->tagline, "it's a fake show!");
        $this->assertEqual($m->runtime, "60");
        $this->assertEqual($m->mpaa, "TV-MA");
        $this->assertEqual($m->playCount, "3");
        $this->assertEqual($m->lastPlayed, "1969-12-31");
        $this->assertEqual($m->episodeGuide, "http://www.fakeshow.com");
        $this->assertEqual($m->id, "99999");
        //test each genre
        $this->assertEqual($m->genres[0], "Comedy");
        $this->assertEqual($m->genres[1], "Drama");
        $this->assertEqual($m->set, "fake set");
        $this->assertEqual($m->premiered, "2004-11-16");
        $this->assertEqual($m->status, "current");
        $this->assertEqual($m->code, "fakecode");
        $this->assertEqual($m->aired, "1969-12-31");
        $this->assertEqual($m->studio, "FAKENETWORK");
        $this->assertEqual($m->trailer, "http://www.youtube.com?q=fakeshow");
        $this->assertEqual($m->actors[0]->name, "Fake Actor A");
        $this->assertEqual($m->actors[0]->role, "Fake Role A");
        $this->assertEqual($m->actors[0]->thumb, "http://www.google.com?q=fake actor a");
        $this->assertEqual($m->actors[1]->name, "Fake Actor B");
        $this->assertEqual($m->actors[1]->role, "Fake Role B");
        $this->assertEqual($m->actors[1]->thumb, "http://www.google.com?q=fake actor b");
        $this->assertEqual($m->actors[2]->name, "Fake Actor C");
        $this->assertEqual($m->actors[2]->role, "Fake Role C");
        $this->assertEqual($m->actors[2]->thumb, "http://www.google.com?q=fake actor c");
        $this->assertEqual(count($m->actors), 3);
        $this->assertEqual($m->resume->position, "1.000000");
        $this->assertEqual($m->resume->total, "30.000000");
        $this->assertEqual($m->dateAdded, "2013-01-28 23:33:03");
    }

    /**
     * Test that the nfo reader correctly handles a file that only has the <episodedetails></episodedetails> tags. It should fetch null values for every item the reader is expecting
     */
    function testEmptyXmlFile() {
        $m = new TvShowNfoReader();
        $m->loadFromFile(dirname(__file__) . "/../../../videos/EmptyXml.nfo");
        $this->assertEqual($m->title, null);
        $this->assertEqual($m->showTitle, null);
        $this->assertEqual($m->rating, null);
        $this->assertEqual($m->votes, null);
        $this->assertEqual($m->epBookmark, null);
        $this->assertEqual($m->year, null);
        $this->assertEqual($m->top250, null);
        $this->assertEqual($m->season, null);
        $this->assertEqual($m->episode, null);
        $this->assertEqual($m->uniqueId, null);
        $this->assertEqual($m->displaySeason, null);
        $this->assertEqual($m->displayEpisode, null);
        $this->assertEqual($m->outline, null);
        $this->assertEqual($m->plot, null);
        $this->assertEqual($m->tagline, null);
        $this->assertEqual($m->runtime, null);
        $this->assertEqual($m->mpaa, null);
        $this->assertEqual($m->playCount, null);
        $this->assertEqual($m->lastPlayed, null);
        $this->assertEqual($m->episodeGuide, null);
        $this->assertEqual($m->id, null);
        //test each genre
        $this->assertEqual($m->genres[0], null);
        $this->assertEqual($m->genres[1], null);
        $this->assertEqual($m->set, null);
        $this->assertEqual($m->premiered, null);
        $this->assertEqual($m->status, null);
        $this->assertEqual($m->code, null);
        $this->assertEqual($m->aired, null);
        $this->assertEqual($m->studio, null);
        $this->assertEqual($m->trailer, null);
        $this->assertEqual(count($m->actors), 0);
        $this->assertEqual($m->resume, null);
        $this->assertEqual($m->dateAdded, null);
    }
}

?>
