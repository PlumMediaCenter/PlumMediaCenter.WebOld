<?php
include_once(dirname(__FILE__) . "/code/Security.class.php");
Security::HandleLogin();
include_once(dirname(__FILE__) . "/code/Enumerations.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title != null ? $title : ""; ?></title>
        <link rel="icon" href="favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" media="screen" href="js/lib/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" media="screen" href="css/style.css">

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
            #navbar-search.form-control{
            }
            #navbar-search-container{
                width:80%;
                float:left;
            }
        </style>
    </head>
    <body>
        <!--<div id="bodyPadding"></div>-->
        <div id="playlistAdder"></div>
        <nav class="navbar navbar-inverse" role="navigation">
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
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li id="browseNav"><a href="Browse.php">Browse</a></li>
                    <li id="browseNavGenre"><a href="BrowseGenre.php">Genres</a></li>
                    <li id="adminNav"><a href="Admin.php">Admin</a></li>
                </ul>
                <form class="navbar-form navbar-right" action="Search.php" method="get" role="search">
                    <div  class="form-group">
                        <div id="navbar-search-container">
                            <input id="search" name="q" class="form-control" type="text" placeholder="Search"/>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
                    </div>
                    <div class="clearfix"></div>
                </form>
                <ul class="nav navbar-nav navbar-right ">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Account Settings</a></li>
                            <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Log In / Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <div id='body-row' class="row">
            <div id='body-col' class="col-lg-12">
                <?php echo isset($body) ? $body : ""; ?>
            </div>
        </div>
    </body>
</html>