<?php

class AdminController {

    function Index() {
        include_once(basePath() . "/code/Library.class.php");
        include_once(basePath() . "/code/database/CreateDatabase.class.php");

        $counts = Library::GetVideoCounts();
        $model = (object) [];
        $model->videoCount = $this->videoCount = $counts->videoCount;
        $model->currentDbVersion = CreateDatabase::CurrentDbVersion();
        $model->latestDbVersion = CreateDatabase::LatestDbVersion();

        $model->movieCount = $counts->movieCount;
        $model->tvShowCount = $counts->tvShowCount;
        $model->tvEpisodeCount = $counts->tvEpisodeCount;
        return view($model);
    }

    function Setup(){
        return view();
    }

}
