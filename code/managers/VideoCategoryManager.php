<?php
include_once(dirname(__FILE__) . '/../Library.class.php');
include_once(dirname(__FILE__) . '/VideoManager.php');

/**
 * A class that handles loading video data from the database
 */
class VideoCategoryManager
{

    public static function GetCategories($categoryNames = null, $propertyNames = null)
    {
        if ($categoryNames === null) {
            $categoryNames = VideoCategoryManager::GetCategoryNames();
        }

        $lib = new Library();
        $allVideoIds = [];

        $categories = [];
        foreach ($categoryNames as $categoryName) {
            $categoryTitle = $categoryName;
            $videoIds = [];

            if ($categoryName === 'Recently Watched') {
                $videoIds = VideoManager::GetRecentlyWatchedVideoIds();
            } else if ($categoryName === 'Recently Added') {
                $videoIds = VideoManager::GetRecentlyAddedVideoIds(30);
            } else if ($categoryName === 'Recently Updated') {
                $videoIds = VideoManager::GetRecentlyUpdatedVideoIds(30);
            } else if ($categoryName === "TV Shows") {
                $videoIds = VideoManager::GetTvShowVideoIds();
            } else if ($categoryName === "Movies") {
                $lib->loadMoviesFromDatabase();
                $videoIds = pickProp($lib->movies, 'videoId', 'int');
            } else if (strpos($categoryName, 'list:') === 0) {
                $categoryTitle = substr($categoryName, strlen('list:'));
                $videoIds = Queries::GetVideoIdsForListName($categoryTitle);
            } else if (strpos($categoryName, 'genre:') === 0) {
                $categoryTitle = substr($categoryName, strlen('genre:'));
                $videoIds = Queries::GetVideoIdsForGenre($categoryTitle);
            }

            //create the new category
            $categories[$categoryName] = new Category($categoryName, $videoIds, $categoryTitle);
            //merge this category's video IDs into the full list
            $allVideoIds = array_merge($allVideoIds, $videoIds);
        }

        $distinctVideoIds = distinct($allVideoIds);
        $result = (object) [];
        $videos = VideoManager::GetVideos($distinctVideoIds, $propertyNames);
        //make a map of videos indexed by videoId
        $result->videos = [];
        foreach ($videos as $video) {
            $result->videos[$video->videoId]  = $video;
        }
        $result->categories = [];
        foreach ($categoryNames as $categoryName) {
            $result->categories[] = $categories[$categoryName];
        }
        return $result;
    }

    public static function GetCategoryNames()
    {
        //get the full list of categories for this user
        $userCategoryNames = DbManager::SingleColumnQuery("
            select concat('list:', name)
            from list 
            where user_id = " . Security::GetUserId() . "
                and name <> 'My List'
        ");

        //get the distinct list of keywords
        $keywordNames = DbManager::SingleColumnQuery("
            select distinct concat('genre:', genre)
            from video_genre
            order by genre asc
        ");

        //ignore 'Recently Updated' for now because the library generator auto-saves every video by default
        return array_merge(['Recently Watched', 'list:My List'], $userCategoryNames, ['Recently Added', 'TV Shows', 'Movies'], $keywordNames);
    }
}
