<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
// require_once('../lib/base.php');

if( isset($_REQUEST["date"]) )
{
	$strPath = "/var/www/log/usage/daily_quota_".$_REQUEST["date"];
	$lines = file($strPath);

	foreach($lines as $line)
	{
		list($quota, $name) = explode("\t", $line);
        $username = str_replace("./", "", $name);
        $username = str_replace("\n", "", $username);
        $raw["$username"] = $quota;
	}

    echo json_encode($raw);
}