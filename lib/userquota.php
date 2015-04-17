<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 caro@u-sync.com
 *
 * 使用者容量相關處理
 */
class OC_UserQuota {
	/**
	 * 取得user的容量大小
	 * @param user id
	 * @return int
	 */
	static public function getUserQuota($userId) {
		$quota = OC_Preferences::getValue($userId, 'files', 'quota', 0);
		if ($quota == 0) {
			return OC_Config::getValue('defaultQuota', 0);
		}
		return $quota;
	}

	/**
	 * 設置user的容量大小
	 * @param user id, quota
	 * @return bool
	 */
	static public function setUserQuota($userId, $quota) {
		if (is_numeric($quota)) {
			$quota = (int)$quota;
		}
		if ($quota <= 0) {
			$quota = OC_Preferences::getValue($userId, 'files', 'quota', 0);
		}
		$quota = OC_Preferences::setValue($userId, 'files', 'quota', $quota);
		return true;
	}

}
