<nav class="navbar navbar-inverse" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand"  href="<?php urlAction('Home/Index'); ?>"> <img src="<?php urlContent("~/Content/Images/logo.png"); ?>" style="height:20px;"> Plum Video Player</a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
            <li id="browseNav"><a href="<?php urlAction('Home/Browse'); ?>">Browse</a></li>
            <li id="browseNavGenre"><a href="<?php urlAction('Home/Genre'); ?>">Genres</a></li>
            <li id="adminNav"><a href="<?php urlAction('Admin/Index'); ?>">Admin</a></li>
        </ul>
        <form class="navbar-form navbar-right" action="<?php urlAction('Home/Search'); ?>" method="get" role="search">
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