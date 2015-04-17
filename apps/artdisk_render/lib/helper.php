<?php
/**
 * ownCloud - Artdisk Render plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * render 通用 function
 */

class OC_ArtdiskRender_Helper {

    /**
     * 回傳算圖 folder/link的名稱
     * @param user id
     * @return string
     */
    static function renderName($userId) {
        $prefix = OC_ArtdiskRender_Config::prefixName();
        return $renderName = $prefix . $userId;
    }

    /**
     * 回傳算圖資料夾路徑
     * @param user id, if full path
     * @return string
     */
    static function renderPath($userId, $ifFullPath = false) {
        $renderRootPath = OC_ArtdiskRender_Config::renderRootPath();
        $renderName = self::renderName($userId);
        $renderPath = $renderRootPath . '/' . $renderName;
        $renderPath = OC_Helper::pathForbiddenChar($renderPath);
        if (!$ifFullPath) {
            return $renderPath;
        }
        return OC_LocalSystem::getLocalFullPath($renderPath);
    }

    /**
     * 回傳連結完整路徑
     * @param user id
     * @return string
     */
    static function linkFullPath($userId) {
        $linkDirFullPath = OC_ArtdiskRender_Config::linkDirFullPath();
        $renderName = self::renderName($userId);
        $linkFullPath = $linkDirFullPath . '/' . $renderName;
        return $linkFullPath = OC_Helper::pathForbiddenChar($linkFullPath);
    }

}
?>