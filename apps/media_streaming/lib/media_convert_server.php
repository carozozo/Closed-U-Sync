<?php
/**
 * ownCloud - Media Convert plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 2.1版 Convert Server
 * 處理 convert server相關資料，以控制轉檔資源分配
 */

class OC_MediaConvertServer {

	/**
	 * 將轉檔server設為預設狀態
	 * @param server ip
	 * @return true
	 */
	static function setConvertServerToDefault($serverIp) {
		return self::updateConvertServer(null, null, OC_MediaConvert::waiting_convert, $serverIp);
	}

	/**
	 * 取得所有轉檔server列表
	 * @return array
	 */
	static function getConvertServerList() {
		$convertServerList = OC_MediaConvertServer_DB::getConvertServerItems();
		foreach ($convertServerList as $index => $convertServer) {
			# 轉換DB中的UTC轉檔時間轉為local時間
			$startTime = $convertServer['start_time'];
			if ($startTime) {
				$convertServerList[$index]['start_time'] = OC_Helper::formatDateTimeUTCToLocal($startTime);
			}
			# 將狀態(int)轉為文字敘述
			$status = $convertServer['status'];
			$convertServerList[$index]['status'] = OC_MediaConvert::streamingStatusArr($status);
		}
		return $convertServerList;
	}

	/**
	 * 預寫不用
	 * 依狀態取得轉檔server列表
	 * @param status
	 * @return array
	 */
	static function getConvertServerListByStatus($status) {
		return OC_MediaConvertServer_DB::getConvertServerItemsByStatus($status);
	}

	/**
	 * 寫入轉檔server資料
	 * @param status, server ip
	 * @return true
	 */
	static function newConvertServer($serverIp, $pid = null, $startTime = null, $status = OC_MediaConvert::waiting_convert) {
		return OC_MediaConvertServer_DB::insertConvertServer($serverIp, $pid, $startTime, $status);
	}

	/**
	 * 更新轉檔server狀態
	 * @param status, server ip
	 * @return true
	 */
	static function updateConvertServerStatus($status, $serverIp) {
		return OC_MediaConvertServer_DB::updateConvertServerStatus($status, $serverIp);
	}

	/**
	 * 更新轉檔server
	 * @param pid,start time, status, server ip
	 * @return true
	 */
	static function updateConvertServer($pid, $startTime, $status, $serverIp) {
		return OC_MediaConvertServer_DB::updateConvertServer($pid, $startTime, $status, $serverIp);
	}

	/**
	 * 刪除轉檔server
	 * @param server ip
	 * @return true
	 */
	static function delConvertServer($serverIp) {
		return OC_MediaConvertServer_DB::delConvertServer($serverIp);
	}

}
?>