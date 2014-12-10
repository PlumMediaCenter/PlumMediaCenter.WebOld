<?php include_once(dirname(__FILE__) . '/code/database/CreateDatabase.class.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-7">
            <br/>
            <a onclick="generateLibrary();" class="btn btn-default">Generate/Update library</a>
            <br/>
            <br/>
            <a href='VideoSources.php' class="btn btn-default">Add/Remove Video Sources</a>
            <br/>
            <br/>
            <a href='api/FetchMissingMetadataAndPosters.php' class="btn btn-default">Fetch and Load Missing Metadata and Posters</a>
            <br/>
            <br/>
            <a id="metadataManagerBtn" href-original="MetadataManager.php" href="MetadataManager.php" class="btn btn-default">Manage Metadata</a>
            <br/>
            <label class="pointer"><input id="allMetadataType" checked="checked" type="radio" value="" name="metadataType"/>All Media</label>&nbsp;&nbsp;&nbsp;
            <label class="pointer"><input id="movieMetadataType" type="radio" value="<?php echo Enumerations::MediaType_Movie; ?>" name="metadataType"/>Movies Only</label>&nbsp;&nbsp;&nbsp;
            <label class="pointer"><input id="tvShowMetadataType" type="radio" value="<?php echo Enumerations::MediaType_TvShow; ?>" name="metadataType"/>Tv Shows/Episodes Only</label>

            <br/>
            <br/>
            <form action="api/Update.php">
                <button type="submit" class="btn btn-default">Check for and install updates</button>
                <label><input type="checkbox" name="force" value="true"/> Force latest update to install</label>
                <br/>Currently installed version <?php echo CreateDatabase::CurrentDbVersion(); ?>
            </form>

            <div id="generateLibraryModal" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                            <p></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <h3>Summary</h3>
            <b>Video Count:</b>
            <?php echo ($videoCount != null) ? $videoCount : "-"; ?>
            <br/>
            <b>Movie Count:</b>
            <?php echo ($movieCount != null) ? $movieCount : "-"; ?>
            <br/>
            <b>Tv Show Count:</b>
            <?php echo ($tvShowCount != null) ? $tvShowCount : "-"; ?>
            <br/>
            <b>Tv Episode Count:</b>
            <?php echo ($tvEpisodeCount != null) ? $tvEpisodeCount : "-"; ?>
            <br/>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#adminNav").addClass("active");
        $("[name=metadataType]").click(function() {
            $("#metadataManagerBtn").attr("href", $("#metadataManagerBtn").attr("href-original") + "?mediaType=" + $(this).val());
        });
    });

    function generateLibrary() {
        $("#generateLibraryModal").modal("show", true);
        var $modalBody = $("#generateLibraryModal .modal-body");
        $modalBody.html("Generating Library. <img src='img/ajax-loader.gif'/>");
        $.ajax({url: "api/GenerateLibrary.php", dataType: "json", complete: function(result, status) {
                if (status === "success") {
                    $modalBody.html("Library has been successfully generated and is up to date.");
                } else {
                    $modalBody.html("There was an error generating library. Please see the <a href='Log.php'>log</a> for more information");
                }
            }
        });
    }

</script>