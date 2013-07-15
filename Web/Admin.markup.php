<?php ?>

<a href='Generate.php' class="btn">Generate library</a>
<br/>
<br/>
<a href='MetadataManager.php' class="btn">Manage Metadata</a>
<br/>
<br/><a href="#videosJsonModal" class="btn" role="button" data-toggle="modal" onclick="getVideosJson();">View videos.json</a>
<div id="videosJsonModal" class="modal hide">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel"></h3>
        <div id="videosJsonModalContent" class="modal-body"></div>
    </div>
</div>
<script type="text/javascript">
    function getVideosJson() {
        $("#videosJsonModalContent").html("<pre>hello</pre>");
    }
</script>