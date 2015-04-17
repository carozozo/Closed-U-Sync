<?php

/**
 * 20130910 備註：這是舊版的converter api, 以後這支api將廢棄不用
 * User space usage API for Media Converter
 * example: /api/mcused.php?username=myname
 * return: json
 */
if (!isset($_REQUEST['username'])) {
	exit ;
}
require_once ('../lib/base.php');
$username = $_REQUEST['username'];

// if user input email as id
$userNameByEmail = OC_User::getUserIdByEmail($username);
if($userNameByEmail){
	$username = $userNameByEmail;
}

# 將user加入session
OC_User::setUserId($username);

$user_path = getenv("DOCUMENT_ROOT") . '/data/' . $username;
$used = OC_files::getDirectorySize($user_path);
$used['quota'] = OC_UserQuota::getUserQuota($username);
if (is_null($used['quota']))
	$used['quota'] = 0;

if(OC_App::isEnabled('media_streaming')){
	$used['isPaidUser'] = OC_User::isPaidUser($username);
	$used['convertTimes'] = OC_MediaConvert::getDailyConverTimes();
	$used['convertLimit'] = OC_MediaConvert::convertLimitTimes();
}

echo json_encode($used);
