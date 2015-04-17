<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

if( OC_App::isEnabled('user_ldap') ){

	$isLDAP = true;
	
	if ($users = OC_USER::getUsers($isLDAP)) {
		if( isset($_REQUEST["type"]) ) {
			if ($_REQUEST["type"] == "text") {
				foreach ($users as $user) {
					echo $user.chr(13);
				}
			}
		}
		else {
			OC_JSON::success(array("data" => array( "group" => '[LDAP users]', "users" => implode( ", ", $users))));
		}
	}
	else{
		OC_JSON::error(array("data" => array( "message" => "Fail to get LDAP group users" )));
	}
} else {
	OC_JSON::error(array("data" => array( "message" => "Fail to get LDAP group users" )));
}