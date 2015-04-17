<?php

if (isset($_REQUEST['token'])) {
	$token = base64_decode(urldecode($_REQUEST['token']));
} else {
	exit ;
}

// require_once "inc_db.php";
require_once ('../lib/base.php');
OC_Auth::directLogin($token);