<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 資源回收桶的屬性，包含檔案屬性
 */
class OC_Recycle_Property extends OC_Files_Property {
    # 資料是否是由 DB 取得
    public $ifFromDb = false;

    # 記錄處理狀態
    public $status;

    # 序號
    public $sn;
    # 檔案所屬的 user
    public $uid;
    # 來源真實路徑(不是完整路徑)
    public $sourcePath;
    /**
     * 回收資料底下的檔案路徑
     * EX：
     * 回收桶回收了資料夾「/1/2」，而底下還有「a.txt」及「/3/b.txt」
     * 回收檔放在「/Recycled/20140128101836/1/2/」
     * 當指定 「/3/b.txt」 時
     * OC_Files_Property 裡儲存的則是 「/Recycled/20140128101836/1/2/3/b.txt」 的檔案資訊
     * 而沒有指定檔案時
     * OC_Files_Property 裡儲存的則是 「/Recycled/20140128101836/1/2」 的檔案資訊
     */
    public $assignPath = '';
    # 回收桶的資料夾路徑(EX：/Recycled/20140128101836)
    public $recycleDirPath;
    # 檔案回收後的路徑(EX：/Recycled/20140128101836/1/113)
    public $recyclePath;
    # 回收的時間(UTC)
    public $recycleTimeUtc;
    # 回收的時間(本地)
    public $recycleTimeLocal;
    # 回收時間戳記
    public $recycleTimeLocalStr;

    # 來源完整路徑
    public $sourceFullPath;
    # 回收桶的資料夾完整路徑
    public $recycleDirFullPath;
    # 回收的檔案完整路徑
    public $recycleFullPath;
    # 檔案從回收桶回復後的路徑
    public $revertFullPath;

    function set($uid, $sourcePath, $recycleTimeUtc = null) {
        if (!$recycleTimeUtc) {
            $recycleTimeUtc = OC_Helper::formatDateTimeLocalToUTC();
        }

        $sourceFullPath = OC_LocalSystem::getFullPathByUserId($uid, $sourcePath);
        $recycleTimeLocal = OC_Helper::formatDateTimeUTCToLocal($recycleTimeUtc);
        $recycleTimeLocalStr = OC_Recycle_Helper::dateTimeToFolderName($recycleTimeLocal);
        $recycleDirPath = OC_Recycle_Config::rootDir();
        $recycleDirPath = $recycleDirPath . $recycleTimeLocalStr;
        $recyclePath = $recycleDirPath . $sourcePath;
        $recycleDirFullPath = OC_LocalSystem::getFullPathByUserId($uid, $recycleDirPath);
        $recycleFullPath = $recycleDirFullPath . $sourcePath;
        # 推算回復後的檔案路徑
        $revertFullPath = OC_Recycle_Helper::renamePathIfConflict($sourceFullPath, $recycleTimeLocalStr);

        $fileType = filetype($sourceFullPath);
        if (!$fileType) {
            $fileType = filetype($recycleFullPath);
        }

        $this -> uid = $uid;
        $this -> sourcePath = $sourcePath;
        $this -> recycleTimeUtc = $recycleTimeUtc;
        $this -> recycleTimeLocal = $recycleTimeLocal;
        $this -> recycleTimeLocalStr = $recycleTimeLocalStr;
        $this -> recycleDirPath = $recycleDirPath;
        $this -> recyclePath = $recyclePath;
        $this -> fileType = $fileType;
        $this -> sourceFullPath = $sourceFullPath;
        $this -> recycleDirFullPath = $recycleDirFullPath;
        $this -> recycleFullPath = $recycleFullPath;
        $this -> revertFullPath = $revertFullPath;

        # 找出要抓取的檔案訊息(預設為來源路徑)
        $path = $sourcePath;
        # DB 有資料， 代表檔案是在回收桶，所以改指為回收路徑
        if ($this -> ifFromDb) {
            $path = $recyclePath;
        }
        $assignPath = $this -> assignPath;
        $path = OC_Helper::pathForbiddenChar($path . '/' . $assignPath);

        $dir = dirname($path);
        $fileName = basename($path);
        $fileItem = new OC_Files_Item($dir, $fileName);
        $fileProperty = $fileItem -> property;
        # 帶入檔案屬性
        foreach ($fileProperty as $key => $val) {
            # 將路徑中「回收桶的路徑」的部份拿掉
            if ($key == 'path' or $key == 'dirname') {
                # EX：「/Recycled/20140129163257/1/dd/rrr」變為「/1/dd/rrr」
                $val = str_replace($recycleDirPath, '', $val);
            }
            $this -> $key = $val;
        }
    }

    /**
     * 取得目前狀態的文字敘述
     */
    function getStatusMsg() {
        if ($status = $this -> status) {
            $status = OC_Recycle_Status::getStatusMsg($status);
            return $status;
        }
    }

}
?>