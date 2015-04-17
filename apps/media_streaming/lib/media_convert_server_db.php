<?php
/**
 * ownCloud - Media Convert plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 2.1版 Convert Server DB
 * 存放 convert server相關資料，以控制轉檔資源分配
 */
class OC_MediaConvertServer_DB {
	/**
	 * 取得轉檔server列表
	 * @return array
	 */
	static function getConvertServerItems() {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_convert_server");
			$result = $query -> execute();
			return $result -> fetchAll();
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertServerItems', $e);
		}
	}

	/**
	 * 依轉檔狀態取得轉檔server列表
	 * @return array
	 */
	static function getConvertServerItemsByStatus($status) {
		try {
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*media_streaming_convert_server WHERE status = ?");
			$result = $query -> execute(array($status));
			return $result -> fetchAll();
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'getConvertServerItemsByStatus', $e);
		}
	}

	/**
	 * 寫入轉檔server資料
	 * @param server ip, pid, start time, status
	 * @return true
	 */
	static function insertConvertServer($serverIp, $pid, $startTime, $status) {
		try {
			$query = OC_DB::prepare("INSERT INTO *PREFIX*media_streaming_convert_server (server_ip, pid, start_time, status) VALUES (?, ?, ?, ?)");
			$result = $query -> execute(array(
				$serverIp,
				$pid,
				$startTime,
				$status,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'insertConvertServer', $e);
		}
	}

	/**
	 * 更新轉檔server狀態
	 * @param status, server ip
	 * @return true
	 */
	static function updateConvertServerStatus($status, $serverIp) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert_server SET status = ? WHERE server_ip = ?");
			$result = $query -> execute(array(
				$status,
				$serverIp,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertServerStatus', $e);
		}
	}

	/**
	 * 更新轉檔server
	 * @param pid, start time, status, server ip
	 * @return true
	 */
	static function updateConvertServer($pid, $startTime, $status, $serverIp) {
		try {
			$query = OC_DB::prepare("UPDATE *PREFIX*media_streaming_convert_server SET pid = ?, start_time = ?, status = ? WHERE server_ip = ?");
			$result = $query -> execute(array(
				$pid,
				$startTime,
				$status,
				$serverIp,
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'updateConvertServer', $e);
		}
	}

	/**
	 * 刪除轉檔server
	 * @param server ip
	 * @return true
	 */
	static function delConvertServer($serverIp) {
		try {
			$query = OC_DB::prepare("DELETE FROM *PREFIX*media_streaming_convert_server WHERE server_ip = ?");
			$result = $query -> execute(array($serverIp, ));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_MediaConvert_DB', 'delConvertServer', $e);
		}
	}

}
?>