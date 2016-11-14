<?php
require_once(dirname(__FILE__) . '/../code/database/CreateDatabase.class.php');

$create = new CreateDatabase("plummediacenter", "plummediacenter", "localhost");
$create->upgradeDatabase();
