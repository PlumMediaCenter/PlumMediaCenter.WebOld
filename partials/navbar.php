<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php"><img src="img/logo.png" style="height:20px;display:inline;">&nbsp;Plum Media Center</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><a href="Browse.php?mediaType=<?php echo Enumerations::MediaType_Movie; ?>">Movies</a></li>
                <li id="browseNav<?php echo Enumerations::MediaType_TvShow; ?>"><a href="Browse.php?mediaType=<?php echo Enumerations::MediaType_TvShow; ?>">Tv Shows</a></li>
                <li id="adminNav"><a href="Admin.php">Admin</a></li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li>
                    <form class="navbar-form navbar-left" role="search" action="SearchResults.php">
                        <div class="form-group">
                            <input name="title" type="text" class="form-control" placeholder="Search">
                        </div>
                        <button type="submit" class="btn btn-default">Submit</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>