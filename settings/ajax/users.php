<?php
$RUNTIME_NOAPPS = TRUE;
//no apps, yet
require_once ('../../lib/base.php');

if (!OC_User::isLoggedIn() || !isset($_REQUEST['action'])) {
	exit ;
}

$action = $_REQUEST['action'];
switch($action) {
	case'getUsers' :
		$start = $_REQUEST['start'];
		$number = $_REQUEST['number'];
		$userList = array();
		$users = OC_User::getUsers();
		$users = array_slice($users, $start, $number);
		foreach ($users as $index => $uid) {
			# user 名稱
			$userList[$index]['name'] = $uid;
			# user 所屬的群組
			$userGroups = OC_Group::getUserGroups($uid);
			$userList[$index]['groups'] = join(", ", $userGroups);
			# user 的容量
			$userList[$index]['quota'] = OC_Helper::humanFileSize(OC_Preferences::getValue($uid, 'files', 'quota', 0));
		}
		OC_JSON::success(array('result' => $userList));
		break;
	case'getGroups' :
		$groups = OC_Group::getGroups();
		foreach ($groups as $index => $group) {
			$groupList[$index]['name'] = $group;
		}
		OC_JSON::success(array('result' => $groupList));
		break;
	default :
		break;
}
?>
