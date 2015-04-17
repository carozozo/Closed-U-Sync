<?php
/**
 * ownCloud - Public Share plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 分享連結中控
 */

class OC_PublicShare {

    /**
     * 依 source path 取得分享連結資料
     * @param source path
     * @return array
     */
    static function getBySource($sourcePath) {
        $uid = OC_User::getUser();
        $item = new OC_PublicShare_Item($uid, $sourcePath);
        $property = $item -> property;
        if ($property -> ifFromDb) {
            return $property;
        }
    }

    /**
     * 新增/更新 public share 資料
     * @param source path
     * @param limit days
     * @return array
     */
    static function insert($sourcePath, $limitDays = null) {
        $uid = OC_User::getUser();
        $item = new OC_PublicShare_Item($uid, $sourcePath);
        $property = $item -> property;
        # 依傳入的 limitDays 算出到期日
        $deadlineUtc = OC_PublicShare_Helper::getDeadlineUtcByLimitDays($limitDays);
        $deadlineLocal = OC_Helper::formatDateTimeUTCToLocal($deadlineUtc);
        # 寫入/更新路徑和到期日
        $item -> replace(null, null, $deadlineLocal);
        return $property;
    }

    /**
     * 依 token 確認資料狀況，及是否需要密碼
     * @param token
     * @return array
     */
    static function checkByToken($token) {
        $item = new OC_PublicShare_Item($token);
        $property = $item -> property;
        if ($property -> ifFromDb) {
            $path = basename($property -> sourcePath);
            if ($property -> isOutOfDeadline) {
                return array(
                    'path' => $path,
                    'result' => 'Share expired'
                );
            }
            if ($property -> pwd) {
                return array(
                    'path' => $path,
                    'result' => 'Need pwd'
                );
            }
            return array('result' => 'Pass');
        }
        return array('result' => 'No data');
    }

    /**
     * 依 pwd 和 token 取得資料
     * @param password
     * @param token
     * @return array
     */
    static function getByPwdToken($pwd, $token) {
        $item = new OC_PublicShare_Item($token);
        $property = $item -> property;
        # 如果有資料，而且沒設密碼，或是密碼正確
        if ($property -> ifFromDb && ($property -> pwd == '' || $pwd == $property -> pwd)) {
            return $property = $item -> property;
        }
    }

    /**
     * 依 token 取得資料
     * @param token
     * @return array
     */
    static function getByToken($token) {
        $item = new OC_PublicShare_Item($token);
        if ($item -> property -> ifFromDb) {
            return $property = $item -> property;
        }
    }

    /**
     * 依 token 更新內容
     * @param token
     * @param source path
     * @param password
     * @param deadline local format
     * @return array
     */
    static function updateByToken($token, $sourcePath, $pwd, $deadlineLocal) {
        $item = new OC_PublicShare_Item($token);
        $item -> replace($sourcePath, $pwd, $deadlineLocal);
        return $property = $item -> property;
    }

    /**
     * 依 token 刪除資料， token 可為字串(以;分隔)
     * @param token
     * @return array
     */
    static function deleteByToken($token) {
        $tokenArr = OC_Helper::strToArr($token, ';');
        $propertyArr = array();
        foreach ($tokenArr as $index => $token) {
            $item = new OC_PublicShare_Item($token);
            $item -> delete();
            $propertyArr[] = $item -> property;
        }
        return $propertyArr;
    }

    /**
     * 刪除path底下所有的分享資料
     * @param path
     */
    static function deleteByPath($path) {
        $uid = OC_User::getUser();
        # 找出指定路徑所有的分享資料
        $dbs = OC_PublicShare_DB::getDbsUnderPath($uid, $path);
        foreach ($dbs as $index => $db) {
            $token = $db['token'];
            $item = new OC_PublicShare_Item($token);
            $item -> delete();
        }
    }

    /**
     * 取得 user 所有分享資料
     * @param sort by what
     * @param sort ASC/DESC
     * @return array
     */
    static function getListByUser($sortBy = 'time', $sort = 'ASC') {
        $items;
        $uid = OC_User::getUser();
        $dbs = OC_PublicShare_DB::getDbs($uid, $sortBy, $sort);
        foreach ($dbs as $index => $db) {
            $item = new OC_PublicShare_Item($db);
            # 將分享資料裡的相關變數轉為array
            $property = $item -> property;
            $items[] = $property;
        }
        return $items;
    }

    /**
     * 取得指定分享資料的檔案列表
     * 此 function 會由 get.js 發動，所以需要設置 file system
     * @param password
     * @param token
     * @param dir path
     * @return tree contents array
     */
    static function getFileList($pwd, $token, $dirPath) {
        # 尾巴補上/
        $dirPath = rtrim($dirPath, '/') . '/';
        $property = self::getByPwdToken($pwd, $token);
        if ($property) {
            $uid = $property -> uid;
            $sourcePath = $property -> sourcePath;
            $isOutOfDeadline = $property -> isOutOfDeadline;
            OC_Util::setupFS($uid);
            # 如果沒過期，指定的路徑是在分享的 sourcePath 底下，而且為資料夾
            if (!$isOutOfDeadline && strpos($dirPath, $sourcePath) === 0 && OC_Filesystem::is_dir($dirPath)) {
                $files = OC_Files::getDirectoryContent($dirPath);
                return $files;
            }
        }
    }

    /**
     * 找出指定路徑底下的所有資料，並更名路徑
     * @param path
     * @param new path
     */
    static function renameByPath($path, $newPath) {
        $uid = OC_User::getUser();
        # 找出指定路徑所有的分享資料
        $dbs = OC_PublicShare_DB::getDbsUnderPath($uid, $path);
        foreach ($dbs as $index => $db) {
            $token = $db['token'];
            $item = new OC_PublicShare_Item($token);
            $property = $item -> property;
            $sourcePath = $property -> sourcePath;
            # 取代為新的 sourcePath
            $sourcePath = str_replace($path, $newPath, $sourcePath);
            $item -> replace($sourcePath);
        }
    }

}
?>
