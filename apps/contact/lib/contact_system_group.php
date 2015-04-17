<?php
/* Hooks provided:
 * delGroup(groupName,contactsArray)
 * updateContactInGroup(groupName)*/

class OC_Contact_System_Group {
    static $groupName;
    static $contactId;
    static $newGroupName;
    static $groupId;

    static function systemGroupEnabled() {
        return OC_Appconfig::getValue('contact', 'systemGroupEnabled', false);
    }

    static function getSystemGroupList() {
        try {
            $queryName = 'name';
            if (OC_Config::getValue('defaultLanguage', '', 'CONFIG_CUSTOM') == 'zh_TW') {
                $queryName = 'convert(name using big5)';
            }
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*contact_system_group ORDER BY " . $queryName . " ASC");
            $result = $query -> execute();
            $groups = array();
            $index = 0;
            while ($row = $result -> fetchRow()) {
                $groups[$index]['systemGroupId'] = $row['id'];
                $groups[$index]['systemGroupName'] = $row['name'];
                // $nameArray[$index] = $row['name'];
                // $idArray[$index] = $row['id'];
                $index++;
                // OC_Log::write('getSystemGroupList', "id=" . $row['id'] . ", name=" . $row['name'], 1);
            }
            // $groups = self::sortIdByGroupName($nameArray, $idArray);
            return $groups;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'getGroupList', $e);
        }
    }

    static function getContactListBySystemGroupId($systemGroupId = NULL) {
        try {
            $query = OC_DB::prepare("SELECT a.*,b.uid FROM *PREFIX*contact_system_group as a ,*PREFIX*contact_system_group_contact as b where b.gid = a.id and b.gid=?");
            $result = $query -> execute(array($systemGroupId));
            $contacts = array();
            $index = 0;
            while ($row = $result -> fetchRow()) {
                $uid = $row['uid'];
                $nickname = OC_User::getUserNickname($uid);
                $contacts[$index]['contact'] = $uid;
                $contacts[$index]['nickname'] = $nickname;
                $index++;
            }
            // $contacts = OC_Contact::sortContactByNickname($nicknameArray, $contactArray, $emailArray);
            return $contacts;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'getContactListBySystemGroupId', $e);
        }
    }

    /**
     * 新增系統群組
     * @param system group name
     * @return bool
     */
    static function addSystemGroup($systemGroupName) {
        try {
            $query = OC_DB::prepare("INSERT INTO *PREFIX*contact_system_group (name) VALUES (?)");
            $result = $query -> execute(array($systemGroupName, ));
            return true;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'addSystemGroup', $e);
        }
    }

    /**
     * 更新系統群組名稱
     * @param system group name, system group id
     * @return bool
     */
    static function updateSystemGroupName($systemGroupName, $systemGroupId) {
        try {
            $query = OC_DB::prepare("UPDATE *PREFIX*contact_system_group SET name = ? WHERE id = ?");
            $result = $query -> execute(array(
                $systemGroupName,
                $systemGroupId,
            ));
            return true;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'updateSystemGroupName', $e);
        }
    }

    /**
     * 刪除系統群組
     * @param system group name, system group id
     * @return bool
     */
    static function deleteSystemGroup($systemGroupId) {
        try {
            $query = OC_DB::prepare("DELETE FROM *PREFIX*contact_system_group WHERE id = ?");
            $result = $query -> execute(array($systemGroupId, ));
            $query = OC_DB::prepare("DELETE FROM *PREFIX*contact_system_group_contact WHERE gid = ?");
            $result = $query -> execute(array($systemGroupId, ));
            return true;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'deleteSystemGroup', $e);
        }
    }

    /**
     * 新增聯絡人id到系統群組
     * @param system group id, user id
     * @return bool
     */
    static function addContactToSystemGroup($gid, $contactId) {
        try {
            $query = OC_DB::prepare("INSERT INTO *PREFIX*contact_system_group_contact (gid,uid) VALUES (?,?)");
            $result = $query -> execute(array(
                $gid,
                $contactId
            ));
            return true;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'addContactToSystemGroup', $e);
        }
    }

    /**
     * 移除系統群組中的聯絡人id
     * @param system group id, user id
     * @return bool
     */
    static function removeContactFromSystemGroup($gid, $contactId) {
        try {
            $query = OC_DB::prepare("DELETE FROM *PREFIX*contact_system_group_contact WHERE gid = ? AND uid = ?");
            $result = $query -> execute(array(
                $gid,
                $contactId
            ));
            return true;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'removeContactFromSystemGroup', $e);
        }
    }

    static function renameSystemGroup($systemGroupId, $systemGroupName) {
        try {
            $query = OC_DB::prepare("UPDATE *PREFIX*contact_system_group SET name=? WHERE id=?");
            $query -> execute(array(
                $systemGroupName,
                $systemGroupId,
            ));
            return $systemGroupName;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'renameSystemGroup', $e);
            return $systemGroupName;
        }
    }

    static function getNameById($id) {
        try {
            $query = OC_DB::prepare("SELECT name FROM *PREFIX*contact_system_group WHERE id = ?");
            $result = $query -> execute(array($id));
            return $nameDB = $result -> fetchOne();
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'getNameById', $e);
            return false;
        }
    }

    //從傳來的groupId中找出所有聯絡人名單
    static function spareGroupsToUserIdArray($gidArr) {
        try {
            $userIdArray = array();
            foreach ($gidArr as $gid) {
                $contactArray = self::getContactListBySystemGroupId($gid);
                foreach ($contactArray as $contact) {
                    $userIdArray[] = $contact['contact'];
                }
            }
            return $userIdArray;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'spareGroupsToUserIdArray', $e);
            return false;
        }
    }

    static function removeContactInAllSystemGroup($userId) {
        try {
            $query = OC_DB::prepare("DELETE FROM *PREFIX*contact_system_group_contact WHERE uid = ?");
            $result = $query -> execute(array($userId));
            return true;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact_System_Group', 'removeContactInAllSystemGroup', $e);
            return false;
        }
    }

}
?>