<?php
/**
 * ownCloud - U-Channel
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 讀取HLS頻道做播放
 */

class OC_U_Channel extends OC_U_Channel_Settings {

	/**
	 * 取得頻道列表
	 * @param 檔案的內部路徑
	 * @return boolean
	 */
	static function getChannelList() {
		$channelListUrl = self::channelListSource();
		$channelList = self::curlGetChannelList($channelListUrl);
		# 將讀到的Json 格式轉為array
		return $channelList = json_decode($channelList, true);
	}

	/**
	 * 讀取頻道列表
	 * @param toUrl(轉檔server的API網址)
	 * @return string
	 */
	private static function curlGetChannelList($toURL) {
		$ch = curl_init();
		# CURLOPT_RETURNTRANSFER:網頁回應,CURLOPT_POST:使用POST,CURLOPT_SSL_VERIFYPEER:SSL驗證
		$options = array(
			CURLOPT_URL => $toURL,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_SSL_VERIFYPEER => false,
		);
		curl_setopt_array($ch, $options);
		return $result = curl_exec($ch);
	}

}
?>