<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

$username = $_REQUEST["username"];
$error = false;
$data["name"] = $username;

// Change Password
if( isset( $_REQUEST["password"] )){
	$password = $_REQUEST["password"];
	if( !OC_User::setPassword( $username, $password )){
		$error = true;
	}
}

// Change Group
$groups = array();
if( isset( $_REQUEST["groups"] )){
	
	$groups = OC_Group::getUserGroups($username);
	foreach( $groups as $i ){
		if(OC_Group::groupExists($i)){
			OC_Group::removeFromGroup( $username, $i );
		}
	}
	
	$groups = json_decode($_REQUEST["groups"],1);
	
	foreach( $groups as $i ){
		if(!OC_Group::groupExists($i)){
			OC_Group::createGroup($i);
		}
		OC_Group::addToGroup( $username, $i );
	}
	$data["groups"] = implode( ", ", OC_Group::getUserGroups( $username ));
}

// Change Quota
if( isset( $_REQUEST["quota"] )){

	$quota = $_REQUEST["quota"];
	$unit = substr($quota, -1);

	switch ($unit)
	{
		case 'G' :
			$size = 1073741824;
            $quotasize = ((int)(substr($quota, 0 , strlen($quota)-1))) * $size;
			break;
		case 'M' :
			$size = 1048576;
			$quotasize = ((int)(substr($quota, 0 , strlen($quota)-1))) * $size;
			break;
		default:
			$quotasize = $quota;
	}

	OC_Preferences:: setValue( $username, 'files', 'quota', $quotasize );
	$data["quota"] = $quotasize;
}

if ($error)
	OC_JSON::error(array("data" => array( "message" => "Change Fail" )));
else{
	OC_JSON::success($data);
}

