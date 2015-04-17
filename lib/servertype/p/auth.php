<?php
/**
 * P開頭的server 為「包含通用Joomla server 的 Own Cloud系統」
 * 裡面為通用的funcation，再由繼承的子class去修改不同的部份
 */
class ServerType_Auth {

	static function directLogin($token) {
		// $sql = "delete from $prefix" . "directlogin where TIME_TO_SEC(TIMEDIFF(now(), createtime)) > 30";
		// $dataset = mysql_query($sql);
		//
		// $sql = "select uid from $prefix" . "directlogin where token = '" . $token . "' and TIME_TO_SEC(TIMEDIFF(now(), createtime)) <= 30 LIMIT 1";
		// $dataset = mysql_query($sql);

		$query = OC_DB::prepare("delete from *PREFIX*directlogin where TIME_TO_SEC(TIMEDIFF(now(), createtime)) > 30");
		$query -> execute();

		$query = OC_DB::prepare("select uid from *PREFIX*directlogin where token = ? and TIME_TO_SEC(TIMEDIFF(now(), createtime)) <= 30 LIMIT 1");
		$row = $query -> execute(array($token)) -> fetchRow();
		
		// if (list($uid) = mysql_fetch_array($dataset)) {
		if (count($row)) {
			$uid = $row['uid'];
			$run = true;
			$uid = strtolower($uid);
			OC_Hook::emit("OC_User", "pre_login", array(
				"run" => &$run,
				"uid" => $uid
			));
			OC_User::setUserId($uid);

			// set language
			if (isset($_REQUEST['lang'])) {
				OC_Preferences::setValue($uid, 'core', 'lang', $_REQUEST['lang']);
			}

			// direct login
			header('Location: ' . OC::$WEBROOT . '/');
		} else {
			header('Location: ' . OC::$WEBROOT . '/home');
		}
	}

}
