<?php
class OC_Contact_Hooks {
	/*
	 @param paramters parameters from postDeleteUser-Hook
	 @return array*/

	static function delContact($arguments) {
		try {
			$contactId = $arguments["uid"];
			$query = OC_DB::prepare("DELETE FROM *PREFIX*contact WHERE id='$contactId'");
			$query -> execute();
			$query = OC_DB::prepare("DELETE FROM *PREFIX*contact WHERE uid='$contactId'");
			$query -> execute();
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Hooks', 'delContact', $e);
		}
	}

	static function removeContactInGroup($arguments) {
		try {
			$contactId = $arguments["uid"];
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*contact_group");
			$result = $query -> execute();
			while ($row = $result -> fetchRow()) {
				$uidDB = $row["uid"];
				$nameDB = $row["name"];
				$contactIdDB = $row["contactId"];
				$pos = strpos($contactIdDB, $contactId);
				if ($pos !== false) {
					//移除已經被刪掉的帳號
					$newContactId = str_replace($contactId . ";", "", $contactIdDB);
					$query = OC_DB::prepare("UPDATE *PREFIX*contact_group SET contactId = '$newContactId' WHERE uid='$uidDB' AND name='$nameDB'");
					$query -> execute();
				}
			}
			$query = OC_DB::prepare("DELETE FROM *PREFIX*contact_group WHERE uid='$contactId'");
			$query -> execute();
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Hooks', 'removeContactInGroup', $e);
		}
	}

	static function removeContactInSystemGroup($arguments) {
		try {
			$userId = $arguments["uid"];
			OC_Contact_System_Group::removeContactInAllSystemGroup($userId);
		} catch(exception $e) {
			OC_Log::writeException('OC_Contact_Hooks', 'removeContactInGroup', $e);
		}
	}

}
