<?php

class HomeController extends Controller {

    /**
     * Default action
     */
    function Index() {
        return RedirectToAction("Browse");
    }

    function Browse() {
        include_once(basePath() . '/Models/Home/BrowseModel.php');
        $m = new \Models\Home\BrowseModel();
        $m->process();
        return View($m);
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

    function GenerateNewLibrary() {
        //set a 1 hour time limit on this process
        set_time_limit(3600);
        include_once(dirname(__file__) . "/../Code/LibraryNew.php");
        $l = new NewLibrary();
        $l->generateLibrary();
    }

}
