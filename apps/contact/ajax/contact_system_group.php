<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('contact');

$action = $_REQUEST['action'];
switch($action) {
	case'getSystemGroupList' :
		$result = OC_Contact_System_Group::getSystemGroupList();
		OC_JSON::success(array('result' => $result));
		break;
	case'getContactListBySystemGroupId' :
		$systemGroupId = $_REQUEST['systemGroupId'];
		$result = OC_Contact_System_Group::getContactListBySystemGroupId($systemGroupId);
		OC_JSON::success(array('result' => $result));
		break;
	case'renameSystemGroup' :
		$systemGroupId = $_REQUEST['systemGroupId'];
		$systemGroupName = $_REQUEST['systemGroupName'];
		$result = OC_Contact_System_Group::renameSystemGroup($systemGroupId, $systemGroupName);
		OC_JSON::success(array('result' => $result));
		break;

	default :
		break;
}
?>