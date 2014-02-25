<?php

class AdminController {

    function Index() {
        include_once(basePath() . "/code/Library.class.php");
        include_once(basePath() . "/code/database/Queries.class.php");
        include_once(basePath() . "/code/database/CreateDatabase.class.php");
        include_once(basePath() . "/Models/AdminModel.php");

        $counts = Library::GetVideoCounts();
        $model = new AdminModel();
        $model->videoCount = $this->videoCount = $counts->videoCount;
        $model->currentDbVersion = CreateDatabase::CurrentDbVersion();
        $model->latestDbVersion = CreateDatabase::LatestDbVersion();

        $model->movieCount = $counts->movieCount;
        $model->tvShowCount = $counts->tvShowCount;
        $model->tvEpisodeCount = $counts->tvEpisodeCount;
        $model->videoSourceCount = count(Queries::GetVideoSources());
        return view($model);
    }

    function Setup() {
        return view();
    }

}
