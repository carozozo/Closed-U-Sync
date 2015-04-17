<?php
class OC_Notification_DB {
	//refer to DB table [oc_notification]
	private static $elementArry = array(
		'sn',
		'appId',
		'publisher',
		'uid',
		'message',
		'link',
		'readed',
		'createDate',
	);
	static function selectNotification($queryArr = NULL, $order = NULL, $limit = NULL) {
		try {
			$queryElementArr = array();
			$queryStr = 'SELECT * FROM *PREFIX*notification';
			if ($queryArr) {
				$queryStr .= ' WHERE ';
			}
			$isFirst = TRUE;
			foreach (self::$elementArry as $key => $element) {
				if ($queryArr && isset($queryArr[$element]) && $queryArr[$element] !== NULL) {
					if ($isFirst == FALSE)
						$queryStr .= ' AND ';
					else
						$isFirst = FALSE;
					$queryStr .= $element . '=?';
					$queryElementArr[] = $queryArr[$element];

				}
			}
			if (is_array($order)) {
				foreach ($order as $key => $val) {
					if ($key == 0)
						$queryStr .= ' ORDER BY ';
					else
						$queryStr .= ' AND ';
					$queryStr .= $val;
				}
			}
			if ($limit)
				$queryStr .= ' LIMIT ' . $limit;
			$query = OC_DB::prepare($queryStr);
			return $notificationArray = $query -> execute($queryElementArr) -> fetchAll();
			// $notificationArray = array();
			// while ($row = $result -> fetchRow()) {
			// $publisherId = $row['publisher'];
			// $row['publisherName'] = OC_Notification::getPublisherNameById($publisherId);
			// $notificationArray[] = $row;
			// }
			// return $notificationArray;
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification_DB', 'selectNotification', $e);
			return FALSE;
		}
	}

	static function insertNotification($queryArr = NULL) {
		try {
			$queryElementArr = array();
			$queryStr = 'INSERT INTO *PREFIX*notification (';
			$isFirst = TRUE;
			foreach (self::$elementArry as $key => $element) {
				if ($queryArr && isset($queryArr[$element]) && $queryArr[$element] !== NULL) {
					if ($isFirst == FALSE)
						$queryStr .= ', ';
					else
						$isFirst = FALSE;
					$queryStr .= $element;
					$queryElementArr[] = $queryArr[$element];
				}
			}
			$queryStr .= ') VALUES (';
			$isFirst = TRUE;
			for ($i = 0; $i < count($queryArr); $i++) {
				if ($isFirst == FALSE)
					$queryStr .= ', ';
				else
					$isFirst = FALSE;
				$queryStr .= '?';
			}
			$queryStr .= ')';
			$query = OC_DB::prepare($queryStr);
			$result = $query -> execute($queryElementArr);
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification_DB', 'insertNotification', $e);
			return FALSE;
		}
	}

	static function updateNotification($queryArr = NULL, $queryWhereArr = NULL) {
		try {
			$queryElementArr = array();
			$queryStr = 'UPDATE *PREFIX*notification SET ';
			$isFirst = TRUE;
			foreach (self::$elementArry as $key => $element) {
				if ($queryArr && isset($queryArr[$element]) && $queryArr[$element] !== NULL) {
					if ($isFirst == FALSE)
						$queryStr .= ', ';
					else
						$isFirst = FALSE;
					$queryStr .= $element . '=?';
					$queryElementArr[] = $queryArr[$element];
				}
			}
			$queryStr .= ' WHERE ';
			$isFirst = TRUE;
			foreach (self::$elementArry as $key => $element) {
				if ($queryWhereArr && isset($queryWhereArr[$element]) && $queryWhereArr[$element] !== NULL) {
					if ($isFirst == FALSE)
						$queryStr .= ' AND ';
					else
						$isFirst = FALSE;
					$queryStr .= $element . '=?';
					$queryElementArr[] = $queryWhereArr[$element];
				}
			}
			$query = OC_DB::prepare($queryStr);
			$result = $query -> execute($queryElementArr);
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification_DB', 'updateNotification', $e);
			return FALSE;
		}
	}

	static function deleteNotification($queryArr) {
		try {
			$queryElementArr = array();
			$queryStr = 'DELETE FROM *PREFIX*notification WHERE ';
			$isFirst = TRUE;
			foreach (self::$elementArry as $key => $element) {
				if ($queryArr && isset($queryArr[$element]) && $queryArr[$element] !== NULL) {
					if ($isFirst == FALSE)
						$queryStr .= ' AND ';
					else
						$isFirst = FALSE;
					$queryStr .= $element . '=?';
					$queryElementArr[] = $queryArr[$element];
				}
			}
			$query = OC_DB::prepare($queryStr);
			$result = $query -> execute($queryElementArr);
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification_DB', 'deleteNotification', $e);
			return FALSE;
		}
	}

	static function delNotificationBeforeDays($days = 0) {
		try {
			// OC_Log::write('delNotificationBeforeDays', is_integer($days), 1);
			if ($days && is_integer($days)) {
				$days = -$days;
				$queryStr = 'DELETE from oc_notification WHERE createDate < DATE_ADD(NOW(),INTERVAL ' . $days . ' Day)';
				$query = OC_DB::prepare($queryStr);
				$result = $query -> execute();
			}
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Notification_DB', 'delNotificationBeforeDays', $e);
			return FALSE;
		}
	}

}
?>
