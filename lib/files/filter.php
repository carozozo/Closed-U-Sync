<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 負責隱藏檔案的相關操作
 */
class OC_Files_Filter {
    # 儲存要隱藏的檔案路徑
    static $hidePathArr = array();
    # 儲存要顯示的檔案路徑
    static $showPathArr = array();

    /**
     * 判斷路徑是否有在隱藏名單內
     * @param $path 路徑
     * @param $calledBy 是由什麼呼叫
     * @return true
     */
    static function isInHidePath($path, $calledBy) {
        $hidePathArr = self::$hidePathArr;

        if (!array_key_exists($path, $hidePathArr)) {
            # 該路徑沒有在隱藏名單內
            return false;
        }
        $hidePathArr = $hidePathArr[$path];
        if (in_array('ALL', $hidePathArr)) {
            # 有指定一定要隱藏
            return true;
        }
        if (in_array($calledBy, $hidePathArr)) {
            # 該路徑有指定隱藏
            return true;
        }
    }

    /**
     * 清空要隱藏的指定路徑
     * @param $pathArr 要清空的隱藏路徑
     */
    static function cleanHidePath($pathArr = null) {
        $hidePathArr = self::$hidePathArr;
        # 全部清空
        if ($pathArr == null) {
            $hidePathArr = array();
            return;
        }
        # 清除指定路徑
        if (!$pathArr = OC_Files_Helper::coverToArr($pathArr)) {
            return;
        }
        foreach ($pathArr as $path) {
            unset($hidePathArr[$path]);
        }
    }

    /**
     * 新增要隱藏的路徑
     * @param $pathArr 路徑，可為 array 或 string
     * @param $inCalledByArr 可為 array 或 string，指定當被什麼呼叫的時候，才要隱藏(預設置為ALL，就是無論是由什麼呼叫，都要隱藏)
     */
    static function addHidePath($pathArr, $inCalledByArr = 'ALL') {
        if (!$pathArr = OC_Files_Helper::coverToArr($pathArr)) {
            return;
        }
        if (!$inCalledByArr = OC_Files_Helper::coverToArr($inCalledByArr)) {
            return;
        }
        foreach ($pathArr as $key => $path) {
            $path = OC_Files_Helper::normalizePath($path);
            $path = rtrim($path, '/');
            foreach ($inCalledByArr as $inCalledBy) {
                if (!array_key_exists($path, self::$hidePathArr)) {
                    self::$hidePathArr[$path] = array();
                }
                if (!in_array($inCalledBy, self::$hidePathArr[$path])) {
                    self::$hidePathArr[$path][] = $inCalledBy;
                }
            }
        }
    }

    /**
     * 判斷路徑是否有在顯示名單內
     * @param $path 路徑
     * @param $calledBy 是由什麼呼叫
     * @return true
     */
    static function isInShowPath($path, $calledBy) {
        $showPathArr = self::$showPathArr;
        if (!array_key_exists($path, $showPathArr)) {
            # 該路徑沒有指定要被什麼呼叫時才顯示
            return true;
        }
        $showPathArr = $showPathArr[$path];
        if (in_array($calledBy, $showPathArr)) {
            # 該路徑有指定
            return true;
        }
    }

    /**
     * 清空要顯示的指定路徑
     * @param $pathArr 要清空的顯示路徑
     */
    static function cleanShowPath($pathArr = null) {
        $showPathArr = self::$showPathArr;
        # 全部清空
        if ($pathArr == null) {
            $showPathArr = array();
            return;
        }
        # 清除指定路徑
        if (!$pathArr = OC_Files_Helper::coverToArr($pathArr)) {
            return;
        }
        foreach ($pathArr as $path) {
            unset($showPathArr[$path]);
        }
    }

    /**
     * 新增要顯示的路徑
     * @param $pathArr 路徑，可為 array 或 string
     * @param $inCalledByArr 可為 array 或 string，指定當被什麼呼叫的時候，才要顯示
     */
    static function addShowPath($pathArr, $inCalledByArr = null) {
        if (!$pathArr = OC_Files_Helper::coverToArr($pathArr)) {
            return;
        }
        if (!$inCalledByArr || !$inCalledByArr = OC_Files_Helper::coverToArr($inCalledByArr)) {
            return;
        }
        foreach ($pathArr as $key => $path) {
            $path = OC_Files_Helper::normalizePath($path);
            $path = rtrim($path, '/');
            foreach ($inCalledByArr as $inCalledBy) {
                if (!array_key_exists($path, self::$showPathArr)) {
                    self::$showPathArr[$path] = array();
                }
                if (!in_array($inCalledBy, self::$showPathArr[$path])) {
                    self::$showPathArr[$path][] = $inCalledBy;
                }
            }
        }
    }

}
