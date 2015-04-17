<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 記錄狀態
 */

class OC_Recycle_Status {
    const create_dir_successed = 'Create folder successed';
    const create_dir_failed = 'Create folder failed';
    const recycle_successed = 'Recycle successed';
    const recycle_failed = 'Recycle failed';
    const revert_successed = 'Revert successed';
    const revert_failed = 'Revert failed';
    const delete_successed = 'Delete successed';
    const delete_failed = 'Delete failed';

    /**
     * 取得指定狀態的文字敘述
     * @param $status 狀態
     * EX：
     * 系統語系設為中文
     * $status=OC_Recycle_Status::revert_successed;
     * echo OC_Recycle_Status::getStatusMsg($status);
     * 會取得「回复成功」
     */
    static function getStatusMsg($status) {
        if ($status) {
            $l = new OC_L10N(OC_Recycle_Config::appId);
            return $l -> t($status);
        }
    }

}
?>