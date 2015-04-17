<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

$username = $_REQUEST["u"];
$password = $_REQUEST["p"];

// Return Success story
if( OC_User::checkPassword( $username, $password )) {
	OC_JSON::success(array("data" => "true"));
}
else{
	OC_JSON::error(array("data" => "false"));
}

?>
