<?php

include_once(dirname(__FILE__) . "/NfoReader.class.php");

class TvEpisodeNfoReader extends NfoReader {

    function parseFile() {
        //parse the nfo file
        $this->title = $this->val("title");
        $this->rating = $this->val("rating");
        $this->season = $this->val("season");
        $this->episode = $this->val("episode");
        $this->plot = $this->val("plot");
        $this->thumb = $this->val("thumb");
        $this->playCount = $this->val("playcount");
        $this->lastPlayed = $this->val("lastplayed");
        $this->credits = $this->val("credits");
        $this->directors = [];
        $directorNodes = $this->doc->getElementsByTagName("director");
        foreach ($directorNodes as $directorNode) {
            $this->directors[] = $directorNode->nodeValue;
        }
        $this->aired = $this->val("aired");
        $this->premiered = $this->val("premiered");
        $this->studio = $this->val("studio");
        $this->mpaa = $this->val("mpaa");
        $this->epbookmark = $this->val("epbookmark");
        $this->displaySeason = $this->val("displayseason");
        $this->displayEpisode = $this->val("displayepisode");

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

        //create the skeleton fileinfo object
        $this->fileInfo = (object) [];
        $this->fileInfo->streamDetails = (object) [];
        $this->fileInfo->streamDetails->video = (object) [];
        $this->fileInfo->streamDetails->video->aspect = null;
        $this->fileInfo->streamDetails->video->codec = null;
        $this->fileInfo->streamDetails->video->durationInSeconds = null;
        $this->fileInfo->streamDetails->video->height = null;
        $this->fileInfo->streamDetails->video->language = null;
        $this->fileInfo->streamDetails->video->longLanguage = null;
        $this->fileInfo->streamDetails->video->scanType = null;
        $this->fileInfo->streamDetails->video->width = null;

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
                    $this->fileInfo->streamDetails->video->aspect = $this->val("aspect", $videoNode);
                    $this->fileInfo->streamDetails->video->codec = $this->val("codec", $videoNode);
                    $this->fileInfo->streamDetails->video->durationInSeconds = $this->val("durationinseconds", $videoNode);
                    $this->fileInfo->streamDetails->video->height = $this->val("height", $videoNode);
                    $this->fileInfo->streamDetails->video->language = $this->val("language", $videoNode);
                    $this->fileInfo->streamDetails->video->longLanguage = $this->val("longlanguage", $videoNode);
                    $this->fileInfo->streamDetails->video->scanType = $this->val("scantype", $videoNode);
                    $this->fileInfo->streamDetails->video->width = $this->val("width", $videoNode);
                }
                $audioNodes = $streamDetailsNode->getElementsByTagName("audio");
                if ($audioNodes !== null) {
                    foreach ($audioNodes as $audioNode) {
                        $codec = $this->val("codec", $audioNode);
                        $channels = $this->val("channels", $audioNode);
                        $audio = (object) [];
                        $audio->codec = $codec;
                        $audio->channels = $channels;
                        $this->fileInfo->streamDetails->audio[] = $audio;
                    }
                }
            }
        }
        //if made it to here, all is good. return true
        return true;
    }

    public $title;
    public $rating;
    public $season;
    public $episode;
    public $plot;
    public $thumb;
    public $playcount;
    public $lastPlayed;
    public $credits;
    public $directors;
    public $aired;
    public $premiered;
    public $studio;
    public $mpaa;
    public $epbookmark;
    public $displaySeason;
    public $displayEpisode;
    public $actors;
    public $fileInfo;

}

?>
