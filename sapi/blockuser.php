<?php

// Server limited
require_once('inc_server.php');

// Do not load FS ...
$RUNTIME_NOSETUPFS = true;

// Init owncloud
require_once('../lib/base.php');

if( isset($_REQUEST["username"]) ){

	$username = $_REQUEST["username"];
	
	if (OC_User::userExists( $username )) {
		$query=OC_DB::prepare("UPDATE *PREFIX*users set uid=? WHERE uid='".$username."'");
		$result=$query->execute(array('##'.$username));
		OC_JSON::success(array( "name" => $username,  "message" => "User blocked!"));
	} elseif (OC_User::userExists( '##'.$username )) {
		OC_JSON::success(array( "name" => $username,  "message" => "User already blocked!"));
	} else {
		OC_JSON::error(array("data" => array( "message" => "User not Exist!" )));
	}
}