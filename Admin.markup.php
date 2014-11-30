<?php include_once(dirname(__FILE__) . '/code/database/CreateDatabase.class.php'); ?>
<a onclick="generateLibrary();" class="btn btn-default">Generate/Update library</a>
<br/>
<br/>
<a href='VideoSources.php' class="btn btn-default">Add/Remove Video Sources</a>
<br/>
<br/>
<a id="metadataManagerBtn" href-original="MetadataManager.php" href="MetadataManager.php" class="btn btn-default">Manage Metadata</a>
<label class="pointer"><input id="allMetadataType" checked="checked" type="radio" value="" name="metadataType"/>All Media</label>&nbsp;&nbsp;&nbsp;
<label class="pointer"><input id="movieMetadataType" type="radio" value="<?php echo Enumerations::MediaType_Movie; ?>" name="metadataType"/>Movies Only</label>&nbsp;&nbsp;&nbsp;
<label class="pointer"><input id="tvShowMetadataType" type="radio" value="<?php echo Enumerations::MediaType_TvShow; ?>" name="metadataType"/>Tv Shows/Episodes Only</label>

<br/>
<br/>
<a href="#videosJsonModal" class="btn btn-default" data-toggle="modal" onclick="getVideosJson();">View library.json</a>
<br/>
<br/>
<a href='Log.php' class="btn btn-default">View Log</a>
<br/>
<br/>
<a href='api/Update.php' class="btn btn-default">Check for and install updates</a>
<br/>Currently installed version <?php echo CreateDatabase::CurrentDbVersion(); ?>

<div id="videosJsonModal" class="modal hide" style="width: 1000px; margin-left: -500px;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel"></h3>
        <div id="videosJsonModalContent" class="modal-body"></div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function() {
        bootbox.setDefaults({animate: false});
        $("[name=metadataType]").click(function() {
            $("#metadataManagerBtn").attr("href", $("#metadataManagerBtn").attr("href-original") + "?mediaType=" + $(this).val());
        });
    });
    function getVideosJson() {
        $.getJSON("api/library.json", function(json) {
            $("#videosJsonModalContent").html("<pre>" + JSON.stringify(json, undefined, 2) + "</pre>");
        });
    }

    function generateLibrary() {
        bootbox.alert("Generating Library. <img src='img/ajax-loader.gif'/>");
        $.ajax({url: "api/GenerateLibrary.php", dataType: "json", complete: function(result, status) {
                bootbox.hideAll();
                if (status === "success") {
                    bootbox.alert("Library has been successfully generated and is up to date.");
                } else {
                    bootbox.alert("There was an error generating library. Please see the <a href='Log.php'>log</a> for more information");
                }
            }
        });
    }

</script>