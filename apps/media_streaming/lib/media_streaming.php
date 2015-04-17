<?php
/**
 * ownCloud - Media Streaming plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 將影片檔轉檔並做串流播放
 * 現行的Meida Convert有1.6版2.1兩個版本
 *
 * V2.2 : 需要Tomcat Server做轉檔, 及Nginx server做轉檔串流播放(邊轉邊播)
 * 預設的轉檔暫存路徑為 /vtmp/輸出檔名(.mp4)，存放在ffmpeg轉檔server,apache無存取權限
 * 預設的轉檔輸出路徑為 /var/www/html/data/video-on-demand/private/輸出檔名(.mp4)，只允許server讀取
 * ☆每隔五分鐘，系統會將轉完的檔案從暫存資料區搬到輸出區☆
 * 預設用來播放的連結路徑為 /var/www/html/data/video-on-demand/public/輸出檔名(.mp4)
 * 轉檔中：Tomcat Server回傳的播放網址
 * 轉完成：回傳link的URL給前端播放器播放
 *
 * V1.6 : 需要Tomcat Server做播放(只支援mp4)
 * 直接將檔案做link到Tomcat Server的temp路徑底下(預設為/var/www/html/data/MG/webapps/AudioGateTest/temp/md5(使用者ID)/輸出檔名(.mp4))
 * 回傳link的URL給前端播放器播放
 */

class OC_MediaStreaming extends OC_MediaStreaming_Settings {
	static $checkM3u8ExistsCount = 0;
	# 超過幾次確認後，則判定m3u8不存在
	static $checkM3u8ExistsMaxTimes = 30;
	# 存放從DB取得的所有狀態文字敘述
	static $streamingStatusArr = '';
	# refer to DB oc_mediastreaming_status
	const convert_deleted = 11;
	# 已轉檔完成，等待檔案從暫存區搬到輸出區
	const waiting_streaming_finish = 4;
	const waiting_convert = 3;
	const had_notice = 2;
	const convert_success = 1;
	const converting = 0;
	# 失敗狀態相關
	const source_not_exists = -1;
	const hls_file_not_exists = -2;
	const output_file_not_exists = -3;
	const convert_media_failed = -4;
	const source_not_media_type = -5;
	const create_streaming_link_failed = -6;
	const system_free_space_not_enough = -7;
	const user_free_space_not_enough = -8;
	const rename_output_to_target_output_failed = -9;
	const source_size_over_limit = -10;
	const convert_times_over_limit = -11;
	const same_target_file_exists = -12;
	const copy_output_to_convert_target_failed = -13;
	const output_not_exists = -14;
	# Tomcat Server失敗狀態相關
	const ask_convert_media_failed = -101;
	const ask_if_converting_failed = -102;
	const ask_check_m3u8_exists_failed = -103;
	const convert_server_busy = -104;
	const convert_server_error = -105;

	/**
	 * 確認是否為影片格式
	 * @param 檔案的內部路徑
	 * @return bool
	 */
	static function isMedia($path) {
		if (OC_Filesystem::file_exists($path)) {
			# 此array依據 media_streaming.js裡的 mediaTypeArray
			$mediaTypeArray = OC_Helper::mediaTypeArr();
			$mime = OC_Filesystem::getMimeType($path);
			if (in_array($mime, $mediaTypeArray)) {
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * 從DB抓取串流資料，向Tomcat確認後，更新串流狀態
	 * @return array
	 */
	static function updateStreamingStatusJob() {
		self::getStreamingListAndUpdateStatus();
	}

	/**
	 * 向Tomcat確認後，取得所有串流資料
	 * @return array
	 */
	static function getStreamingListAndUpdateStatus() {
		$streamingItems = OC_MediaStreaming_DB::getStreamingItems();
		foreach ($streamingItems as $index => $streamingItem) {
			$insertTime = $streamingItem['insert_time'];
			$startTime = $streamingItem['start_time'];
			$status = $streamingItem['status'];
			# 如果DB資料不是轉檔完成，則再向Tomcat確認是否轉檔完成
			if ($status != self::convert_success) {
				$userId = $streamingItem['user_id'];
				$sourcePath = $streamingItem['source_path'];
				$outputName = $streamingItem['output_name'];
				$serverIp = $streamingItem['server_ip'];
				$pid = $streamingItem['pid'];
				$deviceType = $streamingItem['device_type'];
				# 向Tomcat確認是否轉檔完成
				$status = self::curlCheckIfStreamingDone($serverIp, $pid, $startTime, $userId, $sourcePath, $outputName);
				if ($status < 0) {
					self::deleteStreamingFileAndMoveToLog($status, $outputName);
				}
				# 更新DB資料
				OC_MediaStreaming_DB::updateStreamingStatusByOutputName($status, $outputName);
			}
			# 將新的狀態寫入status中
			$streamingItems[$index]['status'] = self::streamingStatusArr($status);
			# 轉換為local time
			$streamingItems[$index]['insert_time'] = OC_Helper::formatDateTimeUTCToLocal($insertTime);
			$streamingItems[$index]['start_time'] = OC_Helper::formatDateTimeUTCToLocal($startTime);
		}
		return $streamingItems;
	}

	/**
	 * 依狀態，取得所有串流資料
	 * @param status
	 * @return array
	 */
	static function getStreamingListByStatus($status) {
		$status = self::streamingStatusArr($status);
		$streamingItems = self::getStreamingListAndUpdateStatus();
		foreach ($streamingItems as $index => $streamingItem) {
			if ($streamingItem['status'] != $status) {
				unset($streamingItems[$index]);
			}
		}
		# index重新排序
		$streamingItems = array_values($streamingItems);
		return $streamingItems;
	}

	/**
	 * 取得串流路徑
	 * @param dir, fileName, deviceType
	 * @return array(status,message)
	 */
	static function getStreamingSource($dir, $fileName, $deviceType) {
		$sourcePath = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
		# 確認檔案是否存在
		if (!OC_Filesystem::file_exists($sourcePath)) {
			return self::returnMessArr('error', self::source_not_exists);
		}
		# 確認檔案是否為影片格式
		if (!self::isMedia($sourcePath)) {
			return self::returnMessArr('error', self::source_not_media_type);
		}
		# 是否開啟HLS
		if (self::useHls()) {
			# 取得串流網址(回傳的可能是路徑也可能是錯誤訊息)
			return self::getStreamingSourceWithHls($dir, $fileName, $deviceType);
		}
		// $returnArr = self::getStreamingSource1_5($dir, $fileName);
		# 直接取得串流網址(1.6版)
		return self::getStreamingSource1_6($dir, $fileName);
	}

	/**
	 * 直接取得串流網址(1.5版)-已停用
	 * @param dir,fileName
	 * @return array(status,message)
	 */
	static function getStreamingSource1_5($dir, $fileName) {
		$userId = OC_User::getUser();
		# 密碼即為帳號
		$pwd = $userId;
		$dir = urlencode($dir);
		$fileName = urlencode($fileName);
		$dirUrl = OC_Helper::getProtocol() . $_SERVER['HTTP_HOST'] . '/dav/webdav.php' . $dir . '/';
		$toURL = OC_Helper::getProtocol() . $_SERVER['HTTP_HOST'] . ':8443/AudioGateTest/AudioServlet?user=' . $userId . '&password=' . $pwd . '&dir=' . $dirUrl . '&fileName=' . $fileName;
		$streamingUrl = self::curlToConvertServer($toURL);
		return self::returnMessArr('success', $streamingUrl);
	}

	/**
	 * 產生link到Tomcat server下,並回傳link的網址(1.6版)
	 * @param dir,fileName
	 * @return array(status,message)
	 */
	static function getStreamingSource1_6($dir, $fileName) {
		$path = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
		# 檔案原始路徑
		$sourcePath = OC_LocalSystem::getLocalPath($path);
		# 檔案完整路徑
		$sourceFullPath = OC_LocalSystem::getLocalFullPath($path);
		# 影片擁有者的Id
		$userId = OC_LocalSystem::getLocalUserIdByPath($path);

		$outputName = self::outputName($userId, $sourcePath);
		$linkFullPath = self::createLink1_6($sourceFullPath, $userId, $outputName);
		# 將完整路徑轉為網址
		if (is_string($linkFullPath)) {
			$streamingDocumentPath1_6 = self::streamingDocumentPath1_6();
			$linkUrl = preg_replace('#' . preg_quote($streamingDocumentPath1_6) . '#', $_SERVER['HTTP_HOST'] . '/', $linkFullPath);
			# 保險,去除開頭斜線及不符合path規則字元
			$linkUrl = OC_Helper::pathForbiddenChar(ltrim($linkUrl, '/'));
			# streaming server的protocol為http
			$linkUrl = "http://" . $linkUrl;
			return self::returnMessArr('success', $linkUrl);
		}
		return self::returnMessArr('error', $linkFullPath);
	}

	/**
	 * 並取得串流網址(2.1版)
	 * @param dir,fileName,deviceType
	 * @return array(status,message)
	 */
	static function getStreamingSourceWithHls($dir, $fileName, $deviceType) {
		$userId = OC_User::getUser();
		$sourcePath = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
		# 透過路徑取得真正的 user id 和 souce path
		$localUserId = OC_LocalSystem::getLocalUserIdByPath($sourcePath);
		$localSourcePath = OC_LocalSystem::getLocalPath($sourcePath);
		$outputName = self::outputName($localUserId, $localSourcePath);

		# 已存在串流資料
		if ($streamingItem = self::getStreamingItem($localUserId, $localSourcePath, $deviceType)) {
			return self::checkIfStreamingAndReturnMess($streamingItem);
		}
		# 沒有指定deviceType時，預設為phone
		if (!$deviceType) {
			$deviceType = 'phone';
		}
		return $convert = self::askStreamingAndReturnMess($userId, $sourcePath, $deviceType);
	}

	/**
	 * 確認m3u8是否存在，超過次數限制後則更新為轉檔預覽失敗
	 * @param m3u8路徑
	 * @return 存在:1, 不存在:-1, 失敗:null
	 */
	static function checkM3u8Exists($hlsUrl) {
		# hlsUrl格式為「http://to.u-sync.com/hls/xxxxxxxxxx_xx.m3u8」
		if ($hlsUrl) {
			$hlsUrlArr = preg_split('#\/#', $hlsUrl);
			$fileName = array_pop($hlsUrlArr);
			$m3u8Exists = self::curlCheckM3u8Exists($fileName);
			if ($m3u8Exists == '-1' && self::$checkM3u8ExistsCount < self::$checkM3u8ExistsMaxTimes) {
				sleep(1);
				self::$checkM3u8ExistsCount++;
				return self::checkM3u8Exists($hlsUrl);
			}
			# 如果m3u8不存在，已經確認超過限制次數
			if ($m3u8Exists == '-1' && self::$checkM3u8ExistsCount >= self::$checkM3u8ExistsMaxTimes) {
				$outputName = preg_replace('#' . preg_quote('.m3u8') . '#', '.mp4', $fileName);
				self::updateM3u8Failed($outputName);
			}
			return $m3u8Exists;
		}
		return null;
	}

	/**
	 *更新資料為「m3u8不存在」，並移到log
	 * @param output name
	 */
	private static function updateM3u8Failed($outputName) {
		if ($outputName) {
			# 要求請止轉檔
			$streamingItem = OC_MediaStreaming_DB::getStreamingByOutputName($outputName);
			$serverIp = $streamingItem['server_ip'];
			$pid = $streamingItem['pid'];
			$startTime = $streamingItem['start_time'];
			self::curlStopConverting($serverIp, $pid, $startTime);
			# 將資料移到log
			$status = self::hls_file_not_exists;
			self::deleteStreamingFileAndMoveToLog($status, $outputName);
		}
	}

	/**
	 * 檔案複製時的串流處理
	 * @param 原本的使用者ID, 原本的來源路徑, 目標使用者ID, 目標來源路徑, 輸出格式
	 * @return string
	 */
	static function copyStreaming($oldUserId, $oldSourcePath, $newUserId, $newSourcePath, $deviceType) {
		$oldOutputName = self::outputName($oldUserId, $oldSourcePath, $deviceType);
		$newOutputName = self::outputName($newUserId, $newSourcePath, $deviceType);
		# 如果來源和目的地不同
		if ($oldUserId != $newUserId || $oldSourcePath != $newSourcePath) {
			$ifCopyStreaming = false;
			# 先判斷source的Streaming資訊
			$oldStreamingItem = OC_MediaStreaming_DB::getStreamingByOutputName($oldOutputName);
			if ($oldStreamingItem) {
				# 如果目的地存在Streaming，則先刪除目的地的相關資料
				if (OC_MediaStreaming_DB::getStreamingByOutputName($newOutputName)) {
					self::deleteStreaming($newUserId, $newSourcePath, $deviceType);
				}
				$oldStatus = $oldStreamingItem['status'];
				if ($oldStatus == self::convert_success) {
					$ifCopyStreaming = true;
				}
				# 如果資料是「轉檔中」，則向Tomcat確認
				if ($oldStatus == self::converting) {
					$serverIp = $oldStreamingItem['server_ip'];
					$pid = $oldStreamingItem['pid'];
					$startTime = $oldStreamingItem['start_time'];
					$result = self::curlCheckIfStreamingDone($serverIp, $pid, $startTime, $oldUserId, $oldSourcePath, $oldOutputName);
					if ($result == self::ask_if_converting_failed || $result == self::convert_media_failed) {
						self::deleteStreamingFileAndMoveToLog($result, $oldOutputName);
					}
					if ($result == self::convert_success) {
						OC_MediaStreaming_DB::updateStreamingStatusByOutputName($result, $oldOutputName);
						$ifCopyStreaming = true;
					}
				}
				if ($oldStatus < 0) {
					self::deleteStreamingFileAndMoveToLog($oldStatus, $oldOutputName);
				}
			}
			# 可以複製Streaming資料
			if ($ifCopyStreaming) {
				OC_MediaStreaming_DB::copyAndUpdateStreamingByOutputName($newUserId, $newSourcePath, $newOutputName, $oldOutputName);
				if (self::copyOutputMedia($oldOutputName, $newOutputName)) {
					return self::createLink($newOutputName);
				}
			}
		}
		# 回傳現有的link full path
		return self::linkFullPath($oldOutputName);
	}

	/**
	 * 檔案更名/移動時的串流處理
	 * @param rename前的使用者ID,rename前的來源路徑,rename後的使用者ID,rename後的來源路徑,輸出格式
	 */
	static function renameStreaming($oldUserId, $oldSourcePath, $newUserId, $newSourcePath, $deviceType) {
		# 先取得新的影片名稱(不含副檔名)
		$pathInfo = pathinfo($newSourcePath);
		$newFileName = $pathInfo['filename'];
		if ($newFileName) {
			$oldOutputName = self::outputName($oldUserId, $oldSourcePath, $deviceType);
			$newOutputName = self::outputName($newUserId, $newSourcePath, $deviceType);
			# 測試發現rename不影響轉檔程式，所以不需要中止轉檔
			// $streamingItem = OC_MediaStreaming_DB::getStreamingByOutputName($oldOutputName);
			// $status = $streamingItem['status'];
			// if ($streamingItem && $status == self::converting) {
			// self::deleteStreaming($oldUserId, $oldSourcePath, $deviceType);
			// }

			# 如果目的地存在Streaming，則先刪除目的地的相關資料
			if (OC_MediaStreaming_DB::getStreamingByOutputName($newOutputName)) {
				self::deleteStreaming($newUserId, $newSourcePath, $deviceType);
			}

			# 測試發現rename不影響轉檔程式，所以直接執行rename相關檔案
			OC_MediaStreaming_DB::renameStreamingByOutputName($newUserId, $newSourcePath, $newOutputName, $oldOutputName);
			# Note: unlink symbolink first, then rename the output file
			self::deleteStreamingLink($oldOutputName);
			if (self::renameOutputMedia($oldOutputName, $newOutputName)) {
				self::createLink($newOutputName);
			}
		}
	}

	/**
	 * 刪除檔案/中斷串流的處理
	 * @param 使用者ID,來源路徑,要刪除的格式
	 */
	static function deleteStreaming($userId, $sourcePath, $deviceType) {
		$outputName = self::outputName($userId, $sourcePath, $deviceType);
		$streamingItem = OC_MediaStreaming_DB::getStreamingByOutputName($outputName);
		if ($streamingItem && $streamingItem['status'] == self::converting) {
			$serverIp = $streamingItem['server_ip'];
			$pid = $streamingItem['pid'];
			$startTime = $streamingItem['start_time'];
			self::curlStopConverting($serverIp, $pid, $startTime);
		}
		# 開始刪除相關檔案(不管轉檔server是否停止轉檔)
		$status = self::convert_deleted;
		self::deleteStreamingFileAndMoveToLog($status, $outputName);
	}

	/**
	 * 移除所有串流播放的link 1.6版(for crontab使用)
	 */
	static function removeStreamingLink1_6() {
		$streamingTempPath1_6 = self::streamingTempPath1_6();
		# 將該資料夾底下的index.jsp列為不要刪除的名單
		$excludePathArr = array($streamingTempPath1_6 . 'index.jsp');
		# 保險, 刪除Tomcat Server temp資料夾底下的檔案
		OC_Helper::deleteDirByFullPath($streamingTempPath1_6, $excludePathArr, false, true, false);
	}

	/**
	 * 移除所有串流播放的link(for crontab使用)
	 */
	static function removeStreamingLink() {
		$linkDirFullPath = self::linkDirFullPath();
		# 保險, 將該資料夾底下的資料夾「crossdomain.xml」列為不要刪除的名單
		$excludePathArr = array($linkDirFullPath . 'crossdomain.xml');
		OC_Helper::deleteDirByFullPath($linkDirFullPath, $excludePathArr, false, true, true);
	}

	/**
	 * 從DB中取得所有串流播放的狀態
	 * @param index
	 * @return string or array
	 */
	static function streamingStatusArr($index = null) {
		# 如果沒值的話，則從DB抓取資料
		if (!self::$streamingStatusArr) {
			self::$streamingStatusArr = OC_MediaStreaming_DB::getStreamingStatus();
		}
		if ($index !== null) {
			return self::$streamingStatusArr[$index];
		}
		return self::$streamingStatusArr;
	}

	/**
	 * 建立暫存資料夾，並回傳轉檔的暫存完整路徑
	 * @param 輸出的檔名
	 * @return string
	 */
	protected static function tmpFullPath($outputName) {
		$tmpDirFullPath = self::tmpDirFullPath();
		OC_Helper::createDirByFullPath($tmpDirFullPath);
		$tmpFullPath = OC_Helper::pathForbiddenChar($tmpDirFullPath . '/' . $outputName);
		return $tmpFullPath;
	}

	/**
	 * 建立輸出資料夾，並回傳轉檔的輸出完整路徑
	 * @param 使用者ID,輸出的檔名
	 * @return string
	 */
	protected static function outputFullPath($outputName) {
		$outputDirFullPath = self::outputDirFullPath() . '/';
		OC_Helper::createDirByFullPath($outputDirFullPath);
		$outputFullPath = $outputDirFullPath . $outputName;
		$outputFullPath = OC_Helper::pathForbiddenChar($outputFullPath);
		return $outputFullPath;
	}

	/**
	 * 確認檔案是否存在及剩餘空間是否足夠
	 * 此處的userId不見得是登入者，所以轉為檔案完整路徑做判斷
	 * @param 使用者ID, 來源路徑, 要確認空間的使用者ID
	 * @return source_not_exists, system_free_space_not_enough, user_free_space_not_enough, true
	 */
	protected static function checkFreeSpace($userId, $sourcePath, $ifCheckUserIdSpace = null) {
		$sourceFullPath = OC_LocalSystem::getFullPathByUserId($userId, $sourcePath);
		if (file_exists($sourceFullPath)) {
			$fileSize = filesize($sourceFullPath);
			$dataDir = OC::$CONFIG_DATADIRECTORY_ROOT;
			$systemFreeSpace = disk_free_space($dataDir);
			if ($systemFreeSpace < $fileSize * 1.2) {
				# email給管理者
				$result = self::system_free_space_not_enough;
				self::sendMailToAdmin($result, $userId, $sourcePath);
				return $result;
			}
			if ($ifCheckUserIdSpace && is_string($ifCheckUserIdSpace)) {
				# 將使用者加入session
				OC_User::setUserId($ifCheckUserIdSpace);
				# 建立使用者的 Filesystem
				OC_Util::setupFS($ifCheckUserIdSpace);
				# 檢查登入者的剩於空間
				$freeSpace = OC_Filesystem::free_space();
				if ($freeSpace < $fileSize * 1.2) {
					return self::user_free_space_not_enough;
				}
			}
			return true;
		}
		return self::source_not_exists;
	}

	/**
	 * 回傳輸出檔的連結完整路徑
	 * @param 使用者ID,輸出的檔名
	 * @return string
	 */
	protected static function linkFullPath($outputName) {
		$linkDirFullPath = self::linkDirFullPath();
		$linkFullPath = OC_Helper::pathForbiddenChar($linkDirFullPath . '/' . $outputName);
		return $linkFullPath;
	}

	/**
	 * 回傳轉檔的輸出檔名
	 * @param 使用者ID,來源路徑,輸出格式
	 * @return string
	 */
	private static function outputName($userId, $sourcePath, $deviceType = '') {
		if (self::useHls()) {
			# 新版streaming 的output name做為轉檔後輸出檔的檔名及後續link的檔名
			return $outputName = md5($userId . $sourcePath) . "_$deviceType.mp4";
		}
		# 舊版的streaming的output name即為link的名稱
		return $outputName = md5($userId . $sourcePath) . ".mp4";
	}

	/**
	 * 比對原始檔和輸出檔的影片時間
	 * @param 來源檔秒數,輸出檔秒數
	 * @return bool
	 */
	protected static function compareFootage($second1, $second2) {
		$result = abs($second1 - $second2);
		if ($result < self::compareSeconds())
			return true;
		return false;
	}

	/**
	 * 刪除streaming相關檔案，並更新Streaming的狀態，然後搬到oc_media_streaming_log
	 * @param 狀態,輸出檔名
	 */
	protected static function deleteStreamingFileAndMoveToLog($status, $outputName) {
		# 先移除連結，再刪除輸出檔
		self::deleteStreamingLink($outputName);
		self::deleteOutputMedia($outputName);
		self::moveStreamingToLog($status, $outputName);
	}

	/**
	 * 更新Streaming的狀態，並搬到oc_media_streaming_log
	 * @param 狀態,輸出檔名
	 */
	protected static function moveStreamingToLog($status, $outputName) {
		# 為異常狀態的話，將訊息寫到owncloud log中
		if ($status < 0) {
			OC_Log::write('OC_MediaStreaming', self::streamingStatusArr($status) . ':' . $outputName, OC_Log::WARN);
		}
		if (OC_MediaStreaming_DB::updateStreamingStatusByOutputName($status, $outputName)) {
			OC_MediaStreaming_DB::copyStreamingToLogByOutputName($outputName);
			OC_MediaStreaming_DB::deleteStreamingByOutputName($outputName);
		}
	}

	/**
	 * 錯誤訊息寫到log，並回傳錯誤訊息
	 * @param 狀態,輸出檔名
	 * @return array('error',error message)
	 */
	private static function setError($status, $outputName) {
		self::deleteStreamingFileAndMoveToLog($status, $outputName);
		# 如果是錯誤狀態，則回傳轉檔處理中給前端
		if ($status < 0) {
			return self::returnMessArr('error', self::waiting_streaming_finish);
		}
		return self::returnMessArr('error', $status);
	}

	/**
	 * 設置streaming 回傳訊息
	 * @param 狀態(成功/失敗), 串流狀態(int)
	 * @return array('success' or 'error',message)
	 */
	protected static function returnMessArr($status = 'success', $returnStatus = null) {
		$message = '';
		if ($returnStatus !== null) {
			if (is_int($returnStatus)) {
				$message = self::streamingStatusArr($returnStatus);
			}
			if (is_string($returnStatus)) {
				$message = $returnStatus;
			}
		}
		/* TODO 暫時先改回英文版
		 $l = new OC_L10N(self::appId);
		 $message = $l -> t($message);
		 # 如果回傳的狀態為「檔案超過限制」
		 if ($returnStatus == self::source_size_over_limit) {
		 $limitSize = self::limitSize();
		 $limitSize = OC_Helper::humanFileSize($limitSize);
		 $message .= ':' . $limitSize . '<br/>';
		 $message .= $l -> t('You can play it by MediaWizard') . ', ';
		 $message .= $l -> t('or upgrade to paied user');
		 }
		 */
		return array(
			'status' => $status,
			'message' => $message,
		);
	}

	/**
	 * 要求Tomcat server轉檔
	 * @param user id, source path, device type, output full path
	 * @return convert_server_busy or ask_convert_media_failed or array(serverIp,pid,startTime,hlsUrl)
	 */
	private static function curlAskStreaming($userId, $sourcePath, $deviceType, $outputFullPath) {
		$deviceTypeItem = OC_MediaStreaming_DB::getDeviceType($deviceType);
		$frameRate = $deviceTypeItem['frame_rate'];
		$frameSize = $deviceTypeItem['frame_size'];
		$videoCodec = $deviceTypeItem['video_codec'];
		$bitRate = $deviceTypeItem['bit_rate'];
		$sourceFullPath = OC_LocalSystem::getFullPathByUserId($userId, $sourcePath);
		$sourceFullPath = urlencode($sourceFullPath);
		// $outputFullPath = urlencode($outputFullPath);
		$toURL = "http://" . self::TomcatServer() . ":8080/LiveStreamTransCode/LoadBalance";
		$toURL .= "?userId=$userId&sourcePath=$sourceFullPath&outputPath=$outputFullPath";
		$toURL .= "&frameRate=$frameRate&frameSize=$frameSize&videoCodec=$videoCodec&bitRate=$bitRate";
		$result = self::curlToConvertServer($toURL);
		$result = self::coverAskStreamingResult($result);
		if ($result == self::ask_convert_media_failed) {
			# email給管理者
			self::sendMailToAdmin($result, $userId, $sourcePath);
		}
		return $result;
	}

	/**
	 * 將Tomcat回傳的Result轉為陣列參數
	 * 轉檔server忙碌，回傳值：server busy
	 * 開始轉檔，回傳值類似：ServerIP:192.168.11.27;pid:32319;startTime:2013-07-12T02:50:24Z;hlsUrl:http://192.168.11.68/hls/a82676926c806a2e7722b2675aad5501_tv;
	 * @param Tomcat回傳的string訊息
	 * @return convert_server_busy or ask_convert_media_failed or array(serverIp,pid,startTime,hlsUrl)
	 */
	private static function coverAskStreamingResult($result) {
		if ($result) {
			# 如果回傳的有包含「busy」
			if (preg_match('/busy/i', $result)) {
				return self::convert_server_busy;
			}
			$resultArr = explode(';', $result);
			$serverIp = '';
			$pid = '';
			$startTime = '';
			$hlsUrl = '';
			foreach ($resultArr as $val) {
				if (stripos($val, 'ServerIP') === 0) {
					$serverIp = preg_replace('#' . preg_quote('serverip:') . '#', '', strtolower($val));
				} else if (stripos($val, 'pid') === 0) {
					$pid = preg_replace('#' . preg_quote('pid:') . '#', '', strtolower($val));
				} else if (stripos($val, 'startTime') === 0) {
					$startTime = preg_replace('#' . preg_quote('starttime:') . '#', '', strtolower($val));
					$startTime = self::coverTimeFromTomcatFormat($startTime);
				} else if (stripos($val, 'hlsUrl') === 0) {
					$hlsUrl = preg_replace('#' . preg_quote('hlsurl:') . '#', '', strtolower($val));
				}
			}
			// return false;
			if ($serverIp && $pid && $startTime && $hlsUrl) {
				return array(
					'serverIp' => $serverIp,
					'pid' => $pid,
					'startTime' => $startTime,
					'hlsUrl' => $hlsUrl,
				);
			}
		}
		return self::ask_convert_media_failed;
	}

	/**
	 * 向Tomcat server確認檔案是否轉檔結束
	 * Tomcat會依照server ip, pid, start time判斷轉檔流程是否還存在
	 * 當流程不存在時(代表已經沒有在轉檔)，則判斷指定的source path和target path的影片時間
	 * @param server ip, pid start time, user id, source path, output name
	 * @return source_not_exists/ask_if_converting_failed/convert_media_failed/converting/waiting_streaming_finish/convert_success
	 */
	protected static function curlCheckIfStreamingDone($serverIp, $pid, $startTime, $userId, $sourcePath, $outputName) {
		$sourceFullPath = OC_LocalSystem::getFullPathByUserId($userId, $sourcePath);
		$outputFullPath = self::outputFullPath($outputName);
		$tmpFullPath = self::tmpFullPath($outputName);
		$startTimeForTomcat = self::coverTimeToTomcatFormat($startTime);
		$sourceFullPath = urlencode($sourceFullPath);
		$toURL = "http://" . self::TomcatServer() . ":8080/LiveStreamTransCode/QueryPidMain";
		$toURL .= "?serverIp=$serverIp&pid=$pid&startTime=$startTimeForTomcat&sourcePath=$sourceFullPath&outputPath=";
		# 向Tomcat確認轉檔是否結束
		$toUrlForOutput = $toURL . $outputFullPath;
		$result = self::curlToConvertServer($toUrlForOutput);
		$result = self::coverCheckIfStreamingDoneResult($result, $outputName, $startTime);
		# 如果輸出檔不存在，則再向Tomcat確認暫存檔是否存在
		if ($result == self::output_not_exists) {
			$toUrlForTmp = $toURL . $tmpFullPath;
			$result = self::curlToConvertServer($toUrlForTmp);
			$result = self::coverCheckIfStreamingDoneResult($result, $outputName, $startTime);
			if ($result == self::convert_success) {
				# 已經轉檔完成，但檔案還放在暫存區
				return self::waiting_streaming_finish;
			}
		}

		if ($result == self::ask_if_converting_failed || $result == self::convert_media_failed) {
			# email給管理者
			self::sendMailToAdmin($result, $userId, $sourcePath, $serverIp, $pid, $startTime);
		}
		return $result;
	}

	/**
	 * 將Tomcat回傳的Result轉為陣列參數
	 * 轉檔中，回傳的值類似：sourceFootage:0;outputFootage:0
	 * 指定的source或output不存在，回傳的值類似：sourceFootage:-1;outputFootage:-1
	 * 轉檔成功，回傳的值類似：sourceFootage:01:12:34;outputFootage:01:12:56
	 * @param Tomcat回傳的string訊息,輸出檔名,開始時間
	 * @return source_not_exists/output_not_exists/ask_if_converting_failed/convert_media_failed/converting/convert_success
	 */
	private static function coverCheckIfStreamingDoneResult($result, $outputName, $startTime) {
		if ($result) {
			$resultArr = explode(';', $result);
			$sourceFootage = null;
			$outputFootage = null;
			foreach ($resultArr as $val) {
				if (stripos($val, 'sourceFootage') === 0) {
					$sourceFootage = preg_replace('#' . preg_quote('sourcefootage:') . '#', '', strtolower($val));
					if ($sourceFootage != '-1') {
						$sourceFootage = OC_Helper::formatTimeToSeconds($sourceFootage);
					}
				} else if (stripos($val, 'outputFootage') === 0) {
					$outputFootage = preg_replace('#' . preg_quote('outputfootage:') . '#', '', strtolower($val));
					if ($outputFootage != '-1') {
						$outputFootage = OC_Helper::formatTimeToSeconds($outputFootage);
					}
				}
			}
			# 拆解完之後都有值，代表回傳的格式正確
			if ($sourceFootage !== null && $outputFootage !== null) {
				$sourceFootage = (int)$sourceFootage;
				$outputFootage = (int)$outputFootage;
				if ($sourceFootage < 0) {
					# 指定的來源檔不存在
					return self::source_not_exists;
				}
				if ($outputFootage < 0) {
					# 指定的輸出檔不存在(這邊也有可能是指暫存檔)
					return self::output_not_exists;
				}
				if ($sourceFootage === 0 && $outputFootage === 0) {
					# 取出的來源和輸出檔影片時間都為0
					return self::converting;
				}
				if ($sourceFootage && $outputFootage) {
					OC_MediaStreaming_DB::updateStreamingFootageByOutputName($sourceFootage, $outputFootage, $outputName);
					if (self::compareFootage($sourceFootage, $outputFootage)) {
						return self::convert_success;
					}
					# 檔案有轉出來，但是影片時間差異太大
					return self::convert_media_failed;
				}
			}
			# Tomcat回傳的格式不正確
		}
		# Tomcat沒回應
		return self::ask_if_converting_failed;
	}

	/**
	 * 刪除輸出檔
	 * @param 輸出檔名
	 */
	protected static function deleteOutputMedia($outputName) {
		$outputFullPath = self::outputFullPath($outputName);
		@unlink($outputFullPath);
	}

	/**
	 * 刪除輸出檔的連結
	 * @param 輸出檔名
	 */
	protected static function deleteStreamingLink($outputName) {
		$linkFullPath = self::linkFullPath($outputName);
		@unlink($linkFullPath);
	}

	/**
	 * 產生檔案的連結到Tomcat Server的temp資料夾底下
	 * @param 使用者ID,輸出檔名
	 * @return link full path, or create_streaming_link_failed, or output_file_not_exists
	 */
	protected static function createLink1_6($sourceFullPath, $userId, $outputName) {
		if (file_exists($sourceFullPath)) {
			$streamingTempPath1_6 = self::streamingTempPath1_6();
			$linkFullPath = $streamingTempPath1_6 . '/' . md5($userId) . '/' . $outputName;
			$linkFullPath = OC_Helper::pathForbiddenChar($linkFullPath);
			if (file_exists($linkFullPath)) {
				return $linkFullPath;
			}
			$linkDirFullPath = dirname($linkFullPath);
			if (OC_Helper::createDirByFullPath($linkDirFullPath) && symlink($sourceFullPath, $linkFullPath)) {
				return $linkFullPath;
			}
			# email給管理者
			$status = self::create_streaming_link_failed;
			self::sendMailToAdmin($status);
			return $status;
		}
		return self::source_not_exists;
	}

	/**
	 * 產生輸出檔的連結
	 * @param 輸出檔名
	 * @return link full path, or create_streaming_link_failed, or output_file_not_exists
	 */
	protected static function createLink($outputName) {
		$outputFullPath = self::outputFullPath($outputName);
		if (file_exists($outputFullPath)) {
			$linkFullPath = self::linkFullPath($outputName);
			$linkDirFullPath = dirname($linkFullPath);
			if (file_exists($linkFullPath)) {
				return $linkFullPath;
			}
			if (OC_Helper::createDirByFullPath($linkDirFullPath) && symlink($outputFullPath, $linkFullPath)) {
				return $linkFullPath;
			}
			# email給管理者
			$status = self::create_streaming_link_failed;
			self::sendMailToAdmin($status);
			return $status;
		}
		return self::output_file_not_exists;
	}

	protected static function sendMailToAdmin($status, $userId = null, $sourcePath = null, $serverIp = null, $pid = null, $startTime = null) {
		$adminEmail = OC_Config::getValue('adminEmail', '');
		$adminName = 'Media Streaming System';
		$email = self::supporterEmail();
		# 將狀態序號轉為訊息
		$status = self::streamingStatusArr($status);
		$emailSubject = $adminName . ' - ' . $status;
		$hostName = $_SERVER["HTTP_HOST"];
		$emailBody = "hostName=$hostName\n\n";
		$emailBody .= "status=$status\n\n";
		if ($userId)
			$emailBody .= "userId=$userId\n\n";
		if ($sourcePath)
			$emailBody .= "sourcePath=$sourcePath\n\n";
		if ($serverIp)
			$emailBody .= "serverIp=$serverIp\n\n";
		if ($pid)
			$emailBody .= "pid=$pid\n\n";
		if ($startTime)
			$emailBody .= "startTime=$startTime\n\n";
		OC_Util::sendJmail($adminEmail, $adminName, $email, $emailSubject, $emailBody);
	}

	/**
	 * 判斷用戶能否播放影片
	 * @param userId, sourcePath
	 * @return true or self::source_size_over_limit
	 */
	private static function ifUserCanStreaming($userId, $sourcePath) {
		$paidSystemEnable = OC_Helper::paidSystemEnable();
		# 有開啟付費機制
		if ($paidSystemEnable) {
			$isPaidUser = OC_User::isPaidUser($userId);
			# 使用者未付款
			if (!$isPaidUser) {
				$localSourceFullPath = OC_LocalSystem::getLocalFullPath($sourcePath);
				# 檔案超過播放限制大小
				if (!self::canStreamingByFileLimitSize($localSourceFullPath)) {
					return self::source_size_over_limit;
				}
			}

		}
		return true;
	}

	/**
	 * 是否可以串流播放的檔案大小限制
	 * @param 檔案路徑
	 * @return bool
	 */
	private static function canStreamingByFileLimitSize($fileFullPath) {
		if ($limitSize = self::limitSize()) {
			$fileSize = filesize($fileFullPath);
			if ($fileSize > $limitSize) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 從DB中取得streaming資料
	 * @param 使用者名稱,檔案路徑,輸出格式
	 * @return streaming array or null
	 */
	protected static function getStreamingItem($userId, $sourcePath, $deviceType) {
		# 如果有指定要播放的device type
		if ($deviceType) {
			$outputName = self::outputName($userId, $sourcePath, $deviceType);
			if ($streamingItem = OC_MediaStreaming_DB::getStreamingByOutputName($outputName)) {
				return $streamingItem;
			}
			return null;
		}
		# 沒有指定device type,則試著找出存在的streaming資料
		if ($streamingItems = OC_MediaStreaming_DB::getStreamingsByUserAndSourcePath($userId, $sourcePath)) {
			$deviceTypeItems = OC_MediaStreaming_DB::getAllDeviceTypes();
			foreach ($deviceTypeItems as $key => $deviceTypeItem) {
				foreach ($streamingItems as $key2 => $streamingItem) {
					$deviceType = $streamingItem['device_type'];
					if ($deviceType == $deviceTypeItem['device_type']) {
						return $streamingItem;
					}
				}
			}
		}
	}

	/**
	 * 確認是否仍在轉檔，並回傳訊息
	 * 只有成功產生link才回傳成功狀態
	 * @param 從DB抓取的streaming資料
	 * @return array(status,message)
	 */
	private static function checkIfStreamingAndReturnMess($streamingItem) {
		$userId = $streamingItem['user_id'];
		$outputName = $streamingItem['output_name'];
		$sourcePath = $streamingItem['source_path'];
		$status = $streamingItem['status'];
		# DB資料為「轉檔伺服器忙碌」
		if ($status == self::convert_server_busy) {
			# 再次要求轉
			$deviceType = $streamingItem['device_type'];
			return self::askStreamingAndReturnMess($userId, $sourcePath, $deviceType);
		}
		# DB資料為「轉檔成功」
		if ($status == self::convert_success) {
			$result = self::createLink($outputName);
			if ($result == self::create_streaming_link_failed || $result == self::output_file_not_exists) {
				return self::setError($result, $outputName);
			}
			# 將link dir路徑轉為 Nginx Server URL
			$result = str_replace(rtrim(self::linkDirFullPath(), '/'), OC_Helper::getProtocol() . self::NginxServer(), $result);
			return self::returnMessArr('success', $result);
		}
		# DB資料為「轉檔中」或「等待轉檔完成」
		if ($status == self::converting || $status == self::waiting_streaming_finish) {
			$serverIp = $streamingItem['server_ip'];
			$pid = $streamingItem['pid'];
			$startTime = $streamingItem['start_time'];
			$result = self::curlCheckIfStreamingDone($serverIp, $pid, $startTime, $userId, $sourcePath, $outputName);
			# 轉檔已經結束，但還在暫存資料夾中
			if ($result == self::waiting_streaming_finish) {
				OC_MediaStreaming_DB::updateStreamingStatusByOutputName($result, $outputName);
				return self::returnMessArr('error', $result);
			}
			if ($result == self::convert_success) {
				# 開始產生連結
				$linkResult = self::createLink($outputName);
				if ($linkResult == self::create_streaming_link_failed || $linkResult == self::output_file_not_exists) {
					return self::setError($linkResult, $outputName);
				}

				OC_MediaStreaming_DB::updateStreamingStatusByOutputName($result, $outputName);
				$linkResult = str_replace($_SERVER['DOCUMENT_ROOT'] . '/data/video-on-demand/public', OC_Helper::getProtocol() . self::NginxServer(), $linkResult);
				return self::returnMessArr('success', $linkResult);
			}
			if ($result == self::converting) {
				return self::returnMessArr('success', $streamingItem['hls_url']);
			}
			if ($result == self::ask_if_converting_failed || $result == self::convert_media_failed) {
				# 要求停止轉檔
				self::curlStopConverting($serverIp, $pid, $startTime);
				return self::setError($result, $outputName);
			}
		}
		if ($status < 0) {
			return self::setError($status, $outputName);
		}
	}

	/**
	 * 寫入資料到DB，系統空間足夠則向Tomcat要求轉檔，及之後的相關處理
	 * @param 使用者ID,真實來源路徑,輸出格式,輸出名稱
	 * @return array(status,message)
	 */
	private static function askStreamingAndReturnMess($userId, $sourcePath, $deviceType) {
		# 透過路徑取得真正的 user id 和 souce path
		$localUserId = OC_LocalSystem::getLocalUserIdByPath($sourcePath);
		$localSourcePath = OC_LocalSystem::getLocalPath($sourcePath);
		$outputName = self::outputName($localUserId, $localSourcePath, $deviceType);
		# 如果觀看者及影片擁有者都沒有播放權限
		$ifUserCanStreaming1 = self::ifUserCanStreaming($userId, $sourcePath);
		$ifUserCanStreaming2 = self::ifUserCanStreaming($localUserId, $sourcePath);
		if ($ifUserCanStreaming1 !== true && $ifUserCanStreaming2 !== true) {
			return self::returnMessArr('error', $ifUserCanStreaming1);
		}
		# 將資料寫入DB
		@OC_MediaStreaming_DB::insertStreaming($localUserId, $localSourcePath, $deviceType, $outputName);
		# 確認系統空間是否足夠
		$result = self::checkFreeSpace($localUserId, $localSourcePath);
		if ($result === self::system_free_space_not_enough) {
			return self::setError($result, $outputName);
		}
		# 指定輸出路徑到暫存資料夾底下
		// $outputFullPath = self::outputFullPath($localUserId, $outputName);
		$tmpFullPath = self::tmpFullPath($outputName);
		# 防呆，在要求轉檔之前先清除相同檔名的輸出檔()
		# apache無法讀取暫存檔路徑，所以無法砍檔
		// @unlink($tmpFullPath);
		# 向Tomcat Server要求轉檔
		$result = self::curlAskStreaming($localUserId, $localSourcePath, $deviceType, $tmpFullPath);
		if ($result == self::convert_server_busy) {
			# 將狀態改為轉檔伺服器忙碌
			OC_MediaStreaming_DB::updateStreamingStatusByOutputName($result, $outputName);
			return self::returnMessArr('error', $result);
		}
		if ($result == self::ask_convert_media_failed) {
			return self::setError($result, $outputName);
		}

		if (is_array($result)) {
			$serverIp = $result['serverIp'];
			$pid = $result['pid'];
			$startTime = $result['startTime'];
			$hlsUrl = $result['hlsUrl'];
			OC_MediaStreaming_DB::updateStreamingByOutputName($serverIp, $pid, $startTime, $hlsUrl, $outputName);
			return self::returnMessArr('success', $hlsUrl);
		}
	}

	/**
	 * copy輸出檔
	 * @param copy前的檔名,copy後的檔名
	 */
	protected static function copyOutputMedia($oldOutputName, $newOutputName) {
		$oldOutputFullPath = self::outputFullPath($oldOutputName);
		$newOutputFullPath = self::outputFullPath($newOutputName);
		if (@copy($oldOutputFullPath, $newOutputFullPath)) {
			return true;
		}
		return false;
	}

	/**
	 * rename輸出檔
	 * @param rename前的檔名,rename後的檔名
	 */
	protected static function renameOutputMedia($oldOutputName, $newOutputName) {
		$oldOutputFullPath = self::outputFullPath($oldOutputName);
		$newOutputFullPath = self::outputFullPath($newOutputName);
		if ($oldOutputFullPath == $newOutputFullPath) {
			return true;
		}
		if (file_exists($oldOutputFullPath)) {
			if (@rename($oldOutputFullPath, $newOutputFullPath)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 將Tomcat回傳的時間格式轉為date time格式
	 * @param Tomcat回傳的時間String
	 * @return string
	 */
	protected static function coverTimeFromTomcatFormat($dateTimeStr) {
		$dateTimeStr = preg_replace('#[a-zA-Z]#', ' ', $dateTimeStr);
		$dateTimeStr = trim($dateTimeStr);
		return $dateTimeStr;
	}

	/**
	 * 將Tomcat回傳的時間格式轉為date time格式
	 * @param DB中的date time string
	 * @return string
	 */
	protected static function coverTimeToTomcatFormat($dateTimeStr) {
		$dateTimeStr = preg_replace('# #', 't', $dateTimeStr);
		$dateTimeStr .= 'z';
		return $dateTimeStr;
	}

	/**
	 * 向Nginx server確認m3u8檔案是否存在
	 * @param string
	 * @return m3u8 exists:1, m3u8 not exists:-1, no respond:ask_check_m3u8_exists_failed,no filename:false
	 */
	private static function curlCheckM3u8Exists($fileName) {
		if ($fileName) {
			$toURL = "http://" . self::NginxServer() . "/sapi/has_m3u8.php?filename=$fileName";
			$result = self::curlToConvertServer($toURL);
			if (!$result) {
				# Nginx server no respond or ask failed, update to db but not move to log
				# 這邊的filename是[output name].m3u8
				$outputName = str_replace('.m3u8', '.mp4', $fileName);
				# email給管理者
				$result = self::ask_check_m3u8_exists_failed;
				self::sendMailToAdmin($result);
				self::deleteStreamingFileAndMoveToLog($result, $outputName);
			}
			return $result;
		}
		return false;
	}

	/**
	 * 向Tomcat server要求停止轉檔
	 * @param 使用者ID,轉檔的server IP,轉檔程序ID,開始時間
	 * @return 1 or 0
	 */
	private static function curlStopConverting($serverIp, $pid, $startTime) {
	    $startTimeForTomcat = self::coverTimeToTomcatFormat($startTime);
		$toURL = "http://" . self::TomcatServer() . ":8080/LiveStreamTransCode/KillProcessMain?serverIp=$serverIp&pid=$pid&startTime=$startTimeForTomcat";
		$result = self::curlToConvertServer($toURL);
	}

	/**
	 * 指向轉檔Server
	 * @param toUrl(轉檔server的API網址)
	 * @return string
	 */
	protected static function curlToConvertServer($toURL) {
		$ch = curl_init();
		# CURLOPT_RETURNTRANSFER:網頁回應,CURLOPT_POST:使用POST,CURLOPT_SSL_VERIFYPEER:SSL驗證
		$options = array(
			CURLOPT_URL => $toURL,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => false,
			CURLOPT_SSL_VERIFYPEER => false,
		);
		curl_setopt_array($ch, $options);
		return $result = curl_exec($ch);
	}

}
?>