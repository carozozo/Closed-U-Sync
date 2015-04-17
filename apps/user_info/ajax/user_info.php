<?php
$RUNTIME_NOSETUPFS = true;
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled(OC_UserInfo::appId);

$action = $_REQUEST['action'];
switch($action) {
	case 'changeNickname' :
		$l = new OC_L10N(OC_UserInfo::appId);
		$nickname = $_REQUEST['nickname'];
		$returnMess = OC_UserInfo::changeNickname($nickname);
		$returnMess = $l -> t($returnMess);
		OC_JSON::success(array('message' => $returnMess));
		break;
	default :
	case 'changePwd' :
		$l = new OC_L10N(OC_UserInfo::appId);
		$oldPwd = $_REQUEST['oldPwd'];
		$newPwd = $_REQUEST['newPwd'];
		$returnMess = OC_UserInfo::changePwd($oldPwd, $newPwd);
		$returnMess = $l -> t($returnMess);
		OC_JSON::success(array('message' => $returnMess));
		break;
	case 'changeEmail' :
		$l = new OC_L10N(OC_UserInfo::appId);
		$email = $_REQUEST['email'];
		# 如果是已驗證，則$vaildateMessColor為"green"
		$vaildateMessColor = $_REQUEST['vaildateMessColor'];
		$returnMess = OC_UserInfo::changeEmail($email, $vaildateMessColor);
		$returnMess = $l -> t($returnMess);
		OC_JSON::success(array('message' => $returnMess));
		break;
	default :
		break;
}
?>