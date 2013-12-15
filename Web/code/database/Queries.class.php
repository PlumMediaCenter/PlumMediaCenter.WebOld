<?php

include_once(dirname(__FILE__) . "/../functions.php");

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
    private static $stmtGetTvEpisodeVideoIdsForShow = null;
    private static $stmtGetEpisodePathsByShowPath = null;
    private static $stmtGetVideo = null;
    private static $stmtGetVideos = null;
    private static $stmtGetTvEpisode = null;
    private static $stmtGetEpisodesInTvShow = null;
    private static $stmtGetVideoProgress = null;
    private static $stmtClearPlaylist = null;
    private static $stmtGetPlaylistVideoIds = null;
    private static $stmtAddPlaylistName = null;
    private static $stmtDeletePlaylistName = null;
    private static $stmtDeletePlaylist = null;
    private static $stmtGetPlaylistNames = null;
    private static $stmtGetVideoIds = null;

    public static function GetTvShowFirstEpisode($tvShowVideoId) {
        $notInStmt = DbManager::NotIn($videoIdsToKeep, false);
        $pdo = DbManager::getPdo();
        $sql = "delete from video where video_id $notInStmt";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        Queries::LogSql($sql, $success);
        return $success;
    }

    /**
     * Deletes any videos that are not in the list of videos to keep.
     * @param int[] $videoIdsToKeep - the list of videoIds to keep. Any video not in this list will be deleted.
     * @return boolean - true if successful, false if failure
     */
    public static function DeleteVideosNotInThisList($videoIdsToKeep) {
        $notInStmt = DbManager::NotIn($videoIdsToKeep, false);
        $notInStmt = (strlen($notInStmt) != false) ? "where video_id $notInStmt" : "";

        //delete all references to this video in the following tables: tv_episode, video, watch_video
        $sql = "delete from watch_video $notInStmt";
        $success = DbManager::NonQuery($sql);
        Queries::LogSql($sql, $success);
        $sql1 = "delete from tv_episode $notInStmt";
        $success = $success && DbManager::NonQuery($sql1);
        Queries::LogSql($sql1, $success);
        $sql2 = "delete from video $notInStmt";
        $success = $success && DbManager::NonQuery($sql2);
        Queries::LogSql($sql2, $success);
        return $success;
    }

    public static function getPlaylistItems($username, $playlistName) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetPlaylistVideoIds == null) {
            $sql = "select item_id, video_id "
                    . "from playlist "
                    . "where username = :username and name = :playlistName "
                    . "order by idx asc";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetPlaylistVideoIds = $stmt;
        }
        $stmt = Queries::$stmtGetPlaylistVideoIds;
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":playlistName", $playlistName);

        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);

        return Queries::FetchAll($stmt, "video_id");
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
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Adds a playlist name to the playlist table. If the playlist name already exists, this will overwrite it.
     * @param string $username - the username of the user who owns the playlist
     * @param string $playlistName - the name of the playlist 
     * @return boolean - success or failure.
     */
    public static function AddPlaylistName($username, $playlistName) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtAddPlaylistName == null) {
            $sql = "insert into playlist_name (username, name) values(:username, :name) "
                    . "on duplicate key update username=:username, name=:name";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtAddPlaylistName = $stmt;
        }
        $stmt = Queries::$stmtAddPlaylistName;
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":name", $playlistName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Deletes a playlist name from the playlist_name table
     * @param string $username - the username of the user who owns the playlist
     * @param string $playlistName - the name of the playlist 
     * @return boolean - success or failure.
     */
    public static function DeletePlaylistName($username, $playlistName) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtDeletePlaylistName == null) {
            $sql = "delete from playlist_name where username=:username and name=:name";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtDeletePlaylistName = $stmt;
        }
        $stmt = Queries::$stmtDeletePlaylistName;
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":name", $playlistName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Deletes all entries of a playlist from the playlist table
     * @param string $username - the username of the user who owns the playlist
     * @param string $playlistName - the name of the playlist 
     * @return boolean - success or failure.
     */
    public static function DeletePlaylist($username, $playlistName) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtDeletePlaylist == null) {
            $sql = "delete from playlist where username=:username and name=:name";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtDeletePlaylist = $stmt;
        }
        $stmt = Queries::$stmtDeletePlaylist;
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":name", $playlistName);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    public static function setPlaylistItems($username, $playlistName, $items) {
        //pdo is annoying for this kind of query. just make a normal query
        $sql = "insert into playlist(username, name, item_id, idx, video_id) values";
        $comma = "";
        foreach ($items as $rank => $item) {
            $sql .= "$comma('$username', '$playlistName', $item->itemId, $rank, $item->videoId)";
            $comma = ",";
        }
        $success = DbManager::nonQuery($sql);
        Queries::LogSql($sql, $success);
        return $success;
    }

    public static function GetPlaylistNames($username) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetPlaylistNames == null) {
            $sql = "select distinct name from playlist_name where username=:username";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetPlaylistNames = $stmt;
        }
        $stmt = Queries::$stmtGetPlaylistNames;
        $stmt->bindParam(":username", $username);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return Queries::FetchAllSingleColumn($stmt, "name");
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
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
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
        Queries::LogStmt($stmt, $success);
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
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * deletes all videos from the video table. 
     */
    public static function truncateTableVideo() {
        $pdo = DbManager::getPdo();
        $sql = "truncate table video";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * deletes all videos from the tv_episode table. 
     */
    public static function truncateTableTvEpisode() {
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
    public static function deleteOrphanedTvEpisodes() {
        $pdo = DbManager::getPdo();
        $sql = "delete from tv_episode where video_id not in (select video_id from video)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
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
        $success = $success && $delSuccess;
        Queries::LogStmt($stmt, $success);
        return $success;
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
        Queries::LogStmt($stmt, $success);
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
    public static function getVideoSources($type = null) {
        $sql = "select location, base_url,  media_type, security_type, refresh_videos from video_source";
        if ($type != null) {
            $sql .= " where media_type = '$type'";
        }
        $sources = DbManager::query($sql);
        Queries::LogSql($sql, $sources);
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
    public static function updateVideoSourceRefreshVideos($refreshVideos = false) {
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
    public static function DeleteVideoSource($location) {
        $pdo = DbManager::getPdo();
        $sql = "delete from video_source where location = :location";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":location", $location);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    public static function GetVideoCounts() {
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
        Queries::LogStmt($stmt, $success);
        return $success;
    }

    /**
     * Returns an array of all videoIds that are of the specified media type
     * @param type $mediaType
     */
    public static function GetVideoIds($mediaType) {
        if (Queries::$stmtGetVideoIds == null) {
            $sql = "select video_id from video where media_type=:mediaType";
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

    public static function GetTvEpisodeVideoIdsForShow($tvShowVideoId) {
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

    public static function getVideoPathsBySourcePath($videoSourcePath, $mediaType) {
        $pdo = DbManager::getPdo();
        $sql = "select path from video where media_type = :mediaType and video_source_path = :videoSourcePath";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":videoSourcePath", $videoSourcePath);
        $success = $stmt->execute();
        Queries::LogStmt($stmt, $success);
        $result = DbManager::FetchAllColumn($stmt, 0);
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
        Queries::LogStmt($stmt, $success);
        $result = DbManager::FetchAllColumn($stmt, 0);
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

    public static function GetVideo($videoId) {
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

    public static function GetEpisodesInTvShow($tvShowVideoId) {
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

    private static function FetchAll($stmt) {
        $result = [];
        $val = $stmt->fetch(PDO::FETCH_ASSOC);
        while ($val != null) {
            $result[] = $val;
            $val = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    private static function FetchAllSingleColumn($stmt, $colName) {
        $result = [];
        $list = DbManager::FetchAllAssociative($stmt);
        foreach ($list as $item) {
            $result[] = $item[$colName];
        }
        return $result;
    }

    private static function FetchAllKeyValuePair($stmt, $keyColName, $valueColName) {
        $result = [];
        $list = DbManager::FetchAllAssociative($stmt);
        foreach ($list as $item) {
            $result[$item[$keyColName]] = $item[$valueColName];
        }
        return $result;
    }

    private static function LogSql($sql, $bSuccess) {
        if (config::$logQueries == true) {
            $success = ($bSuccess == true) ? "<span style='color: green;font-weight:bold;'>Success</span>" : "<span style='color:red;font-weight:bold;'>Failure</span>";
            writeToLog("Query: $success: $sql");
        }
    }

    private static function LogStmt($stmt, $bSuccess) {
        if (config::$logQueries == true) {

            ob_start();
            $success = ($bSuccess == true) ? "<span style='color: green;font-weight:bold;'>Success</span>" : "<span style='color:red;font-weight:bold;'>Failure</span>";
            $stmt->debugDumpParams();
            writeToLog("Query: $success: " . ob_get_contents());
            ob_end_clean();
        }
    }

}

?>
