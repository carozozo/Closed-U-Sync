<?php
/**
 * S開頭的server 為「通用的 Own Cloud系統」
 * 裡面為通用的funcation，再由繼承的子class去修改不同的部份
 */
class ServerType_User {

	static function login($uid, $password) {
		$run = true;

		// if user input email as id
		if ($result = self::getUserIdByEmail($uid)) {
			$uid = $result;
		}

		OC_Hook::emit("OC_User", "pre_login", array(
			"run" => &$run,
			"uid" => $uid
		));

		if ($run) {
			$uid = OC_User::checkPassword($uid, $password);
			if ($uid) {
				OC_Crypt::init($uid, $password);
				return OC_User::setUserId($uid);
			}
		}
		return false;
	}

	static function getUserEmail($uid) {
		return OC_Preferences::getValue($uid, 'settings', 'email');
	}

	static function getUserNickname($uid) {
		return OC_Preferences::getValue($uid, "settings", "nickname", $uid);
	}

	static function getUserIdByEmail($email) {
		$query = OC_DB::prepare("SELECT userid FROM *PREFIX*preferences WHERE configkey = 'email' and configvalue = '$email'");
		$result = $query -> execute();
		return $result -> fetchOne();
	}

	static function validateUserPass($username, $password) {
		if (OC_User::login($username, $password)) {
			OC_Util::setUpFS();
			return TRUE;
		}
		return FALSE;
	}

    static function isPaidUser($username) {
        return TRUE;
    }
}
