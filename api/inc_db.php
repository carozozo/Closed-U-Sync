<?php
// $CONFIG_MAIN is from [/config/config.php]
require_once($_SERVER['DOCUMENT_ROOT'].'/config/config.php');
$dbname = $CONFIG_MAIN["CONFIG"]["dbname"];
$dbuser = $CONFIG_MAIN["CONFIG"]["dbuser"];
$dbpass = $CONFIG_MAIN["CONFIG"]["dbpassword"];
$dbhost = $CONFIG_MAIN["CONFIG"]["dbhost"];
$prefix = $CONFIG_MAIN["CONFIG"]["dbtableprefix"];

$link = mysql_connect($dbhost, $dbuser, $dbpass);

if (!$link) {
	die('Could not connect: ' . mysql_error());
}

$sel = mysql_select_db($dbname, $link);

if (!$sel) {
	die('Can\'t use db: ' . mysql_error());
}
