<?php
/**
 * API要用到的通用Class
 * @author Caro Huang
 * @copyright U-Sync
 */

class OC_API {

	/**
	 * For API Check, set the user to session and setup filesystem if user id or user email pass
	 * @author Caro Huang
	 */
	public static function checkApiUser() {
		$userId = '';
		if (empty($_REQUEST['userId']) && empty($_REQUEST['userEmail'])) {
			OC_JSON::error(array('message' => 'No userId or userEmail'));
		} else if (empty($_REQUEST['userId']) && !empty($_REQUEST['userEmail'])) {
			# 如果有傳入user email, 則轉換為user id
			$userId = OC_User::getUserIdByEmail($_REQUEST['userEmail']);
		} else {
			$userId = $_REQUEST['userId'];
		}

		$userId = strtolower($userId);
		if (!OC_User::userExists($userId)) {
			OC_JSON::error(array('message' => 'The ' . $userId . ' not exists'));
			exit ;
		}
		# 將使用者加入session
		OC_User::setUserId($userId);
		# 建立使用者的 Filesystem
		OC_Util::setupFS($userId);

		$action = '';
		if (isset($_REQUEST['action'])) {
			$action = $_REQUEST['action'];
		}

		return array(
			'userId' => $userId,
			'action' => $action
		);
	}

}
