<?php
/**
 * Audio Streaming plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * Audio Streaming相關設定
 * 所有config取值的function名稱即為config key值
 * 所有儲存config值的function即為 set+(開頭大寫)configKey值
 */

class OC_AudioStreaming_Settings {
	static $appId = 'audio_streaming';
	/* ================== Streaming Config 相關 ================== */

	/**
	 * 設定audio streaing 的所有config初始值
	 */
	static function setDefaultAudioStreamingConfigs() {
		if (!self::streamingDocumentPath()) {
			self::setStreamingDocumentPath('/var/www/html/data/MG/webapps/');
		}
		if (!self::streamingTempPath()) {
			self::setStreamingTempPath('/var/www/html/data/MG/webapps/AudioGateTest/temp/');
		}
	}

	/**
	 * 取得audio streaing 的所有config值
	 * @return array
	 */
	static function getAudioStreamingConfigItems() {
		return OC_Appconfig::getConfigItemsByAppId(self::$appId);
	}

	/**
	 * 取得config檔中，Tomcat Server的路徑
	 * @return string
	 */
	static function streamingDocumentPath() {
		$streamingDocumentPath = OC_Appconfig::getValue(self::$appId, 'streamingDocumentPath');
		if ($streamingDocumentPath) {
			$streamingDocumentPath = OC_Helper::pathForbiddenChar($streamingDocumentPath);
		}
		return $streamingDocumentPath;
	}

	/**
	 * 取得config檔中，Tomcat Server的路徑
	 * @return string
	 */
	static function setStreamingDocumentPath($streamingDocumentPath) {
		# 頭尾加上/
		$streamingDocumentPath = '/' . $streamingDocumentPath;
		$streamingDocumentPath .= '/';
		$streamingDocumentPath = OC_Helper::pathForbiddenChar($streamingDocumentPath);
		OC_Appconfig::setValue(self::$appId, 'streamingDocumentPath', $streamingDocumentPath);
	}

	/**
	 * 取得config檔中，Tomcat Server設定的播放暫存資料夾
	 * @return string
	 */
	static function streamingTempPath() {
		$streamingTempPath = OC_Appconfig::getValue(self::$appId, 'streamingTempPath');
		if ($streamingTempPath) {
			$streamingTempPath = OC_Helper::pathForbiddenChar($streamingTempPath);
		}
		return $streamingTempPath;
	}

	/**
	 * 設定Tomcat Server的播放暫存資料夾
	 * @param string
	 */
	static function setStreamingTempPath($streamingTempPath) {
		# 頭尾加上/
		$streamingTempPath = '/' . $streamingTempPath;
		$streamingTempPath .= '/';
		$streamingTempPath = OC_Helper::pathForbiddenChar($streamingTempPath);
		OC_Appconfig::setValue(self::$appId, 'streamingTempPath', $streamingTempPath);
	}

}
?>