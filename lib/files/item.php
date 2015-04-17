<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 負責檔案訊息的初始化
 */
class OC_Files_Item {
    public $property;
    /**
     * 初始化，設置檔案的屬性
     * @param $dir 檔案所在的資料夾路徑
     * @param $fileName 真正的檔案名稱
     * @param $uid 指定檔案所屬的 user 帳號(有指定的話將建立該帳號的檔案系統)
     */
    function __construct($dir, $fileName, $uid = null) {
        $property = $this -> property = new OC_Files_Property();
        if ($uid) {
            OC_Util::setupFS($uid, null, true);
            $property -> uid = $uid;
        } else {
            $property -> uid = OC_User::getUser();
        }
        $dir = ($dir == '/') ? '' : $dir;
        $this -> _init($dir, $fileName);

    }

    /**
     * 設置遮置名稱和路徑
     * @param $calledBy 被什麼呼叫
     */
    function setMark($calledBy) {
        $property = $this -> property;
        $fileName = $property -> basename;
        $filePath = $property -> path;

        $markName = OC_Files_Mark::getMarkName($filePath, $calledBy);
        $property -> markName = $markName;

        $markPath = OC_Files_Mark::getMarkPath($filePath, $calledBy);
        $property -> markPath = $markPath;
    }

    /**
     * 取得檔案的屬性
     */
    private function _init($dir, $fileName) {
        $property = $this -> property;

        $filePath = OC_Files_Helper::normalizePath($dir . '/' . $fileName);
        $fullPath = OC_LocalSystem::getLocalFullPath($filePath);
        $fileType = OC_Filesystem::filetype($filePath);
        $isDir = OC_Filesystem::is_dir($filePath);
        if ($isDir) {
            $files = array_diff(scandir($fullPath), array(
                '.',
                '..'
            ));
            if (count($files) > 0) {
                # 底下有檔案
                $property -> isEmptyFolder = false;
            }
        }

        $property -> dirname = $dir;
        $property -> basename = $fileName;
        $property -> path = $filePath;
        $property -> type = $fileType;
        $property -> isDir = $isDir;
        $property -> fullPath = $fullPath;

        $size = OC_Filesystem::filesizeWithoutFolder($filePath);
        $sizeHuman = OC_Helper::humanFileSize($size);
        $mime = OC_Files::getMimeType($filePath);
        $readable = OC_Filesystem::is_readable($filePath);
        $writeable = OC_Filesystem::is_writeable($filePath);
        $fileUrl = OC_Files_Helper::getFileUrl($dir, $fileName);
        $imgSrc = OC_Files_Helper::getImgSrc($fileType, $mime);
        $encodeName = OC_Files_Helper::urlencodePath($fileName);

        # 將讀取到的檔案屬性設置到 property 裡面
        $stat = OC_Filesystem::stat($filePath);
        foreach ($stat as $key => $val) {
            # $key = dev, ino, uid, mtime....等等
            # 因為檔案屬性 uid 剛好和 Own Cloud 的 uid(帳號) 變數名稱相同，所以規避
            if ($key == 'uid') {
                $property -> fileUid = $val;
            } else {
                $property -> $key = $val;
            }
            # 代表 val 是 timestamp 格式，將之轉為時間格式
            if (strpos($key, 'time') !== false) {
                $timeHuman = OC_Util::formatDate($val);
                $timeUtc = OC_Helper::formatDateTimeLocalToUTC($timeHuman);
                $timeLocal = OC_Helper::formatDateTimeUTCToLocal($timeHuman);
                $key = $key . 'Human';
                $property -> $key = $timeHuman;
                # UTC 時間
                $keyUtc = $key . 'Utc';
                $property -> $keyUtc = $timeUtc;
                # Local 時間
                $keyLocal = $key . 'Local';
                $property -> $keyLocal = $timeLocal;
            }
        }

        # 取得檔名(不含副檔名)和副檔名
        $pathinfo = pathinfo($filePath);
        $property -> filename = $pathinfo['filename'];
        $property -> extension = $pathinfo['extension'];

        $property -> type = $fileType;
        $property -> size = $size;
        $property -> sizeHuman = $sizeHuman;
        $property -> mime = $mime;
        $property -> readable = ($readable == 1) ? 'true' : 'false';
        $property -> writeable = ($writeable == 1) ? 'true' : 'false';

        $property -> encodeName = $encodeName;
        $property -> fileUrl = $fileUrl;
        $property -> imgSrc = OC_Files_Helper::getImgSrc($fileType, $mime);
    }

}
