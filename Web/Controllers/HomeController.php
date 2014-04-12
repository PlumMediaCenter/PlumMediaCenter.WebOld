<?php

class HomeController extends Controller {

    /**
     * Default action
     */
    function Index() {
        include_once(basePath() . '/Code/Library.class.php');
        $lib = new Library();
        $lib->loadFromDatabase();
        if (count($lib->invalidVideos) > 0) {
            message("$lib->invalidVideos were unable to be loaded. Please regenerate library to fix this problem");
        }
        return View((object) ['videos' => $lib->moviesAndTvShows]);
    }

    function Browse() {
        return RedirectToAction("Index");
    }

    function Genre() {
        return View();
    }

    function TestApi() {
        return View();
    }

    function Search($q) {
        include_once(basePath() . '/code/Library.class.php');
        $videos = Library::SearchByTitle($q);
        return View((object) ['videos' => $videos], 'Index');
    }

    function VideoInfo($videoId) {
        include_once(basePath() . '/Code/Video.class.php');
        $video = Video::GetVideo($videoId);
        //if this is a tv show, load the episodes from the database
        if ($video->getMediaType() == Enumerations\MediaType::TvShow) {
            $video->loadEpisodesFromDatabase();
        }
        return View((object) ['video' => $video]);
    }

    function Play($videoId) {
        include_once(basePath() . '/Models/PlayModel.php');
        $model = new PlayModel();
        $model->init($videoId);
        return View($model);
    }

    function PlayPlaylist($playlistName) {
        include_once(basePath() . '/Models/PlayModel.php');
        $model = new PlayModel();
        $model->initPlaylist($playlistName);
    }

    function Test() {
        include_once(dirname(__file__) . "/../Code/NewLibrary.class.php");
        $l = new NewLibrary();
        $l->generateLibrary();
    }

}
