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
        $this->year = $this->val("year");
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


        $this->fileInfo = (object) [];
        $this->fileInfo->streamDetails = (object) [];

        $infoNode = $this->doc->getElementsByTagName("fileinfo")->item(0);
        $streamDetailsNode = $infoNode->getElementsByTagName("streamdetails")->item(0);
        $videoNode = $streamDetailsNode->getElementsByTagName("video")->item(0);
        $codec = $videoNode->getElementsByTagName("codec")->item(0)->nodeValue;
        $aspect = $videoNode->getElementsByTagName("aspect")->item(0)->nodeValue;
        $width = $videoNode->getElementsByTagName("width")->item(0)->nodeValue;
        $height = $videoNode->getElementsByTagName("height")->item(0)->nodeValue;
        $this->fileInfo->streamDetails->video = (object) [];
        $this->fileInfo->streamDetails->video->codec = $codec;
        $this->fileInfo->streamDetails->video->aspect = $aspect;
        $this->fileInfo->streamDetails->video->width = $width;
        $this->fileInfo->streamDetails->video->height = $height;

        $audioNodes = $streamDetailsNode->getElementsByTagName("audio");
        foreach ($audioNodes as $audioNode) {
            $codec = $audioNode->getElementsByTagName("codec")->item(0)->nodeValue;
            $language = $audioNode->getElementsByTagName("language")->item(0)->nodeValue;
            $channels = $audioNode->getElementsByTagName("channels")->item(0)->nodeValue;
            $audio = (object) [];
            $audio->codec = $codec;
            $audio->language = $language;
            $audio->channels = $channels;
            $this->fileInfo->streamDetails->audio[] = $audio;
        }
        $subtitleNode = $streamDetailsNode->getElementsByTagName("subtitle")->item(0);
        $language = $subtitleNode->getElementsByTagName("language")->item(0)->nodeValue;
        $this->fileInfo->streamDetails->subtitle = (object) [];
        $this->fileInfo->streamDetails->subtitle->language = $language;

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

}

?>
