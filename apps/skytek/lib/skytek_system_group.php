<?php
/**
 * ownCloud - Skytek plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 針對Skytek(天方科技)
 * 支援系統群組的新增/修改/刪除等操作
 *
 * dpid即為系統群組名稱中的前4碼
 * ex:
 * dpid=A0000，dpname=校長室
 * 則 system group name=A0000校長室
 */

class OC_SkytekSystemGrop {
	/**
	 * 新增系統群組
	 * @param 校務單位id, 校務單位名稱
	 * @return int
	 */
	static function addSystemGroup($dpId, $dpName) {
		$dpId = trim($dpId);
		$dpName = trim($dpName);
		if ($dpId && $dpName) {
			# 如果傳送過來的校務單位id已存在
			if (self::checkDpidExists($dpId)) {
				return -1;
			}
			$systemGroupName = self::systemGroupName($dpId, $dpName);
			if (OC_Contact_System_Group::addSystemGroup($systemGroupName)) {
				return 1;
			}
			return 0;
		}
		# 變數為空值
		return -10;
	}

	/**
	 * 修改系統群組名稱
	 * @param 校務單位id, 校務單位名稱
	 * @return int
	 */
	static function updateSystemGroupName($dpId, $dpName) {
		$dpId = trim($dpId);
		$dpName = trim($dpName);
		if ($dpId && $dpName) {
			# 如果傳送過來的校務單位id不存在
			if (!self::checkDpidExists($dpId)) {
				return -2;
			}
			$systemGroupName = self::systemGroupName($dpId, $dpName);
			$systemGroupId = self::getSystemGroupIdByDpid($dpId);
			if (OC_Contact_System_Group::updateSystemGroupName($systemGroupName, $systemGroupId)) {
				return 1;
			}
			return 0;
		}
		# 變數為空值
		return -10;
	}

	/**
	 * 刪除系統群組
	 * @param 校務單位id
	 * @return int
	 */
	static function deleteSystemGroup($dpId) {
		$dpId = trim($dpId);
		$systemGroupId = self::getSystemGroupIdByDpid($dpId);
		# 如果傳送過來的校務單位id不存在
		if (!self::checkDpidExists($dpId)) {
			return -2;
		}
		if (OC_Contact_System_Group::deleteSystemGroup($systemGroupId)) {
			return 1;
		}
		return 0;
	}

	/**
	 * 確認校務單位id是否存在系統群組中
	 * @param 校務單位id
	 * @return bool
	 */
	static function checkDpidExists($dpId) {
		$ifDpidExists = false;
		$systemGroupList = self::getSystemGroupList();
		foreach ($systemGroupList as $index => $systemGroup) {
			if (in_array($dpId, $systemGroup)) {
				// return 'Department Not Exists';
				$ifDpidExists = true;
			}
		}
		return $ifDpidExists;
	}

	/**
	 * 利用校務單位id，取得系統群組id
	 * @param 校務單位id
	 * @return string
	 */
	static function getSystemGroupIdByDpid($dpId) {
		$systemGroupList = self::getSystemGroupList();
		foreach ($systemGroupList as $index => $systemGroup) {
			if (in_array($dpId, $systemGroup)) {
				return $systemGroup['systemGroupId'];
			}
		}
	}

	/**
	 * 將校務單位id和名稱，組合成系統群組名稱
	 * @param 校務單位id, 校務單位名稱
	 * @return string
	 */
	private static function systemGroupName($dpId, $dpName) {
		if ($dpId && $dpName) {
			$systemGroupName = trim($dpId . $dpName);
			return $systemGroupName;
		}
	}

	/**
	 * 將OwnCloud轉換成Skytek需要的系統群組格式
	 * dpid即為系統群組名稱中的前4碼
	 */
	private static function getSystemGroupList() {
		$systemGroupList = OC_Contact_System_Group::getSystemGroupList();
		foreach ($systemGroupList as $index => $systemGroup) {
			$systemGroupName = $systemGroup['systemGroupName'];
			$systemGroupList[$index]['dpid'] = substr($systemGroupName, 0, 4);
		}
		return $systemGroupList;
	}

}
?>