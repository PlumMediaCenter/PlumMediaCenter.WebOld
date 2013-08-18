<form>
    <a  href="#newSourceModal" class="btn" role="button" onclick="openAdd();">Add New Source</a>
    <br/>    <br/>

    <table class="table table-hover">
        <tr>
            <th>Location</th>
            <th>Media Type</th>
            <th>Security Type</th>
            <th>Base URL</th>
            <th></th>
            <th></th>
        </tr>
        <?php foreach ($videoSources as $src) { ?>
            <tr>
                <td> <?php echo $src->location; ?></td>
                <td><?php echo $src->media_type; ?></td>
                <td><?php echo $src->security_type; ?></td>
                <td><a href='<?php echo $src->base_url; ?>'><?php echo $src->base_url; ?></a></td>
                <td><a href="javascript:openEdit('<?php echo $src->location; ?>','<?php echo $src->base_url; ?>','<?php echo $src->media_type; ?>','<?php echo $src->security_type; ?>');">edit</a></td>
                <td><button type="button" class="close" onclick="deleteVideoSource('<?php echo $src->location; ?>');" aria-hidden="true">&times;</button></td>
            </tr>
        <?php } ?>
    </table>
    <div id="newSourceModal" class="modal hide ">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Add/Edit Video Source</h3>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="span2">Base File Path: </div>
                <div class="span3">                  
                    <input type="text" style="width:100%;margin-bottom:0px;" name="location" placeholder="ex: c:/videos/Movies/"/>
                    <?php //this input is used as reference in the edit action....so we can find the correct row in the db to update. ?>
                    <input type="hidden" name="originalLocation"/>
                    <div style="color: red;margin-bottom:10px;">*Please Include trailing slash</div>
                </div>
            </div>
            <div class="row">
                <div class="span2">Security Type: </div>
                <div class="span2">
                    <select id="securityType" name="securityType">
                        <option value="<?php echo Enumerations::SecurityType_Public; ?>">Public</option>
                        <option value="<?php echo Enumerations::SecurityType_LoginRequired; ?>">Login Required</option>
                    </select>
                </div>
            </div>
            <div id="baseUrlRow" class="row">
                <div class="span2">Base URL: </div>
                <div class="span3">                  
                    <input type="text" style="width:100%;margin-bottom:0px;" name="baseUrl" placeholder="ex: http://localhost/videos/movies/"/>
                    <div style="color: red;margin-bottom:10px;">*Please Include trailing slash</div>
                </div>
            </div>
            <div class="row">
                <div class="span2">Media Type: </div>
                <div class="span2">
                    <select name="mediaType">
                        <option value="<?php echo Enumerations::MediaType_Movie; ?>">Movies</option>
                        <option value="<?php echo Enumerations::MediaType_TvShow; ?>">Tv Shows</option>
                    </select>
                </div>
            </div>


        </div>
        <div class="modal-footer">
            <span style="color:red; float:left;" id="addEditMessage"></span>
            <a href="#" class="btn"  data-dismiss="modal" >Close</a>
            <input id="addSourceBtn" type="submit" class="btn btn-primary" name="addSource" value="Save New" onclick="return validateAddEdit();">
            <input id="editSourceBtn" type="submit" class="btn btn-primary" name="editSource" value="Save Edit" onclick="return validateAddEdit();">

        </div>
    </div>
    <div style="display:none;">
        <input id="deleteSource" type="text" name="deleteSource" value=""/>
    </div>
</form>

<script type="text/javascript">

        $(document).ready(function() {
            //show/hide the public url input based on the security type selected    
            $("#securityType").change(setBaseUrlVisibility);
        });

        function setBaseUrlVisibility() {
            if ($("#securityType").val() == "<?php echo Enumerations::SecurityType_Public; ?>") {
                $("#baseUrlRow").show();
            } else {
                $("#baseUrlRow").hide();
            }
        }


        function openAdd() {
            $("#addSourceBtn").show();
            $("#editSourceBtn").hide();
            openAddEdit();
        }

        function openEdit(baseFilePath, baseUrl, mediaType, securityType) {
            $("#addSourceBtn").hide();
            $("#editSourceBtn").show();
            openAddEdit(baseFilePath, baseUrl, mediaType, securityType);

        }

        function openAddEdit(baseFilePath, baseUrl, mediaType, securityType) {
            //if parameters were not provided, clear the inputs
            if (baseFilePath == undefined || baseUrl == undefined || mediaType == undefined || securityType == undefined) {
                baseFilePath = "";
                baseUrl = "";
                mediaType = "";
                securityType = "";
            }
            $("input[name=originalLocation]").val(baseFilePath);
            $("input[name=location]").val(baseFilePath);
            $("input[name=baseUrl]").val(baseUrl);
            $("input[name=mediaType]").val(mediaType);
            $("#securityType").val(securityType);

            //clear the message 
            $("#addEditMessage").html("");
            setBaseUrlVisibility();
            //show the add/edit window
            $("#newSourceModal").modal();
        }

        function validateAddEdit() {
            if ($("input[name=location]").val().length == 0 || $("input[name=baseUrl]").val().length == 0) {
                $("#addEditMessage").html("*Please fill out all fields before clicking save");
                return false;
            }
            return true;
        }


        function deleteVideoSource(sourcePath) {
            if (confirm("Really delete this video source?")) {
                $("#deleteSource").val(sourcePath);
                $("form").submit();
            }
        }
</script>