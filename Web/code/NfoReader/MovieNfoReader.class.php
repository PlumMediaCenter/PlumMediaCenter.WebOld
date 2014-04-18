<?php

include_once(dirname(__FILE__) . "/NfoReader.class.php");

class MovieNfoReader extends NfoReader {

    protected function parseFile() {

        //parse the nfo file
        $this->title = $this->val("title");
        $this->originalTitle = $this->val("originaltitle");
        $this->sortTitle = $this->val("sorttitle");
        $this->set = $this->val("set");
        $this->rating = $this->val("rating");
        $year = $this->val("year");
        //year should be a 4 digit number
        $this->year = (strlen($year) === 4) ? intval($year) : null;
        $this->top250 = $this->val("top250");
        $this->votes = $this->val("votes");
        $this->outline = $this->val("outline");
        $this->plot = $this->val("plot");
        $this->tagline = $this->val("tagline");
        $this->runtime = $this->val("runtime");
        $this->thumb = $this->val("thumb");
        $this->mpaa = $this->val("mpaa");
        $this->playCount = $this->val("playcount");
        $this->id = $this->val("id");
        $this->filenameAndPath = $this->val("filenameandpath");
        $this->trailer = $this->val("trailer");
        $this->genres = [];
        $genreNodeList = $this->doc->getElementsByTagName("genre");
        foreach ($genreNodeList as $genre) {
            $this->genres[] = $genre->nodeValue;
        }
        $this->credits = $this->val("credits");

        //create the skeleton fileinfo object
        $this->fileInfo = (object) [];
        $this->fileInfo->streamDetails = (object) [];
        $this->fileInfo->streamDetails->video = (object) [];
        $this->fileInfo->streamDetails->video->codec = null;
        $this->fileInfo->streamDetails->video->aspect = null;
        $this->fileInfo->streamDetails->video->width = null;
        $this->fileInfo->streamDetails->video->height = null;
        $this->fileInfo->streamDetails->audio = [];
        $this->fileInfo->streamDetails->subtitle = (object) [];
        $this->fileInfo->streamDetails->subtitle->language = null;



        $infoNode = $this->doc->getElementsByTagName("fileinfo")->item(0);
        if ($infoNode !== null) {
            $streamDetailsNode = $infoNode->getElementsByTagName("streamdetails")->item(0);
            if ($streamDetailsNode !== null) {
                $videoNode = $streamDetailsNode->getElementsByTagName("video")->item(0);
                if ($videoNode !== null) {
                    $this->fileInfo->streamDetails->video = (object) [];
                    $this->fileInfo->streamDetails->video->codec = $this->val("codec", $videoNode);
                    $this->fileInfo->streamDetails->video->aspect = $this->val("aspect", $videoNode);
                    $this->fileInfo->streamDetails->video->width = $this->val("width", $videoNode);
                    $this->fileInfo->streamDetails->video->height = $this->val("height", $videoNode);
                }
                $audioNodes = $streamDetailsNode->getElementsByTagName("audio");
                if ($audioNodes !== null) {
                    foreach ($audioNodes as $audioNode) {
                        $codec = $this->val("codec", $audioNode);
                        $language = $this->val("language", $audioNode);
                        $channels = $this->val("channels", $audioNode);
                        $audio = (object) [];
                        $audio->codec = $codec;
                        $audio->language = $language;
                        $audio->channels = $channels;
                        $this->fileInfo->streamDetails->audio[] = $audio;
                    }
                }
                $subtitleNode = $streamDetailsNode->getElementsByTagName("subtitle")->item(0);
                if ($subtitleNode !== null) {
                    $this->fileInfo->streamDetails->subtitle->language = $this->val("language", $subtitleNode);
                }
            }
        }

        $this->directors = [];
        $directorNodes = $this->doc->getElementsByTagName("director");
        foreach ($directorNodes as $directorNode) {
            $this->directors[] = $directorNode->nodeValue;
        }

        $this->actors = [];
        $actorNodeList = $this->doc->getElementsByTagName("actor");
        foreach ($actorNodeList as $actorNode) {
            $actor = (object) [];
            $nameItem = $actorNode->getElementsByTagName("name")->item(0);
            $actor->name = $nameItem != null ? $nameItem->nodeValue : "";
            $roleItem = $actorNode->getElementsByTagName("role")->item(0);
            $actor->role = $roleItem != null ? $roleItem->nodeValue : "";
            //if we have either an actor name or role, add this actor
            if ($actor->name != "" || $actor->role != "") {
                $this->actors[] = $actor;
            }
        }
        //if made it to here, all is good. return true
        return true;
    }

    public $title;
    public $originalTitle;
    public $sortTitle;
    public $set;
    public $rating;
    public $year;
    public $top250;
    public $votes;
    public $outline;
    public $plot;
    public $tagline;
    public $runtime;
    public $thumb;
    public $mpaa;
    public $playCount;
    public $id;
    public $filenameAndPath;
    public $trailer;
    public $genres;
    public $credits;
    public $fileInfo;
    public $directors;
    public $actors;

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
        }
        return $dateTime;
    }

    public function runningTimeSeconds() {
        $runtimeMinutes = $this->runtime;
        $intRuntimeMinutes = ($runtimeMinutes === null) ? null : intval($runtimeMinutes);
        return ($intRuntimeMinutes === null) ? null : intval($intRuntimeMinutes) * 60;
    }

    /* End iVideoMetadata Implementation */
}

?>
