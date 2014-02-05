<?php
include_once(dirname(__FILE__) . "/code/Security.class.php");
Security::HandleLogin();
include_once(dirname(__FILE__) . "/code/Enumerations.class.php");
?>
<!DOCTYPE HTML>
<meta http-equiv="X-UA-Compatible" content="IE=edge"> 
<html>
    <head><title><?php echo $title != null ? $title : ""; ?></title>
        <link rel="icon" href="favicon.ico" type="image/x-icon" />
        <link href="js/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="css/style.css" rel="stylesheet" media="screen">
        <link href="plugins/jquery-ui-1.10.3.custom/css/dark-hive/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" media="screen">
        <script type="text/javascript" src="js/lib/respond/respond.min.js"></script>
        <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.color-2.1.0.min.js"></script>
        <script type="text/javascript" src="js/jquery.utility.js"></script>
        <script type="text/javascript" src="plugins/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="js/lib/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/lib/bootbox/bootbox.min.js"></script>
        <script type="text/javascript" src="js/plumapi.js"></script>
        <script type="text/javascript" src="js/jquery.playlistadder.js"></script>

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
        <style type="text/css">
            .dropdown-backdrop {
                position: static;
            }
        </style>
    </head>
    <body>
        <div id="bodyPadding"></div>
        <div id="playlistAdder"></div>
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand"  href="index.php"> <img src="img/logo.png" style="height:20px;"> Plum Video Player</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li id="browseNav<?php echo Enumerations::MediaType_Movie; ?>" ><a href="Browse.php?mediaType=<?php echo Enumerations::MediaType_Movie; ?>">Movies</a></li>
                    <li id="browseNav<?php echo Enumerations::MediaType_TvShow; ?>"><a href="Browse.php?mediaType=<?php echo Enumerations::MediaType_TvShow; ?>">Tv Shows</a></li>
                    <li id="browseNavGenre"><a href="BrowseGenre.php">Genres</a></li>
                    <!--<li id="adminNav"><a href="Playlist.php">Playlist</a></li>-->
                    <li id="adminNav"><a href="Admin.php">Admin</a></li>
                </ul>
                <form class="navbar-form navbar-right" role="search">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Search">
                    </div>
                    <button type="submit" class="btn btn-default">Submit</button>
                    <div class="clearfix"></div>
                </form>
                <ul class="nav navbar-nav navbar-right ">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Account <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Action</a></li>
                            <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Separated link</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="row" style="margin:0px;">
            <div class="col-lg-12">
                <?php echo isset($body) ? $body : ""; ?>
            </div>
        </div>
    </body>
</html>