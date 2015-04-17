<?php
/**
 * ownCloud - Media Convert plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * Convert DB
 * 存放 convert相關資料，以控制轉檔排程
 */
class OC_MediaConvert_DB {
	/****************************************************************************
	 * 1.6版
	 * 轉檔DB的操作主要由Tomcat Server控制
	 *****************************************************************************/

	/**
	 * 取得轉檔資料1.6版
	 * @param user id, file name, device type
	 * @return array
	 */
	static function getConverterItem1_6($userId, $fileName, $deviceType) {
		try {
			$query = OC_DB::prepare('SELECT * FROM *PREFIX*mediaconverter WHERE userName = ? AND fileName = ? AND deviceType = ? LIMIT 1');
			$result = $query -> execute(array(
				$userId,
				$fileName,
				$deviceType
			));
			$row = $result -> fetchRow();
			if ($row && count($row)) {
				return $row;
			}
		} catch (Exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConverterItem1_6', $e);
		}
	}

	/**
	 * 寫入轉檔資料1.6版
	 * @param user id, source full path, dest folder, device type, frame rate, frame size, video code,bit rate, user email
	 * @return true
	 */
	static function insertConvert1_6($userId, $sourceFullPath, $destFolder, $deviceType, $frameRate, $frameSize, $videoCodec, $bitRate, $email) {
		try {
			$query = OC_DB::prepare('INSERT INTO *PREFIX*mediaconverter (userName, fileName, destFolder, deviceType, frameRate, frameSize, videoCodec, bitRate, Email) values (?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$query -> execute(array(
				$userId,
				$sourceFullPath,
				$destFolder,
				$deviceType,
				$frameRate,
				$frameSize,
				$videoCodec,
				$bitRate,
				$email,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'insertConvert1_6', $e);
		}
	}

	/**
	 * 取得使用者一天內的轉檔次數1.6版
	 * @param user id
	 * @return int
	 */
	static function getConvertItemsInDayByUser1_6($userId) {
		try {
			$query = OC_DB::prepare('SELECT (SELECT count(1) FROM *PREFIX*mediaconverter WHERE userName = ? AND date(registerDate) = date(now())) + (SELECT count(1) FROM *PREFIX*mediaconverter_log WHERE userName = ? AND date(registerDate) = date(now()) AND failDate is null) cnt FROM DUAL');
			$result = $query -> execute(array(
				$userId,
				$userId
			));
			$row = $result -> fetchRow();
			if ($row) {
				$counts = $row["cnt"];
				return $counts;
			} else {
				return 0;
			}
		} catch (Exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItemsInDayByUser1_6', $e);
		}
	}

	/**
	 * 取得轉檔完, 但還未發佈通知或未寄發email的convert資料1.6版
	 * (1.6版轉檔完成後,會將資料搬到log,但不一定代表轉檔成功)
	 * notification: 0未通知, 1已通知
	 * @return int
	 */
	static function getConvertItemsWithoutNotification1_6() {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*mediaconverter_log WHERE notification != ?");
			$result = $query -> execute(array("1", )) -> fetchAll();
			if (count($result)) {
				return $result;
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItemsWithoutNotification1_6', $e);
		}
	}

	/**
	 * 更新convert「是否已通知」1.6版
	 * notification: 0未通知, 1已通知
	 * @param user id, register date, device type, if notification
	 * @return true
	 */
	static function updateConvertItemNotification1_6($userId, $registerDate, $deviceType, $notification = 1) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*mediaconverter_log SET notification = ?  WHERE userName = ? AND registerDate = ? AND deviceType = ?");
			$result = $query -> execute(array(
				$notification,
				$userId,
				$registerDate,
				$deviceType,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertItemNnotification1_6', $e);
		}
	}

	/****************************************************************************
	 * 2.1版
	 * 當中的user id , source path , output name指的是本地端檔案的真正相關資料(轉檔用)
	 * 而target user id, target source path是「要求轉檔的使用者及當時看到的路徑」
	 * target path是轉檔完成後，格式精靈要輸出給user使用的檔案
	 * target output name是轉檔完成後，要轉換成Web Streaming可以觀看的output檔名
	 *****************************************************************************/

	/**
	 * 取得所有轉檔資料
	 * @return array
	 */
	static function getConvertItems() {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_convert ORDER BY insert_time DESC");
			$result = $query -> execute();
			return $result -> fetchAll();
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItems', $e);
		}
	}

	/**
	 * 依轉檔狀態取得資料列表
	 * @param 轉檔狀態
	 * @return array
	 */
	static function getConvertItemsByStatus($status, $orderByInserTime = true) {
		try {
			$queryStr = "SELECT * FROM *PREFIX*media_streaming_convert WHERE status = ? ";
			if ($orderByInserTime) {
				$queryStr .= "ORDER BY insert_time ASC";
			}
			$query = OC_DB::prepare($queryStr);
			$result = $query -> execute(array($status, )) -> fetchAll();
			if (count($result)) {
				return $result;
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItemsByStatus', $e);
		}
	}

	/**
	 * 依output name,取得轉檔資料
	 * @param 輸出檔名, 目標輸出檔名
	 * @return array
	 */
	static function getConvertItemsByOutputName($outputName, $targetOutputName) {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_convert WHERE output_name = ? AND target_output_name = ?");
			$result = $query -> execute(array(
				$outputName,
				$targetOutputName,
			)) -> fetchAll();
			if (count($result)) {
				return $result;
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItemByOutputName', $e);
		}
	}

	/**
	 * 取得該使用者一天之內轉檔的列表
	 * @param 使用者id
	 * @return array
	 */
	static function getConvertItemsInDayByUser($userId) {
		try {
			# 取得今天的日期
			$localDateTime = OC_Helper::formatDateTimeUTCToLocal(null, 'Y-m-d');
			# 將今天的日期轉成utc時間格式
			$utcDateTime = OC_Helper::formatDateTimeLocalToUTC($localDateTime);
			# 找出insert time為今天之內的資料
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_convert WHERE target_user_id = ? AND insert_time > ? AND insert_time <= DATE_ADD( ? , INTERVAL 1 DAY )");
			$result = $query -> execute(array(
				$userId,
				$utcDateTime,
				$utcDateTime,
			));
			return $result -> fetchAll();
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItemsInDayByUser', $e);
		}
	}

	/**
	 * 依使用者帳號及來源路徑，取得轉檔資料
	 * @param user id, source path
	 * @return array
	 */
	static function getConvertItemsByUserIdAndSourcePath($userId, $sourcePath) {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_convert WHERE user_id = ? AND source_path = ?");
			$result = $query -> execute(array(
				$userId,
				$sourcePath,
			));
			return $result -> fetchAll();
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItemsUserIdAndSourcePath', $e);
		}
	}

	/**
	 * 依轉檔server ip，取得轉檔資料
	 * @param server ip
	 * @return array
	 */
	static function getConvertItemByServerIp($serverIp) {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_convert WHERE server_ip = ?");
			$result = $query -> execute(array($serverIp, )) -> fetchAll();
			if (count($result))
				return $result[0];
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertItemByServerIp', $e);
		}
	}

	/**
	 * 寫入轉檔資料(db預設的status=3 待轉檔)
	 * @param user id, source path, device type, output name,目標 user id, 目標 source path, 目標 output name, 寫入時間, 目標 user email
	 * @return true
	 */
	static function insertConvert($userId, $sourcePath, $outputName, $targetUserId, $targetSourcePath, $targetPath, $targetOutputName, $deviceType, $insertTime, $targetEmail) {
		try {
			$query = OC_DB::prepare("INSERT INTO *PREFIX*media_streaming_convert (user_id, source_path, output_name, target_user_id, target_source_path, target_path, target_output_name, device_type, insert_time, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$result = $query -> execute(array(
				$userId,
				$sourcePath,
				$outputName,
				$targetUserId,
				$targetSourcePath,
				$targetPath,
				$targetOutputName,
				$deviceType,
				$insertTime,
				$targetEmail,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'insertConvert', $e);
		}
	}

	/**
	 * 依primary key,更新轉檔資料
	 * @param 轉檔server ip, pid, 開始轉檔時間, hls url, 輸出檔名, 目標輸出檔名, 輸入時間
	 * @return true
	 */
	static function updateConvert($serverIp, $pid, $startTime, $hlsUrl, $outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert SET server_ip = ?, pid = ?, start_time = ?, hls_url = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$serverIp,
				$pid,
				$startTime,
				$hlsUrl,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvert', $e);
		}
	}

	/**
	 * 依primary key,更新影片時間
	 * @param 來源影片時間, 輸出檔影片時間, 輸出檔名, 目標輸出檔名, 輸入時間
	 * @return true
	 */
	static function updateConvertFootage($sourceFootage, $outputFootage, $outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert SET source_footage = ? , output_footage = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$sourceFootage,
				$outputFootage,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertFootage', $e);
		}
	}

	/**
	 * 依primary key更新狀態
	 * @param 狀態, 輸出檔名, 目標輸出檔名, 輸入DB的時間
	 * @return true
	 */
	static function updateConvertStatus($status, $outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert SET status = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$status,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertStatus', $e);
		}
	}

	/**
	 * 依primary key更新「第一次要求轉檔的時間」
	 * @param output name, target output name, insert time
	 * @return true
	 */
	static function updateConvertRequestTime($outputName, $targetOutputName, $insertTime) {
		try {
			$requestTime = OC_Helper::formatDateTimeLocalToUTC();
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert SET request_time = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$requestTime,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertRequestTime', $e);
		}
	}

	/**
	 * 依primary key更新「要求失敗次數」
	 * @param request failed times, output name, target output name, insert time
	 * @return true
	 */
	static function updateConvertRequestFailedTimes($requestFailedTimes, $outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert SET request_failed_times = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$requestFailedTimes,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertRequestFailedTimes', $e);
		}
	}

	/**
	 * 依primary key更新「確認失敗次數」
	 * @param check failed times, output name, target output name, insert time
	 * @return true
	 */
	static function updateConvertCheckFailedTimes($checkFailedTimes, $outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert SET check_failed_times = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$checkFailedTimes,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertCheckFailedTimes', $e);
		}
	}

	/**
	 * 依primary key,更新來源使用者名稱,來源路徑,輸出檔名
	 * @param new user id, new source path, new output name, output name, target output name,
	 * @return true
	 */
	static function updateConvertForRename($newUserId, $newSourcePath, $newOutputName, $outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert SET user_id = ?, source_path = ?, output_name = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$newUserId,
				$newSourcePath,
				$newOutputName,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertForRename', $e);
		}
	}

	/**
	 * 依primary key,將資料複製到log
	 * @param 輸出名稱,目標輸出名稱,輸入DB的時間
	 * @return true
	 */
	static function copyConvertToLog($outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("INSERT INTO *PREFIX*media_streaming_convert_log SELECT * FROM *PREFIX*media_streaming_convert WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			$logTime = OC_Helper::formatDateTimeLocalToUTC();
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert_log SET log_time = ? WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$logTime,
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'copyConvertToLog', $e);
		}
	}

	/**
	 * 依primary key,刪除指定資料
	 * @param 輸出名稱,目標輸出名稱,輸入DB的時間
	 * @return true
	 */
	static function delConvert($outputName, $targetOutputName, $insertTime) {
		try {
			$query = OC_DB::prepare("DELETE FROM *PREFIX*media_streaming_convert WHERE output_name = ? AND target_output_name = ? AND insert_time = ?");
			$result = $query -> execute(array(
				$outputName,
				$targetOutputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'delConvert', $e);
		}
	}

	/**
	 * 依primary key,刪除指定資料
	 * @param 輸出名稱,目標輸出名稱,輸入DB的時間
	 * @return true
	 */
	static function delConvertByUserIdAndSourcePath($localUserId, $localSourcePath) {
		try {
			$query = OC_DB::prepare("DELETE FROM *PREFIX*media_streaming_convert WHERE user_id = ? AND source_path = ?");
			$result = $query -> execute(array(
				$localUserId,
				$localSourcePath,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'delConvertByUserIdAndSourcePath', $e);
		}
	}

	/**
	 * 將今天之前的轉檔資料移到log(除了待轉檔和轉檔中的資料)
	 * @return true
	 */
	static function copyConvertsToLogBeforeToday() {
		try {
			# 取得今天的日期
			$localDateTime = OC_Helper::formatDateTimeUTCToLocal(null, 'Y-m-d');
			# 將今天的日期轉成utc時間格式
			$utcDateTime = OC_Helper::formatDateTimeLocalToUTC($localDateTime);
			$waitintConvert = OC_MediaConvert::waiting_convert;
			$converting = OC_MediaConvert::converting;
			$query = OC_DB::prepare("INSERT INTO *PREFIX*media_streaming_convert_log SELECT * FROM *PREFIX*media_streaming_convert WHERE insert_time < ? AND status != ? AND status != ?");
			$result = $query -> execute(array(
				$utcDateTime,
				$waitintConvert,
				$converting,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'copyConvertsToLogBeforeToday', $e);
		}
	}

	/**
	 * 移除今天之前的轉檔資料(除了待轉檔和轉檔中的資料)
	 * @return true
	 */
	static function delConvertsBeforeToday() {
		try {
			# 取得今天的日期
			$localDateTime = OC_Helper::formatDateTimeUTCToLocal(null, 'Y-m-d');
			# 將今天的日期轉成utc時間格式
			$utcDateTime = OC_Helper::formatDateTimeLocalToUTC($localDateTime);
			$waitintConvert = OC_MediaConvert::waiting_convert;
			$converting = OC_MediaConvert::converting;
			$query = OC_DB::prepare("DELETE FROM *PREFIX*media_streaming_convert WHERE insert_time < ? AND status != ? AND status != ?");
			$result = $query -> execute(array(
				$utcDateTime,
				$waitintConvert,
				$converting,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'delConvertsBeforeToday', $e);
		}
	}

}
?>