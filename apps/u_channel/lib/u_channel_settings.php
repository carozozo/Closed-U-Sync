<?php
/**
 * ownCloud - U-Channel
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * U-Channel相關設定
 * 所有config取值的function名稱即為config key值
 * 所有儲存config值的function即為 set+(開頭大寫)configKey值
 */

class OC_U_Channel_Settings {
	const appId = 'u_channel';
	/**
	 * 取得 U Channel 的所有config值
	 * @return array
	 */
	static function getConfigItems() {
		return $items = OC_Appconfig::getConfigItemsByAppId(self::appId);
	}

	/**
	 * 取得存放頻道列表的檔案
	 * @return string
	 */
	static function channelListSource() {
		return OC_Appconfig::getValue(self::appId, 'channelListSource');
	}

	/**
	 * 設定存放頻道列表的檔案路徑
	 * @param string
	 */
	static function setChannelListSource($channelListSource) {
		// $channelListUrl = OC_Helper::pathForbiddenChar($channelListUrl);
		OC_Appconfig::setValue(self::appId, 'channelListSource', $channelListSource);
	}

}
?>