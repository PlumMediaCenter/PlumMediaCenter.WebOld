<?php
require_once(dirname(__FILE__) . '/Core/Functions.php');
require_once(basePath() . '/Core/Core.php');
require_once(basePath() . '/config.php');
require_once(basePath() . "/Code/DbManager.class.php");
require_once(basePath() . '/Code/Security.class.php');
require_once(basePath() . '/Code/Enumerations.class.php');

$routes = [];
$routes[] = new MvcRoute("Default", "{controller}/{action}/{id}", ['controller' => 'Home', 'action' => 'Index']);
$routes[] = new MvcRoute("Default", "{controller}/{action}", ['controller' => 'Home', 'action' => 'Index']);
executeRouting($routes);
