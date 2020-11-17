<?php

include_once(dirname(__FILE__) . '/../functions.php');
include_once(dirname(__FILE__) . '/../../vendor/autoload.php');


/**
 * A class that handles loading video data from the database
 */
class VideoManager
{

    /**
     * Get the specified video
     */
    public static function GetVideo($videoId, $propertyNames = null)
    {
        return VideoManager::GetVideos([$videoId], $propertyNames)[0];
    }

    /**
     * Get a list of Video models
     */
    public static function GetVideos($videoIds, $propertyNames = null)
    {
        if (count($videoIds) === 0) {
            return [];
        }
        $baseUrl = getBaseUrl();
        $videos = DbManager::QueryManyObject("
            select
                v.video_id as videoId,
                v.title,
                v.sort_title as sortTitle,
                v.running_time_seconds as runtime,
                v.poster_last_modified_date as posterModifiedDate,
                v.plot,
                v.mpaa,
                v.year,
                v.url,
                v.video_source_id as videoSourceId,
                v.media_type as mediaType,
                v.sd_poster_url as sdPosterUrl,
                v.hd_poster_url as hdPosterUrl,
                s.base_url as baseUrl
            from 
                video v JOIN
                video_source s on s.id = v.video_source_id
            where video_id in (" . implode(",", $videoIds) . ")
        ");
        foreach ($videos as $video) {
            $video->baseUrl = hydrateUrl($video->baseUrl);
            $video->videoId = intval($video->videoId);
            $video->runtime = intval($video->runtime);
            $video->year = intval($video->year);
            $video->videoSourceId = intval($video->videoSourceId);

            $video->url = $video->baseUrl . $video->url;

            $video->sdPosterUrl = $video->sdPosterUrl ?
                $video->baseUrl . $video->sdPosterUrl :
                $baseUrl . 'assets/img/posters/BlankPoster.sd.jpg';

            $video->hdPosterUrl = $video->hdPosterUrl ?
                $video->baseUrl . $video->hdPosterUrl :
                $baseUrl . 'assets/img/posters/BlankPoster.hd.jpg';


            if ($video->year === 0) {
                $video->year = null;
            }
            unset($video->baseUrl);
        }
        if (isset($propertyNames)) {
            $videos = filterProperties($videos, $propertyNames);
        }
        return $videos;
    }

    /**
     * Get the list of TV episodes specified. 
     */
    public static function GetTvEpisodes($videoIds, $propertyNames = null)
    {
        //get the baseline video info
        $videos = VideoManager::GetVideos($videoIds);
        //get the episode information
        $episodes = DbManager::QueryManyObject("
            select
               video_id as videoId,
               tv_show_video_id as tvShowVideoId,
               season_number as seasonNumber,
               episode_number as episodeNumber,
               writer,
               director
            from 
                tv_episode
            where video_id in (" . implode(",", $videoIds) . ")
        ");

        //build episode lookup
        $episodeLookup = [];
        foreach ($episodes as $episode) {
            $episodeLookup[$episode->videoId]  = $episode;
        }

        foreach ($videos as $video) {
            $episode = $episodeLookup[$video->videoId];
            $video->tvShowVideoId = intval($episode->tvShowVideoId);
            $video->seasonNumber = intval($episode->seasonNumber);
            $video->episodeNumber = intval($episode->episodeNumber);
            $video->writer = $episode->writer;
            $video->director = $episode->director;
        }

        if (isset($propertyNames)) {
            $videos = filterProperties($videos, $propertyNames);
        }

        return $videos;
    }

    public static function GetTvEpisodesForShow($tvShowVideoId)
    {
        //get all movies and tv shows from the db
        $episodeIds = DbManager::SingleColumnQuery("
            select 
                video.video_id
            from 
                video JOIN
                tv_episode on video.video_id = tv_episode.video_id
            where 
                tv_episode.tv_show_video_id = ?
            order by 
                tv_episode.season_number asc,
                tv_episode.episode_number asc
        ", $tvShowVideoId);

        $videos = VideoManager::GetTvEpisodes($episodeIds);
        return $videos;
    }

    /**
     * Get the list of recently watched videos
     */
    public static function GetRecentlyWatchedVideoIds()
    {
        $videoIds = DbManager::SingleColumnQuery("
            select video_id 
            from recently_watched 
            where user_id = '" . config::$defaultUserId . "' 
            order by date_watched desc
            limit 20
        ");
        return arrayToInt($videoIds);
    }

    /**
     * Get the ids of the most recent added videos
     */
    public static function GetRecentlyAddedVideoIds($numberOfDays)
    {
        $recentVideoIds = DbManager::SingleColumnQuery("
            select 
                video_id 
            from 
                video 
            where 
                date_added between DATE_SUB(NOW(), INTERVAL $numberOfDays DAY) AND NOW() 
            order by date_added desc 
            limit 50
        ");
        $videoIds = Library::ReduceVideoIds($recentVideoIds);
        return $videoIds;
    }

    /**
     * Get the ids of the most recent updated videos
     */
    public static function GetRecentlyUpdatedVideoIds($numberOfDays)
    {
        $recentVideoIds = DbManager::SingleColumnQuery("
            select video_id 
            from video 
            where date_modified between DATE_SUB(NOW(), INTERVAL $numberOfDays DAY) AND NOW() 
            and date_modified > date_added
            order by date_added desc
            limit 50
        ");
        $videoIds = Library::ReduceVideoIds($recentVideoIds);
        return $videoIds;
    }

    /**
     * Get the ids of all TV shows
     */
    public static function GetTvShowVideoIds()
    {
        $ids = DbManager::SingleColumnQuery("
            select
                video_id
            from
                video
            where
                media_type = ?
        ", Enumerations::MediaType_TvShow);
        return $ids;
    }


    /**
     * Get movie/show search suggestions for the given title, ordered in most-likely to least-likely.
     */
    public static function GetSearchSuggestions($title)
    {
        //get every show and movie name (exclude tv episodes)
        $videos = DbManager::QueryAA("
            select
                video_id as videoId,
                title
            from 
                video
            where
                media_type in('" . Enumerations::MediaType_TvShow . "', '" . Enumerations::MediaType_Movie . "')
        ");

        $fuse = new Fuse\Fuse($videos, [
            'keys' => ['title'],
            'threshold' => 0.04,
            'tokenize' => true
        ]);
        $filteredVideos = $fuse->search($title);
        $result = [];
        foreach ($filteredVideos as $video) {
            $result[] = (object)$video;
        }
        return $result;
    }

    /**
     * Search for movies and shows by title
     */
    public static function SearchByTitle($title)
    {
        $suggestions = VideoManager::GetSearchSuggestions($title);
        $videoIds = [];
        $indexByVideoId = [];
        foreach ($suggestions as $index => $suggestion) {
            $videoIds[] = $suggestion->videoId;
            $indexByVideoId[$suggestion->videoId] = $index;
        }

        $videos = VideoManager::GetVideos($videoIds);
        //sort the videos based on the suggestion ID order
        usort($videos, function ($a, $b) use ($indexByVideoId) {
            return $indexByVideoId[$a->videoId] - $indexByVideoId[$b->videoId];
        });
        return $videos;
    }
}
