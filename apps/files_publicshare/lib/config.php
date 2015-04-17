<?php
/**
 * ownCloud - Public Share plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * Public Share 相關設定
 * 所有config取值的function名稱即為config key值
 * 所有儲存config值的function即為 set+(開頭大寫)configKey值
 */

class OC_PublicShare_Config {
    const appId = 'files_publicshare';
    /* ================== Public Share Config 相關 ================== */

    /**
     * 取得 public share 的所有 config 值
     * @return array
     */
    static function getConfigItems() {
        return $items = OC_Appconfig::getConfigItemsByAppId(self::appId);
    }

    /**
     * 取得預設的分享天數限制
     * @return int
     */
    static function shareLimitDays() {
        return intval(OC_Appconfig::getValue(self::appId, 'shareLimitDays', 7));
    }

    /**
     * 設定連結有效天數
     * @param share limit days
     */
    static function setShareLimitDays($shareLimitDays) {
        if (is_numeric($shareLimitDays) && $shareLimitDays > 1) {
            OC_Appconfig::setValue(self::appId, 'shareLimitDays', $shareLimitDays);
        }
    }

    /**
     * 取得是否開啟廣告頁面
     * @return bool
     */
    static function adEnabled() {
        if (OC_Appconfig::getValue(self::appId, 'adEnabled') == 1) {
            return true;
        }
        return false;
    }

    /**
     * 設定是否開啟廣告頁面
     * @param bool or 1 or 0
     */
    static function setAdEnabled($adEnabled) {
        if ($adEnabled == 1 || $adEnabled == 0 || gettype($adEnabled) == 'boolean') {
            OC_Appconfig::setValue(self::appId, 'adEnabled', $adEnabled);
        }
    }

}
?>