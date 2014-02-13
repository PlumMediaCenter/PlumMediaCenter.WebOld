<?php

class APIController extends Controller {

    function DeleteVideoSource($sourcePath) {
        include_once(basePath() . '/Code/database/Queries.class.php');
        include_once(basePath() . '/Code/Video.class.php');

        $success = Queries::DeleteVideoSource($sourcePath);
        //delete every video found in the source
        $videoIds = Queries::GetVideoIdsInSource($sourcePath);
        foreach ($videoIds as $videoId) {
            $deleteSuccess = Video::DeleteVideo($videoId);
            $success = $success && $deleteSuccess;
        }

        return json($success);
    }

    function FetchMissingMetadataAndPosters() {
        require_once(basePath() . '/Code/Library.class.php');

        //allow up to half hour for this script to run
        set_time_limit(1800);
        $result = (object) [];

        $l = new Library();
        $result->successLoadingFromDatabase = $l->loadFromDatabase();
        $result->successFetchingMetadataAndPosters = $l->fetchMissingMetadataAndPosters();
        $result->successWritingToDb = $l->writeToDb();
        $result->successWritingLibraryJson = $l->writeLibraryJson();
        $result->success = $result->successLoadingFromDatabase && $result->successWritingToDb && $result->successFetchingMetadataAndPosters && $result->successWritingLibraryJson;
        return json($result);
    }

    function GetGenreList() {
        $genreNames = DbManager::singleColumnQuery("select genre_name from genre", "genre_name");
        return json($genreNames);
    }

    function GetGenreVideos($genreName) {
        include_once(basePath() . '/Code/Video.class.php');
        $genreVideos = DbManager::query(Video::baseQuery . " where video_id in("
                        . " select video_id "
                        . " from video_genre "
                        . " where genre_name = '$genreName'"
                        . ")");
        return json($genreVideos);
    }

    /**
     * Re-generates the library. It will scan all source folders and 
     * add any videos it finds to the library, as well as remove any videos no longer found
     * in the watch folder
     * @returns boolean - true if successful, false if failure or error
     */
    function GenerateLibrary() {
        require_once(basePath() . '/Code/Library.class.php');
        $l = new Library();
        $successLoadingFromFilesystem = $l->loadFromFilesystem();
        $successWritingToDb = $l->writeToDb();
        $success = $successLoadingFromFilesystem && $successWritingToDb;
        return json($success);
    }

    function GetLibrary() {
        include_once(basePath() . '/Code/Video.class.php');
        $library = [];
        $baseQuery = Video::baseQuery;
        $library["movies"] = DbManager::Query("$baseQuery where media_type = '" . Enumerations::MediaType_Movie . "' order by title asc");
        $library["tvShows"] = DbManager::Query("$baseQuery where media_type =  '" . Enumerations::MediaType_TvShow . "'  order by title asc");
        return json($library);
    }

    function GetNextEpisode($videoId = -1) {
        include_once(basePath() . '/Code/TvShow.class.php');
        $episode = TvShow::GetNextEpisodeToWatch($videoId);

        if ($episode == null) {
            return json(false);
        } else {
            $episode->startSeconds = $episode->videoStartSeconds();
            return json($episode);
        }
    }

    function GetTvShow($videoId = -1) {
        include_once(basePath() . '/Code/Video.class.php');

        $show = Video::GetVideo($videoId);
        $show->loadEpisodesFromDatabase();
        return json($show);
    }

    function GetVideoProgress($videoId = -1) {
        include_once(basePath() . '/Code/Video.class.php');
        $seconds = Video::GetVideoStartSeconds($videoId);
        $result = (object) [];
        $result->videoId = $videoId;
        $result->startSeconds = $seconds;
        return json($result);
    }

    function ServerExists() {
        return json(true);
    }

    function SetVideoProgress($videoId = -1, $seconds = 0, $finished = false) {
        require_once(basePath() . 'Code/database/Queries.class.php');
        require_once(basePath() . 'Code/Video.class.php');
        $timeInSeconds = seconds;
        //if the finished flag was set, retrieve the total length of this video and save THAT time in the watchVideo table so we know this video is finished
        if ($finished === "true") {
            $v = Video::GetVideo($videoId);
            $sec = $v->getLengthInSeconds();
            //if the length was determined, use it
            if ($sec !== false) {
                $timeInSeconds = $sec;
            } else {
                //set the time in seconds to be negative so we know this video is finished, even though we don't know what the actual length is
                $timeInSeconds = -1;
            }
        }
        $success = Queries::insertWatchVideo(Security::GetUsername(), $videoId, $timeInSeconds);
        $result = (object) [];
        $result->success = $success;
        echo json_encode($result);
    }

    function SetPlaylist($playlistName) {
        require_once(basePath() . 'Code/Playlist.class.php');

        $videoIds = [];
        //get the videoIds. if they are in the form of an array, use the array. if they are not, create an array
        if (isset($_GET["videoIds"])) {
            if (is_array($_GET["videoIds"])) {
                $videoIds = $_GET["videoIds"];
            } else {
                $videoIds[] = intval($_GET["videoIds"]);
            }
        }

        $success = Playlist::AddPlaylist(Security::GetUsername(), $playlistName, $videoIds);
        return json((object) ["success" => $success]);
    }

    /**
     * Add a new playlist
     * @param type $playlistName
     * @return type
     */
    function AddToPlaylist($playlistName, $videoIds = []) {
        require_once(basePath() . '/Code/Playlist.class.php');
        $username = security::GetUsername();

        $p = new Playlist($username, $playlistName);
        //load from the database
        $p->loadFromDb();
        //append any new videos
        $p->addRange($videoIds);
        //save changes
        return json($p->writeToDb());
    }

    function DeletePlaylist($playlistName) {
        return json(Playlist::DeletePlaylist(Security::GetUsername(), $playlistName));
    }

    function GetPage($playlistName, $videoIds = []) {
        require_once(basePath() . 'Code/Playlist.class.php');

        $p = new Playlist(Security::GetUsername(), $playlistName);
        //load from the database
        $p->loadFromDb();
        //append any new videos
        $p->addRange($videoIds);
        //save changes
        return json($p->writeToDb());
    }

    function PlaylistItemFinished($playlistName = "", $playlistItemId = "") {
        require_once(basePath() . 'Code/Playlist.class.php');

        //remove the first item from the playlist
        Playlist::RemoveItem(Security::GetUsername(), $playlistName, $playlistItemId);
        return json($video);
    }

    function GetPlaylist($playlistName) {
        require_once(basePath() . 'Code/Playlist.class.php');

        $p = new Playlist(Security::GetUsername(), $playlistName);
        $p->loadFromDb();
        return json($p->getPlaylistVideos());
    }

    function GetPlaylistNames() {
        require_once(basePath() . 'Code/Playlist.class.php');

        return json(Playlist::GetPlaylistNames(Security::GetUsername()));
    }

    function GetPlaylists() {
        require_once(basePath() . 'Code/Playlist.class.php');

        return json(Playlist::GetPlaylists(Security::GetUsername()));
    }

    function GetNextPlaylistItem($playlistName = "") {
        require_once(basePath() . '/Code/Playlist.class.php');

        $video = Playlist::GetFirstVideo(Security::GetUsername(), $playlistName);
        return json($video);
    }

}
