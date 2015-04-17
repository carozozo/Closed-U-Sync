<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

$username = $_REQUEST["username"];

// Return Success story
if( OC_User::deleteUser( $username )){
	OC_JSON::success(array("data" => array( "username" => $username )));
}
else{
	OC_JSON::error(array("data" => array( "message" => "Unable to delete user" )));
}

?>
