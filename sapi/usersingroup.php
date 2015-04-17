<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

if( isset($_REQUEST["groupname"]) ){

	$groupname = $_REQUEST["groupname"];
	
	if ($users = OC_Group::usersInGroup( $groupname )) {
		if( isset($_REQUEST["type"]) ) {
			if ($_REQUEST["type"] == "text") {
				foreach ($users as $user) {
					echo $user.chr(13);
				}
			}
		}
		else {
			OC_JSON::success(array("data" => array( "group" => $groupname, "users" => implode( ", ", $users))));

		}
	}
	else{
		OC_JSON::error(array("data" => array( "message" => "Unable to get group users" )));
	}
}