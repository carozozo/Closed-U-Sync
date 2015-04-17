<?php
/**
 * ownCloud - Public Share Hooks
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 分享連結Hook相關處理
 */

class OC_PublicShare_Hooks {
    /**
     * Remove the item from the database, the owner deleted the file
     * @param $arguments Array of arguments passed from OC_Hook
     */
    public static function deleteItem($arguments) {
        $path = $arguments['path'];
        OC_PublicShare::deleteByPath($path);
    }

    /**
     * Rename the item in the database, the owner renamed the file
     * @param $arguments Array of arguments passed from OC_Hook
     */
    public static function renameItem($arguments) {
        $path = $arguments['oldpath'];
        $newPath = $arguments['newpath'];
        OC_PublicShare::renameByPath($path, $newPath);
    }

}
?>
