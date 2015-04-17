<?php
/**
 * ownCloud - Artdisk Render plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 檔案操作時， Artdisk Render 要連動執行的相關動作
 */
class OC_ArtdiskRender_Hooks {

    /**
     * 檔案被rename前，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function renameItem($arguments) {
        $run = &$arguments[OC_Filesystem::signal_param_run];
        try {
            $path = $arguments[OC_Filesystem::signal_param_oldpath];
            if (OC_Filesystem::is_dir($path)) {
                $run = OC_ArtdiskRender::ifCanRename($path);
            }
        } catch(exception $e) {
            # streaming delete發生錯誤，設置不要處理檔案
            $run = false;
        }
        // $run = false;
    }

    /**
     * 檔案要delete前，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function deleteItem($arguments) {
        $run = &$arguments[OC_Filesystem::signal_param_run];
        try {
            $path = $arguments[OC_Filesystem::signal_param_path];
            if (OC_Filesystem::is_dir($path)) {
                OC_ArtdiskRender::removeLink($path);
            }
        } catch(exception $e) {
            # streaming delete發生錯誤，設置不要處理檔案
            $run = false;
        }
    }
}
?>