<?php

if (isset($_REQUEST['token'])) {
	$tmp = base64_decode($_REQUEST['token']);
} else {
	exit ;
}

require_once "inc_db.php";
//[lib/base.php] must be last required
require_once ('../lib/base.php');

$key = substr($tmp, 0, 16);

$sql = "delete from $prefix" . "tokenlogin where TIME_TO_SEC(TIMEDIFF(now(), createtime)) > 30";
$dataset = mysql_query($sql);

//$sql = "select tokenprefix from $prefix"."tokenlogin where tokenprefix like '".$key."%' and TIME_TO_SEC(TIMEDIFF(now(), createtime)) <= 30 LIMIT 1";
$sql = "select tokenprefix from $prefix" . "tokenlogin where tokenprefix like '" . $key . "%' LIMIT 1";
$dataset = mysql_query($sql);

if (list($tokenprefix) = mysql_fetch_array($dataset)) {
	$authstring = str_replace($tokenprefix, '', $tmp);
	list($username, $password) = explode(':', base64_decode($authstring));
	if (!OC_User::login($username, $password)) {
		echo 'You are from ' . $_SERVER['REMOTE_ADDR'];
		exit ;
	}

	header('Location: ' . OC::$WEBROOT . '/');
}
echo 'You are from ' . $_SERVER['REMOTE_ADDR'];
