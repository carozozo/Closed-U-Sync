<?php

// Server limited
require_once ('inc_server.php');

// Do not load FS ...
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOAPPS = true;

// Init owncloud
require_once "../config/config.php";
require_once ('../lib/base.php');

$dataDir = OC::$CONFIG_DATADIRECTORY_ROOT;
if (($freesysspace = disk_free_space('/')) && ($freedataspace = disk_free_space($dataDir))) {
	OC_JSON::success(array(
		'server' => $_SERVER['HTTP_HOST'],
		'free_system_space' => $freesysspace,
		'system_total_space' => disk_total_space('/'),
		'free_data_space' => $freedataspace,
		'data_total_space' => disk_total_space($dataDir)
	));
} else {
	OC_JSON::error(array("data" => array("message" => "Unable to get server information")));
}
