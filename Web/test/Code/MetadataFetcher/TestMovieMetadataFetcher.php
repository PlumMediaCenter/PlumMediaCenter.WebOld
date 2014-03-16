<?php

require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../../code/MetadataFetcher/MovieMetadataFetcher.class.php');

class TestMovieNfoReader extends UnitTestCase {

    /**
     * Test that the movie reader correctly loads an entirely correct nfo file
     */
    function testFetchByTitle() {
        $fetcher = new MovieMetadataFetcher();
        $fetcher->searchByTitle("Fight Club");

        $this->assertEqual("Fight Club", $fetcher->title());
        $this->assertEqual("Fight Club", $fetcher->originalTitle());
        $this->assertEqual("7.6", $fetcher->rating());
        $this->assertEqual("1999-10-14", $fetcher->year());
        $this->assertEqual(2824, $fetcher->votes());
        $this->assertEqual($fetcher->plot(), "A ticking-time-bomb insomniac and a slippery soap salesman channel primal male aggression into a shocking new form of therapy. Their concept catches on, with underground \"fight clubs\" forming in every town, until an eccentric gets in the way and ignites an out-of-control spiral toward oblivion.");
        $this->assertEqual($fetcher->storyline(), "A ticking-time-bomb insomniac and a slippery soap salesman channel primal male aggression into a shocking new form of therapy. Their concept catches on, with underground \"fight clubs\" forming in every town, until an eccentric gets in the way and ignites an out-of-control spiral toward oblivion.");
        $this->assertEqual("How much can you know about yourself if you've never been in a fight?", $fetcher->tagline());
        $this->assertEqual(139, $fetcher->runtime());
        $this->assertEqual("tt0137523", $fetcher->imdbId());

        $this->assertEqual("http://www.youtube.com/watch?v=SUXWAEX2jlg", $fetcher->trailerUrl());

        $genres = $fetcher->genres();
        $this->assertEqual("Action", $genres[0]);
        $this->assertEqual("Drama", $genres[1]);
        $this->assertEqual("Thriller", $genres[2]);

        $directors = $fetcher->directorList();
        $this->assertEqual("Jeff Cronenweth", $directors[0]);
        $this->assertEqual("David Fincher", $directors[1]);

        //verify that a few of the cast members are present
        $cast = $fetcher->cast();
        $this->assertEqual("Edward Norton", $cast[0]["name"]);
        $this->assertEqual("The Narrator", $cast[0]["role"]);
        $this->assertEqual("Brad Pitt", $cast[1]["name"]);
        $this->assertEqual("Tyler Durden", $cast[1]["role"]);
        
        $this->assertEqual("http://image.tmdb.org/t/p/original/2lECpi35Hnbpa4y46JX0aY3AWTy.jpg", $fetcher->posterUrl());

        $this->assertEqual("R", $fetcher->mpaa());
    }
}
