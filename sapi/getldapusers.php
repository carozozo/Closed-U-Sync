<?php

// Server limited
require_once ('inc_server.php');

// Init owncloud
require_once ('../lib/base.php');

if (OC_App::isEnabled('user_ldap')) {

	$isLDAP = true;

	foreach (OC_USER::getUsers($isLDAP) as $i) {
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
} else {
	OC_JSON::error(array("data" => array("message" => "Fail to get LDAP users")));
}
