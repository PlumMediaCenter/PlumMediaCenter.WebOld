<script type="text/javascript" src="js/VideoSources.js"></script>
<script type="text/javascript" src="plugins/jquery-validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="plugins/jquery-validation/additional-methods.min.js"></script>


<form id="videoSourcesForm" method="post">
    <a id="addNewSourceBtn" class="btn btn-default " role="button">Add New Source</a>
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
                <td><a class="btn  btn-default editSource" data-location="<?php echo $src->location; ?>" data-base-url="<?php echo $src->base_url; ?>" data-media-type="<?php echo $src->media_type; ?>" data-security-type="<?php echo $src->security_type; ?>">Edit</a></td>
                <td><button type="button" class="close deleteSource" data-location="<?php echo $src->location; ?>" aria-hidden="true">&times;</button></td>
            </tr>
        <?php } ?>
    </table>
    <div class="modal fade" id="newSourceModal" tabindex="-1" role="dialog" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Add/Edit Video Source</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-3">Base File Path: </div>
                        <div class="col-xs-9">                  
                            <input type="text" class="form-control" id="location" name="location" placeholder="ex: c:/videos/Movies/"/>
                            <?php //this input is used as reference in the edit action....so we can find the correct row in the db to update. ?>
                            <input type="hidden" name="originalLocation"/>
                           <b>*NOTE: </b>This is a file path that the SERVER can see, not your local computer
                        </div>
                    </div>
                    <div class="row">
                        <br/>
                        <div class="col-xs-3">Security Type: </div>
                        <div class="col-xs-9">
                            <input type="radio" id="securityTypePublic" name="securityType" checked="checked" value="<?php echo Enumerations::SecurityType_Public; ?>">
                            <label for="securityTypePublic">No Security</label>
                            <!--                    &nbsp;
                                                <input type="radio" id="securityTypePrivate" name="securityType"  value="<?php echo Enumerations::SecurityType_LoginRequired; ?>">
                                                <label for="securityTypePrivate">Login Required</label>-->
                        </div>
                    </div>
                    <div id="baseUrlRow" class="row" style="display:block;">
                        <div class="col-xs-3">Base URL: </div>
                        <div class="col-xs-9">                  
                            <input type="text" class="form-control" name="baseUrl" placeholder="ex: http://localhost/videos/movies/"/>
                            <b>*NOTE: </b>This is a url that already exists. You must serve the videos over http using your web server.
                            <br/>
                            <br/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3">Media Type: </div>
                        <div class="col-xs-9">
                            <input type="radio" id="mediaTypeMovie" name="mediaType" value="<?php echo Enumerations::MediaType_Movie; ?>">
                            <label for="mediaTypeMovie">Directory full of movies</label>
                            &nbsp;<br/>
                            <input type="radio" id="mediaTypeTvShow" name="mediaType"  value="<?php echo Enumerations::MediaType_TvShow; ?>">
                            <label for="mediaTypeTvShow">Directory full of Tv Shows (Each in its own tv show folder)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span style="color:red; float:left;" id="addEditMessage"></span>
                    <a href="#" class="btn btn-default "  data-dismiss="modal" >Close</a>
                    <input id="addSourceBtn" type="submit" class="btn btn-primary" name="addSource" value="Save New">
                    <input id="editSourceBtn" type="submit" class="btn btn-primary" name="editSource" value="Save Edit">
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    var enumerations = {SecurityType_Public: "<?php echo Enumerations::SecurityType_Public; ?>"};

</script>