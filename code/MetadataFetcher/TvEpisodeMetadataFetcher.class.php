<?php

include_once(dirname(__FILE__) . '/../../vendor/autoload.php');
include_once(dirname(__FILE__) . '/../../config.php');
include_once(dirname(__FILE__) . "/MetadataFetcher.class.php");
include_once(dirname(__FILE__) . "/TvShowMetadataFetcher.class.php");

class TvEpisodeMetadataFetcher extends MetadataFetcher
{

    /**
     * @var \Tmdb\Repository\TvRepository
     */
    private $tvShowObject = null;
    /**
     * @var TvShowMetadataFetcher
     */
    private $showFetcher = null;
    /**
     * @var \Tmdb\Model\Tv\Episode
     */
    private $episodeObject = null;
    private $episodeNumber = null;
    private $seasonNumber = null;

    /**
     * Search by season name and by preset season and episode numbers. you MUST set the episode and season numbers before calling this
     * @param string $title - the show title
     */
    function searchByTitle($showName, $year = null)
    {
        $this->showFetcher = new TvShowMetadataFetcher();
        $this->showFetcher->searchByTitle($showName, $year);
        $this->tvShowObject = $this->showFetcher->tvShowObject;

        if (isset($this->tvShowObject)) {
            $season = new \Tmdb\Model\Tv\Season();
            $season->setSeasonNumber($this->seasonNumber);

            $episode = new \Tmdb\Model\Tv\Episode();
            $episode->setEpisodeNumber($this->episodeNumber);

            $repo = new \Tmdb\Repository\TvEpisodeRepository($this->showFetcher->getClient());
            $this->episodeObject = $repo->load($this->tvShowObject, $season, $episode);
        }
    }

    /**
     * Search by show id and by preset season and episode numbers. you MUST set the episode and season numbers before calling this
     * @param string $id - the id of the show that this episode belongs to
     */
    function searchById($id)
    {
        $this->searchByShowIdAndSeasonAndEpisodeNumber($id, $this->seasonNumber, $this->episodeNumber);
    }

    public function searchByShowIdAndEpisodeId($showId, $id)
    {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchById($showId);
        $this->episodeObject = $this->tvShowObject->getEpisodeById($id);
    }

    public function searchByShowIdAndSeasonAndEpisodeNumber($showId, $seasonNumber, $episodeNumber)
    {
        //query the TvDb to find a tv show that matches this folder's title. 
        $this->tvShowObject = TvShowMetadataFetcher::GetSearchById($showId);
        $this->episodeObject = $this->tvShowObject->getEpisode($seasonNumber, $episodeNumber);
    }

    public function hasData()
    {
        if (isset($this->tvShowObject) && isset($this->episodeObject)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the episode number of the episode to be fetched
     * @param int $eNum
     */
    public function setEpisodeNumber($eNum)
    {
        $this->episodeNumber = $eNum;
    }

    /**
     * Set the season number of the episode to be fetched
     * @param int $sNum
     */
    public function setSeasonNumber($sNum)
    {
        $this->seasonNumber = $sNum;
    }

    public function actors()
    {
        //TODO we don't care about this info right now...
        return [];
    }

    public function directors()
    {
        //TODO we don't care about this info right now...
        return [];
    }

    public function dayOfTheWeek()
    {
        return $this->tvShowObject->dayOfWeek;
    }

    public function episode()
    {
        return $this->episodeObject->getEpisodeNumber();
    }

    /**
     * @var Date
     */
    public function firstAired()
    {
        return $this->episodeObject->getAirDate();
    }

    public function genres()
    {
        return $this->tvShowObject->genres;
    }

    public function guestStars()
    {
        return $this->episodeObject->guestStars;
    }

    public function id()
    {
        return $this->episodeObject->id;
    }

    public function tmdbId()
    {
        return $this->id();
    }

    public function imdbId()
    {
        return $this->episodeObject->imdbId;
    }

    public function mpaa()
    {
        return $this->showFetcher->mpaa();
    }

    public function plot()
    {
        return $this->episodeObject->getOverview();
    }

    public function posterUrl()
    {
        try {
            $firstImagePath = array_values($this->episodeObject->getImages()->getAll())[0]->getFilePath();

            $imageUrl = $this->showFetcher->getFullUrl($firstImagePath);
            return $imageUrl;
        } catch (Error $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function rating()
    {
        return $this->tvShowObject->getVoteAverage();
    }

    public function season()
    {
        return $this->episodeObject->getSeasonNumber();
    }

    public function showName()
    {
        return $this->tvShowObject->seriesName;
    }

    public function showId()
    {
        return $this->tvShowObject->id;
    }

    public function title()
    {
        return $this->episodeObject->getName();
    }

    public function year() {
        return $this->tvShowObject->year();
    }

    public function writers()
    {
        //TODO we don't care about this info right now...
        return [];
    }
}
