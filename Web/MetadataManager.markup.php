<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="js/ManageMetadata.js"></script>
<script type="text/javascript">
    var mediaType = '<?php echo $selectedTab == null ? Enumerations::MediaType_Movie : $selectedTab; ?>';
</script>



<div class="tabbable" >
    <ul class="mediaTypeTabs nav nav-tabs">
        <li <?php echo $selectedTab == Enumerations::MediaType_Movie ? " class='active' " : ""; ?>><a href="#moviesPane" data-toggle="tab" onclick="setMediaType('<?php echo Enumerations::MediaType_Movie; ?>');">Movies</a></li>
        <li <?php echo $selectedTab == Enumerations::MediaType_TvShow ? " class='active' " : ""; ?>><a href="#tvShowsPane" data-toggle="tab" onclick="setMediaType('<?php echo Enumerations::MediaType_TvShow; ?>');">Tv Shows</a></li>
        <li <?php echo $selectedTab == Enumerations::MediaType_TvEpisode ? " class='active' " : ""; ?>><a href="#tvEpisodesPane" data-toggle="tab" onclick="setMediaType('<?php echo Enumerations::MediaType_TvEpisode; ?>');">Tv Episodes</a></li>
    </ul>
    <div class="buttonArea" style="height:20px;">
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_FetchMetadata; ?>');">Fetch Metadata</a>
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_ReloadMetadata; ?>');">Reload Metadata</a>
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_FetchPoster; ?>');">Fetch Poster</a>
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_GeneratePosters; ?>');">Generate SD and HD Posters</a>
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_FetchAndGeneratePosters; ?>');">Fetch and Generate Sd and HD Poster</a>
    </div>
    <div id="tablesArea" class="tab-content" style="border:1px solid black; min-height: 300px; overflow:auto;">
        <div id="moviesPane" class="tab-pane  <?php echo $selectedTab == Enumerations::MediaType_Movie ? "active" : ""; ?>">
            <h2>Movies</h2>
            <?php printVideoTable($movies); ?>
        </div>
        <div id="tvShowsPane" class="tab-pane  <?php echo $selectedTab == Enumerations::MediaType_TvShow ? "active" : ""; ?>">
            <h2>Tv Shows</h2>

            <?php
            printVideoTable($tvShows);
            ?>
        </div>
        <div id="tvEpisodesPane" class="tab-pane  <?php echo $selectedTab == Enumerations::MediaType_TvEpisode ? "active" : ""; ?>">
            <h2 style="position:relative;">Tv Episodes</h2>

            <?php
            $tvEpisodes = [];
            foreach ($tvShows as $tvShow) {

                foreach ($tvShow->seasons as $season) {
                    foreach ($season as $episode) {
                        $tvEpisodes[] = $episode;
                    }
                }
            }
            printVideoTable($tvEpisodes);
            ?>
        </div>
    </div>
</div>



<script type="text/javascript">
    $(document).ready(function() {
        $(".table-sort").tablesorter();
        $(".table-sort thead tr th").hover(
                function() {
                    //$(this).prop("title","sort");
                },
                function() {
                });

        $("tr").click(function() {
            $("tr.warning").removeClass("warning");
            $(this).addClass("warning").addClass("warning");
        });
        //resize the video grids to fill vertical space
        $(window).resize(resize);
        resize();
    });

    function resize() {
        var height = $(window).height() - 150;
        $("#tablesArea").height(height + "px");
        var newHeight = $("#tablesArea").height() - 70;
        $(".tableScrollArea").height(newHeight + "px");
    }
</script>

<?php

function printVideoTable($videoList) { ?>
    <div class="tableScrollArea">
        <table class="table table-hover table-sort">
            <thead>
                <tr title="sort">
                    <?php if (isset($videoList[0]) && $videoList[0]->mediaType == Enumerations::MediaType_TvEpisode) { ?>
                        <th>Series</th>
                    <?php } ?>
                    <th>Title</th>
                    <th>nfo exists</th>
                    <th>Poster Exists</th>
                    <th>SD Poster</th>
                    <th>HD Poster</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($videoList as $v) {
                    printVideoRow($v);
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

function printVideoRow($v) {
    $vSuccess = true;
    ?>
    <tr style="cursor:pointer;" class="videoRow <?php echo $vSuccess ? "success" : "error"; ?>" mediatype="<?php echo $v->mediaType; ?>" baseurl="<?php echo htmlspecialchars($v->baseUrl); ?>" basepath="<?php echo htmlspecialchars($v->basePath); ?>" fullpath="<?php echo htmlspecialchars($v->fullPath); ?>">
        <?php if ($v->mediaType == Enumerations::MediaType_TvEpisode) { ?>
            <td><?php echo $v->showName; ?></td>
        <?php } ?>
        <td><?php echo $v->title; ?></td>
        <td><?php echo false ? color("Yes", "green") : color("No", "red"); ?></td>
        <td><?php echo $v->posterExists ? color("Yes", "green") : color("No", "red"); ?></td>
        <td><img class="sd" src="<?php echo $v->sdPosterUrl; ?>"/> </td>
        <td><img class="hd" src="<?php echo $v->hdPosterUrl; ?>"/></td>

    </tr>
    <?php
}
?>