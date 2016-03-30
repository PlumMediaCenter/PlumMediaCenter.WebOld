<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "DbManager.class.php");
include_once($basePath . "Enumerations.class.php");
include_once($basePath . "Video.class.php");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Video
 *
 * @author bplumb
 */
class VideoController {

    static function GetVideo($videoId) {
        $videos = VideoController::GetVideos([$videoId]);
        return $videos[0];
    }

    static function GetVideos($videoIds = [], $sortByTitle = true) {
        if (count($videoIds) === 0) {
            return [];
        }
        $inStatement = "video_id " . DbManager::GenerateInStatement($videoIds, false);
        $videoRows = DbManager::GetAllClassQuery(
                        "select * from video where $inStatement");

        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        if ($sortByTitle) {
            VideoController::SortVideosByTitle($videos);
        } else {
            //sort the videos by the provided videoId list
            $videos = VideoController::SortVideosByVideoId($videos, $videoIds);
        }
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    /**
     * Get a single movie with the specified videoId
     * @param type $videoId
     * @return type
     */
    static function GetMovie($videoId = -1) {
        $movies = VideoController::GetMovies([$videoId]);
        return count($movies) === 1 ? $movies[0] : null;
    }

    /**
     * Get an array of movies with the video_id list
     * @param type $videoIds
     */
    static function GetMovies($videoIds = []) {
        if (count($videoIds) === 0) {
            return [];
        }
        $inStatement = DbManager::GenerateInStatement($videoIds, false);
        $videoRows = DbManager::GetAllClassQuery(
                        "select * "
                        . "from video "
                        . "where video_id $inStatement and media_type = '" . Enumerations::MediaType_Movie . "'");
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        VideoController::SortVideosByTitle($videos);
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    static function GetTvShow($videoId = -1) {
        $videos = VideoController::GetTvShows([$videoId]);
        return count($videos) === 1 ? $videos[0] : null;
    }

    static function GetTvShowByEpisodeId($episodeId = -1) {
        $tvShowVideoId = Queries::GetTvShowVideoIdFromEpisodeTable($episodeId);
        $show = Video::GetVideo($tvShowVideoId);
        return $show;
    }

    /**
     * Get a single movie with the specified videoId
     * @param type $videoId
     * @return type
     */
    static function GetTvShows($videoIds = []) {
        if (count($videoIds) === 0) {
            return [];
        }
        $inStatement = "video_id " . DbManager::GenerateInStatement($videoIds, false) . " and ";
        $videoRows = DbManager::GetAllClassQuery(
                        "select * "
                        . "from video "
                        . "where $inStatement media_type = '" . Enumerations::MediaType_TvShow . "'");
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        VideoController::SortVideosByTitle($videos);
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    static function GetTvEpisode($videoId = -1) {
        //get all movies and tv shows from the db
        $videoRows = DbManager::GetAllClassQuery(
                        "select * "
                        . " from video, tv_episode "
                        . " where video.video_id = tv_episode.video_id "
                        . " and video.video_id = $videoId "
                        . " and video.media_type = '" . Enumerations::MediaType_TvEpisode . "'");
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        $videos = PropertyMappings::MapMany($videos, PropertyMappings::$episodeMapping);
        $video = isset($videos[0]) ? $videos[0] : null;
        VideoController::RepairRelativeUrls([$video]);
        return $video;
    }

    /**
     * Get a single movie with the specified videoId
     * @param type $videoId
     * @return type
     */
    static function GetTvEpisodes($videoIds) {
        $inStatement = "video_id " . DbManager::GenerateInStatement($videoIds, false) . " and ";

        //get all movies and tv shows from the db
        $videoRows = DbManager::GetAllClassQuery(
                        "select * "
                        . " from video, tv_episode "
                        . " where video.video_id = tv_episode.video_id "
                        . " and video.video_id $inStatement"
                        . " and tv_show_video_id = $tvShowVideoId "
                        . " and video.media_type = '" . Enumerations::MediaType_TvEpisode . "'");
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        $videos = PropertyMappings::MapMany($videos, PropertyMappings::$episodeMapping);
        VideoController::SortEpisodes($videos);
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    /**
     * Get a single movie with the specified videoId
     * @param type $videoId
     * @return type
     */
    static function GetTvEpisodesByShowVideoId($tvShowVideoId) {
        //get all movies and tv shows from the db
        $videoRows = DbManager::GetAllClassQuery(
                        "select * "
                        . " from video, tv_episode "
                        . " where video.video_id = tv_episode.video_id "
                        . "     and tv_episode.tv_show_video_id = $tvShowVideoId"
                        . "     and video.media_type = '" . Enumerations::MediaType_TvEpisode . "'"
                        . " order by tv_episode.season_number asc, "
                        . "     tv_episode.episode_number asc");
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        $videos = PropertyMappings::MapMany($videos, PropertyMappings::$episodeMapping);
        VideoController::SortEpisodes($videos);
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    public static function GetSearchSuggestions($search) {
        $results = [];
        $title = strtolower($search);

        //split the title by spaces.
        $parts = explode(' ', $search);
        $trimmedParts = [];
        //trim all spacing from the 
        foreach ($parts as $part) {
            //remove any extra spaces
            $part = trim($part);
            if (strlen($part) > 0) {
                $trimmedParts[] = $part;
            }
        }

        if (count($trimmedParts) == 0) {
            return $results;
        }

        $sql = 'select video_id, title from video where (';
        $or = '';
        //construct the where clause
        foreach ($trimmedParts as $part) {
            $sql = $sql . $or . "lower(title) like '%$title%'";
            $or = ' or ';
        }
        $sql = $sql . ') and media_type not like \'' . Enumerations::MediaType_TvEpisode . '\'';
        $matches = DbManager::query($sql);

        //rank each result by how many times each part of the search string appears in the title of each video
        foreach ($matches as $match) {
            $match->rank = 0;
            $rank = 0;
            $title = strtolower($match->title);
            foreach ($trimmedParts as $part) {
                $rank = $rank + substr_count($title, $part);
            }
            $match->rank = $rank;
        }

        usort($matches, array('VideoController', 'SearchCmp'));
        foreach ($matches as $match) {
            $match->videoId = $match->video_id;
            unset($match->video_id);
        }
        return $matches;
    }

    public static function SearchCmp($a, $b) {
        return $b->rank > $a->rank;
    }

    public static function SearchByTitle($search) {
        $suggestions = VideoController::GetSearchSuggestions($search);
        $videoIds = [];
        foreach ($suggestions as $suggestion) {
            $videoIds[] = $suggestion->videoId;
        }

        $videos = VideoController::GetMovies($videoIds);
        $videos = array_merge($videos, VideoController::GetTvShows($videoIds));
        return $videos;
    }

    static function SortVideosByTitle($videos) {
        usort($videos, array("VideoController", 'CmpByName'));
    }

    static function SortVideosByVideoId($videos, $videoIds) {
        $result = [];
        foreach ($videoIds as $videoId) {
            foreach ($videos as $video) {
                if ($video->videoId == $videoId) {
                    $result[] = $video;
                    break;
                }
            }
        }
        return $result;
    }

    static function SortEpisodes($videos) {
        usort($videos, array("VideoController", 'CmpEpisodes'));
    }

    static function CmpByName($a, $b) {
        if (isset($a) && isset($b) && isset($a->name) && isset($b->name)) {
            return strcmp($b->name, $a->name);
        } else {
            return true;
        }
    }

    static function CmpEpisodes($a, $b) {
        if (isset($a) && isset($b) && isset($a->seasonNumber) && isset($b->seasonNumber) && isset($a->episodeNumber) && isset($b->episodeNumber)) {
            $aString = str_pad($a->seasonNumber, 3) . str_pad($a->episodeNumber, 5);
            $bString = str_pad($b->seasonNumber, 3) . str_pad($b->episodeNumber, 5);

            return strcmp($aString, $bString);
        } else {
            return true;
        }
    }

    /**
     * Deletes any videos that are not in the list of videos to keep.
     * @param int[] $videoIdsToKeep - the list of videoIds to keep. Any video not in this list will be deleted.
     * @return boolean - true if successful, false if failure
     */
    public static function DeleteVideos($videoIdsToDelete) {
        $inStmt = DbManager::GenerateInStatement($videoIdsToDelete, false);
        $inStmt = (strlen($inStmt) != false) ? "where video_id $inStmt" : "";
        //if the statement got messed up at all, stop the query right here
        if (strlen($inStmt) < 1 || $inStmt == '' || strlen($inStmt) == false) {
            return false;
        }
        //delete all references to this video in the following tables: tv_episode, video, watch_video
        $sql = "delete from watch_video $inStmt";
        $success = DbManager::NonQuery($sql);
        $sql1 = "delete from tv_episode $inStmt";
        $success = $success && DbManager::NonQuery($sql1);
        $sql2 = "delete from video $inStmt";
        $success = $success && DbManager::NonQuery($sql2);
        return $success;
    }

    public static function FetchMetadata($videoId, $onlineVideoId) {
        $success = true;

        //load the video
        $video = Video::GetVideo($videoId);
        $video->setOnlineVideoDatabaseId($onlineVideoId);

        if (!$onlineVideoId) {
            throw new Exception('onlineVideoId is required');
        }
        try {
            $video->fetchMetadata($onlineVideoId);
            $video->loadMetadata(true);
        } catch (Exception $e) {
            $success = false;
        }

        try {
            $video->fetchPoster();
            $video->generatePosters();
        } catch (Exception $ex) {
            $success = false;
        }
        $writeToDbSuccess = $video->writeToDb();
        $success = $success && $writeToDbSuccess;

        if ($video->mediaType === Enumerations::MediaType_TvShow) {
            //fetch metadata for EVERY tv episode
            $video->loadEpisodesFromDatabase();
            foreach ($video->episodes as $episode) {
                $episodeSuccess = VideoController::FetchMetadata($episode->videoId, $onlineVideoId);
                $success = $success && $episodeSuccess;
            }
        }

        return $success;
    }

    static function RepairRelativeUrls($videos) {
        $bUrl = getBaseUrl();
        //for each poster url, if its a relative url, append the server url to the beginning of it
        foreach ($videos as $video) {
            if (strpos($video->sdPosterUrl, "http") === false && $video->sdPosterUrl !== null) {
                $video->sdPosterUrl = $bUrl . $video->sdPosterUrl;
                $video->hdPosterUrl = $bUrl . $video->hdPosterUrl;
            }
        }
    }

}
