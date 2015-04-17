<?php
/**
 * ownCloud - Media Streaming plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 2.2版 Convert Streaming DB
 * 處理 streaming相關資料
 */
class OC_MediaStreaming_DB {

	/**
	 * 取得所有Streaming資料
	 * @return array
	 */
	static function getStreamingItems() {
		try {
			$query = OC_DB::prepare("SELECT a.*, b.* FROM *PREFIX*media_streaming AS a NATURAL JOIN *PREFIX*media_streaming_status AS b");
			return $result = $query -> execute() -> fetchAll();
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'getStreamingItems', $e);
		}
	}

	/**
	 * 取得Streaming資料
	 * @param 使用者ID,來源路徑,輸出格式
	 * @return array
	 */
	static function getStreaming($userId, $sourcePath, $deviceType) {
		try {
			$query = OC_DB::prepare("SELECT a.*, b.* FROM *PREFIX*media_streaming AS a NATURAL JOIN *PREFIX*media_streaming_status AS b WHERE a.user_id = ? AND a.source_path = ? AND a.device_type = ? LIMIT 1");
			$result = $query -> execute(array(
				$userId,
				$sourcePath,
				$deviceType,
			)) -> fetchAll();
			if (count($result))
				return $result[0];
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'getStreaming', $e);
		}
	}

	/**
	 * 取得Streaming資料(不分輸出格式)
	 * @param 使用者ID,來源路徑
	 * @return array
	 */
	static function getStreamingsByUserAndSourcePath($userId, $sourcePath) {
		try {
			$query = OC_DB::prepare("SELECT a.*, b.* FROM *PREFIX*media_streaming AS a NATURAL JOIN *PREFIX*media_streaming_status AS b WHERE a.user_id = ? AND a.source_path = ?");
			$result = $query -> execute(array(
				$userId,
				$sourcePath,
			)) -> fetchAll();
			if (count($result))
				return $result;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'getStreamingsByUserAndSourcePath', $e);
		}
	}

	/**
	 * 依output name,取得Streaming資料
	 * @param 輸出檔名稱
	 * @return array
	 */
	static function getStreamingByOutputName($outputName) {
		try {
			$query = OC_DB::prepare("SELECT a.*, b.* FROM *PREFIX*media_streaming AS a NATURAL JOIN *PREFIX*media_streaming_status AS b WHERE a.output_name = ? LIMIT 1");
			$result = $query -> execute(array($outputName, )) -> fetchAll();
			if (count($result))
				return $result[0];
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'getStreamingByOutputName', $e);
		}
	}

	/**
	 * 輸入streaming資料
	 * @param 使用者ID,來源路律,輸出格式,輸出檔名稱
	 * @return true
	 */
	static function insertStreaming($userId, $sourcePath, $deviceType, $outputName) {
		try {
			$insertTime = OC_Helper::formatDateTimeLocalToUTC();
			$query = OC_DB::prepare("REPLACE INTO *PREFIX*media_streaming (user_id, source_path, device_type, output_name, insert_time) VALUES (?,?,?,?,?)");
			$result = $query -> execute(array(
				$userId,
				$sourcePath,
				$deviceType,
				$outputName,
				$insertTime,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'insertStreaming', $e);
		}
	}

	/**
	 * 依output name,更新streaming資料
	 * @param 轉檔server ip,pid,開始轉檔時間,hls url,輸出檔名稱
	 * @return true
	 */
	static function updateStreamingByOutputName($serverIp, $pid, $startTime, $hlsUrl, $outputName) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming SET server_ip = ?, pid = ?, start_time = ?, hls_url = ? WHERE output_name = ?");
			$result = $query -> execute(array(
				$serverIp,
				$pid,
				$startTime,
				$hlsUrl,
				$outputName,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'updateStreamingByOutputName', $e);
		}
	}

	/**
	 * 依output name,更新影片長度
	 * @param 影片來源長度,輸出檔影片長度,輸出檔名稱
	 * @return true
	 */
	static function updateStreamingFootageByOutputName($sourceFootage, $outputFootage, $outputName) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming SET source_footage = ? , output_footage = ? WHERE output_name = ?");
			$result = $query -> execute(array(
				$sourceFootage,
				$outputFootage,
				$outputName
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'updateStreamingFootageByOutputName', $e);
		}
	}

	/**
	 * 依output name,更新streaming狀態
	 * @param 狀態int,輸出檔名稱
	 * @return true
	 */
	static function updateStreamingStatusByOutputName($status, $outputName) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming SET status = ? WHERE output_name = ?");
			$result = $query -> execute(array(
				$status,
				$outputName,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'updateStreamingStatusByOutputName', $e);
		}
	}

	/**
	 * rename streaming資料
	 * @param 新user id,新來源路徑,新output name,舊output name
	 * @return true
	 */
	static function renameStreamingByOutputName($newUserId, $newSourcePath, $newOutputName, $oldOutputName) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming SET user_id = ? , source_path = ? , output_name = ? WHERE output_name = ?");
			$result = $query -> execute(array(
				$newUserId,
				$newSourcePath,
				$newOutputName,
				$oldOutputName,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'renameStreamingByOutputName', $e);
		}
	}

	/**
	 * copy streaming資料
	 * @param 新user id,新來源路徑,新output name,舊output name
	 * @return true
	 */
	static function copyAndUpdateStreamingByOutputName($newUserId, $newSourcePath, $newOutputName, $oldOutputName) {
		try {
			$query = OC_DB::prepare("INSERT INTO *PREFIX*media_streaming (user_id, source_path, device_type, output_name, insert_time, server_ip, pid, start_time, hls_url, source_footage, output_footage, status) SELECT ?, ?, device_type, ?, insert_time, server_ip, pid, start_time, hls_url, source_footage, output_footage, status FROM *PREFIX*media_streaming WHERE output_name = ?");
			$result = $query -> execute(array(
				$newUserId,
				$newSourcePath,
				$newOutputName,
				$oldOutputName,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'copyAndUpdateStreamingByOutputName', $e);
		}
	}

	/**
	 * 將 streaming資料複製到log
	 * @param output name
	 * @return true
	 */
	static function copyStreamingToLogByOutputName($outputName) {
		try {
			$query = OC_DB::prepare("INSERT INTO *PREFIX*media_streaming_log SELECT * FROM *PREFIX*media_streaming WHERE output_name = ?");
			$result = $query -> execute(array($outputName, ));

			$logTime = OC_Helper::formatDateTimeLocalToUTC();
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_log SET log_time = ? WHERE output_name = ? AND log_time IS NULL");
			$result = $query -> execute(array(
				$logTime,
				$outputName,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'copyStreamingToLogByOutputName', $e);
		}
	}

	/**
	 * 依output name,刪除streaming 資料
	 * @param 輸出檔名稱
	 * @return true
	 */
	static function deleteStreamingByOutputName($outputName) {
		try {
			$query = OC_DB::prepare("DELETE FROM *PREFIX*media_streaming WHERE output_name = ?");
			$result = $query -> execute(array($outputName, ));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'deleteStreamingByOutputName', $e);
		}
	}

	/*==============Device Type==============*/

	/**
	 * 取得所有device type
	 * @param 是否依frame rate排序
	 * @return array
	 */
	static function getAllDeviceTypes($orderByFrameRate = true) {
		try {
			$queryStr = "SELECT * FROM *PREFIX*media_streaming_device_type";
			if ($orderByFrameRate) {
				$queryStr .= " ORDER BY frame_rate DESC";
			}
			$query = OC_DB::prepare($queryStr);
			return $result = $query -> execute() -> fetchAll();
			if (count($result))
				return $result;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'deleteStreamingByOutputName', $e);
		}
	}

	/**
	 * 取得指定device type的相關資料
	 * @param device type
	 * @return array
	 */
	static function getDeviceType($deviceType) {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_device_type WHERE device_type = ? LIMIT 1");
			$result = $query -> execute(array($deviceType)) -> fetchAll();
			if (count($result))
				return $result[0];
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'deleteStreamingByOutputName', $e);
		}
	}

	/*==============Streaming Status==============*/

	/**
	 * 取得所有streaming狀態
	 * @return array
	 */
	static function getStreamingStatus() {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_status");
			$result = $query -> execute(array()) -> fetchAll();
			if (count($result)) {
				$statusArr = array();
				foreach ($result as $statusItem) {
					$statusArr[$statusItem['status']] = $statusItem['status_title'];
				}
				return $statusArr;
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'getStreamingStatus', $e);
		}
	}

	/**
	 * 新增streaming狀態
	 * @param status, status title
	 * @return true
	 */
	static function insertStreamingStatus($status, $title) {
		try {
			$query = OC_DB::prepare("REPLACE INTO *PREFIX*media_streaming_status (status, status_title) VALUES (?,?)");
			$result = $query -> execute(array(
				$status,
				$title,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaStreaming_DB', 'insertStreamingStatus', $e);
		}
	}

}
?>