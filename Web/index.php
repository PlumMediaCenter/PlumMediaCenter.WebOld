<?php

require_once(dirname(__FILE__) . '/Core/Functions.php');
require_once(basePath() . '/Core/Core.php');
require_once(basePath() . '/config.php');
require_once(basePath() . "/Code/DbManager.class.php");
require_once(basePath() . '/Code/Security.class.php');
require_once(basePath() . '/Code/Enumerations.class.php');
require_once(basePath() . '/Code/lib/php-activerecord/ActiveRecord.php');

//set up database stuff
ActiveRecord\Config::initialize(function($cfg) {
    $cfg->set_model_directory('Code/');
    $cfg->set_connections(array(
        //'development' => 'mysql://username:password@localhost/database_name',
        'development' => 'mysql://' .
        config::$dbUsername . ':' .
        config::$dbPassword . '@' .
        config::$dbHost . '/' .
        config::$dbName));
});

$routes = [];
$routes[] = new MvcRoute("Default", "{controller}/{action}/{id}", ['controller' => 'Home', 'action' => 'Index']);
$routes[] = new MvcRoute("Default", "{controller}/{action}", ['controller' => 'Home', 'action' => 'Index']);
executeRouting($routes);
