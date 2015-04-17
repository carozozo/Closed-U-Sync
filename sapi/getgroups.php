<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

$groups = OC_Group::getGroups();

if( isset($_REQUEST["type"]) ){
	if ($_REQUEST["type"] == "text") {
		foreach ($groups as $group) {
			echo $group.chr(13);
		}
	}
}
else {
	OC_JSON::success(array("data" => array("groups" => implode( ", ", $groups))));
}
