<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$postText = rawurldecode(file_get_contents('php://input'));
} else {
	exit ;
}
//Note: sync can not work in https(ssl), so can not include OwnCloud Class [/lib/base.php]
require_once ($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');
require_once "inc_db.php";
require_once "inc_func.php";

/**
 *  json format => {"userID":"0928887200","/1.mp4":"2012-04-13 14:48:00"}
 */

$client = json_decode($postText, 1);
$username = $client['userID'];
unset($client['userID']);

// writeLog($username.'-syncftime', $postText);

//$CONFIG_MAIN is from [/config/config.php]
$sql = "select configvalue from ".$prefix."appconfig where appId = 'u_drive' and configkey = 'dataDir' limit 1";
$dataset = mysql_query($sql);
list($dataDir) = mysql_fetch_array($dataset);

$dir = $CONFIG_MAIN["CONFIG"]["datadirectory"] . '/' . $username . '/files/' . $dataDir;
date_default_timezone_set("UTC");

list($filename) = array_keys($client);
$filetime = strtotime($client[$filename]);

setlocale(LC_ALL, 'zh_TW.UTF8');
echo touch($dir . $filename, $filetime) ? 1 : 0;
