<?php

include_once("NfoReader.class.php");

class TvShowNfoReader extends NfoReader {

    function parseFile() {
        //parse the nfo file
        $this->title = $this->val("title");
        $this->showTitle = $this->val("showtitle");
        $this->rating = $this->val("rating");
        $this->votes = $this->val("votes");
        $this->epBookmark = $this->val("epbookmark");
        $this->year = $this->val("year");
        $this->top250 = $this->val("top250");
        $this->season = $this->val("season");
        $this->episode = $this->val("episode");
        $this->uniqueId = $this->val("uniqueid");
        $this->displaySeason = $this->val("displayseason");
        $this->displayEpisode = $this->val("displayepisode");
        $this->outline = $this->val("outline");
        $this->plot = $this->val("plot");
        $this->tagline = $this->val("tagline");
        $this->runtime = $this->val("runtime");
        $this->mpaa = $this->val("mpaa");
        $this->playCount = $this->val("playcount");
        $this->lastPlayed = $this->val("lastplayed");
        $this->episodeGuide = $this->val("url", $this->val("episodeGuide"));
        $this->id = $this->val("id");
        $this->genres = [];
        $genreNodeList = $this->doc->getElementsByTagName("genre");
        foreach ($genreNodeList as $genre) {
            $this->genres[] = $genre->nodeValue;
        }
        $this->set = $this->val("set");
        $this->premiered = $this->val("premiered");
        $this->status = $this->val("status");
        $this->code = $this->val("code");
        $this->aired = $this->val("aired");
        $this->studio = $this->val("studio");
        $this->trailer = $this->val("trailer");
        $this->actors = [];
        $actorNodeList = $this->doc->getElementsByTagName("actor");
        foreach ($actorNodeList as $actorNode) {
            $actor = (object) [];
            $nameItem = $actorNode->getElementsByTagName("name")->item(0);
            $actor->name = $nameItem != null ? $nameItem->nodeValue : "";
            $roleItem = $actorNode->getElementsByTagName("role")->item(0);
            $actor->role = $roleItem != null ? $roleItem->nodeValue : "";
            $thumbItem = $actorNode->getElementsByTagName("thumb")->item(0);
            $actor->thumb = $thumbItem != null ? $thumbItem->nodeValue : "";
            //if we have either an actor name or role, add this actor
            if ($actor->name != "" || $actor->role != "" || $actor->thumb != "") {
                $this->actors[] = $actor;
            }
        }

        $this->resume = (object) [];
        $this->resume->position = $this->val("position", $this->doc->getElementsByTagName("resume")->item(0));
        $this->resume->total = $this->val("total", $this->doc->getElementsByTagName("resume")->item(0));
        $this->dateAdded = $this->val("dateadded");

        //if made it to here, all is good. return true
        return true;
    }

    public $title;
    public $showTitle;
    public $rating;
    public $epBookmark;
    public $year;
    public $top250;
    public $season;
    public $episode;
    public $uniqueId;
    public $displaySeason;
    public $displayEpisode;
    public $votes;
    public $outline;
    public $plot;
    public $tagline;
    public $runtime;
    public $mpaa;
    public $playCount;
    public $lastPlayed;
    public $episodeGuide;
    public $id;
    public $genres;
    public $set;
    public $premiered;
    public $status;
    public $code;
    public $aired;
    public $studio;
    public $trailer;
    public $actors;
    public $resume;
    public $dateAdded;

    /* iVideoMetadata implementation */

    public function title() {
        return $this->title;
    }

    public function rating() {
        return $this->rating;
    }

    public function plot() {
        return $this->plot;
    }

    public function mpaa() {
        return $this->mpaa;
    }

    public function genres() {
        return $this->genres;
    }

    public function releaseDate() {
        $dateTime = null;
        if ($this->year !== null) {
            $dateTime = new DateTime();
            $dateTime->setDate($this->year, 1, 1);
            $dateTime->setTime(0, 0, 0);
        }
        return $dateTime;
    }

    public function runningTimeSeconds() {
        $runtimeMinutes = $this->runtime;
        $intRuntimeMinutes = ($runtimeMinutes === null) ? null : intval($runtimeMinutes);
        return ($intRuntimeMinutes === null) ? null : intval($intRuntimeMinutes) * 60;
    }

    public function posterUrl() {
        return null;
    }

    /* End iVideoMetadata Implementation */
}

?>
