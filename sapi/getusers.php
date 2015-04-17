<?php

// Server limited
require_once ('inc_server.php');

// Init owncloud
require_once ('../lib/base.php');

foreach (OC_User::getUsers() as $i) {
	$users[] = array(
		"username" => $i,
		"groups" => join(", ", OC_Group::getUserGroups($i)),
		'quota' => OC_Helper::humanFileSize(OC_UserQuota::getUserQuota($i))
	);
}

if (isset($_REQUEST["type"])) {
	if ($_REQUEST["type"] == "text") {
		foreach ($users as $user) {
			echo $user["username"] . chr(13);
		}
	}
} else {
	echo json_encode($users);
}
