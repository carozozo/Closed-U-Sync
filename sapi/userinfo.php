<?php

// Server limited
require_once ('inc_server.php');

// Do not load FS ...
$RUNTIME_NOSETUPFS = true;

// Init owncloud
require_once ('../lib/base.php');

if (isset($_REQUEST["username"])) {

	$username = $_REQUEST["username"];

	if (OC_User::userExists($username)) {
		OC_JSON::success(array(
			"name" => $username,
			"groups" => join(", ", OC_Group::getUserGroups($username)),
			'quota' => OC_Helper::humanFileSize(OC_UserQuota::getUserQuota($i))
		));
	} else {
		OC_JSON::error(array("data" => array("message" => "Unable to get user")));
	}
}
