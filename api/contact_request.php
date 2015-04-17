<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

header('Content-Type: application/json; charset=utf-8');
switch($action) {
	case "getContactList" :
		$contactList = OC_Contact::getContactList($userId);
		echo json_encode($contactList);
		//var_dump($contactList);
		break;
	case "addContact" :
		if (!empty($_REQUEST['contactId'])) {
			$contactObj = new OC_Contact(NULL, $_REQUEST['contactId'], $_REQUEST['contactNickname']);
			echo json_encode($contactObj -> addContact($userId));
		} else {
			echo json_encode('No contactId');
		}
		break;
	case "renameContactNickname" :
		if (!empty($_REQUEST['contactId']) && !empty($_REQUEST['contactNickname'])) {
			$contactObj = new OC_Contact(NULL, $_REQUEST['contactId'], $_REQUEST['contactNickname']);
			echo json_encode($contactObj -> renameContactNickname($userId, $_REQUEST['contactNickname']));
		} else {
			echo json_encode('No contactId or contactNickname');
		}
		break;
	case "delContact" :
		if (!empty($_REQUEST['contactId'])) {
			$contactObj = new OC_Contact(NULL, $_REQUEST['contactId']);
			echo json_encode($contactObj -> delContact($userId));
		} else {
			echo json_encode('No contactId');
		}
		break;
	case "getGroupList" :
		$groupList = OC_Contact_Group::getGroupList($userId);
		echo json_encode($groupList);
		break;
	case "addGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$contactGroupObj = new OC_Contact_Group($_REQUEST['groupName'], NULL);
			echo json_encode($contactGroupObj -> addGroup($userId));
		} else {
			echo json_encode('No groupName');
		}
		break;
	case "renameGroup" :
		if (!empty($_REQUEST['groupName']) && !empty($_REQUEST['newGroupName'])) {
			$contactGroupObj = new OC_Contact_Group($_REQUEST['groupName'], NULL, $_REQUEST['newGroupName']);
			echo json_encode($contactGroupObj -> renameGroup($userId));
		} else {
			echo json_encode('No groupName or newGroupName');
		}
		break;
	case "delGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$groupName = $_REQUEST['groupName'];
			$groupId = OC_Contact_Group::getIdByName($userId, $groupName);
			$contactGroupObj = new OC_Contact_Group($_REQUEST['groupName'], NULL, NULL, $groupId);
			echo json_encode($contactGroupObj -> delGroup($userId));
		} else {
			echo json_encode('No groupName');
		}
		break;
	case "getContactListByGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$groupName = $_REQUEST['groupName'];
			$groupId = OC_Contact_Group::getIdByName($userId, $groupName);
			echo json_encode(OC_Contact_Group::getContactListByGroupId($userId, $groupId));
		} else {
			echo json_encode('No groupName');
		}
		break;
	case "updateContactInGroup" :
		if (!empty($_REQUEST['groupName'])) {
			$contactId = $_REQUEST['contactId'] ? $_REQUEST['contactId'] : '';
			$groupName = $_REQUEST['groupName'];
			$groupId = OC_Contact_Group::getIdByName($userId, $groupName);
			$contactGroupObj = new OC_Contact_Group(NULL, $contactId, NULL, $groupId);
			echo json_encode($contactGroupObj -> updateContactInGroup($userId));
		} else {
			echo json_encode('No groupName');
		}
		break;
	default :
		break;
}
