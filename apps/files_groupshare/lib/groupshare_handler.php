<?php
//require_once '../../../3rdparty/joomla-platform/libraries/import.php';
// configure from joomla-platform for mailer use.
//require_once '../../../3rdparty/joomla-platform/config.php';

class OC_GroupShare_Handler {
    public function __construct() {

    }

    static function getGroupShareList($userId = NULL, $source) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT gids,uids FROM *PREFIX*groupshare WHERE LOWER(uid_owner)=? AND source =? LIMIT 1");
            $result = $query -> execute(array(
                $userId,
                $source
            ));
            $contacts = array();
            $groupNameArray = array();

            while ($row = $result -> fetchRow()) {
                $gids = $row['gids'];
                $gidArray = preg_split('/;/', $gids, -1, PREG_SPLIT_NO_EMPTY);
                $index = 0;
                //TODO
                foreach ($gidArray as $gid) {
                    $groupNameArray[$index]['gid'] = $gid;
                    //如果取出的gid是's'開頭，則代表是system group
                    $groupNameArray[$index]['name'] = self::getGroupNameByGid($gid);
                    $index++;
                }
                $uids = $row['uids'];
                $uidArray = preg_split('/;/', $uids, -1, PREG_SPLIT_NO_EMPTY);
                $index = 0;
                foreach ($uidArray as $uid) {
                    $contacts[$index]['contact'] = $uid;
                    $nickname = (class_exists('OC_Contact')) ? OC_Contact::getNicknameById($userId, $uid) : DIFF_User::getUserNickname($uid);
                    if ($nickname) {
                        $contacts[$index]['nickname'] = $nickname;
                    } else {
                        $contacts[$index]['nickname'] = $uid;
                    }
                    $index++;
                }
            }
            // $userArray = OC_Contact::sortContactByNickname($nicknameArray, $contactArray);
            $contacts = OC_Contact::sortContactByBig5Nickname($contacts);
            $permission = OC_GroupShare_Manager::getPermission($userId, $source);
            $returnVal = array(
                "groupNameArray" => $groupNameArray,
                "userArray" => $contacts,
                "permission" => $permission
            );
            return $returnVal;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'getGroupShareList', $e);
            return FALSE;
        }
    }

    //找出指定的資料是否存在
    static function getGroupShareCount($userId = NULL, $source) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*groupshare WHERE LOWER(uid_owner)=? AND source =? LIMIT 1");
            $query -> execute(array(
                $userId,
                $source
            ));
            return $count = $query -> numRows();
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'getGroupShareCount', $e);
            return FALSE;
        }
    }

    //移除指定的群組($userId=分享者,$gid=被分享群組)
    static function removeGidsInGroupShare($userId = NULL, $gid) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*groupshare WHERE LOWER(uid_owner) =?");
            $result = $query -> execute(array($userId));
            while ($row = $result -> fetchRow()) {
                $gids = $row['gids'];
                //將gids拆解為陣列，並比對裡面是否有要尋找的$gid
                $gidArray = preg_split('/;/', $gids, -1, PREG_SPLIT_NO_EMPTY);
                if (in_array($gid, $gidArray)) {
                    //將指定的$gid移除從$gids中移除
                    $pos = array_search($gid, $gidArray);
                    unset($gidArray[$pos]);
                    $newGids = join(";", $gidArray);
                    $source = $row['source'];
                    $uids = $row['uids'];
                    OC_GroupShare_Handler::updateGroupShare($userId, $source, $newGids, $uids);
                }
            }
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'removeGidsInGroupShare', $e);
            return FALSE;
        }
    }

    //移除指定的被分享者($userId=分享者,$uid=被分享者)
    static function removeUidsInGroupShare($userId = NULL, $uid) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $selectFromAll = false;
            if ($userId == '__ALL_USER' || $userId == NULL) {
                $selectFromAll = true;
            }
            if ($selectFromAll) {
                $query = OC_DB::prepare("SELECT * FROM *PREFIX*groupshare");
                $result = $query -> execute();
            } else {
                $query = OC_DB::prepare("SELECT * FROM *PREFIX*groupshare WHERE LOWER(uid_owner) =?");
                $result = $query -> execute(array($userId));
            }
            while ($row = $result -> fetchRow()) {
                $ownerId = $row['uid_owner'];
                $uids = $row['uids'];
                $uidArray = preg_split('/;/', $uids, -1, PREG_SPLIT_NO_EMPTY);
                if (in_array($uid, $uidArray)) {
                    $pos = array_search($uid, $uidArray);
                    unset($uidArray[$pos]);
                    $newUids = join(";", $uidArray);
                    $source = $row['source'];
                    $gids = $row['gids'];
                    OC_GroupShare_Handler::updateGroupShare($ownerId, $source, $gids, $newUids);
                }
            }
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'removeUidsInGroupShare', $e);
            return FALSE;
        }
    }

    //更新oc_groupshare資料
    static function updateGroupShare($userId = NULL, $source, $gids, $uids) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            //如果要分享的資料夾，其實也是別人分享的，則找出真正的source
            // $source2 = OC_GroupShare::getSourceByTarget($source);
            // if ($source2 != FALSE) {
            // $source = $source2;
            // }
            // OC_Log::write('updateGroupShare source', $source, 1);
            if ($gids == "" && $uids == "") {
                $query = OC_DB::prepare("DELETE FROM *PREFIX*groupshare WHERE LOWER(uid_owner)=? AND source=?");
                $query -> execute(array(
                    $userId,
                    $source
                ));
            } else if (self::getGroupShareCount($userId, $source) > 0) {
                $query = OC_DB::prepare("UPDATE *PREFIX*groupshare set gids =?,uids =? WHERE LOWER(uid_owner)=? AND source =? LIMIT 1");
                $query -> execute(array(
                    $gids,
                    $uids,
                    $userId,
                    $source
                ));
            } else {
                $query = OC_DB::prepare("INSERT INTO *PREFIX*groupshare (uid_owner, source, gids, uids) values (?,?,?,?)");
                $query -> execute(array(
                    $userId,
                    $source,
                    $gids,
                    $uids
                ));
                self::sendMail($userId, $source, $uids);
            }
            //同時更新oc_groupshare_file資料
            self::updateGroupShareFiles($userId, $source, $gids, $uids);
            return TRUE;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'updateGroupShare', $e);
            return FALSE;
        }
    }

    static function sendMail($userId, $source, $uids) {
        try {
            $userName = OC_User::getUserNickname($userId);
            if (empty($userName))
                $userName = $userId;
            $source = preg_split('/\//', $source);
            $file = array_pop($source);
            $uidArray = self::spareUsersToUserIdArray($uids);
            foreach ($uidArray as $uid) {
                //先從被分享者的聯絡人清單中找到分享者的暱稱，沒有的話才帶入分享者的自訂暱稱
                $userName = (class_exists('OC_Contact')) ? OC_Contact::getNicknameById($uid, $userId) : $userName;
                $email = OC_User::getUserIdByEmail($uid);
                if (!empty($email)) {
                    $emailSubject = $userName . "分享了資料夾[$file]給您";
                    $emailBody = $emailSubject . "\n\n";
                    $emailBody .= "請至" . OC_Helper::siteTitle() . "登入後確認\n\n";
                    $emailBody .= "感謝您的使用\n\n";
                    $emailBody .= "        " . OC_Helper::siteTitle() . "工作團隊";
                    $return = JFactory::getMailer() -> sendMail('mgtran@u-sync.com', 'U-Sync檔案分享', $email, $emailSubject, $emailBody);
                    //$return = JFactory::getMailer() -> sendMail('mgtran@u-sync.com', '雲端格式精靈', 'caro@u-sync.com', 'aaa', '內容');
                }
            }
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'sendMail', $e);
            return FALSE;
        }
    }

    static function deleteGroupshareByUidOwner($uid) {
        try {
            $userId = strtolower($uid);
            $query = OC_DB::prepare("DELETE FROM *PREFIX*groupshare WHERE LOWER(uid_owner)=?");
            $query -> execute(array($uid));
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'deleteGroupshareByUidOwner', $e);
            return FALSE;
        }
    }

    //預寫不用
    static function deleteGroupshareWhereUids($uid) {
        try {
            $userId = strtolower($uid);
            $query = OC_DB::prepare("SELECT * FROM *PREFIX*groupshare WHERE uids LIKE '%$uid%' ");
            $result = $query -> execute();
            while ($row = $result -> fetchRow()) {
                $uids = $row['uids'];
                $uidArray = preg_split('/;/', $uids, -1, PREG_SPLIT_NO_EMPTY);
                if (in_array($uid, $uidArray)) {
                    $query = OC_DB::prepare("DELETE FROM *PREFIX*groupshare WHERE uid_owner='$uid'");
                    $query -> execute();
                }
            }
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'deleteGroupshareWhereUids', $e);
            return FALSE;
        }
    }

    static function updateGroupShareFiles($userId = NULL, $source, $gids, $uids) {
        try {
            //TODO
            $userId = OC_User::getUserByUserInput($userId);
            $gidArray = preg_split('/;/', $gids, -1, PREG_SPLIT_NO_EMPTY);
            $systemGidArr = array();
            $gidArr = array();

            foreach ($gidArray as $key => $gid) {
                //gid如果開頭為字串's'，代表為system group id
                if (stripos($gid, 's') === 0) {
                    $gid = preg_replace('/s/', '', $gid);
                    $systemGidArr[] = $gid;
                } else {
                    $gidArr[] = $gid;
                }
            }
            $array1 = OC_Contact_System_Group::spareGroupsToUserIdArray($systemGidArr);
            $array2 = OC_Contact_Group::spareGroupsToUserIdArray($userId, $gidArr);
            $array3 = self::spareUsersToUserIdArray($uids);
            //要更新的被分享者名單
            $uidArray = array_merge($array1, $array2, $array3);
            //取得分享權限
            $permission = OC_GroupShare_Manager::getPermission($userId, $source);
            # 如果找不到分享權限，則改為預設值1
            if (!$permission) {
                $permission = 1;
            }
            foreach ($uidArray as $uid) {
                self::insertGroupshare_files($userId, $source, $uid, $permission);
            }
            //找出分享名單有，但更新後卻沒有的使用者
            $sharedUserIdArray = self::getSharedIdArrayInGroupShareFiles($userId, $source);
            $diffArray = array_merge(array_diff($sharedUserIdArray, $uidArray));
            foreach ($diffArray as $uid) {
                self::deleteGroupshare_files($userId, $source, $uid);
                //發佈移除通知
                if (OC_APP::isEnabled('notification')) {
                    //多國語系的內容用<>包住
                    $sourceFolderName = basename($source);
                    $message = $userId . ' <unshare the folder> [' . $sourceFolderName . ']';
                    OC_Notification::addNotification('files_groupshare', 'Group Share', $uid, $message);
                }
                //self::deleteGroupshare_filesWherePermissionsNo($source, $uid, $permission);
            }
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'updateGroupShareFiles', $e);
            return FALSE;
        }
    }

    static function spareUsersToUserIdArray($uids) {
        try {
            $userIdArray = array();
            $uidArray = preg_split('/;/', $uids, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($uidArray as $uid) {
                $userIdArray[] = strtolower($uid);
            }
            return $userIdArray;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'spareUsersToUserIdArray', $e);
            return FALSE;
        }
    }

    //找出被分享者名單
    static function getSharedIdArrayInGroupShareFiles($userId = NULL, $source) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $query = OC_DB::prepare("SELECT uid_shared_with FROM *PREFIX*groupshare_files WHERE uid_owner=? AND source =?");
            $result = $query -> execute(array(
                $userId,
                $source
            ));
            $userArray = array();
            while ($row = $result -> fetchRow()) {
                $userArray[] = $row['uid_shared_with'];
            }
            return $userArray;
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'getSharedIdArrayInGroupShareFiles', $e);
            return FALSE;
        }
    }

    static function insertGroupshare_files($userId = NULL, $source, $uid, $permission = 1) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $uid = strtolower($uid);
            $target = self::sourceToTargetStr($userId, $source, $uid);
            //如果要寫入的資料已經不存在，則寫入
            if (self::getGroupshareFilesConut($userId, $uid, $source) <= 0) {
                $query = OC_DB::prepare("INSERT INTO *PREFIX*groupshare_files (uid_owner,uid_shared_with,source,target,permissions) values (?,?,?,?,?)");
                $query -> execute(array(
                    $userId,
                    $uid,
                    $source,
                    $target,
                    $permission
                ));
                //新增一筆分享資料，發送給通知中心
                if (OC_App::isEnabled('notification')) {
                    $folderName = basename($source);
                    //多國語系的內容用<>包住
                    $message = $userId . ' <share an folder> [' . $folderName . '] <to you>';
                    $link = '/files/index.php?dir=/' . OC_GroupShare::groupSharedDir();
                    OC_Notification::addNotification('files_groupshare', 'Group Share', $uid, $message, $link);
                }
            }

            /*
             $queryStr = "INSERT INTO *PREFIX*groupshare_files (uid_owner,uid_shared_with,source,target,permissions) ";
             $queryStr .= "SELECT ?,?,?,?,? ";
             $queryStr .= "  FROM dual WHERE CONCAT(?,?,?) ";
             $queryStr .= "NOT IN (SELECT CONCAT(uid_owner,uid_shared_with,source) ";
             $queryStr .= "          FROM *PREFIX*groupshare_files) ";
             $query = OC_DB::prepare($queryStr);
             $query -> execute(array($userId, $uid, $source, $target, $permission, $userId, $uid, $source));
             */
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'insertGroupshare_files', $e);
            return FALSE;
        }
    }

    static function deleteGroupshare_files($userId = NULL, $source, $uid) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $queryStr = "DELETE FROM *PREFIX*groupshare_files WHERE LOWER(uid_owner)=? ";
            $dateArray = array($userId);
            if ($source != NULL) {
                $queryStr .= "AND source=? ";
                $dateArray[] = $source;
            }

            if ($uid != NULL) {
                $queryStr .= "AND uid_shared_with=? ";
                $dateArray[] = $uid;
            }
            $query = OC_DB::prepare($queryStr);
            $query -> execute($dateArray);
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'deleteGroupshare_files', $e);
            return FALSE;
        }
    }

    //預寫不用
    static function deleteGroupshare_filesWherePermissionsNo($userId = NULL, $source, $uid, $permission) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $queryStr = "DELETE FROM *PREFIX*groupshare_files WHERE LOWER(uid_owner)=? ";
            $dateArray = array($userId);
            if ($source != NULL) {
                $queryStr .= "AND source LIKE ? ";
                $dateArray[] = $source . '%';
            }
            if ($uid != NULL) {
                $queryStr .= "AND uid_shared_with = ? ";
                $dateArray[] = $uid;
            }
            if ($permission != NULL) {
                $queryStr .= "AND permissions = ? ";
                $dateArray[] = $permission;
            }
            $query = OC_DB::prepare($queryStr);
            $query -> execute(array($dateArray));
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'deleteGroupshare_filesWherePermissionsNo', $e);
            return FALSE;
        }
    }

    static function deleteGroupshareFileByUidOwner($uid) {
        try {
            $uid = strtolower($uid);
            $query = OC_DB::prepare("DELETE FROM *PREFIX*groupshare_files WHERE LOWER(uid_owner)=?");
            $query -> execute(array($uid));
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'deleteGroupshareFileByUidOwner', $e);
            return FALSE;
        }
    }

    //預寫不用
    static function deleteGroupshareFileWhereUidSharedWidth($uid) {
        try {
            $uid = strtolower($uid);
            $query = OC_DB::prepare("DELETE FROM *PREFIX*groupshare_files WHERE LOWERuid_shared_with)=?");
            $query -> execute(array($uid));
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'deleteGroupshareFileWhereUidSharedWidth', $e);
            return FALSE;
        }
    }

    //將source路徑轉為target路徑
    static function sourceToTargetStr($userId = NULL, $source, $uid = NULL) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $uid = strtolower($uid);
            $sourceArray = preg_split('/\//', $source);
            //取得路徑最後的檔名
            $sourceLast = array_pop($sourceArray);
            $newSourceLast = OC_GroupShare::groupSharedDir() . "/" . $sourceLast;
            $nickname = OC_User::getUserNickname($userId);
            $nickname = (!empty($nickname)) ? $nickname : $userId;
            return $target = "/$uid/files/$newSourceLast - [owner-$nickname]";
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'sourceToTargetStr', $e);
            return FALSE;
        }
    }

    static function getGroupshareFilesConut($userId = NULL, $uid = NULL, $source = NULL) {
        try {
            $requirement = "";
            $queryArr = array();
            if ($userId != NULL) {
                $requirement .= "uid_owner=?";
                $queryArr[] = $userId;
            }
            if ($uid != NULL) {
                if ($requirement != "")
                    $requirement .= " AND ";
                $requirement .= "uid_shared_with=?";
                $queryArr[] = $uid;
            }
            if ($source != NULL) {
                if ($requirement != "")
                    $requirement .= " AND ";
                $requirement .= "source=?";
                $queryArr[] = $source;
            }
            $queryStr = 'SELECT * FROM *PREFIX*groupshare_files WHERE ' . $requirement . ' LIMIT 1';
            $query = OC_DB::prepare($queryStr);
            $query -> execute($queryArr);
            return $count = $query -> numRows();
        } catch(exception $e) {
            OC_Log::writeException('OC_GroupShare_Handler', 'getGroupshareFilesConut', $e);
            return FALSE;
        }
    }

    //將前端傳過來的group id 或 system group id 轉為 group name
    static function getGroupNameByGid($gid) {
        //如果取出的gid是's'開頭，則代表是system group
        if (strpos($gid, 's') === 0) {
            $gid = preg_replace('/s/', '', $gid);
            return OC_Contact_System_Group::getNameById($gid);
        } else {
            return OC_Contact_Group::getNameById($gid);
        }
    }

}
?>