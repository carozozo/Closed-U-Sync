<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

if( isset($_REQUEST["username"]) ){

	$username = $_REQUEST["username"];
	
	if ($groups = OC_Group::getUserGroups( $username )) {
		if( isset($_REQUEST["type"]) ) {
			if ($_REQUEST["type"] == "text") {
				foreach ($groups as $group) {
					echo $group.chr(13);
				}
			}
		}
		else {
			OC_JSON::success(array("data" => array( "username" => $username, "groups" => implode( ", ", $groups))));
		}
	}
	else{
		OC_JSON::error(array("data" => array( "message" => "Unable to get user group" )));
	}
}