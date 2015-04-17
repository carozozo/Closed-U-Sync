<?php
/**
 * ownCloud - Artdisk Render plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * Artdisk Render相關設定
 * 所有config取值的function名稱即為config key值
 * 所有儲存config值的function即為 set+(開頭大寫)configKey值
 */

class OC_ArtdiskRender_Config {
    const appId = 'artdisk_render';

    /**
     * 取得 art disk 的所有config值
     * @return array
     */
    static function getConfigItems() {
        return OC_Appconfig::getConfigItemsByAppId(self::appId);
    }

    /**
     * 取得 render folder/link 前綴詞
     * @return string
     */
    static function prefixName() {
        return OC_Appconfig::getValue(self::appId, 'prefixName', 'render-');
    }

    /**
     * 設定render folder/link 前綴詞
     * @param string
     */
    static function setPrefixName($prefixName) {
        $prefixName = trim($prefixName);
        if ($prefixName) {
            OC_Appconfig::setValue(self::appId, 'prefixName', $prefixName);
        }
    }

    /**
     * 取得要放在 user 底下的 render folder 路徑
     * @return string
     */
    static function renderRootPath() {
        return OC_Appconfig::getValue(self::appId, 'renderRootPath', '/');
    }

    /**
     * 設定render folder 要放在 user 底下的 render folder 路徑
     * @param string
     */
    static function setRenderRootPath($renderRootPath) {
        $renderRootPath = trim($renderRootPath);
        $renderRootPath = OC_Helper::pathForbiddenChar($renderRootPath, true, true);
        OC_Appconfig::setValue(self::appId, 'renderRootPath', $renderRootPath);
    }

    /**
     * 取得放置連結的位置
     * @return string
     */
    static function linkDirFullPath() {
        return OC_Appconfig::getValue(self::appId, 'linkDirFullPath', '/var/www/html/data/render/files/');
    }

    /**
     * 設定放置連結的位置
     * @param string
     */
    static function setLinkDirFullPath($linkDirFullPath) {
        $linkDirFullPath = trim($linkDirFullPath);
        $linkDirFullPath = '/' . $linkDirFullPath;
        $linkDirFullPath .= '/';
        $linkDirFullPath = OC_Helper::pathForbiddenChar($linkDirFullPath);
        OC_Appconfig::setValue(self::appId, 'linkDirFullPath', $linkDirFullPath);
    }

}
?>