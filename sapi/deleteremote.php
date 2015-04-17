<?php
// Server limited
require_once('inc_server.php');

// Do not load FS ...
$RUNTIME_NOSETUPFS = true;

require_once('../lib/base.php');

if (!isset($_REQUEST["username"]) || !isset($_REQUEST["username"])) {
	OC_JSON::error(array("data" => array( "message" => "Unable to delete token" )));
	exit;
}

$username = $_REQUEST["username"];
$token = $_REQUEST["token"];

$query=OC_DB::prepare("DELETE FROM *PREFIX*authtoken WHERE token=? AND user=?");
$result=$query->execute(array($token,$username));

if ($result) {
	OC_JSON::success(array("data" => array( "username" => $username, "token" => $token)));
} else {
	OC_JSON::error(array("data" => array( "message" => "Unable to delete token" )));
}
