<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 資源回收桶相關設定
 */
class OC_Recycle_Config {
    const appId = 'files_recycle';
    const rootDir = '/Recycled';

    /**
     * 取得回收桶根目錄(前後加上/)
     * @return string 回收桶根目錄(在 user 的檔案列表下)
     */
    static function rootDir() {
        $rootDir = self::rootDir;
        $rootDir = OC_Helper::pathForbiddenChar($rootDir, true, true);
        return $rootDir;
    }

}
?>