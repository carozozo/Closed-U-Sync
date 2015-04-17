<?php
/**
 * ownCloud - Public Share plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 負責所有分享連結操作
 * 包含和 短連結 server 溝通，DB資料處理
 */

class OC_PublicShare_Item {
    # 分享連結屬性
    public $property;

    /**
     * 根據前端傳送過來的資訊建立分享連結資料
     * 如果第一個參數為 array ，則判定為 DB 資料
     * 如果只傳一個參數，判定為 token
     * 傳兩個參數，判定為 user id, source path
     * 而 user id，及 source path 為前端傳送過來的值(不一定為檔案實際擁有者)
     */
    function __construct() {
        $property = $this -> property = new OC_PublicShare_Property();

        $numargs = func_num_args();
        $db = null;
        if ($numargs == 1) {
            $arg1 = func_get_arg(0);
            if (is_array($arg1)) {
                # 判定為 DB 資料，將資料帶入屬性
                $db = $arg1;
            } else {
                $db = OC_PublicShare_DB::getDbByToken($arg1);
            }
        }
        if ($numargs == 2) {
            $uid = func_get_arg(0);
            $sourcePath = func_get_arg(1);
            $localSourcePath = OC_LocalSystem::getLocalPath($sourcePath);
            if (OC_FILESYSTEM::is_dir($sourcePath)) {
                $localSourcePath .= '/';
                $localSourcePath = OC_Helper::pathForbiddenChar($localSourcePath);
            }
            $db = OC_PublicShare_DB::getDbBySource($uid, $localSourcePath);
        }

        # 找出 DB 資料
        if ($db) {
            # 將 DB 的資料帶入屬性
            $this -> _initByDb($db);
            return;
        }

        # 初始屬性
        $this -> _init($uid, $sourcePath);
    }

    /**
     * 新增/更新相關的值並寫入 DB
     * @param source path
     * @param password
     * @param deadline
     */
    function replace($sourcePath = null, $pwd = null, $deadlineLocal = null) {
        $property = $this -> property;
        if ($sourcePath !== null) {
            $property -> sourcePath = $sourcePath;
        }
        if ($pwd !== null) {
            $property -> pwd = $pwd;
        }
        if ($deadlineLocal !== null) {
            $property -> deadlineLocal = $deadlineLocal;
            $property -> deadlineUtc = OC_Helper::formatDateTimeLocalToUTC($deadlineLocal);
        }
        # 向短連結網站取得短連結網址，並寫入屬性
        $property -> url = OC_PublicShare_Helper::getUrl($property -> token);
        $property -> shortUrl = OC_PublicShare_Helper::getShortUrl($property -> url);
        $this -> _replaceDb();
    }

    /**
     * 刪除 public share
     */
    function delete() {
        $this -> _deleteDb();
    }

    /**
     * 新增/更新 DB 資料
     */
    private function _replaceDb() {
        $property = $this -> property;
        $uid = $property -> uid;
        $sourcePath = $property -> sourcePath;
        $token = $property -> token;
        $pwd = $property -> pwd;
        $deadlineUtc = $property -> deadlineUtc;
        $insertTimeUtc = $property -> insertTimeUtc;
        if (OC_PublicShare_DB::insertDb($uid, $sourcePath, $token, $pwd, $deadlineUtc, $insertTimeUtc)) {
            # 新增成功，設置狀態
            $property -> status = 'Update successed';
        }
    }

    /**
     * 刪除 DB 資料
     */
    private function _deleteDb() {
        $property = $this -> property;
        $token = $property -> token;
        if (OC_PublicShare_DB::deleteDbByToken($token)) {
            # 刪除成功，設置狀態
            $property -> status = 'Delete successed';
        }
    }

    /**
     * 將前端取得的資料放到通用變數中
     * @param user id
     * @param source path
     */
    private function _init($uid, $sourcePath) {
        $property = $this -> property;
        if (!OC_User::userExists($uid)) {
            $property -> status = 'User not exists';
            return;
        }
        if (!$sourcePath || !OC_FILESYSTEM::file_exists($sourcePath)) {
            $property -> status = 'File not exists';
            return;
        }
        if (!OC_FILESYSTEM::is_readable($sourcePath)) {
            $property -> status = 'File not readable';
            return;
        }
        $localSourcePath = OC_LocalSystem::getLocalPath($sourcePath);
        $sourceFullPath = OC_LocalSystem::getLocalFullPath($sourcePath);
        if (OC_FILESYSTEM::is_dir($sourcePath)) {
            $localSourcePath .= '/';
            $sourceFullPath .= '/';
        }
        $property -> uid = $uid;
        $sourcePath = OC_LocalSystem::getLocalPath($localSourcePath);
        $property -> sourcePath = $sourcePath;
        $property -> token = OC_PublicShare_Helper::createRandomToken();
        # 因為還未寫入DB，所以先不寫入 insert time/short url...相關資訊
        $property -> fileType = OC_Filesystem::filetype($sourcePath);
        $property -> sourceFullPath = OC_LocalSystem::getLocalFullPath($sourceFullPath);
        $property -> shareLimitDays = $shareLimitDays = OC_PublicShare_Config::shareLimitDays() - 1;
        $property -> url = OC_PublicShare_Helper::getUrl($property -> token);
        # 計算出預計的到期日
        $deadlineUtc = OC_PublicShare_Helper::getDeadlineUtcByLimitDays($property -> shareLimitDays);
        $property -> deadlineUtc = $deadlineUtc;
        $property -> deadlineLocal = OC_Helper::formatDateTimeUTCToLocal($deadlineUtc, 'Y-m-d');

    }

    /**
     * 將DB中取得的資料放到通用變數中
     * @param DB data
     */
    private function _initByDb($db) {
        $property = $this -> property;
        $property -> ifFromDb = true;
        $sourcePath = $db['source_path'];

        $uid = $db['uid'];
        $dir = dirname($sourcePath);
        $fileName = basename($sourcePath);
        # 將 DB 取得的 uid 帶入，OC_Files_Item 會設置  filesystem，後續的 OC_Filesystem 才有作用
        $fileItem = new OC_Files_Item($dir, $fileName, $uid);
        $fileProperty = $fileItem -> property;
        # 帶入檔案屬性
        foreach ($fileProperty as $key => $val) {
            $property -> $key = $val;
        }

        $property -> uid = $uid;
        $property -> sourcePath = $sourcePath;
        $property -> token = $db['token'];
        $property -> pwd = $db['pwd'];
        $property -> insertTimeUtc = $db['insert_time'];
        $property -> updateTimeUtc = $db['update_time'];
        $property -> deadlineUtc = $db['deadline'];

        $property -> insertTimeLocal = OC_Helper::formatDateTimeUTCToLocal($db['insert_time'], 'Y-m-d');
        $property -> updateTimeLocal = OC_Helper::formatDateTimeUTCToLocal($db['update_time'], 'Y-m-d');
        $property -> deadlineLocal = OC_Helper::formatDateTimeUTCToLocal($db['deadline'], 'Y-m-d');
        $property -> url = OC_PublicShare_Helper::getUrl($property -> token);
        $property -> shortUrl = OC_PublicShare_Helper::getShortUrl($property -> url);
        $property -> shareLimitDays = OC_PublicShare_Config::shareLimitDays() - 1;
        $property -> isOutOfDeadline = OC_PublicShare_Helper::isOutOfDeadline($db['deadline']);
    }

}
?>