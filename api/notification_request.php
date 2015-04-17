<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

switch($action) {
	case 'getNotificationCount' :
		try {
			$count = OC_Notification::getNotificationCount($userId);
			// OC_Log::write($count, gettype($count), 1);
			if (!is_bool($count)) {
				OC_JSON::success(array('count' => $count));
			} else {
				OC_JSON::error();
			}
		} catch (Exception $e) {
			OC_Log::writeException('notification_request', 'getNotificationCount', $e);
			OC_JSON::error(array('getNotificationCount Exception'));
		}
		break;
	case 'resetNotificationCount' :
		try {
			$result = OC_Notification::resetNotificationCount($userId);
			if ($result) {
				OC_JSON::success();
			} else {
				OC_JSON::error();
			}
		} catch (Exception $e) {
			OC_Log::writeException('notification_request', 'resetNotificationCount', $e);
			OC_JSON::error(array('resetNotificationCount Exception'));
		}
		break;
	case 'getNotification' :
		try {
			$limit = (isset($_REQUEST['limit'])) ? $_REQUEST['limit'] : NULL;
			$notificationList = OC_Notification::getNotification($userId, $limit);
			if (!is_bool($notificationList) && $notificationList !== FALSE) {
				OC_JSON::success(array('notificationList' => $notificationList));
			} else {
				OC_JSON::error();
			}
		} catch (Exception $e) {
			OC_Log::writeException('notification_request', 'getNotification', $e);
			OC_JSON::error(array('getNotification Exception'));
		}
		break;
	case 'delNotification' :
		try {
			$sn = (isset($_REQUEST['sn'])) ? $_REQUEST['sn'] : NULL;
			$result = OC_Notification::delNotification($userId, $sn);
			if ($result) {
				OC_JSON::success();
			} else {
				OC_JSON::error();
			}
		} catch (Exception $e) {
			OC_Log::writeException('notification_request', 'delNotification', $e);
			OC_JSON::error(array('delNotification Exception'));
		}
		break;
	default :
		break;
}
