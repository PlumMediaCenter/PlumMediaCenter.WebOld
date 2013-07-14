<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="js/ManageMetadata.js"></script>
<div class="tabbable">
    <ul class=" nav nav-tabs">
        <li class="active"><a href="#moviesPane" data-toggle="tab">Movies</a></li>
        <li><a href="#tvShowsPane" data-toggle="tab">Tv Shows</a></li>
        <li><a href="#tvEpisodesPane" data-toggle="tab">Tv Episodes</a></li>
    </ul>
    <div class="buttonArea" style="height:20px;">
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_FetchMetadata;?>');">Fetch Metadata</a>
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_ReloadMetadata;?>');">Reload Metadata</a>
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_FetchPoster;?>');">Fetch Poster</a>
        <a class="btn action " onclick="action('<?php echo Enumerations::MetadataManagerAction_GeneratePosters;?>');">Generate SD and HD Posters</a>
    </div>
    <div class="tab-content">
        <div id="moviesPane" class="tab-pane active">
            <h2>Movies</h2>
            <?php printVideoTable($movies); ?>
        </div>
        <div id="tvShowsPane" class="tab-pane">
            <h2>Tv Shows</h2>

            <?php
            printVideoTable($tvShows);
            ?>
        </div>
        <div id="tvEpisodesPane" class="tab-pane">
            <h2>Tv Episodes</h2>

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
            });
</script>

<?php

function printVideoTable($videoList) { ?>
    <table class="table table-hover table-sort">
        <thead>
            <tr title="sort">
                <th>Title</th>
                <th>nfo file exists</th>
                <th>Poster Exists</th>
                <th>Has SD Poster</th>
                <th>Has HD Poster</th>
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
    <?php
}

function printVideoRow($v) {
    $vSuccess = $v->getSdPosterExists() && $v->getHdPosterExists();
    ?>
    <tr style="cursor:pointer;" class="<?php echo $vSuccess ? "success" : "error"; ?>" mediatype="<?php echo $v->getMediaType(); ?>" baseurl="<?php echo htmlspecialchars($v->baseUrl); ?>" basepath="<?php echo htmlspecialchars($v->basePath); ?>" fullpath="<?php echo htmlspecialchars($v->fullPath); ?>">
        <td><?php echo $v->title; ?></td>
        <td><?php echo $v->hasMetadata() ? color("Yes", "green") : color("No", "red"); ?></td>
        <td><?php echo $v->posterExists() ? color("Yes", "green") : color("No", "red"); ?></td>
        <td><?php echo $v->getSdPosterExists() ? color("Yes", "green") : color("No", "red"); ?> </td>
        <td><?php echo $v->getHdPosterExists() ? color("Yes", "green") : color("No", "red"); ?> </td>

    </tr>
    <?php
}
?>