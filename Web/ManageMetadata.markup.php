<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<div class="tabbable">
    <ul class=" nav nav-tabs">
        <li class="active"><a href="#moviesPane" data-toggle="tab">Movies</a></li>
        <li><a href="#tvShowsPane" data-toggle="tab">Tv Shows</a></li>
        <li><a href="#tvEpisodesPane" data-toggle="tab">Tv Episodes</a></li>
    </ul>
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
    });
</script>

<?php

function printVideoTable($videoList) { ?>
    <table class="table table-hover table-sort">
        <thead>
            <tr title="sort">
                <th>Title</th>
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
    $vSuccess = $v->sdPosterExists && $v->hdPosterExists;
    ?>
    <tr class="<?php echo $vSuccess ? "success" : "error"; ?>">
        <td><?php echo $v->title; ?></td>
        <td><?php echo $v->sdPosterExists ? color("Yes", "green") : color("No", "red"); ?></td>
        <td><?php echo $v->hdPosterExists ? color("Yes", "green") : color("No", "red"); ?></td>

    </tr>
    <?php
}
?>