<?php
/**
 * ownCloud - Media Streaming plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * Media Streaming相關設定
 * 所有config取值的function名稱即為config key值
 * 所有儲存config值的function即為 set+(開頭大寫)configKey值
 */

class OC_MediaStreaming_Settings {
	const appId = 'media_streaming';
	/* ================== Streaming Config 相關 ================== */

	/**
	 * 取得media streaing 的所有config值
	 * @return array
	 */
	static function getConfigItems() {
		return $items = OC_Appconfig::getConfigItemsByAppId(self::appId);
	}

	/**
	 * 取得config檔中，技術支援者的Email
	 * @return string
	 */
	static function supporterEmail() {
		return OC_Appconfig::getValue(self::appId, 'supporterEmail', 'supportEmail@u-sync.com');
	}

	/**
	 * 設定技術支援者的Email
	 * @param string
	 */
	static function setSupporterEmail($supporterEmail) {
		if ($supporterEmail && stripos($supporterEmail, '@')) {
			OC_Appconfig::setValue(self::appId, 'supporterEmail', $supporterEmail);
		}
	}

	/**
	 * 取得config檔中，是否開啟hls播放功能
	 * @return bool
	 */
	static function useHls() {
		if (OC_Appconfig::getValue(self::appId, 'useHls') == 1) {
			return true;
		}
		return false;
	}

	/**
	 * 設定是否開啟hls播放功能
	 * @param bool or 1 or 0
	 */
	static function setUseHls($useHls) {
		if ($useHls == 1 || $useHls == 0 || gettype($useHls) == 'boolean') {
			OC_Appconfig::setValue(self::appId, 'useHls', $useHls);
		}
	}

	/**
	 * 取得config檔中，Tomcat Server的位址
	 * @return string
	 */
	static function tomcatServer() {
		return OC_Appconfig::getValue(self::appId, 'tomcatServer');
	}

	/**
	 * 設定Tomcat Server的位址
	 * @param string
	 */
	static function setTomcatServer($tomcatServer) {
		OC_Appconfig::setValue(self::appId, 'tomcatServer', $tomcatServer);
	}

	/**
	 * 取得config檔中，NginxServer的位址
	 * @return string
	 */
	static function nginxServer() {
		return OC_Appconfig::getValue(self::appId, 'nginxServer');
	}

	/**
	 * 設定NginxServer的位址
	 * @param string
	 */
	static function setNginxServer($nginxServer) {
		OC_Appconfig::setValue(self::appId, 'nginxServer', $nginxServer);
	}

	/**
	 * 取得config檔中，串流播放的檔案大小限制
	 * @return string
	 */
	static function limitSize() {
		return OC_Appconfig::getValue(self::appId, 'limitSize');
	}

	/**
	 * 設定串流播放的檔案大小限制
	 * @param numbser string or number
	 */
	static function setLimitSize($limitSize) {
		if (is_numeric($limitSize) && $limitSize > 0) {
			OC_Appconfig::setValue(self::appId, 'limitSize', $limitSize);
		}
	}

	/**
	 * 取得config檔中，Tomcat Server(1.6版)設定的播放暫存資料夾(streaming 1.6版中, 產生的link要放在此處底下)
	 * @return string
	 */
	static function streamingTempPath1_6() {
		$streamingTempPath1_6 = OC_Appconfig::getValue(self::appId, 'streamingTempPath1_6');
		if ($streamingTempPath1_6) {
			$streamingTempPath1_6 = OC_Helper::pathForbiddenChar($streamingTempPath1_6);
		}
		return $streamingTempPath1_6;
	}

	/**
	 * 設定Tomcat Server(1.6版)的播放暫存資料夾(streaming 1.6版中, 產生的link要放在此處底下)
	 * @param string
	 */
	static function setStreamingTempPath1_6($streamingTempPath1_6) {
		# 頭尾加上/
		$streamingTempPath1_6 = '/' . $streamingTempPath1_6;
		$streamingTempPath1_6 .= '/';
		$streamingTempPath1_6 = OC_Helper::pathForbiddenChar($streamingTempPath1_6);
		OC_Appconfig::setValue(self::appId, 'streamingTempPath1_6', $streamingTempPath1_6);
	}

	/**
	 * 取得config檔中，Tomcat Server(1.6版)的路徑
	 * @return string
	 */
	static function streamingDocumentPath1_6() {
		$streamingDocumentPath1_6 = OC_Appconfig::getValue(self::appId, 'streamingDocumentPath1_6');
		if ($streamingDocumentPath1_6) {
			$streamingDocumentPath1_6 = OC_Helper::pathForbiddenChar($streamingDocumentPath1_6);
		}
		return $streamingDocumentPath1_6;
	}

	/**
	 * 取得config檔中，Tomcat Server(1.6版)的路徑
	 * @return string
	 */
	static function setStreamingDocumentPath1_6($streamingDocumentPath1_6) {
		# 頭尾加上/
		$streamingDocumentPath1_6 = '/' . $streamingDocumentPath1_6;
		$streamingDocumentPath1_6 .= '/';
		$streamingDocumentPath1_6 = OC_Helper::pathForbiddenChar($streamingDocumentPath1_6);
		OC_Appconfig::setValue(self::appId, 'streamingDocumentPath1_6', $streamingDocumentPath1_6);
	}

	/**
	 * TODO
	 * 取得config檔中，轉檔Server轉檔後要放的暫存路徑
	 * @return string
	 */
	protected static function tmpDirFullPath() {
		$tmpDirFullPath = OC_Appconfig::getValue(self::appId, 'tmpDirFullPath');
		if ($tmpDirFullPath) {
			$tmpDirFullPath = OC_Helper::pathForbiddenChar($tmpDirFullPath);
		}
		return $tmpDirFullPath;
	}

	/**
	 * TODO
	 * 設定轉檔Server轉檔後的暫存路徑
	 * @param string
	 */
	static function setTmpDirFullPath($tmpDirFullPath) {
		# 頭尾加上/
		$tmpDirFullPath = '/' . $tmpDirFullPath;
		$tmpDirFullPath .= '/';
		$tmpDirFullPath = OC_Helper::pathForbiddenChar($tmpDirFullPath);
		OC_Appconfig::setValue(self::appId, 'tmpDirFullPath', $tmpDirFullPath);
	}

	/**
	 * 取得config檔中，轉檔Server轉檔完成，最後要放置的路徑
	 * @return string
	 */
	protected static function outputDirFullPath() {
		$outputDirFullPath = OC_Appconfig::getValue(self::appId, 'outputDirFullPath');
		if ($outputDirFullPath) {
			$outputDirFullPath = OC_Helper::pathForbiddenChar($outputDirFullPath);
		}
		return $outputDirFullPath;
	}

	/**
	 * 轉檔Server轉檔完成，最後要放置的路徑
	 * @param string
	 */
	static function setOutputDirFullPath($outputDirFullPath) {
		# 頭尾加上/
		$outputDirFullPath = '/' . $outputDirFullPath;
		$outputDirFullPath .= '/';
		$outputDirFullPath = OC_Helper::pathForbiddenChar($outputDirFullPath);
		OC_Appconfig::setValue(self::appId, 'outputDirFullPath', $outputDirFullPath);
	}

	/**
	 * 取得config檔中，轉檔Server轉檔後，連結的存放路徑
	 * @return string
	 */
	protected static function linkDirFullPath() {
		$linkDirFullPath = OC_Appconfig::getValue(self::appId, 'linkDirFullPath');
		if ($linkDirFullPath) {
			$linkDirFullPath = OC_Helper::pathForbiddenChar($linkDirFullPath);
		}
		return $linkDirFullPath;
	}

	/**
	 * 設定轉檔Server轉檔後，連結的存放路徑
	 * @param string
	 */
	static function setLinkDirFullPath($linkDirFullPath) {
		# 頭尾加上/
		$linkDirFullPath = '/' . $linkDirFullPath;
		$linkDirFullPath .= '/';
		$linkDirFullPath = OC_Helper::pathForbiddenChar($linkDirFullPath);
		OC_Appconfig::setValue(self::appId, 'linkDirFullPath', $linkDirFullPath);
	}

	/**
	 * 取得config檔中，Nginx Server檔案資料的備份位置
	 * @return string
	 */
	protected static function nginxBackupPath() {
		$nginxBackupPath = OC_Appconfig::getValue(self::appId, 'nginxBackupPath');
		if ($nginxBackupPath) {
			$nginxBackupPath = OC_Helper::pathForbiddenChar($nginxBackupPath);
		}
		return $nginxBackupPath;
	}

	/**
	 * 設定Nginx Server檔案資料的備份位置
	 * @param string
	 */
	static function setNginxBackupPath($nginxBackupPath) {
		# 頭尾加上/
		$nginxBackupPath = '/' . $nginxBackupPath;
		$nginxBackupPath .= '/';
		$nginxBackupPath = OC_Helper::pathForbiddenChar($nginxBackupPath);
		OC_Appconfig::setValue(self::appId, 'nginxBackupPath', $nginxBackupPath);
	}

	/**
	 * 取得config檔中，當輸出檔和來源檔大於?秒的時候，判斷為轉檔失敗
	 * @return int
	 */
	protected static function compareSeconds() {
		$compareSeconds = OC_Appconfig::getValue(self::appId, 'compareSeconds');
		return (int)$compareSeconds;
	}

	/**
	 * 設定config檔中，當輸出檔和來源檔大於?秒的時候，判斷為轉檔失敗
	 * @return int
	 */
	static function setCompareSeconds($compareSeconds) {
		if (is_numeric($compareSeconds) && $compareSeconds >= 0) {
			OC_Appconfig::setValue(self::appId, 'compareSeconds', $compareSeconds);
		}
	}

	/* ================== Convert Config 相關  ================== */

	/**
	 * 取得config檔中，是否開啟格式精靈(轉檔)功能
	 * @return bool
	 */
	static function convertEnable() {
		if (OC_Appconfig::getValue(self::appId, 'convertEnable') == 1) {
			return true;
		}
		return false;
	}

	/**
	 * 設定是否開啟格式精靈(轉檔)功能
	 * @param bool or 1 or 0
	 */
	static function setConvertEnable($convertEnable) {
		if ($convertEnable == 1 || $convertEnable == 0 || gettype($convertEnable) == 'boolean') {
			OC_Appconfig::setValue(self::appId, 'convertEnable', $convertEnable);
		}
	}

	/**
	 * 取得config檔中，是否開啟hls轉檔功能
	 * @return bool
	 */
	static function useHlsConvert() {
		if (OC_Appconfig::getValue(self::appId, 'useHlsConvert') == 1) {
			return true;
		}
		return false;
	}

	/**
	 * 設定是否開啟hls播放功能
	 * @param bool or 1 or 0
	 */
	static function setUseHlsConvert($useHlsConvert) {
		if ($useHlsConvert == 1 || $useHlsConvert == 0 || gettype($useHlsConvert) == 'boolean') {
			OC_Appconfig::setValue(self::appId, 'useHlsConvert', $useHlsConvert);
		}
	}

	/**
	 * 取得轉檔後是否發佈通知
	 * @return bool
	 */
	static function notificationAfterConvert() {
		if (OC_Appconfig::getValue(self::appId, 'notificationAfterConvert') == 1) {
			return true;
		}
		return false;
	}

	/**
	 * 設定轉檔後是否發佈通知
	 * @param bool or 1 or 0
	 */
	static function setNotificationAfterConvert($notificationAfterConvert) {
		if ($notificationAfterConvert == 1 || $notificationAfterConvert == 0 || gettype($notificationAfterConvert) == 'boolean') {
			OC_Appconfig::setValue(self::appId, 'notificationAfterConvert', $notificationAfterConvert);
		}
	}

	/**
	 * 取得轉檔後是否寄發Email
	 * @return bool
	 */
	static function sendEmailAfterConvert() {
		if (OC_Appconfig::getValue(self::appId, 'sendEmailAfterConvert') == 1) {
			return true;
		}
		return false;
	}

	/**
	 * 設定轉檔後是否寄發Email
	 * @param bool
	 */
	static function setSendEmailAfterConvert($sendEmailAfterConvert) {
		if ($sendEmailAfterConvert == 1 || $sendEmailAfterConvert == 0 || gettype($sendEmailAfterConvert) == 'boolean') {
			OC_Appconfig::setValue(self::appId, 'sendEmailAfterConvert', $sendEmailAfterConvert);
		}
	}

	/**
	 * 取得格式精靈的輸出資料夾路徑
	 * @return string
	 */
	static function convertDirPath() {
		return OC_Appconfig::getValue(self::appId, 'convertDirPath', 'MediaWizard');
	}

	/**
	 * 設定格式精靈的輸出資料夾名稱(路徑)
	 * @param string
	 */
	static function setConvertDirPath($convertDirPath) {
		# 開頭加上/
		$convertDirPath = '/' . $convertDirPath;
		# 去除尾巴的/
		$convertDirPath = rtrim($convertDirPath, '/');
		$convertDirPath = OC_Helper::pathForbiddenChar($convertDirPath);
		OC_Appconfig::setValue(self::appId, 'convertDirPath', $convertDirPath);
	}

	/**
	 * 取得config檔中，指定的轉檔次數限制
	 * @return int
	 */
	static function convertLimitTimes() {
		return (int)OC_Appconfig::getValue(self::appId, 'convertLimitTimes', 0);
	}

	/**
	 * 設定轉檔次數限制
	 * @param numebr string or number
	 */
	static function setConvertLimitTimes($convertLimitTimes) {
		if (is_numeric($convertLimitTimes) && $convertLimitTimes > 0) {
			OC_Appconfig::setValue(self::appId, 'convertLimitTimes', $convertLimitTimes);
		}
	}

	/**
	 * 取得轉檔失敗時重試次數上限
	 * @return int
	 */
	static function convertMaxFailedTimes() {
		return (int)OC_Appconfig::getValue(self::appId, 'convertMaxFailedTimes', 1);
	}

	/**
	 * 設定轉檔失敗時重試次數上限
	 * @param numebr string or number
	 */
	static function setConvertMaxFailedTimes($convertMaxFailedTimes) {
		if (is_numeric($convertMaxFailedTimes) && $convertMaxFailedTimes > 0) {
			OC_Appconfig::setValue(self::appId, 'convertMaxFailedTimes', $convertMaxFailedTimes);
		}
	}

}
?>