<?php
class OC_GroupShare_Manager {
    public function __construct() {

    }

    static function getGroupShareManagerList($userId = NULL, $sortBy = 'path', $sort = 'ASC') {
        try {
            # 目前 sortBy 只有一個選項
            $sortBy = ' ORDER BY CONVERT(`source` using big5) ';
            $sortBy .= strtoupper($sort);
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*groupshare WHERE uid_owner= ?" . $sortBy);
            $result = $query -> execute(array($userId));
            $groupShareList = array();
            $index = 0;

            while ($row = $result -> fetchRow()) {
                $source = preg_replace('/\/' . $userId . '\/files/', '', $row['source']);
                $gids = $row['gids'];
                $uids = $row['uids'];
                $groupShareList[$index]['source'] = $source;
                $groupShareList[$index]['gids'] = self::spliteGidsToArray($gids);
                $groupShareList[$index]['uids'] = self::spliteUidsToArray($userId, $uids);
                $source = $row['source'];
                $groupShareList[$index]['permission'] = self::getPermission($userId, $source);
                $index++;
            }
            # 暫時不用(觀查使用狀況再調整)
            // $groupShareList = self::sortGroupShareListByBig5Source($groupShareList);
            return $groupShareList;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Manager', getGroupShareManagerList, $e);
            return FALSE;
        }
    }

    static function updatePermission($userId = NULL, $source, $permission) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("UPDATE *PREFIX*groupshare_files set permissions =? WHERE LOWER(uid_owner)=? AND source =?");
            $result = $query -> execute(array(
                $permission,
                $userId,
                $source
            ));
            return $source;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Manager', updatePermission, $e);
            return FALSE;
        }
    }

    static function removeGroupShare($userId = NULL, $source) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("DELETE FROM *PREFIX*groupshare WHERE uid_owner=? AND source =?");
            $result = $query -> execute(array(
                $userId,
                $source
            ));
            $query = OC_DB::prepare("DELETE FROM *PREFIX*groupshare_files WHERE uid_owner=? AND source =?");
            $result = $query -> execute(array(
                $userId,
                $source
            ));
            return $source;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Manager', removeGroupShare, $e);
            return FALSE;
        }
    }

    static function getPermission($userId = NULL, $source) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT permissions FROM *PREFIX*groupshare_files WHERE LOWER(uid_owner)=? AND source =? LIMIT 1");
            $result = $query -> execute(array(
                $userId,
                $source
            )) -> fetchOne();
            return $result;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Manager', getPermission, $e);
            return FALSE;
        }
    }

    static private function spliteGidsToArray($gids) {
        try {
            $groupArray = array();
            $gidArray = preg_split('/;/', $gids, -1, PREG_SPLIT_NO_EMPTY);
            $index = 0;
            foreach ($gidArray as $gid) {
                $groupArray[$index]['gid'] = $gid;
                $groupArray[$index]['name'] = OC_GroupShare_Handler::getGroupNameByGid($gid);
                $index++;
            }
            return $groupArray;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Manager', spliteGidsToArray, $e);
            return FALSE;
        }
    }

    static private function spliteUidsToArray($userId = NULL, $uids) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $userArray = array();
            $uidArray = preg_split('/;/', $uids, -1, PREG_SPLIT_NO_EMPTY);
            $index = 0;
            foreach ($uidArray as $uid) {
                $userArray[$index]['uid'] = $uid;
                $userArray[$index]['nickname'] = (class_exists('OC_Contact')) ? OC_Contact::getNicknameById($userId, $uid) : $uid;
                $index++;
            }
            return $userArray;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Manager', spliteUidsToArray, $e);
            return FALSE;
        }
    }

    private static function sortGroupShareListByBig5Source($groupShareList) {
        //如果系統預設的語系是zh_TW
        if (OC_Config::getValue('defaultLanguage', NULL, 'CONFIG_CUSTOM') == 'zh_TW') {
            $sourceArr = array();
            foreach ($groupShareList as $key => $groupShare) {
                //將 source 從utf8 轉成 big5 (如果遇到big5無法呈現在字元，則找出替代字或乎略)
                $sourceArr[] = iconv('UTF-8', 'big5//TRANSLIT//IGNORE', $groupShare['source']);
            }
            array_multisort($sourceArr, SORT_STRING, SORT_ASC, $groupShareList);
        }
        return $groupShareList;
    }

}
?>