<?php

class OC_GroupShare_Handler_Hooks {

	//群組內容更新之後要執行的動作
	static function updateGroupShareFiles($arguments) {
		try {
			$userId = $arguments["userId"];
			$userId = OC_User::getUserByUserInput($userId);
			$groupId = $arguments["groupId"];
			//要找的id =3 ，但搜尋出來的gid可能為3、13、33(所有包含"3")的資料
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*groupshare WHERE LOWER(uid_owner) =?");
			$result = $query -> execute(array($userId));
			while ($row = $result -> fetchRow()) {
				$gids = $row['gids'];
				//將gids拆解為陣列，並比對裡面是否有已經更新名單的$groupId
				$gidArray = preg_split('/;/', $gids, -1, PREG_SPLIT_NO_EMPTY);
				if (in_array($groupId, $gidArray)) {
					$source = $row['source'];
					$uids = $row['uids'];
					//重新更新分享名單
					OC_GroupShare_Handler::updateGroupShareFiles($userId, $source, $gids, $uids);
				}
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_GroupShare_Handler_Hooks', 'updateGroupShareFiles', $e);
			return FALSE;
		}	}

	//在聯絡表中移除群組之後要執行的動作
	static function removeGidsInGroupShare($arguments) {
		try {
			$userId = (!empty($arguments["userId"])) ? $arguments["userId"] : OC_User::getUser();
			$groupName = $arguments["groupName"];
			$gid = OC_Contact_Group::getIdByName($userId, $groupName);
			OC_GroupShare_Handler::removeGidsInGroupShare($userId, $gid);
		} catch(exception $e) {
			OC_Log::writeException('OC_GroupShare_Handler_Hooks', 'removeGidsInGroupShare', $e);
			return FALSE;
		}
	}
	//在聯絡表中移除聯絡人之後要執行的動作
	static function removeUidsInGroupShare($arguments) {
		try {
			$userId = (!empty($arguments["userId"])) ? $arguments["userId"] : OC_User::getUser();
			$contactId = $arguments["contactId"];
			OC_GroupShare_Handler::removeUidsInGroupShare($userId, $contactId);
		} catch(exception $e) {
			OC_Log::writeException('OC_GroupShare_Handler_Hooks', 'removeUidsInGroupShare', $e);
			return FALSE;
		}
	}

	//移除帳號之後要執行的動作
	static function updateGroupShareFilesByDelUser($arguments) {
		try {
			$uid = $arguments["uid"];
			OC_GroupShare_Handler::deleteGroupshareByUidOwner($uid);
			OC_GroupShare_Handler::deleteGroupshareFileByUidOwner($uid);
			OC_GroupShare_Handler::removeUidsInGroupShare("__ALL_USER", $uid);
			//OC_GroupShare_Handler::deleteGroupshareFileWhereUidSharedWidth($uid);
		} catch(exception $e) {
			OC_Log::writeException('OC_GroupShare_Handler_Hooks', 'updateGroupShareFilesByDelUser', $e);
			return FALSE;
		}
	}

	static function deleteItem($arguments) {
		$path = $arguments[OC_Filesystem::signal_param_path];
		OC_GroupShare::deleteItem($path);
	}

	static function createItem($arguments) {
		$path = $arguments[OC_Filesystem::signal_param_path];
		OC_GroupShare_Notification::notificationWhenContentUpdatedByOwner($path);
		OC_GroupShare_Notification::notificationWhenContentUpdatedBySharedUser($path);
	}

	static function updateItem($arguments) {
		$path = $arguments[OC_Filesystem::signal_param_path];
		OC_GroupShare::updateItem($path);
	}

	static function renameItem($arguments) {
		$oldPath = $arguments[OC_Filesystem::signal_param_oldpath];
		$newPath = $arguments[OC_Filesystem::signal_param_newpath];
		if (OC_Filesystem::is_dir($oldPath)) {
			OC_GroupShare::renameItem($oldPath, $newPath);
		}
		OC_GroupShare_Notification::notificationWhenContentUpdatedByOwner($newPath);
		OC_GroupShare_Notification::notificationWhenContentUpdatedBySharedUser($newPath);
	}

	static function copyItem($arguments) {
		$newPath = $arguments[OC_Filesystem::signal_param_newpath];
		OC_GroupShare_Notification::notificationWhenContentUpdatedByOwner($newPath);
		OC_GroupShare_Notification::notificationWhenContentUpdatedBySharedUser($newPath);
	}

}
