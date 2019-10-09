<!DOCTYPE HTML>
<html ng-controller="BaseController as base">
    <head>
        <?php

        function baseUrl() {
            $fullUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";
            //remove everything after the root folder
            $rootFolderName = pathinfo(dirname(__FILE__) . '/', PATHINFO_FILENAME);
            $baseFolderIndex = strpos($fullUrl, $rootFolderName) + strlen($rootFolderName);
            $fullUrl = substr($fullUrl, 0, $baseFolderIndex);
            $fullUrl = $fullUrl . '/';
            //remove any double slashes
            return $fullUrl;
        }
        ?>
        <base href="<?php echo baseUrl(); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes">	
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="PlumMediaCenter">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" sizes="57x57" href="assets/img/apple-icon-57x57.png" />
        <link rel="apple-touch-icon" sizes="72x72" href="assets/img/apple-icon-72x72.png" />
        <link rel="apple-touch-icon" sizes="114x114" href="assets/img/apple-icon-114x114.png" />
        <link rel="apple-touch-icon" sizes="144x144" href="assets/img/apple-icon-144x144.png" />
        <link rel="icon" href="favicon.ico" type="assets/image/x-icon" />

        <title ng-bind="base.globals.title">Plum Media Center</title>

        <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="lib/pnotify/pnotify.custom.min.css" rel="stylesheet">

        <script type="text/javascript" src="lib/jquery/jquery-1.11.2.min.js"></script>       
        <script type="text/javascript" src="lib/lodash/lodash.min.js"></script>
        <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <script type="text/javascript" src="lib/bootstrap/js/bootstrap.min.js"></script>

        <script type="text/javascript" src="lib/angular/angular.min.js"></script>
        <script type="text/javascript" src="lib/angular/angular-touch.min.js"></script>    
        <script type="text/javascript" src="lib/angular/angular-animate.min.js"></script>

        <script type="text/javascript" src="lib/angular-ui-router/angular-ui-router.min.js"></script>
        <script type="text/javascript" src="lib/angular-ui-bootstrap/ui-bootstrap-tpls-0.12.0.min.js"></script>
        <script type="text/javascript" src="lib/ngInfiniteScroll/ng-infinite-scroll.min.js"></script>


        <link href="lib/video-js/video-js.css" rel="stylesheet">
        <script src="lib/video-js/video.js"></script>

        <script type="text/javascript" src="dist/app.min.js"></script>
        <script type="text/javascript" src="dist/templates.js"></script> 

        <link href="dist/app.min.css" rel="stylesheet">

    </head>  
    <body>
    <navbar ng-if="!base.globals.hideNavbar"></navbar>
    <div id="bodyContent" ng-class="{fill: base.globals.hideNavbar, 'navbar-adjust': !base.globals.hideNavbar}"> 
        <div class="fill" ui-view autoscroll="true"></div>
    </div>

    <script>
        //only run livereload when on localhost
        if (window.location.href.indexOf('localhost') > -1) {
            document.write('<script src="http://' + (location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1"></' + 'script>')
        }
    </script>
</body>
</html>
