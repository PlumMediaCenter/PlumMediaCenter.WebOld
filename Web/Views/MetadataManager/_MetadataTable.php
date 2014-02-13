<div class="tableScrollArea">
    <table class="table table-sort">
        <thead>
            <tr title="sort">
                <?php if ($model->type == Enumerations::MediaType_TvEpisode) { ?>
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
            foreach ($model->videos as $v) {
                echo MetadataManagerController::GetVideoMetadataRow($v);
            }
            ?>
        </tbody>
    </table>
</div>