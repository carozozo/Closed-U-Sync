<?php
class OC_Notification {

	static function getNotificationCount($userId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			// $notificationArray = OC_Notification_DB::selectNotification(NULL, NULL, $userId, NULL, 0);
			$queryArr = array(
				'uid' => $userId,
				'readed' => 0
			);
			$notificationArray = OC_Notification_DB::selectNotification($queryArr);
			return count($notificationArray);
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification', 'getNotificationCount', $e);
			return FALSE;
		}
	}

	static function resetNotificationCount($userId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$queryArr = array('readed' => 1);
			$queryWhereArr = array(
				'uid' => $userId,
				'readed' => 0
			);
			OC_Notification_DB::updateNotification($queryArr, $queryWhereArr);
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification', 'resetNotificationCount', $e);
			return FALSE;
		}
	}

	static function getNotification($userId = NULL, $limit = NULL) {
		try {
			//刪除1年前的舊通知
			OC_Notification::delNotificationBeforeDays(365);

			$userId = OC_User::getUserByUserInput($userId);
			$order = array('createDate DESC');
			$queryArr = array('uid' => $userId, );
			$notificationArray = OC_Notification_DB::selectNotification($queryArr, $order, $limit);
			foreach ($notificationArray as $key => $notification) {
				$appId = $notification['appId'];
				$publisher = self::changeLanguage($appId, $notification['publisher']);
				$message = $notification['message'];
				$link = $notification['link'];
				//找出符合<xxxxx>格式的字串
				preg_match_all('#<[\w\s]*>#', $message, $matches, PREG_SET_ORDER);
				if (count($matches)) {
					for ($i = 0; $i < count($matches); $i++) {
						//將字串<xxxxx>轉為xxxxx，並轉換語系
						$messageMatch = $matches[$i][0];
						$newMessage = str_replace('<', '', $messageMatch);
						$newMessage = str_replace('>', '', $newMessage);
						$l = new OC_L10N($appId);
						$newMessage = self::changeLanguage($appId, $newMessage);
						//將$message裡面<xxxxx>格式的字串取代
						$message = str_replace($messageMatch, $newMessage, $message);
					}
				}
				$notificationArray[$key]['publisher'] = $publisher;
				$notificationArray[$key]['message'] = $message;
				$notificationArray[$key]['link'] = $link;
			}
			return $notificationArray;
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification', 'getNotification', $e);
			return FALSE;
		}
	}

	static function changeLanguage($appId, $message) {
		$l = new OC_L10N($appId);
		return $message = $l -> t($message);
	}

	static function addNotification($appId = NULL, $publisher = NULL, $uid = NULL, $message = NULL, $link = NULL) {
		try {
			if ($uid && $message) {
				$queryArr = array(
					'uid' => $uid,
					'message' => $message,
				);
				if ($appId)
					$queryArr['appId'] = $appId;
				if ($publisher)
					$queryArr['publisher'] = $publisher;
				if ($link)
					$queryArr['link'] = $link;

				//找出是否有相同的訊息
				$orderBy = array('createDate DESC');
				$notificationItems = OC_Notification_DB::selectNotification($queryArr, $orderBy, '1');
				if (count($notificationItems) > 0) {
					//有相同訊息，則只要更新為「未讀」及「產生日期」
					$now = OC_Helper::formatDateTimeUTCToLocal();
					$updateQueryArr = array(
						'readed' => 0,
						'createDate' => $now
					);
					return OC_Notification_DB::updateNotification($updateQueryArr, $queryArr);
				}

				return OC_Notification_DB::insertNotification($queryArr);
			}
			return 'No uid or message';
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification', 'addNotification', $e);
			return FALSE;
		}
	}

	static function delNotification($userId = NULL, $sn = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			if ($sn) {
				$queryArr = array('sn' => $sn, );
			} else {
				$queryArr = array('uid' => $userId, );
			}
			return OC_Notification_DB::deleteNotification($queryArr);
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification', 'delNotification', $e);
			return FALSE;
		}
	}

	//刪除指定天數前的舊通知
	static function delNotificationBeforeDays($days = 0) {
		try {
			return OC_Notification_DB::delNotificationBeforeDays($days);
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification', 'delNotificationBeforeDays', $e);
			return FALSE;
		}
	}

}
?>
