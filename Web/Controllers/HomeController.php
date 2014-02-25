<?php

class HomeController extends Controller {

    /**
     * Default action
     */
    function Index() {
        include_once(basePath() . '/Code/Library.class.php');
        $lib = new Library();
        $lib->loadFromDatabase();
        return View((object) ['videos' => $lib->moviesAndTvShows]);
    }

    function Browse() {
        return redirectToAction("Index");
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
        if ($video->getMediaType() == Enumerations::MediaType_TvShow) {
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

}
