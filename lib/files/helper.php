<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 負責檔案的通用function
 */
class OC_Files_Helper {
    /**
     * 取得 Web 端檔案列表 url
     */
    static function baseUrl() {
        return OC_Helper::linkTo('files', 'index.php?dir=');
    }

    /**
     * 取得 Web 端檔案下載 url
     */
    static function downloadUrl() {
        return OC_Helper::linkTo('files', 'download.php?file=');
    }

    /**
     * 將字串轉為 url 可讀模式
     * @param $strPath 字串
     */
    static function urlencodePath($strPath) {
        $strPath = str_replace('+', '%20', urlencode($strPath));
        $strPath = str_replace('%2F', '/', $strPath);
        return $strPath;
    }

    /**
     * 取得檔案的 url
     * @param $dir 資料夾路徑
     * @param $fileName 檔名
     */
    static function getFileUrl($dir, $fileName) {
        $filePath = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
        $fileType = OC_Filesystem::filetype($filePath);
        $baseUrl = self::baseUrl();
        $downloadUrl = self::downloadUrl();
        $fileName = self::urlencodePath($fileName);
        $dir = self::urlencodePath($dir);
        $fileUrl = ($fileType == 'dir') ? $baseUrl . $dir . '/' . $fileName : $downloadUrl . $dir . '/' . $fileName;
        return $fileUrl;
    }

    /**
     * 取得檔案的要顯示在顯端的縮圖路徑
     * @param $fileType 檔案類型
     * @param $mime 檔案的 mime type
     */
    static function getImgSrc($fileType, $mime) {
        $imgSrc = ($fileType == 'dir') ? OC_Helper::mimetypeIcon('dir') : OC_Helper::mimetypeIcon($mime);
        return $imgSrc;
    }

    /**
     * normalize a path, removing any double, add leading /, etc
     * @param string $path
     * @return string
     */
    static function normalizePath($path) {
        if ($path) {
            $path = OC_Helper::pathForbiddenChar($path);
            return $path;
        }
    }

    /**
     * 如果是台灣地區，則依中文檔名排序
     * @param $files OC_Files_Property 的陣列
     */
    static function sortFileByBig5FileName($files) {
        # 如果系統預設的語系是zh_TW
        if (OC_Config::getValue('defaultLanguage', null, 'CONFIG_CUSTOM') === 'zh_TW') {
            $fileNameArr = array();
            foreach ($files as $key => $file) {
                # 將file name 從utf8 轉成 big5 (如果遇到big5無法呈現在字元，則找出替代字或乎略)
                $fileNameArr[] = iconv("UTF-8", "big5//TRANSLIT//IGNORE", $file -> markName);
            }
            array_multisort($fileNameArr, SORT_STRING, SORT_ASC, $files);
        }
        return $files;
    }

    /**
     * 將 string 轉換成 array
     * 如果引數不是 string 或 array，則不回傳
     * @param $arg 要轉換的變數
     * @return array
     */
    static function coverToArr($argArr) {
        if (!is_array($argArr)) {
            if (!is_string($argArr)) {
                return;
            }
            $argArr = array($argArr);
        }
        # 檢查如果 array 裡面有不是 string 的值，則不回傳
        foreach ($argArr as $arg) {
            if (!is_string($arg)) {
                return;
            }
        }
        return $argArr;
    }

}
