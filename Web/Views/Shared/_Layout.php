<?php
Security::HandleLogin();
?><!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title; ?></title>
        <link rel="icon" href="<?php urlContent("~/favicon.ico"); ?>" type="image/x-icon" />
        <link rel="stylesheet" media="screen" href="<?php urlContent("~/Scripts/lib/bootstrap/css/bootstrap.min.css"); ?>">
        <link href="<?php urlContent("~/Scripts/lib/jquery-ui-1.10.3.custom/css/dark-hive/jquery-ui-1.10.3.custom.min.css"); ?>" rel="stylesheet" media="screen">
        <link rel="stylesheet" media="screen" href="<?php urlContent("~/Content/style.css"); ?>">

        <script type="text/javascript">
            //this variable will be used to hold any 'global' variables that may need to be referenced by other components.
            //this prevents pollution of the global namespace.
            var app = {};
            var baseUrl = "<?php echo baseUrl();?>";
        </script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/lib/respond/respond.min.js"); ?>"></script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/lib/jquery/jquery-1.10.2.min.js"); ?>"></script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/lib/jquery-color/jquery.color-2.1.0.min.js"); ?>"></script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/jquery.utility.js"); ?>"></script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/lib/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"); ?>"></script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/lib/bootstrap/js/bootstrap.min.js"); ?>"></script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/plumapi.js"); ?>"></script>
        <script type="text/javascript"> window.plumapi.baseUrl = "<?php urlContent("~/"); ?>";</script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/jquery.playlistadder.js"); ?>"></script>
        <script type="text/javascript" src="<?php urlContent("~/Scripts/Shared/shared.js"); ?>"></script>
        <?php partial("~/Views/Shared/_Enumerations.php");?>
        <script type="text/javascript">
            var username = "<?php echo Security::GetUsername(); ?>";
            
            //determines how many pixels are avaible from below the navbar to the bottom of the nonscrollable portion of the screen.
            function displayHeight() {
                return $("body").height() - $("#bodyPadding").height();
            }
            $(document).ready(function() {
                $("#playlistAdder").playlistAdder({username: username});
                $("#playlistAdder").playlistAdder("hide");
            });

            function addToPlaylist(videoId) {
                $("#playlistAdder").playlistAdder('show', videoId);
            }
        </script>
        <style type="text/css">
            #navbar-search.form-control{
            }
            #navbar-search-container{
                width:80%;
                float:left;
            }
        </style>
        <?php renderSection('head', true); ?>
    </head>
    <body>
        <div id="playlistAdder"></div>
        <?php partial('~/Views/Shared/_Navbar.php'); ?>
        <div id='body-row' class="row">
            <div id='body-col' class="col-lg-12">
                <?php renderSection('body'); ?>
            </div>
        </div>
        <?php //Wait modal?>
        <div class="modal fade" id="waitModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <p id="waitMessage"></p>
                        <img id="waitImg" src="<?php urlContent("~/Content/Images/ajax-loader.gif"); ?>"/>
                    </div>
                </div>  
            </div>
        </div>
        <?php //Message modal ?>
        <div class="modal fade" id="messageModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <p id="message"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>  
            </div>
        </div>
    </body>
</html>