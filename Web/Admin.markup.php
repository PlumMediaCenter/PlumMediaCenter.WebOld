<?php ?>

<a href='UpdateLibrary.php' class="btn">Generate/Update library</a>
<br/>
<br/>
<a href='VideoSources.php' class="btn">Add/Remove Video Sources</a>
<br/>
<br/>
<a href='MetadataManager.php' class="btn">Manage Metadata</a>
<br/>
<br/>
<a href="#videosJsonModal" class="btn" role="button" data-toggle="modal" onclick="getVideosJson();">View videos.json</a>
<br/>
<br/>
<a href='Log.php' class="btn">View Log</a>
<br/>
<br/>
<a href='javascript:eraseVideosJson();' class="btn">Clear videos.json</a>

<div id="videosJsonModal" class="modal hide" style="width: 1000px; margin-left: -500px;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel"></h3>
        <div id="videosJsonModalContent" class="modal-body"></div>
    </div>
</div>
<script type="text/javascript">
    function getVideosJson() {
        $.getJSON("videos.json", function(json) {
            $("#videosJsonModalContent").html("<pre>" + JSON.stringify(json, undefined, 2) + "</pre>");
        });
    }

    function eraseVideosJson() {
        $.ajax({url: "ajax/EraseVideos.json.php", success: function() {
                alert("Erased videos.json");
            }});
    }
</script>