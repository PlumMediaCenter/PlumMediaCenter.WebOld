<?php include_once(dirname(__FILE__) . '/code/database/CreateDatabase.class.php'); ?>
<br/>
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

<a href='api/Update.php' class="btn btn-default">Check for and install updates</a>
<br/>Currently installed version <?php echo CreateDatabase::CurrentDbVersion(); ?>

<div id="generateLibraryModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">
                <p>One fine body&hellip;</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function() {
        bootbox.setDefaults({animate: false});
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