<?php include_once(dirname(__FILE__) . "/code/Enumerations.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head><title><?php echo $title != null ? $title : ""; ?></title>
        <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.color-2.1.0.min.js"></script>
        <script type="text/javascript" src="plugins/bootstrap/js/bootstrap.min.js"></script>
        <link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="css/style.css" rel="stylesheet" media="screen">

    </head>
    <body>
        <div class="content">
            <div class="navbar navbar-inverse navbar-fixed-top">
                <div class="navbar-inner">
                    <div class="container">
                        <a class="brand" href="index.php">Plum Video Player</a>
                        <div class="nav-collapse collapse">
                            <ul class="nav">
                                <li id="browseNav<?php echo Enumerations::MediaType_Movie; ?>" ><a href="Browse.php?mediaType=<?php echo Enumerations::MediaType_Movie; ?>">Movies</a></li>
                                <li id="browseNav<?php echo Enumerations::MediaType_TvShow; ?>"><a href="Browse.php?mediaType=<?php echo Enumerations::MediaType_TvShow; ?>">Tv Shows</a></li>
                                <li id="adminNav"><a href="Admin.php">Admin</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <?php
                echo isset($body) ? $body : "";
                ?>
            </div>
        </div>
    </body>
</html>