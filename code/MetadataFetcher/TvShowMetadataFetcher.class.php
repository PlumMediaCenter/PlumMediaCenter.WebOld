<?php

include_once(dirname(__FILE__) . '/../../vendor/autoload.php');
include_once(dirname(__FILE__) . '/../../config.php');
include_once(dirname(__FILE__) . '/MetadataFetcher.class.php');

class TvShowMetadataFetcher extends MetadataFetcher
{

    /**
     * @var \Tmdb\Model\Tv
     */
    public $tvShowObject;

    /**
     * @var \Tmdb\Client
     */
    private $client;

    function getClient()
    {
        if ($this->client == null) {
            $token = new \Tmdb\ApiToken(config::$tmdbApiKey);
            $this->client = new \Tmdb\Client($token);
        }
        return $this->client;
    }

    function searchByTitle($title)
    {
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchByTitle($title);
        $this->fetchSuccess = $this->tvShowObject != null;
        return $this->fetchSuccess;
    }

    function searchById($id)
    {
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchById($id);
        $this->fetchSuccess = $this->tvShowObject != null;
        return $this->fetchSuccess;
    }

    function getFetchersByTitle($title)
    {
        $this->fetchSuccess = false;
        $fetchers = [];
        $searchResults = TV_Shows::search($title);
        $resultCount = count($searchResults);
        for ($i = 0; $i < $resultCount; $i++) {
            $result = $searchResults[$i];
            $id = $result->id;
            $fetcher = new TvShowMetadataFetcher();
            $fetcher->setLanguage($this->language);
            $fetcher->searchById($id);
            $fetchers[] = $fetcher;
        }

        return $fetchers;
    }

    static function GetSearchByTitle($title)
    {
        $client = (new TvShowMetadataFetcher())->getClient();
        $api = $client->getSearchApi();
        $response = $api->searchTv($title);

        if ($response['total_results'] < 1) {
            //throw new Exception("No tv shows found for '$title'");
            return null;
        } else {
            //just assume that the first search result is the one that we want.
            $id = $response['results'][0]['id'];
            return TvShowMetadataFetcher::GetSearchById($id);
        }
    }

    static function GetSearchById($id)
    {
        $client = (new TvShowMetadataFetcher())->getClient();
        $repo = new \Tmdb\Repository\TvRepository($client);
        $tvShow = $repo->load($id);

        //if we found the tv show l
        if ($tvShow == false) {
            //echo "No TV show found using TvDB ID '" . $this->onlineMovieDatabaseId . "'<br/>";
            // throw new Exception("No tv shows found with id of $id");
        } else {
            return $tvShow;
        }
    }

    function actors()
    {
        //TODO add actors. we don't use them right now so skip them.
        //return $this->fetchSuccess ? $this->tvShowObject->actors : null;
        return [];
    }

    function bannerUrl()
    {
        return $this->fetchSuccess ? $this->getFullUrl($this->tvShowObject->getBackdropImage()) : null;
    }

    function airTime()
    {
        return $this->fetchSuccess ? $this->tvShowObject->airTime : null;
    }

    function dayOfWeek()
    {
        return $this->fetchSuccess ? $this->tvShowObject->dayOfWeek : null;
    }

    function firstAired()
    {
        return $this->fetchSuccess ? $this->tvShowObject->getFirstAirDate() : null;
    }

    function genres()
    {
        //   $genreObjects = array_values($this->tvShowObject->getGenres()->getAll());
        $result = [];
        // foreach($genreObjects as $genre){
        //     $result[] = $genre->getName();
        // }
        return $result;
    }

    function imdbId()
    {
        //TODO
        return $this->fetchSuccess ? $this->tvShowObject->imdbId : null;
    }

    function title()
    {
        return $this->fetchSuccess ? $this->tvShowObject->getName() : null;
    }

    function mpaa()
    {
        try {
            return filterLanguageOrFirst($this->tvShowObject->getContentRatings())->getRating();
        } catch (Exception $e) {
            return null;
        }
    }

    function network()
    {
        //TODO figure out how the heck to get the network from the stupid generic collection
        try {
            return filterLanguageOrFirst($this->tvShowObject->getNetworks())->getName();
        } catch (Exception $e) {
            return null;
        }
    }

    function posterUrl()
    {
        return $this->fetchSuccess ? $this->getFullUrl($this->tvShowObject->getPosterPath()) : null;
    }

    function getFullUrl($relativeUrl)
    {
        if (isset($relativeUrl) && $relativeUrl != null) {
            $configRepository = new \Tmdb\Repository\ConfigurationRepository($this->getClient());
            $config = $configRepository->load();
            $imageHelper = new \Tmdb\Helper\ImageHelper($config);

            $url = $this->fetchSuccess ? 'https:' . $imageHelper->getUrl($relativeUrl) : null;
            return $url;
        } else {
            return null;
        }
    }

    function plot()
    {
        return $this->fetchSuccess ? $this->tvShowObject->getOverview() : null;
    }

    function rating()
    {
        return $this->fetchSuccess ? $this->tvShowObject->getVoteAverage() : null;
    }

    function runtime()
    {
        //TODO
        return $this->fetchSuccess ? $this->tvShowObject->getEpisodeRunTime()[0] : null;
    }

    function seriesName()
    {
        return $this->fetchSuccess ? $this->tvShowObject->name : null;
    }

    function status()
    {
        //TODO
        return $this->fetchSuccess ? $this->tvShowObject->getStatus() : null;
    }

    function tmdbId()
    {
        return $this->fetchSuccess ? $this->tvShowObject->id : null;
    }

    function onlineVideoId()
    {
        return $this->tmdbId();
    }
}

function filterLanguageOrFirst($items)
{
    $filteredItems = $items->filterLanguage('en');
    if ($filteredItems->count() > 0) {
        return array_values($filteredItems->getAll())[0];
    } else {
        return array_values($items->getAll())[0];
    }
}
