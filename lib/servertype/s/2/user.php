<?php
/**
 * S2 server 為「包含SkyTek 天方科技 的 Own Cloud系統」
 */
class DIFF_User extends ServerType_User {

	static function login($uid, $password) {
		$valid = false;
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
			/**
			 * owncloud中的user會預先匯入SkyTek提供的名單(帳號=學校工號)
			 * 如果uid不存在owncloud系統中
			 */
			if (!OC_User::userExists($uid)) {
				return FALSE;
			}

			/* 不使用 OwnCloud 驗證，改採一律由 SkyTek 驗證 (系統內建帳號除外) */
			$pwd = md5($password);
			$strApiUrl = OC_Config::getValue('authUrl', NULL, 'CONFIG_SkyTek') . "?uid=$uid&pwd=$pwd&pw_type=md5";
			if ($strApiUrl) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					'uid' => $uid,
					'pwd' => $pwd,
					'pw_type' => 'md5',
				));
				curl_setopt($ch, CURLOPT_URL, $strApiUrl);

				$file_data = curl_exec($ch);
				curl_close($ch);
				$hexcode = bin2hex($file_data);

				// check value within Utf-8 format file
				if (!(($hexcode == 'efbbbf59') || ($file_data == 'Y'))) {
					// 若 SkyTek 認證未過則再檢查是否為系統內建帳號
					if (!OC_User::checkPassword($uid, $password)) {
						return FALSE;
					}
				}

				OC_Crypt::init($uid, $password);
				$valid = OC_User::setUserId($uid);
			}
		}
		return $valid;
	}

}
