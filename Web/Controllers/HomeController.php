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
        return $this->Index();
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

}
