<?php
/**
 * P開頭的server 為「包含通用Joomla server 的 Own Cloud系統」
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
		$JoomlaUser = OC_Joomla::getUserInfo($uid);
		return $JoomlaUser['email'];
	}

	static function getUserNickname($uid) {
		$JoomlaUser = OC_Joomla::getUserInfo($uid);
		if (!empty($JoomlaUser['name']))
			return $JoomlaUser['name'];
		return $uid;
	}

	static function getUserIdByEmail($email) {
		return OC_Joomla::getUserIdByEmail($email);
	}

	static function validateUserPass($username, $password) {
		//先和owncloud確認，沒過的時候才和joomla確認
		if (OC_User::login($username, $password)) {
			OC_Util::setUpFS();
			return TRUE;
		}

		if ($_SERVER["SERVER_PORT"] == "443") {
			$url = 'https://' . $_SERVER["SERVER_NAME"] . '/home';
		} else {
			$url = 'http://' . $_SERVER["SERVER_NAME"] . ':' . $_SERVER["SERVER_PORT"] . '/home';
		}

		// if user input email as id, check email from Joomla!
		require_once $_SERVER["DOCUMENT_ROOT"] . '/home/configuration.php';
		$jconfig = new JConfig;
		$dbname = $jconfig -> db;
		$dbuser = $jconfig -> user;
		$dbpass = $jconfig -> password;
		$dbhost = $jconfig -> host;
		$prefix = $jconfig -> dbprefix;
		$link = mysql_connect($dbhost, $dbuser, $dbpass);
		if (!$link) {
			die('Could not connect: ' . mysql_error());
		}
		$sel = mysql_select_db($dbname, $link);
		if (!$sel) {
			die('Can\'t use db: ' . mysql_error());
		}

		$query = "SELECT username FROM " . $prefix . "users WHERE email= '" . $username . "'";
		$dataset = mysql_query($query);
		if (list($result) = mysql_fetch_array($dataset)) {
			$username = $result;
		}

		$valid = false;

		$strUpdateuser = $url . '/index.php?option=com_users&format=xmlrpc&task=cloud.CheckPwd';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'username' => $username,
			'password' => $password
		));
		curl_setopt($ch, CURLOPT_URL, $strUpdateuser);

		$file_data = curl_exec($ch);
		curl_close($ch);
		$hexcode = bin2hex($file_data);

		// check value within Utf-8 format file
		if (($hexcode == 'efbbbf31') || ($file_data == 1)) {
			//$result = '1';
			$valid = true;
		} elseif ($hexcode == 'efbbbf2d31') {
			//$result = '-1';
			$valid = false;
		} else {
			$valid = false;
		}

		if ($valid) {
			$valid = OC_User::setPassword($username, $password);
			if ($valid) {
				$valid = OC_User::login($username, $password);
				if ($valid) {
					OC_Util::setUpFS();
					return true;
				}
			}
		}
		return $valid;
	}

	static function isPaidUser($username) {
		return TRUE;
	}

}
