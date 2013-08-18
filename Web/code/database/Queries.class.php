<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");
include_once(dirname(__FILE__) . "/../Enumerations.class.php");

class Queries {

    private static $stmtInsertVideo = null;
    private static $getAllVideoPaths = null;
    private static $stmtGetVideoIdByPath = null;
    private static $stmtGetVideoMetadataLastModifiedDate = null;
    private static $stmtUpdateVideo = null;
    private static $stmtVideoCount = null;
    private static $stmtAddVideoSource = null;

    /**
     * Retrieves the list of all video file paths currently in the database
     */
    public static function getAllVideoPathsInCurrentLibrary() {
        $pdo = DbManager::getPdo();
        if (Queries::$getAllVideoPaths == null) {
            $sql = "select video_id, file_path from video";
            $stmt = $pdo->prepare($sql);
            Queries::$getAllVideoPaths = $stmt;
        }
        $stmt = Queries::$getAllVideoPaths;
        $stmt->execute();
        $list = [];
        return Queries::fetchAllKeyValuePair($stmt, "video_id", "file_path");
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
    public static function insertVideo($title, $plot, $mpaa, $releaseDate, $filePath, $filetype, $mediaType, $metadataModifiedDate) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtInsertVideo == null) {
            $sql = "insert into video(title, plot, mpaa, release_date, file_path, filetype, media_type, metadata_last_modified_date)" .
                    " values(:title, :plot, :mpaa, :releaseDate, :filePath, :filetype, :mediaType, :metadataLastModifiedDate)";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtInsertVideo = $stmt;
        }
        $stmt = Queries::$stmtInsertVideo;
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":plot", $plot);
        $stmt->bindParam(":mpaa", $mpaa);
        $stmt->bindParam(":releaseDate", $releaseDate);
        $stmt->bindParam(":filePath", $filePath);
        $stmt->bindParam(":filetype", $filetype);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":metadataLastModifiedDate", $metadataModifiedDate);

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
    public static function updateVideo($videoId, $title, $plot, $mpaa, $releaseDate, $filePath, $fileType, $mediaType, $metadataModifiedDate) {
        if ($videoId == null || $videoId == -1) {
            Queries::insertVideo($title, $plot, $mpaa, $releaseDate, $filePath, $fileType, $mediaType, $metadataModifiedDate);
        }
        $pdo = DbManager::getPdo();
        if (Queries::$stmtUpdateVideo == null) {
            $sql = "update video set "
                    . "video_title = :title, plot=:plot, mpaa=:mpaa, release_date=:releaseDate, file_path=:filePath, filetype=:fileType, "
                    . "media_type=:mediaType, metadata_last_modified_date= :metadataLastModifiedDate "
                    . "where video_id = :videoId";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtUpdateVideo = $stmt;
        }
        $stmt = Queries::$stmtUpdateVideo;
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":plot", $plot);
        $stmt->bindParam(":mpaa", $mpaa);
        $stmt->bindParam(":releaseDate", $releaseDate);
        $stmt->bindParam(":filePath", $filePath);
        $stmt->bindParam(":fileType", $fileType);
        $stmt->bindParam(":mediaType", $mediaType);
        $stmt->bindParam(":metadataLastModifiedDate", $metadataModifiedDate);
        $stmt->bindParam(":videoId", $videoId);
        $stmt->execute();
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
     * 
     * @param array $filePaths - the list of filepaths that are going to be deleted  
     * @return boolean - true if successful, false if unsuccessful
     */
    public static function deleteVideosByFilePaths($filePaths) {
        //if no file paths were provided, no videos will be deleted. return success.
        if (count($filePaths) === 0) {
            return false;
        }
        $pdo = DbManager::getPdo();

        //get the list of video ids for the deleted videos
        $filePathsStmt = '';
        $notFirstTime = false;
        $filePathsStmt = DbManager::generateInStatement($filePaths);
        $sql = "SELECT video_id FROM video WHERE file_path IN ($filePathsStmt)";
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

    public static function getVideoIdByPath($filePath) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtGetVideoIdByPath == null) {
            $sql = "select video_id from video where file_path = :filePath";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtGetVideoIdByPath = $stmt;
        }
        $stmt = Queries::$stmtGetVideoIdByPath;
        $stmt->bindParam(":filePath", $filePath);
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
        $sql = "select location, base_url,  media_type, security_type from video_source";
        if($type != null){
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
                $sql = "insert into video_source(location, base_url, media_type, security_type) 
                values(:location, :baseUrl, :mediaType, :securityType)";
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

}
?>
