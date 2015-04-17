<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

$name = $_REQUEST["groupname"];

// Return Success story
if( OC_Group::deleteGroup( $name )){
	OC_JSON::success(array("data" => array( "groupname" => $name )));
}
else{
	OC_JSON::error(array("data" => array( "message" => "Unable to delete group" )));
}

?>
