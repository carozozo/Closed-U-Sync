<?php

/*
 * media converter server use only
 */

// check if from valid ip
require_once '../sapi/inc_server.php';
require_once '../config/config.php';
date_default_timezone_set('Asia/Taipei');

// Files we need
require_once '../3rdparty/Sabre/DAV/includes.php';
require_once '../3rdparty/Sabre/HTTP/includes.php';

// prepare file path
$subdir = str_replace('\\', '/', substr($_SERVER["SCRIPT_NAME"], 0, strpos($_SERVER["SCRIPT_NAME"], 'mcdav.php') - 1));
if ($subdir == '/') {
	$subdir = '';
}
$pageurl = 'http';
if (array_key_exists("HTTPS", $_SERVER)) {
	if ($_SERVER["HTTPS"] == "on") {
		$pageurl .= "s";
	}
}
$pageurl .= "://";
if ($_SERVER["SERVER_PORT"] != "80") {
	$pageurl .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $subdir;
} else {
	$pageurl .= $_SERVER["SERVER_NAME"] . $subdir;
}

// set up authentication
$auth = new Sabre_HTTP_BasicAuth();

if (!(list($username, $password) = $auth -> getUserPass())) {
	$auth -> requireLogin();
	echo "Authentication required\n";
	die();
}

// set the path and authentication realm
$path = $CONFIG_MAIN['CONFIG']['datadirectory'] . '/' . $username . '/files';
$realm = 'mcdav';

// create the WebDAV server
$tree = new Sabre_DAV_ObjectTree(new Sabre_DAV_FS_Directory($path));
$server = new Sabre_DAV_Server($tree);
$server -> setBaseUri($subdir . '/mcdav.php/');

// add the browser plug-in
$server -> addPlugin(new Sabre_DAV_Browser_Plugin());

// add the locks plug-in
$backend = new Sabre_DAV_Locks_Backend_File('tmp/locks');
$server -> addPlugin(new Sabre_DAV_Locks_Plugin($backend));

// handle the request
$server -> exec();
