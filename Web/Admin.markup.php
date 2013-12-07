<?php ?>
<a onclick="generateLibrary();" class="btn">Generate/Update library</a>
<br/>
<br/>
<a href='VideoSources.php' class="btn">Add/Remove Video Sources</a>
<br/>
<br/>
<a id="metadataManagerBtn" href-original="MetadataManager.php" href="MetadataManager.php" class="btn">Manage Metadata</a>
<input id="allMetadataType" checked="checked" type="radio" value="" name="metadataType"/> <label for="movieMetadataType">All Media</label>&nbsp;&nbsp;&nbsp;
<input id="movieMetadataType" type="radio" value="<?php echo Enumerations::MediaType_Movie; ?>" name="metadataType"/><label for="movieMetadataType">Movies Only</label>&nbsp;&nbsp;&nbsp;
<input id="tvShowMetadataType" type="radio" value="<?php echo Enumerations::MediaType_TvShow; ?>" name="metadataType"/><label for="movieMetadataType">Tv Shows/Episodes Only</label>

<br/>
<br/>
<a class="btn" onclick="fetchMissingMetadataAndPosters();">Fetch and Generate Missing Metadata and Posters</a>

<br/>
<br/>
<a href="#videosJsonModal" class="btn" role="button" data-toggle="modal" onclick="getVideosJson();">View library.json</a>
<br/>
<br/>
<a href='Log.php' class="btn">View Log</a>

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