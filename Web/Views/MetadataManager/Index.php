<?php section("head"); ?>
.
<script type = "text/javascript" src = "<?php urlContent("~/Scripts/jquery.tablesorter.min.js"); ?>"></script>
<script type="text/javascript" src="<?php urlContent("~/Scripts/MetadataManager/Index.js"); ?>"></script>
<script type="text/javascript">
    var mediaType = "<?php echo $model->selectedTab; ?>";
    var moviesLoaded = <?php echo $model->moviesLoaded ? "true" : "false"; ?>;
    var tvShowsLoaded = <?php echo $model->tvShowsLoaded ? "true" : "false"; ?>;
    var tvEpisodesLoaded = <?php echo $model->tvEpisodesLoaded ? "true" : "false"; ?>;
</script>
<?php endSection(); ?>
<div class="tabbable" >
    <ul class="mediaTypeTabs nav nav-tabs">
        <li <?php echo $model->selectedTab == Enumerations::MediaType_Movie ? " class='active' " : ""; ?>><a href="#moviesPane" data-toggle="tab" onclick="setMediaType('<?php echo Enumerations::MediaType_Movie; ?>');">Movies</a></li>
        <li <?php echo $model->selectedTab == Enumerations::MediaType_TvShow ? " class='active' " : ""; ?>><a href="#tvShowsPane" data-toggle="tab" onclick="setMediaType('<?php echo Enumerations::MediaType_TvShow; ?>');">Tv Shows</a></li>
        <li <?php echo $model->selectedTab == Enumerations::MediaType_TvEpisode ? " class='active' " : ""; ?>><a href="#tvEpisodesPane" data-toggle="tab" onclick="setMediaType('<?php echo Enumerations::MediaType_TvEpisode; ?>');">Tv Episodes</a></li>
    </ul>
    <b>Show rows: </b>
    <label for="showAll" style="margin-left:10px;">All</label>
    <input type="radio" id="showAll" value="all" checked="checked" name="showRows"/>
    <label for="showMissing" style="margin-left:10px;">Missing something</label>
    <input type="radio" id="showMissing" value="missing" name="showRows"/>
    <div class="buttonArea" style="height:20px;">
        <a class="btn btn-default actionBtn"  data-action="<?php echo Enumerations::MetadataManagerAction_FetchMetadata; ?>">Fetch Metadata</a>
        <a class="btn btn-default actionBtn" data-action="<?php echo Enumerations::MetadataManagerAction_ReloadMetadata; ?>">Reload Metadata</a>
        <a class="btn btn-default actionBtn hide" data-action="<?php echo Enumerations::MetadataManagerAction_FetchPoster; ?>">Fetch Poster</a>
        <a class="btn btn-default actionBtn" data-action="<?php echo Enumerations::MetadataManagerAction_GeneratePosters; ?>">Generate SD and HD Posters</a>
        <a class="btn btn-default  actionBtn" data-action="<?php echo Enumerations::MetadataManagerAction_FetchAndGeneratePosters; ?>">Fetch and Generate Sd and HD Poster</a>
    </div>
    <div id="tablesArea" class="tab-content" style="border:1px solid black; min-height: 300px; overflow:auto;margin-top:10px;">
        <div id="moviesPane" class="tab-pane  <?php echo $model->selectedTab == Enumerations::MediaType_Movie ? "active" : ""; ?>">
            <h2>Movies</h2>
            <div id="moviesTableArea">
                <?php partial("~/Views/MetadataManager/_MetadataTable.php", (object) ["videos" => $model->movies, "type" => Enumerations::MediaType_Movie]); ?>
            </div>
        </div>
        <div id="tvShowsPane" class="tab-pane  <?php echo $model->selectedTab == Enumerations::MediaType_TvShow ? "active" : ""; ?>">
            <h2>Tv Shows</h2>
            <div id="tvShowsTableArea">
                <?php partial("~/Views/MetadataManager/_MetadataTable.php", (object) ["videos" => $model->tvShows, "type" => Enumerations::MediaType_TvShow]); ?>

            </div>
        </div>
        <div id="tvEpisodesPane" class="tab-pane  <?php echo $model->selectedTab == Enumerations::MediaType_TvEpisode ? "active" : ""; ?>">
            <h2 style="position:relative;">Tv Episodes</h2>
            <div id="tvEpisodesTableArea">
                <?php partial("~/Views/MetadataManager/_MetadataTable.php", (object) ["videos" => $model->tvEpisodes, "type" => Enumerations::MediaType_TvEpisode]); ?>
            </div>
        </div>
    </div>
</div>

