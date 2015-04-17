<?php

/**
 * 20130910 備註：這是舊版的converter api, 以後這支api將廢棄不用
 * API for media converter add files
 * example: /api/mcadd.php?username=myname&file=/avi/mymovie.mov&folder=/格式精靈/&devicetype=phone&email=e@mail
 * return: success 1, fail -1
 */

setlocale(LC_ALL, 'en_US.UTF8');
// $RUNTIME_NOAPPS = FALSE;
require_once "../lib/base.php";

$username = $_REQUEST['username'];

// if user input email as id
$userNameByEmail = OC_User::getUserIdByEmail($username);
if($userNameByEmail){
	$username = $userNameByEmail;
}

# 將user加入session
OC_User::setUserId($username);
# 架構filesystem
OC_Util::setupFS($username);

$deviceType = $_REQUEST['devicetype'];
$filePath = $_REQUEST['file'];
$dir = dirname($filePath);
$fileName = basename($filePath);
$returnArr = OC_MediaConvert::convertMedia($dir, $fileName, $deviceType);
if ($returnArr['status'] == 'success') {
	echo 1;
} else {
	echo 0;
}

/*
 $userName = $_REQUEST['username'];
 $fileName = 'https://' . $_SERVER["HTTP_HOST"] . '/dav/mcdav.php' . $_REQUEST['file'];
 $destFolder = 'https://' . $_SERVER["HTTP_HOST"] . '/dav/mcdav.php' . $_REQUEST['folder'];

 // parameter setup
 $deviceType = $_REQUEST['devicetype'];
 $query = OC_DB::prepare('SELECT frame_rate, frame_size, video_codec, bit_rate FROM *PREFIX*media_streaming_device_type WHERE device_type= ?');
 $result = $query -> execute(array($deviceType));
 $row = $result -> fetchRow();
 if ($row) {
 $frameRate = $row["frame_rate"];
 $frameSize = $row["frame_size"];
 $videoCodec = $row["video_codec"];
 $bitRate = $row["bit_rate"];
 }

 $Email = $_REQUEST['email'];
 try {
 $query = OC_DB::prepare('INSERT INTO *PREFIX*mediaconverter (userName, fileName, destFolder, deviceType, frameRate, frameSize, videoCodec, bitRate, Email) values (?, ?, ?, ?, ?, ?, ?, ?, ?)');
 $result = $query -> execute(array(
 $userName,
 $fileName,
 $destFolder,
 $deviceType,
 $frameRate,
 $frameSize,
 $videoCodec,
 $bitRate,
 $Email
 ));
 } catch (Exception $e) {
 // echo $e->getMessage();
 echo -1;
 exit ;
 }

 // success
 echo 1;
 */
