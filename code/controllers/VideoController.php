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
class VideoController
{

    static function GetVideo($videoId)
    {
        $videos = VideoController::GetVideos([$videoId]);
        return $videos[0];
    }

    static function GetVideos($videoIds = [])
    {
        if (count($videoIds) === 0) {
            return [];
        }
        $inStatement = "video_id " . DbManager::GenerateInStatement($videoIds, false);
        $videoRows = DbManager::GetAllClassQuery("
            select 
                * 
            from 
                video 
            where 
                $inStatement
            order by sort_title asc
        ");

        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    /**
     * Get a single movie with the specified videoId
     * @param type $videoId
     * @return type
     */
    static function GetMovie($videoId = -1)
    {
        $movies = VideoController::GetMovies([$videoId]);
        return count($movies) === 1 ? $movies[0] : null;
    }

    /**
     * Get an array of movies with the video_id list
     * @param type $videoIds
     */
    static function GetMovies($videoIds = [])
    {
        if (count($videoIds) === 0) {
            return [];
        }
        $inStatement = DbManager::GenerateInStatement($videoIds, false);
        $videoRows = DbManager::GetAllClassQuery("
            select 
                * 
            from 
                video 
            where 
                video_id $inStatement and media_type = '" . Enumerations::MediaType_Movie . "'
            order by sort_title asc
        ");
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    static function GetTvShow($videoId = -1)
    {
        $videos = VideoController::GetTvShows([$videoId]);
        return count($videos) === 1 ? $videos[0] : null;
    }

    static function GetTvShowByEpisodeId($episodeId = -1)
    {
        $tvShowVideoId = Queries::GetTvShowVideoIdFromEpisodeTable($episodeId);
        $show = Video::GetVideo($tvShowVideoId);
        return $show;
    }

    static function GetTvShows($videoIds = [])
    {
        if (count($videoIds) === 0) {
            return [];
        }
        $inStatement = "video_id " . DbManager::GenerateInStatement($videoIds, false) . " and ";
        $videoRows = DbManager::GetAllClassQuery("
            select 
                * 
            from 
                video 
            where 
                $inStatement media_type = '" . Enumerations::MediaType_TvShow . "'
            order by sort_title asc
        ");
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        VideoController::RepairRelativeUrls($videos);
        return $videos;
    }

    static function GetTvEpisode($videoId = -1)
    {
        //get all movies and tv shows from the db
        $videoRows = DbManager::GetAllClassQuery(
            "select * "
                . " from video, tv_episode "
                . " where video.video_id = tv_episode.video_id "
                . " and video.video_id = $videoId "
                . " and video.media_type = '" . Enumerations::MediaType_TvEpisode . "'"
        );
        $videos = PropertyMappings::MapMany($videoRows, PropertyMappings::$videoMapping);
        $videos = PropertyMappings::MapMany($videos, PropertyMappings::$episodeMapping);
        $video = isset($videos[0]) ? $videos[0] : null;
        VideoController::RepairRelativeUrls([$video]);
        return $video;
    }

    public static function FetchMetadata($videoId, $tmdbId)
    {
        $success = true;

        //load the video
        $video = Video::GetVideo($videoId);
        $video->setOnlineVideoDatabaseId($tmdbId);

        if (!$tmdbId) {
            throw new Exception('tmdbId is required');
        }
        try {
            $video->fetchMetadata($tmdbId);
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
                $episodeSuccess = VideoController::FetchMetadata($episode->videoId, $tmdbId);
                $success = $success && $episodeSuccess;
            }
        }

        return $success;
    }

    static function RepairRelativeUrls($videos)
    {
        $bUrl = getBaseUrl();
        //for each poster url, if its a relative url, prepend the base url
        foreach ($videos as $video) {
            if (strpos($video->sdPosterUrl, "http") === false && $video->sdPosterUrl !== null) {
                $video->sdPosterUrl = $bUrl . $video->sdPosterUrl;
                $video->hdPosterUrl = $bUrl . $video->hdPosterUrl;
            }
        }
    }
}
