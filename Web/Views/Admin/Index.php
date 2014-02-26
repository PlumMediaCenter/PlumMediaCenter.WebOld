<?php section("head"); ?>
<script type="text/javascript" src="<?php urlContent("~/Scripts/Admin/Index.js");?>"></script>
<script type="text/javascript">

    function fetchMissingMetadataAndPosters() {
        bootbox.alert("Fetching missing metadata and posters. <img src='img/ajax-loader.gif'/>");
        $.ajax({url: "api/FetchMissingMetadataAndPosters.php", dataType: "json", complete: function(result, status) {
                bootbox.hideAll();
                if (status === "success") {
                    bootbox.alert("Successfully fetched all missing metadata and posters");
                } else {
                    bootbox.alert("There was an error fetching missing metadata and posters. Please see the <a href='Log.php'>log</a> for more information");
                }
            }
        });
    }
</script>
<?php endSection(); ?>

<p id="admin-stats" class="center bold"><?php echo "$model->movieCount Movies &bull; $model->tvShowCount Tv Shows &bull; $model->tvEpisodeCount Tv Episodes"; ?></p>
<a id="generateLibraryBtn" class="btn btn-default">Generate/Update library</a>
<br/>
<br/>
<a href="<?php urlAction('VideoSources/Index');?>" class="btn btn-default">Manage Video Sources (<?php echo $model->videoSourceCount;?>)</a> 
<br/>
<br/>
<a id="metadataManagerBtn" href="<?php urlAction("MetadataManager/Index");?>" class="btn btn-default">Manage Metadata</a>

<br/>
<br/>
<a class="btn btn-default" onclick="fetchMissingMetadataAndPosters();">Fetch and Generate Missing Metadata and Posters</a>
<br/>
<br/>
<a href='Log.php' class="btn btn-default">View Log</a>
<br/>
<br/>
<a href="<?php urlAction('Setup');?>" class="btn btn-default">Install/Update Database</a> Current database model version: <?php echo $model->currentDbVersion; ?>. Latest available db model version: <?php echo $model->latestDbVersion; ?>. 

<div id="videosJsonModal" class="modal hide" style="width: 1000px; margin-left: -500px;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel"></h3>
        <div id="videosJsonModalContent" class="modal-body"></div>
    </div>
</div>