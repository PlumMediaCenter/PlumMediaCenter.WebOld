<?php

include_once(dirname(__FILE__) . '/MetadataFetcher.class.php');
include_once(dirname(__FILE__) . '/../lib/TMDB4PHP/Client.php');

/**
 * 
 *
 * @author bplumb
 */
class MovieMetadataFetcher extends MetadataFetcher {

    private $tmdbId;
    private $countryCode;
    private $tmdb;
    private $movieAsset;
    private $info;
    private $release;
    private $trailers;
    private $castAndCrew;
    private $posters;

    function __construct($apiKey = null, $countryCode = "US") {
        if ($apiKey === null) {
            $apiKey = config::$tmdbApiKey;
        }
        $this->countryCode = $countryCode;

        $this->tmdb = TMDB\Client::getInstance($apiKey);
    }

    private function clean() {
        $this->tmdbId = null;
        $this->movieAsset = null;
        $this->info = null;
        $this->release = null;
        $this->trailers = null;
        $this->cast = null;
        $this->posters = null;
    }

    /**
     * Search TMDB for a movie with the specified title and optionally the year, 
     * return a result
     * @param string $title - The title of the video. Required
     * @param string|int $year - the year the video was released...optional.
     * @return type
     */
    function searchByTitle($title, $year = null) {
        $this->clean();
        $this->fetchSuccess = false;

        if ($year === null) {
            $results = $this->tmdb->search('movie', array('query' => $title));
        } else {
            $results = $this->tmdb->search('movie', array('query' => $title, 'year' => $year));
        }

        if (count($results) > 0) {
            foreach ($results as $id => $movie) {
                $this->tmdbId = $id;
                $this->fetchSuccess = true;
                break;
            }
        } else {
            throw new Exception("No movie with title '$title' found in the TMDB database.");
        }
        return $this->fetchSuccess;
    }

    /**
     * Sets the tmdb id of the movie we want to fetch metadata for. This search function does not actually do the searching.
     * The searching is performed once the metadata starts being requested. In this way, only the metadata actually being used
     * will be fetched.
     * @param type $id - the tmdb id
     */
    function searchById($id) {
        $this->clean();
        $this->fetchSuccess = false;
        $this->tmdbId = $id;
    }

    /**
     * Automatically fetch all of the metadata for this entire class. We are assuming that the user will want to use ALL of it.
     */
    function preFetchAll() {
        $this->fetchSuccess = false;
        $this->fetchCastAndCrew();
        $this->fetchInfo();
        $this->fetchRelease();
        $this->fetchTrailers();
    }

    private function movieAsset() {
        if ($this->movieAsset === null) {
            $this->movieAsset = new \TMDB\structures\Movie($this->tmdbId);
        }
        //if this item has no id (which it should because we just passed it one), then 
        //the id of the movie was not valid.
        if (isset($this->movieAsset->id) === false) {
            throw new Exception("No movie with id $this->tmdbId was found in the tmdb database");
        }
        return $this->movieAsset;
    }

    /**
     * Fetch the info metadata
     */
    private function fetchInfo() {
        if ($this->info === null) {
            $this->info = $this->movieAsset();
            $this->fetchSuccess = true;
        }
    }

    /**
     * Fetch the release metadata
     */
    private function fetchRelease() {
        if ($this->release == null) {
            $this->release = [];
            $movieAsset = $this->movieAsset();
            $releases = $movieAsset->releases();
            $countries = $releases->countries;
            if (count($releases) > 0) {
                $this->fetchSuccess = true;

                //default to the first release found. 
                $selectedRelease = $countries[0];
                //find the release with the country code matching ours
                foreach ($countries as $key => $release) {
                    if ($release->iso_3166_1 === $this->countryCode) {
                        $selectedRelease = $release;
                    }
                }
                //at this point, we have the first release or the release with our specified country code. 
                $this->release = $selectedRelease;
            }
        }
    }

    /**
     * Fetch the trailers metadata
     */
    private function fetchTrailers() {
        if ($this->trailers === null) {
            $this->trailers = [];
            $trailers = $this->movieAsset()->trailers();
            if (count($trailers)) {
                $this->fetchSuccess = true;

                $this->trailer = "http://www.youtube.com/watch?v=" . $trailers->youtube[0]->source;
            }
        }
    }

    /**
     * Fetch the cast metadata
     */
    private function fetchCastAndCrew() {
        if ($this->castAndCrew == null) {
            $this->castAndCrew = $this->movieAsset()->casts();
            if (count($this->castAndCrew) > 0) {
                $this->fetchSuccess = true;
            }
        }
    }

    private function fetchImages() {
        if ($this->posters == null) {
            $this->posters = [];
            if ($this->tmdbId != null) {
                $images = $this->movieAsset()->images();
                if (count($images->posters) > 0) {
                    $this->posters = $images->posters;
                    $this->fetchSuccess = true;
                }
            }
        }
    }

    function title() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->title : null;
    }

    function originalTitle() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->original_title : null;
    }

    function rating() {
        return ($this->info !== null) ? $this->info->vote_average : null;
    }

    function year() {
        $this->fetchRelease();
        return ($this->info !== null) ? $this->info->release_date : null;
    }

    function votes() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->vote_count : null;
    }

    function plot() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->overview : null;
    }

    function storyline() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->overview : null;
    }

    function tagline() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->tagline : null;
    }

    function runtime() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->runtime : null;
    }

    function imdbId() {
        $this->fetchInfo();
        return ($this->info !== null) ? $this->info->imdb_id : null;
    }

    function mpaa() {
        $this->fetchRelease();
        return ($this->release !== null) ? $this->release->certification : null;
    }

    function trailerUrl() {
        $this->fetchTrailers();
        return strlen($this->trailer) > 0 ? $this->trailer : null;
    }

    function genres() {
        $this->fetchInfo();
        $genres = [];
        if ($this->info !== null) {
            foreach ($this->info->genres as $genre) {
                $genres[] = $genre->name;
            }
        }
        return $genres;
    }

    function directorList() {
        $directorList = [];
        $this->fetchCastAndCrew();
        if ($this->castAndCrew !== null) {
            foreach ($this->castAndCrew['crew'] as $crewMember) {
                if (strpos(strtoupper($crewMember->job), "DIRECTOR") !== false) {
                    $directorList[] = $crewMember->name;
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
        $this->fetchCastAndCrew();
        $castList = [];
        $i = 0;
        if (count($this->castAndCrew) > 0) {

            foreach ($this->castAndCrew["cast"] as $castMember) {
                $castList[] = [];
                $castList[$i]["name"] = $castMember->name;
                $castList[$i++]["role"] = $castMember->character;
            }
        }
        return $castList;
    }

    function thumb() {
        $this->fetchImages();
        return "";
    }

    function posterUrl() {

        $poster = $this->movieAsset()->poster();
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
        $filepath = $img->file_path;
        $size = "original";
        $url = $this->tmdb->image_url("poster", 1280, $img->file_path);
        return $url;
    }

}

?>
