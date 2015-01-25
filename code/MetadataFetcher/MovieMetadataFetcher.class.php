<?php

include_once(dirname(__FILE__) . "/MetadataFetcher.class.php");
include_once(dirname(__FILE__) . "/../TMDB_v3/tmdb_v3.php");
include_once(dirname(__FILE__) . "/../../config.php");

/**
 * 
 *
 * @author bplumb
 */
class MovieMetadataFetcher extends MetadataFetcher {

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
        $this->fetchSuccess = false;

        $searchResults = $this->tmdb->searchMovie($title, 1, false);
        if ($searchResults["total_results"] > 0) {
            $firstItemId = $searchResults["results"][0]["id"];
            $this->tmdbId = $firstItemId;
            $this->fetchSuccess = true;
        }

        return $this->fetchSuccess;
    }
    
    function getFetchersByTitle($title) {
        $this->fetchSuccess = false;
        $fetchers = [];
        $searchResults = $this->tmdb->searchMovie($title, 1, false);
        $results = $searchResults["results"];
        $resultCount = count($results); 
        for($i = 0; $i < $resultCount; $i++){
            $result = $results[$i];
            $id = $result["id"];
            $fetcher = new MovieMetadataFetcher();
            $fetcher->searchById($id);
            $fetchers[] = $fetcher;
        }

        return $fetchers;
    }

    /**
     * Sets the tmdb id of the movie we want to fetch metadata for. This search function does not actually do the searching.
     * The searching is performed once the metadata starts being requested. In this way, only the metadata actually being used
     * will be fetched.
     * @param type $id - the tmdb id
     */
    function searchById($id) {
        $this->fetchSuccess = false;
        $this->tmdbId = $id;
    }

    /**
     * Automatically fetch all of the metadata for this entire class. We are assuming that the user will want to use ALL of it.
     */
    function preFetchAll() {
        $this->fetchSuccess = false;
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
            if (count($this->cast) > 0) {
                $this->fetchSuccess = true;
            }
        }
    }

    /**
     * Fetch the info metadata
     */
    private function fetchInfo() {
        if ($this->info == null) {
            $this->info = $this->tmdb->movieDetail($this->tmdbId);
            if (count($this->info) > 0) {
                $this->fetchSuccess = true;
            }
        }
    }

    /**
     * Fetch the release metadata
     */
    private function fetchRelease() {
        if ($this->release == null) {
            $this->release = [];
            $releases = $this->tmdb->movieRelease($this->tmdbId);
            if (count($releases) > 0) {
                $this->fetchSuccess = true;

                if(count($releases["countries"]) > 0){
                    //just grab the first release in the list, should usually be the US
                    $this->release = $releases["countries"][0];
                }else{
                    //we couldn't find ANY release countries. just make an empty one
                    $this->release = [
                        "iso_3166_1" => null,
                        "certification"=>null,
                        "release_date"=> null
                    ];
                }
            }
        }
    }

    /**
     * Fetch the trailers metadata
     */
    private function fetchTrailers() {
        if ($this->trailer === null) {
            $this->trailer = "";

            $trailers = $this->tmdb->movieTrailer($this->tmdbId);
            if (count($trailers)) {
                $this->fetchSuccess = true;
                if (count($trailers["youtube"]) > 0) {
                    $this->trailer = "http://www.youtube.com/watch?v=" . $trailers["youtube"][0]["source"];
                }else{
                    $this->trailer = "";
                }
            }
        }
    }

    private function fetchImages() {
        if ($this->posters == null) {
            $this->posters = [];
            if ($this->tmdbId != null) {
                $this->posters = $this->tmdb->moviePoster($this->tmdbId);
                if (count($this->posters) > 0) {
                    $this->fetchSuccess = true;
                }
            }
        }
    }

    function title() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["title"] : null;
    }

    function originalTitle() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["original_title"] : null;
    }

    function rating() {
        // return $this->info["rating"];
        return 10.0;
    }
    
    function onlineVideoId(){
        return $this->tmdbId;
    }

    function year() {
        $this->fetchRelease();
        return count($this->info) > 0 ? $this->info["release_date"] : null;
    }

    function votes() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["vote_count"] : null;
    }

    function plot() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["overview"] : null;
    }

    function storyline() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["overview"] : null;
    }

    function tagline() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["tagline"] : null;
    }

    function runtime() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["runtime"] : null;
    }

    function mpaa() {
        $this->fetchRelease();
        return count($this->release) > 0 ? $this->release["certification"] : null;
    }

    function imdbId() {
        $this->fetchInfo();
        return count($this->info) > 0 ? $this->info["imdb_id"] : null;
    }

    function trailerUrl() {
        $this->fetchTrailers();
        return strlen($this->trailer) > 0 ? $this->trailer : null;
    }

    function genreList() {
        $this->fetchInfo();
        $genres = [];
        if (count($this->info) > 0) {
            foreach ($this->info["genres"] as $g) {
                $genres[] = $g["name"];
            }
        }
        return $genres;
    }

    function directorList() {
        $directorList = [];
        $this->fetchCast();
        if (count($this->cast) > 0) {
            foreach ($this->cast["crew"] as $c) {
                if (strpos(strtoupper($c["job"]), "DIRECTOR") !== false) {
                    $directorList[] = $c["name"];
                }
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
        if (count($this->cast) > 0) {

            foreach ($this->cast["cast"] as $c) {
                $directorList[] = $c["name"];
                $castList[$i]["name"] = $c["name"];
                $castList[$i++]["role"] = $c["character"];
            }
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
