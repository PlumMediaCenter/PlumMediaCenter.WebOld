<html>
    <head><title><?php echo $title != null ? $title : ""; ?></title>
        <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="plugins/bootstrap/js/bootstrap.min.js"></script>
        <link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="css/style.css" rel="stylesheet" media="screen">

    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="index.php">Roku LAN Video Player</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li id="homeNav" ><a href="index.php">Home</a></li>
                            <li id="browseNav"><a href="Browse.php">Browse</a></li>
                            <li id="adminNav"><a href="Admin.php">Admin</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div style=""class="container">
            <?php
            echo isset($body) ? $body : "";
            ?>
        </div>
    </body>
</html>