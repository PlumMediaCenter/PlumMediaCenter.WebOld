<?php
include_once(dirname(__FILE__) . "/MetadataFetcher.class.php");
include_once(dirname(__FILE__) . "/../TMDB_v3/tmdb_v3.php");
include_once(dirname(__FILE__) . "/../../config.php");

/**
 * 
 *
 * @author bplumb
 */
class MovieMetadataFetcher extends MetadataFetcher{

    private $tmdb;
    private $tmdbId;
    private $info;
    private $release;
    private $trailer;
    private $cast;
    private $posters;

    function __construct() {
        $this->tmdb = new TMDBv3(config::$tmdbApiKey, null);
    }

    /**
     * Searched tmdb to find the tmdb id of the first video in the search results matching the provided movie title.
     * Then sets the tmdb id of the movie we want to fetch metadata for. This search function does not actually do the searching.
     * The searching is performed once the metadata starts being requested. In this way, only the metadata actually being used
     * will be fetched.
     * @param type $id - the tmdb id
     */
    function searchByTitle($title) {
        $searchResults = $this->tmdb->searchMovie($title, 1, false);
        $firstItemId = $searchResults["results"][0]["id"];
        $this->tmdbId = $firstItemId;
    }

    /**
     * Sets the tmdb id of the movie we want to fetch metadata for. This search function does not actually do the searching.
     * The searching is performed once the metadata starts being requested. In this way, only the metadata actually being used
     * will be fetched.
     * @param type $id - the tmdb id
     */
    function searchById($id) {
        $this->tmdbId = $id;
    }

    /**
     * Automatically fetch all of the metadata for this entire class. We are assuming that the user will want to use ALL of it.
     */
    function preFetchAll() {
        $this->fetchCast();
        $this->fetchInfo();
        $this->fetchRelease();
        $this->fetchTrailers();
    }

    /**
     * Fetch the cast metadata
     */
    private function fetchCast() {
        if ($this->cast == null) {
            $this->cast = $this->tmdb->movieCast($this->tmdbId);
        }
    }

    /**
     * Fetch the info metadata
     */
    private function fetchInfo() {
        if ($this->info == null) {
            $this->info = $this->tmdb->movieDetail($this->tmdbId);
        }
    }

    /**
     * Fetch the release metadata
     */
    private function fetchRelease() {
        if ($this->release == null) {
            $releases = $this->tmdb->movieRelease($this->tmdbId);
            //just grab the first release in the list, should usually be the US
            $this->release = $releases["countries"][0];
        }
    }

    /**
     * Fetch the trailers metadata
     */
    private function fetchTrailers() {
        if ($this->trailer == null) {
            $trailers = $this->tmdb->movieTrailer($this->tmdbId);
            $this->trailer = "http://www.youtube.com/watch?v=" . $trailers["youtube"][0]["source"];
        }
    }

    private function fetchImages() {
        if ($this->posters == null) {
            $this->posters = $this->tmdb->moviePoster($this->tmdbId);
        }
    }

    function title() {
        $this->fetchInfo();
        return $this->info["title"];
    }

    function originalTitle() {
        $this->fetchInfo();
        return $this->info["original_title"];
    }

    function rating() {
        // return $this->info["rating"];
        return 10.0;
    }

    function year() {
        $this->fetchRelease();
        return $this->release["release_date"];
    }

    function votes() {
        $this->fetchInfo();
        return $this->info["vote_count"];
    }

    function plot() {
        $this->fetchInfo();
        return $this->info["overview"];
    }

    function storyline() {
        $this->fetchInfo();
        return $this->info["overview"];
    }

    function tagline() {
        $this->fetchInfo();
        return $this->info["tagline"];
    }

    function runtime() {
        $this->fetchInfo();
        return $this->info["runtime"];
    }

    function mpaa() {
        $this->fetchRelease();
        return $this->release["certification"];
    }

    function imdbId() {
        $this->fetchInfo();
        return $this->info["imdb_id"];
    }

    function trailerUrl() {
        $this->fetchTrailers();
        return $this->trailer;
    }

    function genreList() {
        $this->fetchInfo();
        $genres = [];
        foreach ($this->info["genres"] as $g) {
            $genres[] = $g["name"];
        }
        return $genres;
    }

    function directorList() {
        $directorList = [];
        $this->fetchCast();
        foreach ($this->cast["crew"] as $c) {
            if (strpos(strtoupper($c["job"]), "DIRECTOR") !== false) {
                $directorList[] = $c["name"];
            }
        }
        return $directorList;
    }

    /**
     * Fetches the cast list, and returns an array in common format.
     * @return array with the following items in each: name, character
     */
    function cast() {
        $this->fetchCast();
        $castList = [];
        $i = 0;
        foreach ($this->cast["cast"] as $c) {
            $directorList[] = $c["name"];
            $castList[$i]["name"] = $c["name"];
            $castList[$i++]["role"] = $c["character"];
        }
        return $castList;
    }

    function thumb() {
        $this->fetchImages();
        return "";
    }

    function posterUrl() {
        $this->fetchImages();
        //if there are no posters for this video, quit.
        if (!isset($this->posters)) {
            return null;
        }
        if (!isset($this->posters[0])) {
            return null;
        }
        //grab the first image in the list
        $img = $this->posters[0];
        $filepath = $img["file_path"];
        $size = "original";
        $url = $this->tmdb->getImageURL() . $img["file_path"];
        return $url;
    }

}

?>
