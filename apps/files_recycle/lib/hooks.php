<?php
/**
 * ownCloud - Files Recycle Hooks
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 資源回收桶 Hook 相關處理
 */
class OC_Recycle_Hooks {

    /**
     * 檔案被刪除時回收
     */
    static function recyle($arguments) {
        # 停止 OC_Filessytem 的 delete 動作
        $arguments[OC_Filesystem::signal_param_run] = false;
        $path = $arguments[OC_Filesystem::signal_param_path];
        OC_Recycle::recyle($path);
    }

    /**
     * 檔案上傳後，從暫存檔移到目的地之前，如果目的地原本就有相同檔案，則先回收原本存在的檔案
     */
    static function recBeforeUploaded($arguments) {
        $path = $arguments[OC_Filesystem::signal_param_path];
        if (OC_Filesystem::file_exists($path)) {
            OC_Recycle::recyle($path);
        }
    }

}
?>