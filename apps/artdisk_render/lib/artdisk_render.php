<?php
/**
 * ownCloud - Artdisk Render plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 台藝大算圖農場
 * User 建立算圖農場資料夾(預設為 /var/www/html/data/帳號/files/render-帳號)
 * 並建立 symbo link 至帳號 render 底下(預設為 /var/www/html/data/render/files/render-帳號)
 * 台藝大後端電腦會自動抓取連結底下的檔案做3D算圖
 */

class OC_ArtdiskRender {

    /**
     * 取得算圖資料夾路徑
     * @return render path
     */
    static function getRenderPath() {
        $userId = OC_User::getUser();
        $renderItem = new OC_ArtdiskRender_Item($userId);
        return $renderItem -> property -> renderPath;
    }

    /**
     * 建立算圖資料夾，並建立 symbo link 至帳號 render 底下
     * @return render property
     */
    static function createRender() {
        $userId = OC_User::getUser();
        $renderItem = new OC_ArtdiskRender_Item($userId);
        $renderItem -> create();
        return $renderItem -> property;
    }

    /**
     * 刪除連結
     */
    static function removeLink($path) {
        $userId = OC_User::getUser();
        $renderItem = new OC_ArtdiskRender_Item($userId);
        $renderPath = $renderItem -> property -> renderPath;
        # 如果 delete 的路徑是在 render path 本層/上層
        if (strpos($renderPath, $path) === 0) {
            $renderItem -> removeLink();
        }
    }

    /**
     * 禁止使用者 rename Render 資料夾
     * @return bool
     */
    static function ifCanRename($path) {
        $userId = OC_User::getUser();
        $renderItem = new OC_ArtdiskRender_Item($userId);
        $renderPath = $renderItem -> property -> renderPath;
        # 如果 rename 的路徑是在 render folder path 當中
        if (strpos($renderPath, $path) === 0) {
            return false;
        }
        return true;
    }

}
?>