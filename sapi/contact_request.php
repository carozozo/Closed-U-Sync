<?php
require_once ('inc_server.php');
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

switch ($action) {
	case "getContactList" :
		$contactList = OC_Contact::getContactList($userId);
		OC_JSON::success(array('result' => $contactList));
		break;
	case "addContact" :
		if (!empty($_REQUEST['contactId'])) {
			$contactObj = new OC_Contact(null, $_REQUEST['contactId'], $_REQUEST['contactNickname']);
			$addContact = $contactObj -> addContact($userId);
			OC_JSON::success(array('result' => $addContact));
		} else {
			OC_JSON::error(array('result' => 'No contactId'));
		}
		break;
	case "renameContactNickname" :
		if (!empty($_REQUEST['contactId']) && !empty($_REQUEST['contactNickname'])) {
			$contactObj = new OC_Contact(null, $_REQUEST['contactId'], $_REQUEST['contactNickname']);
			$renameContactNickname = $contactObj -> renameContactNickname($userId, $_REQUEST['contactNickname']);
			OC_JSON::success(array('result' => $addContact));
		} else {
			OC_JSON::error(array('result' => 'No contactId or contactNickname'));
		}
		break;
	case "delContact" :
		if (!empty($_REQUEST['contactId'])) {
			$contactObj = new OC_Contact(null, $_REQUEST['contactId']);
			$delContact = $contactObj -> delContact($userId);
			OC_JSON::success(array('result' => $delContact));
		} else {
			OC_JSON::error(array('result' => 'No contactId'));
		}
		break;
	case "getGroupList" :
		$groupList = OC_Contact_Group::getGroupList($userId);
		OC_JSON::success(array('result' => $groupList));
		break;
	case "addGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$contactGroupObj = new OC_Contact_Group($_REQUEST['groupName'], null);
			$addGroup = $contactGroupObj -> addGroup($userId);
			OC_JSON::success(array('result' => $addGroup));
		} else {
			OC_JSON::error(array('result' => 'No groupName'));
		}
		break;
	case "renameGroup" :
		if (!empty($_REQUEST['groupName']) && !empty($_REQUEST['newGroupName'])) {
			$contactGroupObj = new OC_Contact_Group($_REQUEST['groupName'], null, $_REQUEST['newGroupName']);
			$renameGroup = $contactGroupObj -> renameGroup($userId);
			OC_JSON::success(array('result' => $renameGroup));
		} else {
			OC_JSON::error(array('result' => 'No groupName or newGroupName'));
		}
		break;
	case "delGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$groupName = $_REQUEST['groupName'];
			$groupId = OC_Contact_Group::getIdByName($userId, $groupName);
			$contactGroupObj = new OC_Contact_Group($_REQUEST['groupName'], null, null, $groupId);
			$delGroup = $contactGroupObj -> delGroup($userId);
			OC_JSON::success(array('result' => $delGroup));
		} else {
			OC_JSON::error(array('result' => 'No groupName'));
		}
		break;
	case "getContactListByGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$groupName = $_REQUEST['groupName'];
			$groupId = OC_Contact_Group::getIdByName($userId, $groupName);
			$getContactListByGroupId = OC_Contact_Group::getContactListByGroupId($userId, $groupId);
			OC_JSON::success(array('result' => $getContactListByGroupId));
		} else {
			OC_JSON::error(array('result' => 'No groupName'));
		}
		break;
	case "updateContactInGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$contactId = $_REQUEST['contactId'] ? $_REQUEST['contactId'] : '';
			$groupName = $_REQUEST['groupName'];
			$groupId = OC_Contact_Group::getIdByName($userId, $groupName);
			$contactGroupObj = new OC_Contact_Group(null, $contactId, null, $groupId);
			$updateContactInGroup = $contactGroupObj -> updateContactInGroup($userId);
			OC_JSON::success(array('result' => $updateContactInGroup));
		} else {
			OC_JSON::error(array('result' => 'No groupName'));
		}
		break;
	case 'addSystemGroup' :
		$dpId = $_REQUEST['dpid'];
		$dpName = $_REQUEST['dpname'];
		$result = OC_SkytekSystemGrop::addSystemGroup($dpId, $dpName);
		OC_JSON::success(array('result' => $result));
		break;
	case 'updateSystemGroupName' :
		$dpId = $_REQUEST['dpid'];
		$dpName = $_REQUEST['dpname'];
		$result = OC_SkytekSystemGrop::updateSystemGroupName($dpId, $dpName);
		OC_JSON::success(array('result' => $result));
		break;
	case 'deleteSystemGroup' :
		$dpId = $_REQUEST['dpid'];
		$result = OC_SkytekSystemGrop::deleteSystemGroup($dpId);
		OC_JSON::success(array('result' => $result));
		break;
	default :
		OC_JSON::error();
		break;
}
