<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('contact');

try {
	$action = $_REQUEST['action'];
	$groupName = NULL;
	$contactId = NULL;
	$newGroupName = NULL;
	$groupId = NULL;
	if (isset($_REQUEST['groupName']))
		$groupName = $_REQUEST['groupName'];
	if (isset($_REQUEST['contactId']))
		$contactId = $_REQUEST['contactId'];
	if (isset($_REQUEST['newGroupName']))
		$newGroupName = $_REQUEST['newGroupName'];
	if (isset($_REQUEST['groupId']))
		$groupId = $_REQUEST['groupId'];

	$contactGroupObj = new OC_Contact_Group($groupName, $contactId, $newGroupName, $groupId);
	$result = $contactGroupObj -> $action();
	OC_JSON::success(array('result' => $result));
} catch(exception $e) {
	OC_JSON::error();
}
?>