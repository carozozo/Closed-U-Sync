<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');


/**
 * @get a list of all users has no group
 * @returns array with user ids
 */
function usersNoGroup(){
	$query=OC_DB::prepare('SELECT uid from *PREFIX*users WHERE uid not in (SELECT distinct uid FROM *PREFIX*group_user)');
	$result=$query->execute();
	while($row=$result->fetchRow()) {
		$users[]=$row['uid'];
	}
	return $users;
}

if ($users = usersNoGroup()) {
	if (isset($_REQUEST["type"]) ) {
		if ($_REQUEST["type"] == "text") {
			foreach ($users as $user) {
				echo $user.chr(13);
			}
		}
	} else {
		OC_JSON::success(array("data" => array( "group" => "[no group]", "users" => implode( ", ", $users))));
	}
} else {
	OC_JSON::error(array("data" => array( "message" => "Unable to get group users" )));
}
