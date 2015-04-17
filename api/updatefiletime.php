<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$postText = rawurldecode(file_get_contents('php://input'));
} else {
	exit ;
}


/**
 *  json format => {"userID":"0928887200","/1.mp4":"2012-04-13 14:48:00"}
 */

$client = json_decode($postText, 1);

$username = $client['userID'];
unset($client['userID']);

// require_once "inc_func.php";
// writeLog($username.'-syncfiletime', $postText);

require_once "../config/config.php";
$dir = $CONFIG_MAIN['CONFIG']['datadirectory'] . '/' . $username . '/files';

date_default_timezone_set("UTC");

list($filename) = array_keys($client);
$filetime = strtotime($client[$filename]);

setlocale(LC_ALL, 'zh_TW.UTF8');
echo touch($dir . $filename, $filetime) ? 1 : 0;
