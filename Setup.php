<?php
include_once("code/database/Queries.class.php");
include_once("config.php");

$success = null;
//if the generateDatabase button was clicked, generate the database
if (isset($_POST["setup"])) {
    $success = false;
    $username = isset($_POST["mysqlRootUsername"]) ? $_POST["mysqlRootUsername"] : null;
    $password = isset($_POST["mysqlRootPassword"]) ? $_POST["mysqlRootPassword"] : "";
    $host = isset($_POST["mysqlHostName"]) ? $_POST["mysqlHostName"] : null;
    if ($username != null && $password !== null && $host != null) {
        include_once("code/database/CreateDatabase.class.php");

        $cd = new CreateDatabase($username, $password, $host);
        $success = $cd->upgradeDatabase();
        //mark all existing video sources as updated so that every video will be refreshed.
        Queries::updateVideoSourceRefreshVideos(1);
    }

    //delete any previously existing library.json file 
    file_put_contents(dirname(__FILE__) . "/api/library.json", "");
}
?>
<html>
    <head>
        <link rel="stylesheet" href="lib/bootstrap/css/bootstrap.min.css"/>
        <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="lib/bootstrap/css/js/bootstrap.min.js"></script>
    </head>
    <body class="container">
        <?php if ($success === true) { ?>
            PlumVideoPlayer has successfully installed on the database and is ready to go! Add video sources to the player <a href="VideoSources.php">here</a>. 
        <?php } else if ($success === false) { ?>
            There was an error installing the PlumVideoPlayer on the database. Please check the <a href="Log.php">log</a> for more information.
        <?php } else { ?>
            <form method="post">
                <p>Welcome to the Plum Video Player. <br/>In order to install this web application, you must have a MySql database available and know the root login information.</p>
                <p>This process will create a new database on your mysql instance called '<?php echo config::$dbName; ?>' and will create all necessary tables/triggers/etc.. needed for this application.</p>
                <p>Simply enter your root database login information and click setup, and let us do the rest!</p>
                <div>
                    <div class="span3"> MySql Root Username: </div>
                    <div class="span2">
                        <input type="text" name="mysqlRootUsername" placeholder="MySql Root Username" value="root"/>
                    </div>
                </div>
                <div>
                    <div class="span3">MySql Root Password: </div>
                    <div class="span2">
                        <input type="text" name="mysqlRootPassword" placeholder="MySql Root Password" />
                    </div>
                </div>
                <div>
                    <div class="span3">MySql host name:</div>
                    <div class="span2"> <input type="text" name="mysqlHostName" placeholder="MySql Host Name" value="localhost"/>
                    </div>
                </div>
                <div>
                    <div class="span3"></div>
                    <div class="span2" style="text-align:left;">
                        <br/>
                        <input class="btn btn-primary" type="submit" value="Setup" name="setup"/>
                    </div>
                </div>
            </form>

        <?php } ?>
</html>