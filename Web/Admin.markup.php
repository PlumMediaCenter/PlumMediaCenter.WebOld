<?php ?>
<a onclick="generateLibrary();" class="btn">Generate/Update library</a>
<br/>
<br/>
<a href='VideoSources.php' class="btn">Add/Remove Video Sources</a>
<br/>
<br/>
<a href='MetadataManager.php' class="btn">Manage Metadata</a>
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
    function getVideosJson() {
        $.getJSON("api/library.json", function(json) {
            $("#videosJsonModalContent").html("<pre>" + JSON.stringify(json, undefined, 2) + "</pre>");
        });
    }

    function generateLibrary() {
        bootbox.alert("Generating Library. <img src='img/ajax-loader.gif'/>");
        $.ajax({url: "api/GenerateLibrary.php", dataType: "json", success: function(result) {
                bootbox.hideAll();
                if (result.success == true) {
                    bootbox.alert("Total Success: " + result.success);
                } else {
                    bootbox.alert(result);
                }
            }
        });
    }

    function fetchMissingMetadataAndPosters() {
        bootbox.alert("Fetching missing metadata and posters. <img src='img/ajax-loader.gif'/>");
        $.ajax({url: "api/FetchMissingMetadataAndPosters.php", dataType: "json", success: function(result) {
                bootbox.hideAll();
                if (result.success == true) {
                    bootbox.alert("Total Success: " + result.success);
                } else {
                    bootbox.alert(result);
                }
            }
        });
    }
</script>