<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 負責檔名遮罩的相關操作
 */
class OC_Files_Mark {
    # 儲存要另外顯示的檔案名稱
    static $markFileNameArr = array();

    /**
     * 判斷是否有要另外顯示的檔名，並回傳
     * @param $filePath 檔案路徑
     * @return $marknName 另外顯示的檔名
     */
    static function getMarkName($path, $calledBy) {
        $markFileNameArr = self::$markFileNameArr;
        $name = basename($path);
        # 該路徑沒有要另外顯示的檔名
        if (!array_key_exists($path, $markFileNameArr)) {
            return $name;
        }
        $markFileNameArr = $markFileNameArr[$path];
        if (array_key_exists('ALL', $markFileNameArr)) {
            $markName = $markFileNameArr['ALL'];
            # 有指定一定要顯示另外的檔名
            return $markName = $markFileNameArr['ALL'];
        }
        if (array_key_exists($calledBy, $markFileNameArr)) {
            $markName = $markFileNameArr[$calledBy];
            # 有在指定的 calledBy 中，才要另外顯示檔名
            return $markName = $markFileNameArr[$calledBy];
        }
        return $name;
    }

    static function getMarkPath($path, $calledBy) {
        $markFileNameArr = self::$markFileNameArr;
        foreach ($markFileNameArr as $realPath => $inCalledByArr) {
            if (strpos($path, $realPath) === 0) {
                foreach ($inCalledByArr as $inCalledBy => $markName) {
                    if ($inCalledBy == 'ALL' || $inCalledBy == $calledBy) {
                        $markPath = str_replace($realPath, $markName, $path);
                        $markPath = OC_Files_Helper::normalizePath($markPath);
                        return $markPath;
                    }
                }
            }
        }
        return $path;

        # 該路徑沒有要另外顯示的檔名
        if (!array_key_exists($path, $markFileNameArr)) {
            return $path;
        }
        $markFileNameArr = $markFileNameArr[$path];
        if (array_key_exists('ALL', $markFileNameArr)) {
            # 有指定一定要顯示另外的檔名，則將原本的路徑轉為另外顯示的路徑
            return $markPath = str_replace($name, $markFileNameArr['ALL'], $path);
        }
        if (array_key_exists($calledBy, $markFileNameArr)) {
            # 有在指定的 calledBy 中，才要另外顯示檔名
            return $markPath = str_replace($name, $markFileNameArr[$calledBy], $path);
        }
    }

    /**
     * 新增/更改要另外要顯示檔名的路徑
     * @param $pathArr 檔案路徑，可為 array 或 string
     * @param $fileName 要顯示的檔名
     * @param $inCalledByArr 可為 array 或 string，指定當被什麼呼叫的時候，才要另外顯示檔名(預設置為ALL，就是無論是由什麼呼叫，都要另外顯示檔名)
     */
    static function addMarkFileName($pathArr, $fileName, $inCalledByArr = 'ALL') {
        if (!$pathArr = OC_Files_Helper::coverToArr($pathArr)) {
            return;
        }
        if (!$inCalledByArr = OC_Files_Helper::coverToArr($inCalledByArr)) {
            return;
        }
        if (!is_string($fileName)) {
            return;
        }
        foreach ($pathArr as $key => $path) {
            $path = OC_Files_Helper::normalizePath($path);
            $path = rtrim($path, '/');
            foreach ($inCalledByArr as $inCalledBy) {
                if (!array_key_exists($path, self::$markFileNameArr)) {
                    self::$markFileNameArr[$path] = array();
                }
                if (!array_key_exists($inCalledBy, self::$markFileNameArr[$path])) {
                    self::$markFileNameArr[$path][$inCalledBy] = $fileName;
                }
            }
        }
    }

}
