<?php

// Server limited
require_once ('inc_server.php');

// Init owncloud
require_once ('../lib/base.php');

$groups = array();
if (isset($_REQUEST["groups"])) {
	$groups = json_decode($_REQUEST["groups"], 1);
}
$username = $_REQUEST["username"];
$password = $_REQUEST["password"];

// Does the group exist?
if (in_array($username, OC_User::getUsers())) {
	OC_JSON::error(array("data" => array("message" => "User already exists")));
	exit();
}

// Return Success story
if (OC_User::createUser($username, $password)) {
	if (isset($_REQUEST["quota"])) {
		$quota = $_REQUEST["quota"];
		$unit = substr($quota, -1);
		switch ($unit) {
			case 'G' :
				$size = 1073741824;
				break;
			case 'M' :
				$size = 1048576;
				break;
			default :
				$size = 1;
		}
		$quotasize = ((int)(substr($quota, 0, strlen($quota) - 1))) * $size;
		OC_UserQuota::setUserQuota($username, $quotasize);
	}

	foreach ($groups as $i) {
		if (!OC_Group::groupExists($i)) {
			OC_Group::createGroup($i);
		}
		OC_Group::addToGroup($username, $i);
	}
	OC_Util::setupFS($username);
	OC_JSON::success(array("data" => array(
			"username" => $username,
			"groups" => implode(", ", OC_Group::getUserGroups($username))
		)));
} else {
	OC_JSON::error(array("data" => array("message" => "Unable to add user")));
}
?>
