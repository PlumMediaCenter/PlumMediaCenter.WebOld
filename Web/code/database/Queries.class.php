<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");
include_once(dirname(__FILE__) . "/../Enumerations.class.php");

class Queries {

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
    private static $getTvEpisodeSeasonEpisodeAndVideoIdForShow = null;
    private static $stmtGetEpisodePathsByShowPath = null;
    private static $stmtGetVideo = null;
    private static $stmtGetVideos = null;
    private static $stmtGetTvEpisode = null;
    private static $stmtGetEpisodesInTvShow = null;
    private static $stmtGetVideoProgress = null;
    private static $stmtClearPlaylist = null;
    private static $stmtGetPlaylistVideoIds = null;

    public static function getPlaylistVideoIds($username, $playlistName) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetPlaylistVideoIds == null) {
            $sql = "select video_id "
                    . "from playlist "
                    . "where username = :username and name = :playlistName "
                    . "order by idx asc";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetPlaylistVideoIds = $stmt;
        }
        $stmt = Queries::$stmtGetPlaylistVideoIds;
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":playlistName", $playlistName);

        $stmt->execute();
        return Queries::fetchAllSingleColumn($stmt, "video_id");
    }

    public static function clearPlaylist($username, $playlistName) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtClearPlaylist == null) {
            $sql = "delete from playlist where username = :username and name = :playlistName";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtClearPlaylist = $stmt;
        }
        $stmt = Queries::$stmtClearPlaylist;
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":playlistName", $playlistName);
        return $stmt->execute();
    }

    public static function setPlaylistItems($username, $playlistName, $videoIds) {
        //pdo is annoying for this kind of query. just make a normal query
        $sql = "insert into playlist(username, name, idx, video_id) values";
        $comma = "";
        foreach ($videoIds as $rank => $videoId) {
            $sql .= "$comma('$username', '$playlistName', $rank, $videoId)";
            $comma = ",";
        }
        return DbManager::nonQuery($sql);
    }

    public static function getPlaylistNames($username) {
        return DbManager::singleColumnQuery("select distinct name from playlist where username='$username'");
    }

    /**
     * Retrieves the list of all video file paths currently in the database
     */
    public static function getAllVideoPathsInCurrentLibrary() {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetAllVideoPaths == null) {
            $sql = "select video_id, path from video";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetAllVideoPaths = $stmt;
        }
        $stmt = Queries::$stmtGetAllVideoPaths;
        $stmt->execute();
        $list = [];
        return Queries::fetchAllKeyValuePair($stmt, "video_id", "path");
    }

    private static function fetchAll($stmt) {
        $result = [];
        $val = $stmt->fetch(PDO::FETCH_ASSOC);
        while ($val != null) {
            $result[] = $val;
            $val = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    private static function fetchAllSingleColumn($stmt, $colName) {
        $result = [];
        $list = DbManager::fetchAllAssociative($stmt);
        foreach ($list as $item) {
            $result[] = $item[$colName];
        }
        return $result;
    }

    private static function fetchAllKeyValuePair($stmt, $keyColName, $valueColName) {
        $result = [];
        $list = DbManager::fetchAllAssociative($stmt);
        foreach ($list as $item) {
            $result[$item[$keyColName]] = $item[$valueColName];
        }
        return $result;
    }

    /**
     * Inserts a record into the video table 
     * @param type $title -- the title of the video
     * @param type $filePath -- the full filepath of the video
     * @param type $filetype -- the filetype of the video
     * @param type $mediaType -- the media type of the video (movie, tv show, tv episode   
     */
    public static function insertVideo($title, $plot, $mpaa, $releaseDate, $videoPath, $filetype, $mediaType, $metadataModifiedDate, $videoSourcePath, $videoSourceUrl, $runningTimeSeconds) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtInsertVideo == null) {
            $sql = "insert into video(title, plot, mpaa, release_date, path, filetype, media_type, metadata_last_modified_date, video_source_path, video_source_url, running_time_seconds)" .
                    " values(:title, :plot, :mpaa, :releaseDate, :filePath, :filetype, :mediaType, :metadataLastModifiedDate, :videoSourcePath, :videoSourceUrl, :runningTimeSeconds)";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtInsertVideo = $stmt;
        }
        $stmt = Queries::$stmtInsertVideo;
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":plot", $plot);
        $stmt->bindParam(":mpaa", $mpaa);
        $stmt->bindParam(":releaseDate", $releaseDate);
        $stmt->bindParam(":filePath", $videoPath);
        $stmt->bindParam(":filetype", $filetype);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":metadataLastModifiedDate", $metadataModifiedDate);
        $stmt->bindParam(":videoSourcePath", $videoSourcePath);
        $stmt->bindParam(":videoSourceUrl", $videoSourceUrl);
        $stmt->bindParam(":runningTimeSeconds", $runningTimeSeconds);
        $stmt->execute();
    }

    /**
     * Updates a record into the video table 
     * @param int $videoId - the videoId of the video to update. if -1, this function performs a new insert instead of an update
     * @param string $title -- the title of the video
     * @param string $filePath -- the full filepath of the video
     * @param string $filetype -- the filetype of the video
     * @param string $mediaType -- the media type of the video (movie, tv show, tv episode   
     */
    public static function updateVideo($videoId, $title, $plot, $mpaa, $releaseDate, $videoPath, $fileType, $mediaType, $metadataModifiedDate, $videoSourcePath, $videoSourceUrl, $runningTimeSeconds) {
        if ($videoId == null || $videoId == -1) {
            Queries::insertVideo($title, $plot, $mpaa, $releaseDate, $videoPath, $fileType, $mediaType, $metadataModifiedDate, $videoSourcePath, $videoSourceUrl, $runningTimeSeconds);
        }
        $pdo = DbManager::getPdo();
        if (Queries::$stmtUpdateVideo == null) {
            $sql = "update video set "
                    . "title = :title, plot=:plot, mpaa=:mpaa, release_date=:releaseDate, path=:path, filetype=:fileType, "
                    . "media_type=:mediaType, metadata_last_modified_date= :metadataLastModifiedDate, video_source_path=:videoSourcePath, video_source_url=:videoSourceUrl, running_time_seconds=:runningTimeSeconds "
                    . "where video_id = :videoId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtUpdateVideo = $stmt;
        }
        $stmt = Queries::$stmtUpdateVideo;
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":plot", $plot);
        $stmt->bindParam(":mpaa", $mpaa);
        $stmt->bindParam(":releaseDate", $releaseDate);
        $stmt->bindParam(":path", $videoPath);
        $stmt->bindParam(":fileType", $fileType);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":metadataLastModifiedDate", $metadataModifiedDate);
        $stmt->bindParam(":videoSourcePath", $videoSourcePath);
        $stmt->bindParam(":videoSourceUrl", $videoSourceUrl);
        $stmt->bindParam(":runningTimeSeconds", $runningTimeSeconds);
        $stmt->bindParam(":videoId", $videoId);
        $success = $stmt->execute();
        return $success;
    }

    public static function insertTvEpisode($videoId, $tvShowVideoId, $seasonNumber, $episodeNumber, $writer = "", $director = "") {
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
        return $stmt->execute();
    }

    /**
     * deletes all videos from the video table. 
     */
    public static function truncateTableVideo() {
        $pdo = DbManager::getPdo();
        $sql = "truncate table video";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    /**
     * deletes all videos from the tv_episode table. 
     */
    public static function truncateTableTvEpisode() {
        $pdo = DbManager::getPdo();
        $sql = "truncate table tv_episode";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    /**
     * Deletes all rows from the tv_episode table that are no longer associated with a valid video
     */
    public static function deleteOrphanedTvEpisodes() {
        $pdo = DbManager::getPdo();
        $sql = "delete from tv_episode where video_id not in (select video_id from video)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    /**
     * 
     * @param array $filePaths - the list of filepaths that are going to be deleted  
     * @return boolean - true if successful, false if unsuccessful
     */
    public static function deleteVideosByVideoPaths($videoPaths) {
        //if no file paths were provided, no videos will be deleted. return success.
        if (count($videoPaths) === 0) {
            return false;
        }
        $pdo = DbManager::getPdo();

        //get the list of video ids for the deleted videos
        $videoPathStmt = '';
        $notFirstTime = false;
        $videoPathStmt = DbManager::generateInStatement($videoPaths);
        $sql = "SELECT video_id FROM video WHERE path IN ($videoPathStmt)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        $videoIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        //now delete all videos with the found ids.
        $videoIdStr = DbManager::generateInStatement($videoIds, false);
        $delSql = "delete from video where video_id in ($videoIdStr)";
        $delStmt = $pdo->prepare($delSql);
        $delSuccess = $delStmt->execute();
        return $success && $delSuccess;
    }

    public static function getTvShowVideoIdFromEpisodeTable($videoId) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetTvShowVideoIdFromEpisodeTable == null) {
            $sql = "select tv_show_video_id from tv_episode where video_id = :videoId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetTvShowVideoIdFromEpisodeTable = $stmt;
        }
        $stmt = Queries::$stmtGetTvShowVideoIdFromEpisodeTable;
        $stmt->bindParam(":videoId", $videoId);
        $success = $stmt->execute();
        $videoId = $stmt->fetch();
        if ($success === true) {
            $tvShowVideoId = $videoId["tv_show_video_id"];
            //if the tvShowVideoId is null, return -1. otherwise, return the tvShowVideoId found
            return $tvShowVideoId === null ? -1 : $tvShowVideoId;
        } else {
            return -1;
        }
    }

    public static function getVideoIdByVideoPath($videoPath) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetVideoIdByVideoPath == null) {
            $sql = "select video_id from video where path = :videoPath";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoIdByVideoPath = $stmt;
        }
        $stmt = Queries::$stmtGetVideoIdByVideoPath;
        $stmt->bindParam(":videoPath", $videoPath);
        $success = $stmt->execute();
        $videoId = $stmt->fetch();
        if ($success === true) {
            $videoId = $videoId["video_id"];
            //if the videoId is null, return -1. otherwise, return the videoId found
            return $videoId === null ? -1 : $videoId;
        } else {
            return -1;
        }
    }

    public static function getVideoMetadataLastModifiedDate($videoId) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetVideoMetadataLastModifiedDate == null) {
            $sql = "select metadata_last_modified_date from video where video_id = :videoId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoMetadataLastModifiedDate = $stmt;
        }
        $stmt = Queries::$stmtGetVideoMetadataLastModifiedDate;
        $stmt->bindParam(":videoId", $videoId);
        $success = $stmt->execute();
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
    public static function getVideoSources($type = null) {
        $sql = "select location, base_url,  media_type, security_type, refresh_videos from video_source";
        if ($type != null) {
            $sql .= " where media_type = '$type'";
        }
        $sources = DbManager::query($sql);
        return $sources;
    }

    /**
     * Adds a new video source to the vide_source table
     */
    public static function addVideoSource($location, $baseUrl, $mediaType, $securityType) {

        if ($location != null && $baseUrl != null && $mediaType != null && $securityType != null) {
            $pdo = DbManager::getPdo();
            if (Queries::$stmtAddVideoSource == null) {
                $sql = "insert into video_source(location, base_url, media_type, security_type, refresh_videos) 
                values(:location, :baseUrl, :mediaType, :securityType, 1)";
                $stmt = $pdo->prepare($sql);
                Queries::$stmtAddVideoSource = $stmt;
            }
            $stmt = Queries::$stmtAddVideoSource;
            $stmt->bindParam(":location", $location);
            $stmt->bindParam(":baseUrl", $baseUrl);
            $stmt->bindParam(":mediaType", $mediaType);
            $stmt->bindParam(":securityType", $securityType);
            $success = $stmt->execute();
            return $success;
        }
        return false;
    }

    /**
     * Updates an existing video source in the database
     */
    public static function updateVideoSource($originalLocation, $newLocation, $baseUrl, $mediaType, $securityType, $refreshVideos = 1) {
        if ($originalLocation != null && $newLocation != null && $baseUrl != null && $mediaType != null && $securityType != null) {
            $pdo = DbManager::getPdo();
            if (Queries::$stmtUpdateVideoSource == null) {
                $sql = "update video_source set location=:location, base_url=:baseUrl, media_type=:mediaType, security_type=:securityType, refresh_videos=:refreshVideos
                    where location=:originalLocation";
                $stmt = $pdo->prepare($sql);
                Queries::$stmtUpdateVideoSource = $stmt;
            }
            $stmt = Queries::$stmtUpdateVideoSource;
            $stmt->bindParam(":location", $newLocation);
            $stmt->bindParam(":baseUrl", $baseUrl);
            $stmt->bindParam(":mediaType", $mediaType);
            $stmt->bindParam(":securityType", $securityType);
            $stmt->bindParam(":originalLocation", $originalLocation);
            $stmt->bindParam(":refreshVideos", $refreshVideos);

            $success = $stmt->execute();
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
    public static function updateVideoSourceRefreshVideos() {
        $pdo = DbManager::getPdo();
        $sql = "update video_source set refresh_videos=0";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        return $success;
    }

    /**
     * Deletes a video source from the video_source table
     * @param string $location - the location used as the primary key to identify the video source to delete
     * @return boolean - true if successful, false if failure
     */
    public static function deleteVideoSource($location) {
        $success = DbManager::nonQuery("delete from video_source where location = '$location'");
        return $success;
    }

    public static function getVideoCounts() {
        if (Queries::$stmtVideoCount == null) {
            $sql = "select count(*) from video where media_type=:mediaType";
            $pdo = DbManager::getPdo();
            Queries::$stmtVideoCount = $pdo->prepare($sql);
        }
        $stmt = Queries::$stmtVideoCount;
        //get movie count
        $m = Enumerations::MediaType_Movie;
        $stmt->bindParam(":mediaType", $m);
        $success = $stmt->execute();
        $movieCount = $stmt->fetch();
        $movieCount = $movieCount[0];
        //get tv show count
        $s = Enumerations::MediaType_TvShow;
        $stmt->bindParam(":mediaType", $s);
        $success = $stmt->execute();
        $tvShowCount = $stmt->fetch();
        $tvShowCount = $tvShowCount[0];

        //get tv episode count
        $e = Enumerations::MediaType_TvEpisode;
        $stmt->bindParam(":mediaType", $e);
        $success = $stmt->execute();
        $tvEpisodeCount = $stmt->fetch();
        $tvEpisodeCount = $tvEpisodeCount[0];

        $counts = (object) array("movieCount" => $movieCount, "tvShowCount" => $tvShowCount, "tvEpisodeCount" => $tvEpisodeCount);
        return $counts;
    }

    public static function insertWatchVideo($username, $videoId, $timeInSeconds) {
        $dateWatched = date("Y-m-d H:i:s");
        $pdo = DbManager::getPdo();
        $sql = "insert into watch_video (username, video_id, time_in_seconds, date_watched)
            values(:username, :videoId, :timeInSeconds, :dateWatched) 
            on duplicate key update time_in_seconds=:timeInSeconds,date_watched=:dateWatched";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":videoId", $videoId);
        $stmt->bindParam(":timeInSeconds", $timeInSeconds);
        $stmt->bindParam(":dateWatched", $dateWatched);
        $success = $stmt->execute();
        return $success;
    }

    public static function getLastEpisodeWatched($username, $tvShowVideoId) {
        $pdo = DbManager::getPdo();
        $sql = "select w.video_id, w.time_in_seconds
                from watch_video w, tv_episode e
                where w.video_id = e.video_id
                and e.tv_show_video_id = :tvShowVideoId
                and w.username = :username
                and w.date_watched = (
                  select max(sw.date_watched) 
                  from watch_video sw, tv_episode se
                  where sw.video_id = se.video_id
                  and se.tv_show_video_id = :tvShowVideoId
                  and sw.username = :username
                )";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":tvShowVideoId", $tvShowVideoId);
        $stmt->bindParam(":username", $username);
        $success = $stmt->execute();
        if ($success === true) {
            $rows = Dbmanager::fetchAllClass($stmt);
            if (count($rows) > 0) {
                return $rows[0];
            }
        }
        return false;
    }

    public static function getTvEpisodeSeasonEpisodeAndVideoIdForShow($tvShowVideoId) {
        if (Queries::$getTvEpisodeSeasonEpisodeAndVideoIdForShow == null) {
            $pdo = DbManager::getPdo();
            $sql = "select video_id, season_number, episode_number from tv_episode
                where tv_show_video_id=:tvShowVideoId";
            $stmt = $pdo->prepare($sql);
            Queries::$getTvEpisodeSeasonEpisodeAndVideoIdForShow = $stmt;
        }
        $stmt = Queries::$getTvEpisodeSeasonEpisodeAndVideoIdForShow;
        $stmt->bindParam(":tvShowVideoId", $tvShowVideoId);

        $success = $stmt->execute();
        return $success;
    }

    public static function getVideoPathsBySourcePath($videoSourcePath, $mediaType) {
        $pdo = DbManager::getPdo();
        $sql = "select path from video where media_type = :mediaType and video_source_path = :videoSourcePath";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":videoSourcePath", $videoSourcePath);
        $success = $stmt->execute();
        $result = DbManager::fetchAllColumn($stmt, 0);
        return $result;
    }

    /**
     * Fetch a list of all tv episode file paths from the database that are linked with the show path provided
     * @param type $showPath - the path of the tv show
     * @return array - an array of tv episode paths
     */
    public static function getEpisodePathsByShowPath($showPath) {

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
        $result = DbManager::fetchAllColumn($stmt, 0);
        return $result;
    }

    public static function getVideos($videoIdList) {
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
        if ($success === true) {
            $v = Dbmanager::fetchAllClass($stmt);
            if (count($v) > 0) {
                $v = $v[0];
                return $v;
            }
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    public static function getVideo($videoId) {
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
            $v = Dbmanager::fetchAllClass($stmt);
            if (count($v) > 0) {
                $v = $v[0];
                return $v;
            }
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    public static function getTvEpisode($videoId) {
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
        if ($success === true) {
            $v = Dbmanager::fetchAllClass($stmt);
            if (count($v) > 0) {
                $v = $v[0];
                return $v;
            }
        }
        //return false if no videos were found or an error occurred.
        return false;
    }

    public static function getEpisodesInTvShow($tvShowVideoId) {
        if (Queries::$stmtGetEpisodesInTvShow == null) {
            $pdo = DbManager::getPdo();
            $sql = "select * from video v, tv_episode e
                where e.tv_show_video_id=:videoId
                and v.video_id=e.video_id";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetEpisodesInTvShow = $stmt;
        }
        $stmt = Queries::$stmtGetEpisodesInTvShow;
        $stmt->bindParam(":videoId", $tvShowVideoId);

        $success = $stmt->execute();
        if ($success === true) {
            $v = Dbmanager::fetchAllClass($stmt);
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
    public static function getVideoProgress($username, $videoId) {
        if (Queries::$stmtGetVideoProgress == null) {
            $pdo = DbManager::getPdo();
            $sql = "select time_in_seconds
                    from watch_video
                    where video_id = :videoId
                    and username = :username";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoProgress = $stmt;
        }
        $stmt = Queries::$stmtGetVideoProgress;
        $stmt->bindParam(":videoId", $videoId);
        $stmt->bindParam(":username", $username);

        $success = $stmt->execute();
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

}

?>
