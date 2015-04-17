<?php
/*
 Hooks provided:
 delContact(contactId)*/

class OC_Contact {
    static $groupName = NULL;
    static $contactId = NULL;
    static $contactNickname = NULL;

    function __construct($groupName = NULL, $contactId = NULL, $contactNickname = NULL) {
        try {
            if ($groupName)
                self::$groupName = $groupName;
            if ($contactId) {
                $contactId = strtolower($contactId);
                $contactId = trim($contactId);
                //輸入的contactId有@，有可能是email
                if (strpos($contactId, "@") !== FALSE) {
                    //透過email找出帳號
                    $userIdByEmail = self::getUserIdByEmail($contactId);
                    if ($userIdByEmail) {
                        self::$contactId = $userIdByEmail;
                    } else {
                        self::$contactId = $contactId;
                    }
                } else {
                    self::$contactId = $contactId;
                }
            }
            //沒有傳暱稱的話，則預設為Id
            self::$contactNickname = ($contactNickname) ? $contactNickname : $contactId;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', '__construct', $e);
        }
    }

    static function getContactList($userId = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*contact WHERE uid=? ORDER BY id ASC");
            $result = $query -> execute(array($userId));
            $contacts = array();
            $index = 0;
            while ($row = $result -> fetchRow()) {
                $uid = $row['id'];
                //找出使用者自訂的聯絡人暱稱，沒有的話則找出「聯絡人定義的暱稱」
                $nickname = (!empty($row['nickname'])) ? $row['nickname'] : DIFF_User::getUserNickname($uid);
                $contacts[$index]['contact'] = $uid;
                $contacts[$index]['nickname'] = $nickname;
                $contacts[$index]['email'] = DIFF_User::getUserEmail($uid);

                $index++;
            }
            $contacts = self::sortContactByBig5Nickname($contacts);
            return $contacts;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'getContactList', $e);
        }
    }

    static function getContactById($userId = NULL, $id = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*contact WHERE uid=? and id =? LIMIT 1");
            $result = $query -> execute(array(
                $userId,
                $id
            ));
            while ($row = $result -> fetchRow()) {
                return $row;
            }
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'getContactById', $e);
        }
    }

    static function addContact($userId = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $contactId = self::$contactId;
            $contactNickname = self::$contactNickname;
            //確認帳號是否存在，而且不為本人
            $userExists = self::checkUserExists($userId);
            if ($userExists) {
                //確認帳號是否已在聯絡人名單中
                $contactExists = self::checkContactExists($userId);
                if (!$contactExists) {
                    $query = OC_DB::prepare("INSERT INTO *PREFIX*contact (uid,id,nickname) values (?,?,?)");
                    $query -> execute(array(
                        $userId,
                        $contactId,
                        $contactNickname
                    ));
                    return TRUE;
                } else {
                    return 'User already in your contact list';
                }
            } else {
                return 'User not exists';
            }
            return 'System error';
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'addContact', $e);
            return 'System error';
        }
    }

    static function checkUserExists($userId = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $contactId = self::$contactId;
            //確認USER LDAP是否有開啟(讀取LDAP較吃資源)
            $ldapEnabled = OC_App::isEnabled('user_ldap');
            if ($ldapEnabled) {
                //確認輸入的名稱是否存在，而且不為本人
                $allUsersArray = OC_USER::getUsers();
                if ($contactId != $userId && in_array($contactId, $allUsersArray)) {
                    return TRUE;
                }
            } else {
                $query = OC_DB::prepare("SELECT * FROM *PREFIX*users WHERE uid='$contactId' and uid != '$userId'");
                $count = $query -> numRows();
                if ($count > 0) {
                    return TRUE;
                }
            }
            return FALSE;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'checkUserExists', $e);
            return FALSE;
        }
    }

    static function checkContactExists($userId = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $contactId = self::$contactId;
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*contact WHERE uid='$userId' and id = '$contactId'");
            $count = $query -> numRows();
            if ($count > 0) {
                return TRUE;
            }
            return FALSE;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'checkContactExists', $e);
            return FALSE;
        }
    }

    static function renameContactNickname($userId = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $contactId = self::$contactId;
            $contactNickname = self::$contactNickname;
            $query = OC_DB::prepare("UPDATE *PREFIX*contact SET nickname=? WHERE uid=? AND id=? LIMIT 1");
            $query -> execute(array(
                $contactNickname,
                $userId,
                $contactId
            ));
            return $contactNickname;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'renameContactNickname', $e);
            return FALSE;
        }
    }

    static function delContact($userId = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $contactId = self::$contactId;
            $query = OC_DB::prepare("DELETE FROM *PREFIX*contact WHERE  uid=? AND id=?");
            $query -> execute(array(
                $userId,
                $contactId
            ));
            //刪除群組底下指定的聯絡人
            $contactGroupObj = new OC_Contact_Group(NULL, $contactId);
            $contactGroupObj -> removeContactInAllGroup();
            //執行其它有hook到這支程式的function
            OC_Hook::emit("OC_Contact", "delContact", array(
                "userId" => $userId,
                "contactId" => $contactId
            ));
            return TRUE;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'delContact', $e);
            return FALSE;
        }
    }

    static function getUserIdByEmail($email) {
        return DIFF_User::getUserIdByEmail($email);
    }

    static function getNicknameById($userId = NULL, $contactId) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT nickname FROM *PREFIX*contact WHERE uid=? AND id=? LIMIT 1");
            $result = $query -> execute(array(
                $userId,
                $contactId
            ));
            $row = $result -> fetchRow();
            $nickname = ($row) ? $row['nickname'] : OC_User::getUserNickname($contactId);
            if (!$nickname) {
                $nickname = $contactId;
            }
            return $nickname;
        } catch(exception $e) {
            OC_Log::writeException('OC_Contact', 'getNicknameById', $e);
            return FALSE;
        }
    }

    static function sortContactByBig5Nickname($contacts) {
        //如果系統預設的語系是zh_TW
        if (OC_Config::getValue('defaultLanguage', NULL, 'CONFIG_CUSTOM') == 'zh_TW') {
            $nicknameArr = array();
            foreach ($contacts as $key => $contact) {
                //將 nickname 從utf8 轉成 big5 (如果遇到big5無法呈現在字元，則找出替代字或乎略)
                $nicknameArr[] = iconv('UTF-8', 'big5//TRANSLIT//IGNORE', $contact['nickname']);
            }
            array_multisort($nicknameArr, SORT_STRING, SORT_ASC, $contacts);
        }
        return $contacts;
    }

}
?>