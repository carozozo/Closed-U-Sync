<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 資源回收桶中控
 */
class OC_Recycle {

    /**
     * 產生資源回收桶資料夾
     */
    static function createRecycleFolder() {
        $rootDir = OC_Recycle_Config::rootDir();
        if (!OC_Filesystem::file_exists($rootDir)) {
            OC_Filesystem::mkdir($rootDir);
        }
        # 在webDav中，設定Recycled資料夾無法更名/移動
        $webDav_FS_Plugin = new OC_Connector_Sabre_FileSystemPlugin();
        $webDav_FS_Plugin -> addRejectMovePath($rootDir);
        # 隱藏資料夾
        OC_Files::addHidePath($rootDir);
        OC_Connector_Sabre_Directory::addHidePath($rootDir);
    }

    /**
     * 將檔案移到資源回收桶
     * 如果參數為2個，則判定為 dir, files(以 | 分隔的字串)
     * 參數為1個，則判定為 source path
     */
    static function recyle() {
        $numargs = func_num_args();
        if ($numargs == 2) {
            $dir = func_get_arg(0);
            $files = func_get_arg(1);
            $fileArr = OC_Helper::strToArr($files, '|');
            foreach ($fileArr as $file) {
                $sourcePath = $dir . '/' . $file;
                self::recyle($sourcePath);
            }
            return;
        }
        
        $sourcePath = func_get_arg(0);
        $sourcePath = OC_Helper::pathForbiddenChar($sourcePath);
        # 如果路徑為空值或是根目錄，則不執行刪除
        if (!$sourcePath or $sourcePath == '/') {
            return;
        }
        $uid = OC_LocalSystem::getLocalUserIdByPath($sourcePath);
        $localSourcePath = OC_LocalSystem::getLocalPath($sourcePath);
        # 執行回收前先取得檔案類型
        $fileType = OC_Filesystem::filetype($sourcePath);
        $item = new OC_Recycle_Item($uid, $localSourcePath);
        $property = $item -> property;
        $item -> recycle();
        if ($property -> status == OC_Recycle_Status::recycle_successed) {
            # 發動 file system 的 post delete
            OC_Hook::emit("OC_Filesystem", OC_Filesystem::signal_post_delete, array(
                OC_Filesystem::signal_param_path => $sourcePath,
                OC_Filesystem::signal_param_fileType => $fileType
            ));
        }
    }

    /**
     * 取得回收桶列表
     * 沒有指定回收序號的話，則找出 DB 中所有該帳號的回收資料
     * @param $sn 回收序號
     * @param $assignPath 指定的檔案
     * @param $sortBy 依什麼排序
     * @param $sort 升/降序
     */
    static function getRecList($sn = null, $assignPath = null, $sortBy = 'time', $sort = 'ASC') {
        if (!$sn) {
            $uid = OC_User::getUser();
            $dbs = OC_Recycle_DB::getDbsByUid($uid, $sortBy, $sort);
            $items = array();
            foreach ($dbs as $db) {
                $item = new OC_Recycle_Item($db);
                $items[] = $item -> property;
            }
            return $items;
        }
        $sort = strtoupper($sort);
        return self::_getContent($sn, $assignPath, $sortBy, $sort);
    }

    /**
     * 回復回收桶資料
     * @param $sn 以[|]分隔的序號字串
     * @param $assignPath 以[|]分隔的回收的檔案路徑
     */
    static function revert($sn, $assignPath) {
        $snArr = OC_Helper::strToArr($sn, '|');
        $assignPathArr = OC_Helper::strToArr($assignPath, '|');
        $items = array();
        foreach ($snArr as $index => $eachSn) {
            # 將 sn 轉為數字
            $eachSn = intval($eachSn);
            $eachAssignPath = (isset($assignPathArr[$index])) ? $assignPathArr[$index] : '';
            $item = new OC_Recycle_Item($eachSn, $eachAssignPath);
            $property = $item -> property;
            if ($property -> ifFromDb) {
                $item -> revert();
                $items[] = $property;
            }
        }
        return $items;
    }

    /**
     * 刪除回收桶資料
     * @param $sn 以[|]分隔的序號字串
     * @param $assignPath 以[|]分隔的回收的檔案路徑
     */
    static function delete($sn = null, $assignPath = null) {
        if (!$sn && !$assignPath) {
            return self::_cleanUp();
        }
        $snArr = OC_Helper::strToArr($sn, '|');
        $assignPathArr = OC_Helper::strToArr($assignPath, '|');
        $items = array();
        foreach ($snArr as $index => $eachSn) {
            # 將 sn 轉為數字
            $eachSn = intval($eachSn);
            $eachAssignPath = (isset($assignPathArr[$index])) ? $assignPathArr[$index] : '';
            $item = new OC_Recycle_Item($eachSn, $eachAssignPath);
            $property = $item -> property;
            if ($property -> ifFromDb) {
                $item -> delete();
                $items[] = $property;
            }
        }
        return $items;
    }

    /**
     * 清空帳號下全部的回收資料
     * @return true
     */
    private static function _cleanUp() {
        $uid = OC_User::getUser();
        $rootDir = OC_Recycle_Config::rootDir();
        $rootDirFullPath = OC_LocalSystem::getLocalFullPath($rootDir);
        # 清除回收桶根目錄底下的所有檔案
        OC_Helper::deleteDirByFullPath($rootDirFullPath, false);
        # 清除 DB 資料
        OC_Recycle_DB::delDbByUid($uid);
        return true;
    }

    /**
     * 取得回收資料結構
     * @param $sn 回收序號
     * @param $assignPath 回收資料底下的檔案路徑
     * @return true
     */
    private static function _getContent($sn, $assignPath, $sortBy = 'time', $sort = 'ASC') {
        $item = new OC_Recycle_Item($sn, $assignPath);
        $property = $item -> property;
        $recycleDirPath = $property -> recycleDirPath;
        $recyclePath = $property -> recyclePath;

        # 幫助排序用的 array(因為 $items 有放入 parent 的值，所以這邊也要先放一個空值，才能用 array_multisort)
        $sortByArr = array('');
        # 放入指定的檔案路徑回收資料
        $items = array('parent' => $property);
        $filePath = OC_Helper::pathForbiddenChar($recyclePath . '/' . $assignPath);
        # 取得指定路徑底下的檔案回收內容
        if (OC_Filesystem::is_dir($filePath)) {
            $files = OC_Files::getDirectoryContent($filePath);
            foreach ($files as $index => $fileProperty) {
                $subFileName = $fileProperty -> basename;
                $subFilePath = OC_Helper::pathForbiddenChar($assignPath . '/' . $subFileName);
                $item = new OC_Recycle_Item($sn, $subFilePath);
                $property = $item -> property;
                if ($sortBy == 'time') {
                    # 放入要排序的項目
                    $sortByArr[] = $property -> recycleTimeLocalStr;
                } else {
                    $subAssignPath = $property -> assignPath;
                    $sortByArr[] = $subAssignPath;
                }
                # 放入該指定的檔案路徑底下的子回收資料
                $items[] = $property;
            }

            $sort = strtoupper($sort);
            if ($sort == 'ASC') {
                array_multisort($sortByArr, SORT_LOCALE_STRING, SORT_ASC, $items);
            } else {
                array_multisort($sortByArr, SORT_LOCALE_STRING, SORT_DESC, $items);
            }

        }
        return $items;
    }

}
?>