<?php

include_once(dirname(__FILE__) . "/../functions.php");

include_once(dirname(__FILE__) . "/../DbManager.class.php");
include_once(dirname(__FILE__) . "/../Enumerations.class.php");

class Queries
{

    private static $stmtInsertVideo = null;
    private static $stmtGetAllVideoPaths = null;
    private static $stmtGetVideoIdByVideoPath = null;
    private static $stmtGetTvShowVideoIdFromEpisodeTable = null;
    private static $stmtGetVideoMetadataLastModifiedDate = null;
    private static $stmtUpdateVideo = null;
    private static $stmtVideoCount = null;
    private static $stmtAddVideoSource = null;
    private static $stmtUpdateVideoSource = null;
    private static $stmtInsertTvEpisode = null;
    private static $stmtGetTvEpisodeVideoIdsForShow = null;
    private static $stmtGetEpisodePathsByShowPath = null;
    private static $stmtGetVideo = null;
    private static $stmtGetVideosByMediaType = null;
    private static $stmtGetVideos = null;
    private static $stmtGetTvEpisode = null;
    private static $stmtGetEpisodesInTvShow = null;
    private static $stmtGetVideoProgress = null;
    private static $stmtClearPlaylist = null;
    private static $stmtGetVideoIdsForListName = null;
    private static $stmtGetPlaylistVideoIds = null;
    private static $stmtAddPlaylistName = null;
    private static $stmtDeletePlaylistName = null;
    private static $stmtDeletePlaylist = null;
    private static $stmtGetPlaylistNames = null;
    private static $stmtGetVideoIds = null;
    private static $stmtGetListId = null;
    private static $statements = [];

    /**
     * Deletes the video with the specified videoId
     * @param type $videoId
     * @return type
     */
    public static function DeleteVideo($videoId)
    {
        return Queries::DeleteVideos([$videoId]);
    }

    /**
     * Deletes the videos with the specified videoIds
     * @param int[] $videoId
     * @param boolean $notIn - if true, this function deletes videos NOT in the provided list. if false, the videos with the specified ids are deleted
     * @return type
     */
    public static function DeleteVideos($videoIds)
    {
        //if the video list is empty OR is not a valid array, return immediately
        if (is_array($videoIds) === false || count($videoIds) === 0) {
            return true;
        }
        $inStmt = DbManager::GenerateInStatement($videoIds, false);
        $finalSuccess = true;

        try {
            //get the video_ids of all of the tv shows
            $tvShowIds = DbManager::SingleColumnQuery("select video_id from video where video_id $inStmt and media_type = '" . Enumerations::MediaType_TvShow . "'");
            $tvShowIn = DbManager::GenerateInStatement($tvShowIds);
            $episodeIds = DbManager::SingleColumnQuery("select video_id from tv_episode where tv_show_video_id $inStmt");

            if (count($tvShowIds) > 0) {
                //delete all of the episodes for this show
                Queries::DeleteVideos($episodeIds);
            }
        } catch (Exception $e) {
        }

        //delete all references to this video from related tables
        $success = DbManager::NonQuery("delete from video_genre where video_id $inStmt");
        $finalSuccess = $finalSuccess === true && $success === true;

        //delete all references to this video from related tables
        $success = DbManager::NonQuery("delete from watch_video where video_id $inStmt");
        $finalSuccess = $finalSuccess === true && $success === true;

        $success = DbManager::NonQuery("delete from tv_episode where video_id $inStmt");
        $finalSuccess = $finalSuccess === true && $success === true;

        $success = DbManager::NonQuery("delete from recently_watched where video_id $inStmt");
        $finalSuccess = $finalSuccess === true && $success === true;

        $success = DbManager::NonQuery("delete from list_item where video_id $inStmt");
        $finalSuccess = $finalSuccess === true && $success === true;

        $success = DbManager::NonQuery("delete from video where video_id $inStmt");
        $finalSuccess = $finalSuccess === true && $success === true;

        return $finalSuccess;
    }

    public static function getPlaylistItems($userId, $playlistName)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetPlaylistVideoIds == null) {
            $sql = "select item_id, video_id "
                . "from playlist "
                . "where user_id = :userId and name = :playlistName "
                . "order by idx asc";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetPlaylistVideoIds = $stmt;
        }
        $stmt = Queries::$stmtGetPlaylistVideoIds;
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":playlistName", $playlistName);

        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);

        return Queries::FetchAll($stmt, "video_id");
    }

    public static function clearPlaylist($userId, $playlistName)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtClearPlaylist == null) {
            $sql = "delete from playlist where user_id = :userId and name = :playlistName";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtClearPlaylist = $stmt;
        }
        $stmt = Queries::$stmtClearPlaylist;
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":playlistName", $playlistName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    public static function GetListId($listName, $userId)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetListId == null) {
            $sql = "
                select list_id
                from list
                where name = :listName
                and user_id = :userId
            ";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetListId = $stmt;
        }
        $stmt = Queries::$stmtGetListId;
        $stmt->bindParam(":listName", $listName);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        $row = Queries::FetchAllSingleColumn($stmt, "list_id");
        if (count($row) > 0) {
            return $row[0];
        } else {
            throw new Exception("Cannot determine listId for list '$listName' and user with id $userId");
        }
    }

    public static function GetVideoIdsForListName($listName, $sortMethod = 'title')
    {
        $sortColumn = $sortMethod === 'title' ? 'v.title' : 'li.display_order';
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetVideoIdsForListName == null) {
            $sql = "
                select li.video_id
                from
                    list_item li join
                    list l on l.list_id = li.list_id join
                    video v on v.video_id = li.video_id
                where l.name = :listName
				order by $sortColumn asc
            ";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoIdsForListName = $stmt;
        }
        $stmt = Queries::$stmtGetVideoIdsForListName;
        $stmt->bindParam(":listName", $listName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return Queries::FetchAllSingleColumn($stmt, "video_id", 'int');
    }

    /**
     * Add a video to the specified list
     */
    public static function AddToList($listName, $videoIds, $userId)
    {
        $stmt = Queries::GetOrAddStatement('addToList', function () {
            $pdo = DbManager::getPdo();
            return $pdo->prepare("
                insert into list_item (list_id, video_id, display_order)
                values(
                    :listId,
                    :videoId,
                    :displayOrder
                );
            ");
        });
        foreach ($videoIds as $videoId) {
            $listId = Queries::GetListId($listName, $userId);
            $currentMax = DbManager::GetSingleItem("
                select max(display_order)
                from list_item
                where list_id = $listId
            ");

            $currentMax = $currentMax !== null ?  $currentMax : 0;
            $displayOrder = $currentMax + 1;

            $stmt->bindParam(":listId", $listId);
            $stmt->bindParam(":videoId", $videoId);
            $stmt->bindParam(":displayOrder", $displayOrder);
            $success = $stmt->execute();
            Queries::LogStmt($stmt, $success);
        }
    }

    /**
     * Add a video to the specified list
     */
    public static function RemoveFromList($listName, $videoIds, $userId)
    {
        $stmt = Queries::GetOrAddStatement('removeFromList', function () {
            $pdo = DbManager::getPdo();
            return $pdo->prepare("
                delete from list_item
                where video_id = :videoId
                and list_id = :listId
            ");
        });

        $listId = Queries::GetListId($listName, $userId);
        foreach ($videoIds as $videoId) {
            $stmt->bindParam(":listId", $listId);
            $stmt->bindParam(":videoId", $videoId);
            $success = $stmt->execute();
            Queries::LogStmt($stmt, $success);
        }
    }

    public static function GetVideoListInfo($userId, $videoId)
    {
        $stmt = DbManager::getPdo()->prepare("
            select
                list.name,
                list_item.video_id
            from list
            left join list_item
                on list.list_id = list_item.list_id
                and list_item.video_id = :videoId
            where list.user_id = :userId
            order by list.name asc;
        ");
        $stmt->bindParam(":videoId", $videoId);
        $stmt->bindParam(":userId", $userId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $rows = Queries::FetchAll($stmt);
        $result = [];

        foreach ($rows as $row) {
            $listName = $row['name'];
            $isInList = $row['video_id'] == null ? false : true;
            $result[$listName] = $isInList;
        }
        return $result;
    }

    public static function IsInList($listName, $videoId, $userId)
    {
        $listId = Queries::GetListId($listName, $userId);
        $stmt = Queries::GetOrAddStatement('isInList', function () {
            $pdo = DbManager::getPdo();
            return $pdo->prepare("
                select count(*) as count from list_item
                where video_id = :videoId
                and list_id = :listId
            ");
        });

        $listId = Queries::GetListId($listName, $userId);
        $stmt->bindParam(":listId", $listId);
        $stmt->bindParam(":videoId", $videoId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $count = Queries::FetchAllSingleColumn($stmt, "count")[0];
        return $count > 0;
    }

    /**
     * Adds a playlist name to the playlist table. If the playlist name already exists, this will overwrite it.
     * @param string $userId - the userId of the user who owns the playlist
     * @param string $playlistName - the name of the playlist
     * @return boolean - success or failure.
     */
    public static function AddPlaylistName($userId, $playlistName)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtAddPlaylistName == null) {
            $sql = "insert into playlist_name (user_id, name) values(:userId, :name) "
                . "on duplicate key update user_id=:userId, name=:name";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtAddPlaylistName = $stmt;
        }
        $stmt = Queries::$stmtAddPlaylistName;
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":name", $playlistName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Deletes a playlist name from the playlist_name table
     * @param string $userId - the userId of the user who owns the playlist
     * @param string $playlistName - the name of the playlist
     * @return boolean - success or failure.
     */
    public static function DeletePlaylistName($userId, $playlistName)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtDeletePlaylistName == null) {
            $sql = "delete from playlist_name where user_id=:userId and name=:name";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtDeletePlaylistName = $stmt;
        }
        $stmt = Queries::$stmtDeletePlaylistName;
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":name", $playlistName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Deletes all entries of a playlist from the playlist table
     * @param string $userId - the userId of the user who owns the playlist
     * @param string $playlistName - the name of the playlist
     * @return boolean - success or failure.
     */
    public static function DeletePlaylist($userId, $playlistName)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtDeletePlaylist == null) {
            $sql = "delete from playlist where user_id=:userId and name=:name";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtDeletePlaylist = $stmt;
        }
        $stmt = Queries::$stmtDeletePlaylist;
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":name", $playlistName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    public static function setPlaylistItems($userId, $playlistName, $items)
    {
        //pdo is annoying for this kind of query. just make a normal query
        $sql = "insert into playlist(user_id, name, item_id, idx, video_id) values";
        $comma = "";
        foreach ($items as $rank => $item) {
            $sql .= "$comma('$userId', '$playlistName', $item->itemId, $rank, $item->videoId)";
            $comma = ",";
        }
        $success = DbManager::nonQuery($sql);
        Queries::LogSql($sql, $success);
        return $success;
    }

    public static function GetPlaylistNames($userId)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetPlaylistNames == null) {
            $sql = "select distinct name from playlist_name where user_id=:userId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetPlaylistNames = $stmt;
        }
        $stmt = Queries::$stmtGetPlaylistNames;
        $stmt->bindParam(":userId", $userId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return Queries::FetchAllSingleColumn($stmt, "name");
    }

    /**
     * Retrieves the list of all video file paths currently in the database
     */
    public static function getAllVideoPathsInCurrentLibrary()
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetAllVideoPaths == null) {
            $sql = "select video_id, path from video";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetAllVideoPaths = $stmt;
        }
        $stmt = Queries::$stmtGetAllVideoPaths;
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $list = Queries::FetchAllKeyValuePair($stmt, "video_id", "path");
        return $list;
    }

    /**
     * Inserts a record into the video table
     * @param type $title -- the title of the video
     * @param type $filePath -- the full filepath of the video
     * @param type $filetype -- the filetype of the video
     * @param type $mediaType -- the media type of the video (movie, tv show, tv episode
     * @return boolean - success or failure
     */
    private static function InsertVideo($title, $sortTitle, $plot, $mpaa, $year, $url, $path, $filetype, $mediaType, $metadataModifiedDate, $posterModifiedDate, $runningTimeSeconds, $sdPosterUrl, $hdPosterUrl, $videoSourceId)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtInsertVideo == null) {
            $sql = "
                insert into video(
                    title,
                    sort_title,
                    plot,
                    mpaa,
                    year,
                    url,
                    path,
                    filetype,
                    media_type,
                    metadata_last_modified_date,
                    poster_last_modified_date,
                    running_time_seconds,
                    sd_poster_url,
                    hd_poster_url,
                    video_source_id,
                    date_added,
                    date_modified
                ) values(
                    :title,
                    :sortTitle,
                    :plot,
                    :mpaa,
                    :year,
                    :url,
                    :path,
                    :fileType,
                    :mediaType,
                    :metadataModifiedDate,
                    :posterModifiedDate,
                    :runningTimeSeconds,
                    :sdPosterUrl,
                    :hdPosterUrl,
                    :videoSourceId,
                    CURDATE(),
                    CURDATE()
                )
            ";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtInsertVideo = $stmt;
        }
        $stmt = Queries::$stmtInsertVideo;
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":sortTitle", $sortTitle);
        $stmt->bindParam(":plot", $plot);
        $stmt->bindParam(":mpaa", $mpaa);
        $stmt->bindParam(":year", $year);
        $stmt->bindParam(":url", $url);
        $stmt->bindParam(":path", $path);
        $stmt->bindParam(":fileType", $filetype);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":metadataModifiedDate", $metadataModifiedDate);
        $stmt->bindParam(":posterModifiedDate", $posterModifiedDate);
        $stmt->bindParam(":runningTimeSeconds", $runningTimeSeconds);
        $stmt->bindParam(":sdPosterUrl", $sdPosterUrl);
        $stmt->bindParam(":hdPosterUrl", $hdPosterUrl);
        $stmt->bindParam(":videoSourceId", $videoSourceId);

        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if (!$success) {
            return [
                implode(':', $stmt->errorInfo())
            ];
        } else {
            return [];
        }
    }

    /**
     * Updates a record into the video table
     * @param int $videoId - the videoId of the video to update. if -1, this function performs a new insert instead of an update
     * @param string $title -- the title of the video
     * @param string $filePath -- the full filepath of the video
     * @param string $filetype -- the filetype of the video
     * @param string $mediaType -- the media type of the video (movie, tv show, tv episode
     */
    public static function InsertOrUpdateVideo($videoId, $title, $sortTitle, $plot, $mpaa, $year, $url, $videoPath, $fileType, $mediaType, $metadataModifiedDate, $posterModifiedDate, $runningTimeSeconds, $sdPosterUrl, $hdPosterUrl, $videoSourceId)
    {
        if ($videoId == null || $videoId == -1) {
            Queries::InsertVideo($title, $sortTitle, $plot, $mpaa, $year, $url, $videoPath, $fileType, $mediaType, $metadataModifiedDate, $posterModifiedDate, $runningTimeSeconds, $sdPosterUrl, $hdPosterUrl, $videoSourceId);
        }
        $pdo = DbManager::getPdo();
        if (Queries::$stmtUpdateVideo == null) {
            $sql = "
                update video set
                    title = :title,
                    sort_title = :sortTitle,
                    plot = :plot,
                    mpaa = :mpaa,
                    year = :year,
                    url = :url,
                    path = :path,
                    filetype = :fileType,
                    media_type = :mediaType,
                    metadata_last_modified_date = :metadataModifiedDate,
                    poster_last_modified_date = :posterModifiedDate,
                    running_time_seconds = :runningTimeSeconds,
                    sd_poster_url = :sdPosterUrl,
                    hd_poster_url = :hdPosterUrl,
                    video_source_id = :videoSourceId,
                    date_modified = CURDATE()
                where
                    video_id = :videoId
            ";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtUpdateVideo = $stmt;
        }
        $stmt = Queries::$stmtUpdateVideo;
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":sortTitle", $sortTitle);
        $stmt->bindParam(":plot", $plot);
        $stmt->bindParam(":mpaa", $mpaa);
        $stmt->bindParam(":year", $year, PDO::PARAM_INT);
        $stmt->bindParam(":url", $url);
        $stmt->bindParam(":path", $videoPath);
        $stmt->bindParam(":fileType", $fileType);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":metadataModifiedDate", $metadataModifiedDate);
        $stmt->bindParam(":posterModifiedDate", $posterModifiedDate);
        $stmt->bindParam(":runningTimeSeconds", $runningTimeSeconds);
        $stmt->bindParam(":sdPosterUrl", $sdPosterUrl);
        $stmt->bindParam(":hdPosterUrl", $hdPosterUrl);
        $stmt->bindParam(":videoSourceId", $videoSourceId);
        $stmt->bindParam(":videoId", $videoId);

        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if (!$success) {
            return [
                implode(':', $stmt->errorInfo())
            ];
        } else {
            return [];
        }
    }

    public static function InsertTvEpisode($videoId, $tvShowVideoId, $seasonNumber, $episodeNumber, $writer = "", $director = "")
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtInsertTvEpisode == null) {
            $sql = "insert into tv_episode(video_id, tv_show_video_id, season_number, episode_number, writer, director)" .
                " values(:videoId, :tvShowVideoId, :seasonNumber, :episodeNumber, :writer, :director) " .
                " on duplicate key update tv_show_video_id=:tvShowVideoId, season_number=:seasonNumber,
                                    episode_number=:episodeNumber, writer=:writer, director=:director;";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtInsertTvEpisode = $stmt;
        }
        $stmt = Queries::$stmtInsertTvEpisode;
        $stmt->bindParam(":videoId", $videoId);
        $stmt->bindParam(":tvShowVideoId", $tvShowVideoId);
        $stmt->bindParam(":seasonNumber", $seasonNumber);
        $stmt->bindParam(":episodeNumber", $episodeNumber);
        $stmt->bindParam(":writer", $writer);
        $stmt->bindParam(":director", $director);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if (!$success) {
            return [
                implode(':', $stmt->errorInfo())
            ];
        } else {
            return [];
        }
    }

    /**
     * deletes all videos from the video table.
     */
    public static function TruncateTableVideo()
    {
        $pdo = DbManager::getPdo();
        $sql = "truncate table video";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Deletes all videos from the tv_episode table.
     */
    public static function TruncateTableTvEpisode()
    {
        $pdo = DbManager::getPdo();
        $sql = "truncate table tv_episode";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Deletes all rows from the tv_episode table that are no longer associated with a valid video
     */
    public static function DeleteOrphanedTvEpisodes()
    {
        $pdo = DbManager::getPdo();
        $sql = "delete from tv_episode where video_id not in (select video_id from video)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    public static function GetTvShowVideoIdFromEpisodeTable($videoId)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetTvShowVideoIdFromEpisodeTable == null) {
            $sql = "select tv_show_video_id from tv_episode where video_id = :videoId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetTvShowVideoIdFromEpisodeTable = $stmt;
        }
        $stmt = Queries::$stmtGetTvShowVideoIdFromEpisodeTable;
        $stmt->bindParam(":videoId", $videoId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $videoId = $stmt->fetch();
        if ($success === true) {
            $tvShowVideoId = $videoId["tv_show_video_id"];
            //if the tvShowVideoId is null, return -1. otherwise, return the tvShowVideoId found
            return $tvShowVideoId === null ? -1 : $tvShowVideoId;
        } else {
            return -1;
        }
    }

    public static function GetVideoIdByVideoPath($videoPath)
    {
        $videoId = null;
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetVideoIdByVideoPath == null) {
            $sql = "select video_id from video where path = :videoPath";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoIdByVideoPath = $stmt;
        }
        $stmt = Queries::$stmtGetVideoIdByVideoPath;
        $stmt->bindParam(":videoPath", $videoPath);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $row = $stmt->fetch();
        if ($success === true && $row) {
            $videoId = $row["video_id"];
            //if the videoId is null, return -1. otherwise, return the videoId found
            $videoId = $videoId === null ? -1 : $videoId;
        } else {
            $videoId = -1;
        }
        return intval($videoId);
    }

    public static function GetVideoMetadataLastModifiedDate($videoId)
    {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetVideoMetadataLastModifiedDate == null) {
            $sql = "select metadata_last_modified_date from video where video_id = :videoId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoMetadataLastModifiedDate = $stmt;
        }
        $stmt = Queries::$stmtGetVideoMetadataLastModifiedDate;
        $stmt->bindParam(":videoId", $videoId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        //if the stmt failed execution, exit failure
        if ($success === false) {
            return false;
        } else {
            //return the valid video id
            $videoId = $stmt->fetch();
            return $videoId["metadata_last_modified_date"];
        }
    }

    /**
     * Gets an associative array of the video sources
     * @return associative array of video sources
     */
    public static function GetVideoSources($type = null)
    {
        $sql = "select id, location, base_url,  media_type, security_type, refresh_videos from video_source";
        if ($type != null) {
            $sql .= " where media_type = '$type'";
        }
        $sources = DbManager::query($sql);
        Queries::LogSql($sql, $sources);
        return $sources;
    }

    /**
     * Gets an associative array of the video sources
     * @return associative array of video sources
     */
    public static function GetVideoSourcesById($ids)
    {
        $inStmt = DbManager::GenerateInStatement($ids);
        $sql = "select id, location, base_url,  media_type, security_type, refresh_videos from video_source where id $inStmt";

        $sources = DbManager::query($sql);
        Queries::LogSql($sql, $sources);
        return $sources;
    }

    /**
     * Adds a new video source to the vide_source table
     */
    public static function AddVideoSource($location, $baseUrl, $mediaType, $securityType)
    {
        if ($location != null && $baseUrl != null && $mediaType != null && $securityType != null) {
            $pdo = DbManager::getPdo();
            if (Queries::$stmtAddVideoSource == null) {
                $sql = "insert into video_source(location, base_url, media_type, security_type, refresh_videos)
                            values(:location, :baseUrl, :mediaType, :securityType, true)";
                $stmt = $pdo->prepare($sql);
                Queries::$stmtAddVideoSource = $stmt;
            }
            $stmt = Queries::$stmtAddVideoSource;
            $stmt->bindParam(":location", $location);
            $stmt->bindParam(":baseUrl", $baseUrl);
            $stmt->bindParam(":mediaType", $mediaType);
            $stmt->bindParam(":securityType", $securityType);
            $success = $stmt->execute();
            Queries::LogStmt($stmt, $success);
            return $success;
        }
        return false;
    }

    /**
     * Updates an existing video source in the database
     */
    public static function UpdateVideoSource($videoSourceId, $path, $baseUrl, $mediaType, $securityType, $refreshVideos = 1)
    {
        if ($path != null && $baseUrl != null && $mediaType != null && $securityType != null) {
            $oldVideoSource = Queries::GetVideoSourcesById([$videoSourceId])[0];
            $pdo = DbManager::getPdo();
            if (Queries::$stmtUpdateVideoSource == null) {
                $sql = "update video_source set location=:location, base_url=:baseUrl, media_type=:mediaType, security_type=:securityType, refresh_videos=:refreshVideos
                                where id=:id";
                $stmt = $pdo->prepare($sql);
                Queries::$stmtUpdateVideoSource = $stmt;
            }
            $stmt = Queries::$stmtUpdateVideoSource;
            $stmt->bindParam(":location", $path);
            $stmt->bindParam(":baseUrl", $baseUrl);
            $stmt->bindParam(":mediaType", $mediaType);
            $stmt->bindParam(":securityType", $securityType);
            $stmt->bindParam(":id", $videoSourceId);
            $stmt->bindParam(":refreshVideos", $refreshVideos);
            $success = $stmt->execute();
            Queries::LogStmt($stmt, $success);

            //replace all videos with the video source changes
            $success = $success && (Queries::StringReplace("video", "path", $oldVideoSource->location, $path));
            $success = $success && (Queries::StringReplace("video", "url", $oldVideoSource->base_url, $baseUrl));
            $success = $success && (Queries::StringReplace("video", "video_source_path", $oldVideoSource->location, $path));
            $success = $success && (Queries::StringReplace("video", "video_source_url", $oldVideoSource->base_url, $baseUrl));

            return $success;
        }
        return false;
    }

    public static function StringReplace($tableName, $columnName, $oldValue, $newValue)
    {
        if ($tableName != null && $columnName != null && $oldValue != null && $newValue != null) {
            $pdo = DbManager::getPdo();
            $sql = "UPDATE $tableName SET $columnName = REPLACE($columnName, :oldValue1, :newValue) WHERE $columnName LIKE :oldValue2;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":oldValue1", $oldValue);
            $stmt->bindParam(":newValue", $newValue);
            $fuzzyOldValue = "%$oldValue%";
            $stmt->bindParam(":oldValue2", $fuzzyOldValue);

            $success = $stmt->execute();
            Queries::LogStmt($stmt, $success);
            return $success;
        }
        return false;
    }

    /**
     * Updates the refresh_videos column in the video_source table. This is usually done once all videos have been refreshed for that video source
     * @param string $location - the location of the video source used as the primary key for the table
     * @param boolean $refreshVideos - the flag to be set to either true or false
     * @return boolean - true if successful, false if failure
     */
    public static function UpdateVideoSourceRefreshVideos($refreshVideos = false)
    {
        //if the param was not zero, then we will use a 1
        $refreshVideos = $refreshVideos != false ? true : false;
        $pdo = DbManager::getPdo();
        $sql = "update video_source set refresh_videos=:refreshVideos";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":refreshVideos", $refreshVideos);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Deletes a video source from the video_source table
     * @param string $location - the location used as the primary key to identify the video source to delete
     * @return boolean - true if successful, false if failure
     */
    public static function DeleteVideoSource($videoSourceId)
    {
        $totalSuccess = true;
        $success = Queries::DeleteVideosInSource($videoSourceId);
        $totalSuccess = $totalSuccess && $success;
        $success = DbManager::NonQuery("delete from video_source where id = ?", $videoSourceId);
        $totalSuccess = $totalSuccess && $success;

        return $totalSuccess;
    }

    public static function DeleteVideosInSource($videoSourceId)
    {
        //delete all videos that are referenced in this video source
        $videoIds = DbManager::SingleColumnQuery(
            "
                select
                    video_id
                from
                    video
                where
                    video_source_id = ?
            ",
            $videoSourceId
        );

        $success = Queries::DeleteVideos($videoIds);
        return $success;
    }

    public static function GetVideoCounts()
    {
        if (Queries::$stmtVideoCount == null) {
            $sql = "select count(*) from video where media_type=:mediaType";
            $pdo = DbManager::getPdo();
            if ($pdo == false) {
                return false;
            }
            Queries::$stmtVideoCount = $pdo->prepare($sql);
        }
        $stmt = Queries::$stmtVideoCount;
        //get movie count
        $m = Enumerations::MediaType_Movie;
        $stmt->bindParam(":mediaType", $m);
        $success = $stmt->execute();
        //if the statement was unable to be executed, return failure
        if ($success == false) {
            return false;
        }
        Queries::LogStmt($stmt, $success);

        $movieCount = $stmt->fetch();
        $movieCount = ($movieCount != null) ? $movieCount[0] : 0;
        //get tv show count
        $s = Enumerations::MediaType_TvShow;
        $stmt->bindParam(":mediaType", $s);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);

        $tvShowCount = $stmt->fetch();
        $tvShowCount = ($tvShowCount != null) ? $tvShowCount[0] : 0;

        //get tv episode count
        $e = Enumerations::MediaType_TvEpisode;
        $stmt->bindParam(":mediaType", $e);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $tvEpisodeCount = $stmt->fetch();
        $tvEpisodeCount = ($tvEpisodeCount != null) ? $tvEpisodeCount[0] : 0;

        $counts = (object) array("movieCount" => $movieCount, "tvShowCount" => $tvShowCount, "tvEpisodeCount" => $tvEpisodeCount);
        return $counts;
    }

    public static function InsertWatchVideo($userId, $videoId, $timeInSeconds)
    {
        $dateWatched = date("Y-m-d H:i:s");
        $pdo = DbManager::getPdo();
        $sql = "insert into watch_video (user_id, video_id, time_in_seconds, date_watched)
                        values(:userId, :videoId, :timeInSeconds, :dateWatched)
                        on duplicate key update time_in_seconds=:timeInSeconds,date_watched=:dateWatched";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":videoId", $videoId);
        $stmt->bindParam(":timeInSeconds", $timeInSeconds);
        $stmt->bindParam(":dateWatched", $dateWatched);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Returns an array of all videoIds that are of the specified media type
     * @param type $mediaType
     */
    public static function GetVideoIds($mediaType)
    {
        if (Queries::$stmtGetVideoIds == null) {
            $sql = "select video_id from video where media_type=:mediaType order by sort_title asc";
            $pdo = DbManager::getPdo();
            if ($pdo == false) {
                return [];
            }
            Queries::$stmtGetVideoIds = $pdo->prepare($sql);
        }
        $stmt = Queries::$stmtGetVideoIds;
        $stmt->bindParam(":mediaType", $mediaType);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if ($success == false) {
            return false;
        }
        $videoIds = DbManager::FetchAllColumn($stmt, 0);
        return $videoIds;
    }

    public static function GetLastEpisodeWatched($userId, $tvShowVideoId)
    {
        $pdo = DbManager::getPdo();
        $sql = "select w.video_id, w.time_in_seconds
                            from watch_video w, tv_episode e
                            where w.video_id = e.video_id
                            and e.tv_show_video_id = :tvShowVideoId
                            and w.user_id = :userId
                            and w.date_watched = (
                              select max(sw.date_watched)
                              from watch_video sw, tv_episode se
                              where sw.video_id = se.video_id
                              and se.tv_show_video_id = :tvShowVideoId
                              and sw.user_id = :userId
                            )";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":tvShowVideoId", $tvShowVideoId);
        $stmt->bindParam(":userId", $userId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);

        if ($success === true) {
            $rows = Dbmanager::FetchAllClass($stmt);
            if (count($rows) > 0) {
                return $rows[0];
            }
        }
        return false;
    }

    /*
     * Returns a list of videoIds pointing to episodes in the specified tv show
     */

    public static function GetTvEpisodeVideoIdsForShow($tvShowVideoId)
    {
        if (Queries::$stmtGetTvEpisodeVideoIdsForShow == null) {
            $pdo = DbManager::getPdo();
            $sql = "select video_id from tv_episode
                            where tv_show_video_id=:tvShowVideoId
                            order by season_number asc, episode_number asc";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetTvEpisodeVideoIdsForShow = $stmt;
        }
        $stmt = Queries::$stmtGetTvEpisodeVideoIdsForShow;
        $stmt->bindParam(":tvShowVideoId", $tvShowVideoId);

        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return DbManager::FetchAllClass($stmt);
    }

    /**
     * Fetch a list of all tv episode file paths from the database that are linked with the show path provided
     * @param type $showPath - the path of the tv show
     * @return array - an array of tv episode paths
     */
    public static function GetEpisodePathsByShowPath($showPath)
    {

        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetEpisodePathsByShowPath == null) {
            $sql = "select path from video where video_id in (
                                    select video_id from tv_episode where tv_show_video_id = (
                                        select video_id from video where path = :showPath
                                    )
                                )";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetEpisodePathsByShowPath = $stmt;
        }
        $stmt = Queries::$stmtGetEpisodePathsByShowPath;
        $stmt->bindParam(":showPath", $showPath);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $result = DbManager::FetchAllColumn($stmt, 0);
        return $result;
    }

    public static function GetVideos($videoIdList)
    {
        $videoIds = join(",", $videoIdList);
        if (Queries::$stmtGetVideos == null) {
            $pdo = DbManager::getPdo();
            $sql = "select * from video
                            where video_id in(:videoIds";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideos = $stmt;
        }
        $stmt = Queries::$stmtGetVideos;
        $stmt->bindParam(":videoIds", $videoIds);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if ($success === true) {
            $v = Dbmanager::FetchAllClass($stmt);
            if (count($v) > 0) {
                $v = $v[0];
                return $v;
            }
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    public static function GetVideosById($videoIds, $columns = [])
    {
        $pdo = DbManager::getPdo();
        if (count($columns) > 0) {
            $columnString = implode(",", $columns);
        } else {
            $columnString = "*";
        }
        $inStmt = DbManager::GenerateInStatement($videoIds, false);
        $sql = "select $columnString from video where video_id $inStmt";
        $stmt = $pdo->prepare($sql);
        Queries::$stmtGetVideo = $stmt;

        $success = $stmt->execute();
        if ($success === true) {
            $rows = Dbmanager::FetchAllClass($stmt);
            return $rows;
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    public static function GetVideo($videoId)
    {
        if (Queries::$stmtGetVideo == null) {
            $pdo = DbManager::getPdo();
            $sql = "select * from video
                            where video_id=:videoId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideo = $stmt;
        }
        $stmt = Queries::$stmtGetVideo;
        $stmt->bindParam(":videoId", $videoId);

        $success = $stmt->execute();
        if ($success === true) {
            $v = Dbmanager::FetchAllClass($stmt);
            if (count($v) > 0) {
                $v = $v[0];
                return $v;
            }
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    public static function GetTvEpisode($videoId)
    {
        if (Queries::$stmtGetTvEpisode == null) {
            $pdo = DbManager::getPdo();
            $sql = "select * from video v, tv_episode e
                            where v.video_id=:videoId
                            and v.video_id=e.video_id";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetTvEpisode = $stmt;
        }
        $stmt = Queries::$stmtGetTvEpisode;
        $stmt->bindParam(":videoId", $videoId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if ($success === true) {
            $v = Dbmanager::FetchAllClass($stmt);
            if (count($v) > 0) {
                $v = $v[0];
                return $v;
            }
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    public static function GetEpisodesInTvShow($tvShowVideoId)
    {
        if (Queries::$stmtGetEpisodesInTvShow == null) {
            $pdo = DbManager::getPdo();
            $sql = "select * from video v, tv_episode e
                            where e.tv_show_video_id=:videoId
                            and v.video_id=e.video_id
                            order by e.season_number asc, e.episode_number asc";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetEpisodesInTvShow = $stmt;
        }
        $stmt = Queries::$stmtGetEpisodesInTvShow;
        $stmt->bindParam(":videoId", $tvShowVideoId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if ($success === true) {
            $v = Dbmanager::FetchAllClass($stmt);
            if (count($v) > 0) {
                return $v;
            }
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    /**
     * Fetches the time in seconds that a video was last registered to have played to.
     * @param int $videoId - the videoId of the video to get the video progress of
     * @return int - the number of seconds the video was last played until, or
     */
    public static function GetVideoProgress($userId, $videoId)
    {
        if (Queries::$stmtGetVideoProgress == null) {
            $pdo = DbManager::getPdo();
            $sql = "select time_in_seconds
                                from watch_video
                                where video_id = :videoId
                                and user_id = :userId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoProgress = $stmt;
        }
        $stmt = Queries::$stmtGetVideoProgress;
        $stmt->bindParam(":videoId", $videoId);
        $stmt->bindParam(":userId", $userId);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        if ($success === true) {
            $v = Dbmanager::fetchSingleItem($stmt);
            if ($v === false) {
                return 0;
            } else {
                return intval($v);
            }
        }
        //return 0 if no videos were found or an error occurred.
        return 0;
    }

    private static function FetchAll($stmt)
    {
        $result = [];
        $val = $stmt->fetch(PDO::FETCH_ASSOC);
        while ($val != null) {
            $result[] = $val;
            $val = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    private static function FetchAllSingleColumn($stmt, $colName, $cast = null)
    {
        $result = [];
        $list = DbManager::FetchAllAA($stmt);
        foreach ($list as $item) {
            $value =  $item[$colName];
            if ($cast == 'int') {
                $value = (int) $value;
            }
            $result[] = $value;
        }
        return $result;
    }

    private static function FetchAllKeyValuePair($stmt, $keyColName, $valueColName)
    {
        $result = [];
        $list = DbManager::FetchAllAA($stmt);
        foreach ($list as $item) {
            $result[$item[$keyColName]] = $item[$valueColName];
        }
        return $result;
    }

    private static function LogSql($sql, $bSuccess)
    {
        if (config::$logQueries == true) {
            $success = ($bSuccess == true) ? "<span style='color: green;font-weight:bold;'>Success</span>" : "<span style='color:red;font-weight:bold;'>Failure</span>";
        }
    }

    private static function LogStmt($stmt, $bSuccess)
    {
        if (config::$logQueries == true) {

            ob_start();
            $success = ($bSuccess == true) ? "<span style='color: green;font-weight:bold;'>Success</span>" : "<span style='color:red;font-weight:bold;'>Failure</span>";
            $stmt->debugDumpParams();
            ob_end_clean();
        }
    }

    public static function InsertRecentlyWatched($userId, $videoId)
    {
        $dateWatched = date("Y-m-d H:i:s");

        $pdo = DbManager::getPdo();
        $sql = "
            INSERT INTO recently_watched (user_id, video_id, date_watched)
                VALUES(:userId, :videoId, :dateWatched )
                ON DUPLICATE KEY UPDATE user_id=:userId, video_id=:videoId, date_watched=:dateWatched";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":videoId", $videoId);
        $stmt->bindParam(":dateWatched", $dateWatched);
        $success = $stmt->execute();
        return $success;
    }

    /**
     * Generate a statement, or get a cached statement if it already exists
     */
    private static function GetOrAddStatement($key, $callback)
    {
        if (!isset(Queries::$statements[$key])) {
            $stmt = $callback();
            Queries::$statements[$key]  = $stmt;
        }
        return Queries::$statements[$key];
    }

    /**
     * Delete all keywords associated with a video
     */
    public static function DeleteVideoGenres($videoId)
    {
        return DbManager::NonQuery("delete from video_genre where video_id = ?", $videoId);
    }

    public static function InsertVideoGenres($videoId, $genres)
    {
        //skip insert if no genres were provided
        if (count($genres) == 0) {
            return true;
        }

        //remove duplicates
        $genres = array_unique($genres);

        $pdo = DbManager::getPdo();
        $sql = "insert into video_genre (video_id, genre) values ";
        $i = 0;
        foreach ($genres as $genre) {
            $comma = $i > 0 ? ',' : '';
            $sql .= "$comma (:videoId" . $i . ", :genre" . $i++ . ")";
        }
        $stmt = $pdo->prepare($sql);
        for ($j = 0; $j < $i; $j++) {
            $stmt->bindParam(":videoId" . $j, $videoId);
            $stmt->bindParam(":genre" . $j, $genres[$j]);
        }

        $stmt->execute();
        return true;
    }

    public static function GetVideoIdsForGenre($genre)
    {
        $videoIds = DbManager::SingleColumnQuery("
            select g.video_id
            from
                video_genre g join
                video v on v.video_id = g.video_id
            where genre = ?
            order by lower(v.title) asc
        ", $genre);
        return arrayToInt($videoIds);
    }
}
