<?php
/* Hooks provided:
 * delGroup(groupName,contactsArray)
 * updateContactInGroup(groupName)*/

class OC_Contact_Group {
	static $groupName;
	static $contactId;
	static $newGroupName;
	static $groupId;

	function __construct($groupName = NULL, $contactId = NULL, $newGroupName = NULL, $groupId = NULL) {
		try {
			if ($groupName)
				self::$groupName = $groupName;
			if ($contactId)
				self::$contactId = $contactId;
			if ($newGroupName)
				self::$newGroupName = $newGroupName;
			if ($groupId)
				self::$groupId = $groupId;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', '__construct', $e);
		}
	}

	static function getGroupList($userId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$queryName = 'name';
			if (OC_Config::getValue('defaultLanguage', '', 'CONFIG_CUSTOM') == 'zh_TW') {
				$queryName = 'convert(name using big5)';
			}

			$query = OC_DB::prepare("SELECT id,name FROM *PREFIX*contact_group WHERE uid=? ORDER BY " . $queryName . " ASC");
			$result = $query -> execute(array($userId));
			$groups = array();
			$nameArray = array();
			$idArray = array();
			$index = 0;
			while ($row = $result -> fetchRow()) {
				$groups[$index]['groupId'] = $row['id'];
				$groups[$index]['groupName'] = $row['name'];
				// $nameArray[$index] = $row['name'];
				// $idArray[$index] = $row['id'];
				$index++;
			}
			// $groups = self::sortIdByGroupName($nameArray, $idArray);
			return $groups;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'getGroupList', $e);
		}
	}

	static function getContactListByGroupId($userId = NULL, $groupId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$groupId = (empty($groupId)) ? self::$groupId : $groupId;
			$query = OC_DB::prepare("SELECT contactId FROM *PREFIX*contact_group WHERE uid=? AND id=? LIMIT 1");
			$result = $query -> execute(array(
				$userId,
				$groupId
			));
			$contactIdDB = $result -> fetchOne();
			$contactsArray = preg_split("/\;/", $contactIdDB, -1, PREG_SPLIT_NO_EMPTY);
			$contacts = array();
			$index = 0;
			foreach ($contactsArray as $contactId) {
				//找出使用者自訂的聯絡人暱稱，沒有的話則找出「聯絡人定義的暱稱」
				$contactDB = OC_Contact::getContactById($userId, $contactId);
				$nickname = (!empty($contactDB['nickname'])) ? $contactDB['nickname'] : OC_User::getUserNickname($contactId);
				$nickname = (!empty($nickname)) ? $nickname : $contactId;
				$contacts[$index]['contact'] = $contactId;
				$contacts[$index]['nickname'] = $nickname;
				$contacts[$index]['email'] = OC_User::getUserEmail($contactId);
				$index++;
			}
			$contacts = OC_Contact::sortContactByBig5Nickname($contacts);
			return $contacts;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'getContactListByGroupId', $e);
		}
	}

	static function getCountContactInGroupByGroupId($groupId = NULL) {
		$groupId = (empty($groupId)) ? self::$groupId : $groupId;
		$query = OC_DB::prepare("SELECT contactId FROM *PREFIX*contact_group WHERE id=?");
		$result = $query -> execute(array($groupId));
		$contactIdArray = null;
		while ($row = $result -> fetchRow()) {
			$contactId = $row['contactId'];
			$contactIdArray = preg_split('/;/', $contactId, -1, PREG_SPLIT_NO_EMPTY);
		}
		return count($contactIdArray);
	}

	static function addGroup($userId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$groupName = self::$groupName;
			$query = OC_DB::prepare("INSERT INTO *PREFIX*contact_group (uid,name) values (?,?)");
			$query -> execute(array(
				$userId,
				$groupName
			));
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'addGroup', $e);
			return FALSE;
		}
	}

	static function renameGroup($userId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$groupName = self::$groupName;
			$newGroupName = self::$newGroupName;
			$query = OC_DB::prepare("UPDATE *PREFIX*contact_group SET name=? WHERE uid=? AND name=? LIMIT 1");
			$query -> execute(array(
				$newGroupName,
				$userId,
				$groupName
			));
			return $newGroupName;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'renameGroup', $e);
			return $groupName;
		}
	}

	static function delGroup($userId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$groupName = self::$groupName;
			//執行其它有hook到這支程式的function(要先執行，否則group刪除後，其它function無法從DB得知內容)
			//回傳group名稱，及底下包含的聯絡人名稱(暫時沒用到)
			$contactsArray = self::getContactListByGroupId();
			OC_Hook::emit("OC_Contact_Group", "delGroup", array(
				"userId" => $userId,
				"groupName" => $groupName,
				"contactsArray" => $contactsArray
			));
			$query = OC_DB::prepare("DELETE FROM *PREFIX*contact_group WHERE uid=? AND name=?");
			$query -> execute(array(
				$userId,
				$groupName
			));
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'delGroup', $e);
			return FALSE;
		}
	}

	static function ifContactIdInGroup($groupName, $contactId) {
		try {
			$query = OC_DB::prepare("SELECT id FROM *PREFIX*contact_group WHERE name='$groupName' AND contactId LIKE '%$contactId%'");
			$count = $query -> numRows();
			if ($count > 0)
				return TRUE;
			return FALSE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'ifContactIdInGroup', $e);
			return FALSE;
		}
	}

	//預寫
	static function addContactInGroup() {
		try {
			$userId = OC_User::getUser();
			$groupName = self::$groupName;
			$contactId = self::$contactId;
			$query = OC_DB::prepare("SELECT contactId FROM *PREFIX*contact_group WHERE uid='$userId' AND name='$groupName' LIMIT 1");
			$count = $query -> numRows();
			if ($count > 0) {
				$result = $query -> execute();
				$contactIdDB = $result -> fetchOne();
				$pos = strpos($contactIdDB, $contactId);
				if ($pos === false) {
					$contactId = $contactIdDB . $contactId . ";";
					$query = OC_DB::prepare("UPDATE *PREFIX*contact_group SET contactId = '$contactId' WHERE uid='$userId' AND name='$groupName' LIMIT 1");
					$query -> execute();
				}
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'addContactInGroup', $e);
			return FALSE;
		}
	}

	//預寫
	static function removeContactInGroup() {
		try {
			$userId = OC_User::getUser();
			$groupName = self::$groupName;
			$contactId = self::$contactId;
			$query = OC_DB::prepare("SELECT contactId FROM *PREFIX*contact_group WHERE uid='$userId' AND name='$groupName' LIMIT 1");
			$count = $query -> numRows();
			if ($count > 0) {
				$result = $query -> execute();
				$contactIdDB = $result -> fetchOne();
				$pos = strpos($contactIdDB, $contactId);
				if ($pos !== false) {
					$contactId = str_replace($contactId . ";", "", $contactIdDB);
					$query = OC_DB::prepare("UPDATE *PREFIX*contact_group SET contactId = '$contactId' WHERE uid='$userId' AND name='$groupName' LIMIT 1");
					$query -> execute();
				}
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'removeContactInGroup', $e);
			return FALSE;
		}
	}

	static function updateContactInGroup($userId = NULL) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$groupId = self::$groupId;
			$contactId = self::$contactId;
			$query = OC_DB::prepare("UPDATE *PREFIX*contact_group SET contactId=? WHERE uid=? AND id=? LIMIT 1");
			$query -> execute(array(
				$contactId,
				$userId,
				$groupId
			));
			//執行其它有hook到這支程式的function
			OC_Hook::emit("OC_Contact_Group", "updateContactInGroup", array(
				"userId" => $userId,
				"groupId" => $groupId,
			));
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'updateContactInGroup', $e);
			return FALSE;
		}
	}

	static function removeContactInAllGroup() {
		try {
			$userId = OC_User::getUser();
			$contactId = self::$contactId;
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*contact_group WHERE uid=? ORDER BY id ASC");
			$result = $query -> execute(array($userId));
			while ($row = $result -> fetchRow()) {
				$groupNameDB = $row['name'];
				$contactIdDB = $row['contactId'];
				$pos = strpos($contactIdDB, $contactId);
				if ($pos !== false) {
					$newContactId = str_replace($contactId . ";", "", $contactIdDB);
					$newContactId = str_replace($contactId, "", $contactIdDB);
					$query = OC_DB::prepare("UPDATE *PREFIX*contact_group SET contactId=? WHERE uid=? AND name=? LIMIT 1");
					$query -> execute(array(
						$newContactId,
						$userId,
						$groupNameDB
					));
				}
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'removeContactInAllGroup', $e);
			return FALSE;
		}
	}

	static function getNameById($id) {
		try {
			$query = OC_DB::prepare("SELECT name FROM *PREFIX*contact_group WHERE id = ?");
			$result = $query -> execute(array($id));
			return $nameDB = $result -> fetchOne();
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'getNameById', $e);
			return FALSE;
		}
	}

	static function getIdByName($userId = NULL, $groupName) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			$query = OC_DB::prepare("SELECT id FROM *PREFIX*contact_group WHERE uid = ? AND name = ?");
			$result = $query -> execute(array(
				$userId,
				$groupName
			));
			return $id = $result -> fetchOne();
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'getIdByName', $e);
			return FALSE;
		}
	}

	//從傳來的groupId中找出所有聯絡人名單
	static function spareGroupsToUserIdArray($userId = NULL, $gidArr) {
		try {
			$userId = OC_User::getUserByUserInput($userId);
			//例：$gids = "1;2;3;5"
			$userIdArray = array();
			foreach ($gidArr as $gid) {
				// $groupName = self::getNameById($gid);
				$contactArray = self::getContactListByGroupId($userId, $gid);
				foreach ($contactArray as $contact) {
					$userIdArray[] = $contact['contact'];
				}
			}
			return $userIdArray;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'spareGroupsToUserIdArray', $e);
			return FALSE;
		}
	}

	//範例：$gids=1;3;4....，轉成「同學,朋友,情人」
	static function gidsToGroupNames($gids) {
		try {
			$gidArray = preg_split('/;/', $gids, -1, PREG_SPLIT_NO_EMPTY);
			$groupNameArray = array();
			foreach ($gidArray as $gid) {
				$groupNameArray[] = self::getNameById($gid);
			}
			return $groupNameArray;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'gidsToGroupNames', $e);
			return FALSE;
		}
	}

	static function sortIdByGroupName($nameArray, $idArray) {
		try {
			$groups = array();
			array_multisort($nameArray, $idArray);
			$nameLength = count($nameArray);
			$idLength = count($idArray);
			if ($nameLength == $idLength) {
				for ($i = 0; $i < $idLength; $i++) {
					$groups[$i]['groupName'] = $nameArray[$i];
					$groups[$i]['groupId'] = $idArray[$i];
				}
			}
			return $groups;
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Group', 'sortIdByGroupName', $e);
			return FALSE;
		}
	}

}
?>