<?php
include_once(dirname(__FILE__) . "/code/Security.class.php");
Security::HandleLogin();
include_once(dirname(__FILE__) . "/code/Enumerations.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title != null ? $title . '-' : ""; ?>Plum Media Center</title>
        <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.color-2.1.0.min.js"></script>
        <script type="text/javascript" src="js/jquery.utility.js"></script>
        <link href="plugins/jquery-ui-1.10.3.custom/css/dark-hive/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" media="screen">
        <script type="text/javascript" src="plugins/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>

        <?php /* bootstrap */?>
        <script type="text/javascript" src="lib/bootstrap/js/bootstrap.min.js"></script>
        <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        
        <script type="text/javascript" src="lib/bootbox/bootbox.min.js"></script>
        <script type="text/javascript" src="js/jquery.playlistadder.js"></script>
        <link href="css/style.css" rel="stylesheet">
        <link rel="icon" href="favicon.ico" type="image/x-icon" />
        <script type="text/javascript">
            var username = "<?php echo Security::GetUsername(); ?>";
            var enumerations = <?php
$c = new ReflectionClass("Enumerations");
echo json_encode($c->getConstants());
?>;
            enumerations.movie = "<?php echo Enumerations::MediaType_Movie; ?>";
            enumerations.tvShow = "<?php echo Enumerations::MediaType_TvShow; ?>";
            enumerations.tvEpisode = "<?php echo Enumerations::MediaType_TvEpisode; ?>";


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
    </head>
    <body>
        <div id="bodyPadding"></div>
        <div id="playlistAdder"></div>
        <?php include_once(dirname(__FILE__ ) . '/partials/navbar.php');?>
        <div class="container">
            <div id="containerRelativer">
                <?php echo isset($body) ? $body : ""; ?>
            </div>
        </div>
    </body>
</html>