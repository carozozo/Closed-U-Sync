<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

switch ($action) {
	case 'getUserNickname' :
		$nickname = OC_User::getUserNickname($userId);
		OC_JSON::success(array('message' => $nickname));
		break;
	// XXX 規劃取代 api/uemail.php(需等IOS支援後，才刪除api/uemail.php)
	case 'getUserEmail' :
		$email = OC_User::getUserEmail($userId);
		OC_JSON::success(array('message' => $email));
		break;
	// XXX 規劃取代 api/ispaiduser.php(需等IOS支援後，才刪除api/ispaiduser.php)
	case 'isPaidUser' :
		$isPaidUser = OC_User::isPaidUser($userId);
		OC_JSON::success(array('message' => $isPaidUser));
		break;
	// XXX 規劃取代 api/uid.php(需等IOS支援後，才刪除api/uid.php)
	case 'getUserIdByEmail' :
		OC_JSON::success(array('message' => $userId));
		break;
	default :
		OC_JSON::error();
		break;
}
