<form>
    <a  href="#newSourceModal" class="btn" role="button" data-toggle="modal" onclick="openAddEdit();">Add New Source</a>
    <br/>    <br/>

    <table class="table table-hover">
        <tr>
            <th>Location</th>
            <th>Media Type</th>
            <th>Security Type</th>
            <th>Base URL</th>
        </tr>
        <?php foreach ($videoSources as $videoSource) { ?>
            <tr>
                <td> <?php echo $videoSource->location; ?></td>
                <td><?php echo $videoSource->media_type; ?></td>
                <td><?php echo $videoSource->security_type; ?></td>
                <td><?php echo $videoSource->base_url; ?></td>
                <td><button type="button" class="close" onclick="deleteVideoSource('<?php echo $videoSource->location; ?>');" aria-hidden="true">&times;</button></td>
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
                    <div style="color: red;margin-bottom:10px;">*Please Include trailing slash</div>
                </div>
            </div>
            <div class="row">
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
            <div class="row">
                <div class="span2">Security Type: </div>
                <div class="span2">
                    <select name="securityType">
                        <option value="<?php echo Enumerations::SecurityType_Public; ?>">Public</option>
                        <option value="<?php echo Enumerations::SecurityType_LoginRequired; ?>">Login Required</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <span style="color:red; float:left;" id="addEditMessage"></span>
            <a href="#" class="btn"  data-dismiss="modal" >Close</a>
            <input id="addEditSourceBtn" type="submit" class="btn btn-primary" name="addEditSource" value="Save" onclick="return validateAddEdit();">
        </div>
    </div>
    <div style="display:none;">
        <input id="deleteSource" type="text" name="deleteSource" value=""/>
    </div>
</form>

<script type="text/javascript">

        function openAddEdit() {
            //clear the message 
            $("#addEditMessage").html("");
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