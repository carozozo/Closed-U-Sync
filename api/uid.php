<?php

if (isset($_REQUEST['email'])) {
	$email = $_REQUEST['email'];
} else {
	exit ;
}

// do not load apps
$RUNTIME_NOAPPS = true;
require_once ('../lib/base.php');

// if user input email as id
if (strpos($email, '@')) {
	echo OC_User::getUserIdByEmail($email);
}
