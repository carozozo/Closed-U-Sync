<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 資源回收桶檔案操作
 * 注意，這邊不使用 OC_Filesystem 的檔案操作，以免產生不必要的 hooks
 */

class OC_Recycle_Handler {
    # 屬性
    private $property;
    # 目標屬性
    private $newProperty;

    /**
     * 帶入串流屬性
     */
    function __construct($property) {
        $this -> property = $property;
    }

    /**
     * 將檔案移到回收桶
     */
    function recycleFile() {
        # 先產生資料夾
        $this -> _createDir();
        $property = $this -> property;
        $sourceFullPath = $property -> sourceFullPath;
        $recycleFullPath = $property -> recycleFullPath;
        $result = OC_Helper::renamer($sourceFullPath, $recycleFullPath);
        if ($result) {
            $property -> status = OC_Recycle_Status::recycle_successed;
        } else {
            $property -> status = OC_Recycle_Status::recycle_failed;
            $this -> deleteDir();
        }
    }

    /**
     * 將檔案從回收桶回復
     */
    function revertFile() {
        $property = $this -> property;
        # 取得指定的檔案路徑
        $assignPath = $property -> assignPath;
        $recycleFullPath = $property -> recycleFullPath;
        $recycleFullPath .= $assignPath;
        $revertFullPath = $property -> revertFullPath;
        $revertFullPath .= $assignPath;
        $result = OC_Helper::copyr($recycleFullPath, $revertFullPath);
        if ($result) {
            $property -> status = OC_Recycle_Status::revert_successed;
        } else {
            $property -> status = OC_Recycle_Status::revert_failed;
        }
    }

    /**
     * 將檔案從回收桶刪除
     */
    function delFile() {
        $property = $this -> property;
        $recycleFullPath = $property -> recycleFullPath;
        $assignPath = $property -> assignPath;
        $recycleFullPath .= $assignPath;
        $result = OC_Helper::rmdirr($recycleFullPath);
        if ($result) {
            $property -> status = OC_Recycle_Status::delete_successed;
        } else {
            $property -> status = OC_Recycle_Status::delete_failed;
        }
    }

    /**
     * 刪除回收資料夾
     * 注意：這邊不能使用 OC_Filesystem::rmdir
     * 否則會造成無限回圈
     */
    function deleteDir() {
        $property = $this -> property;
        $recycleDirFullPath = $property -> recycleDirFullPath;
        if ($recycleDirFullPath) {
            OC_Helper::deleteDirByFullPath($recycleDirFullPath);
        }
    }

    /**
     * 產生回收資料夾
     */
    private function _createDir() {
        try {
            $property = $this -> property;
            $recycleDirFullPath = $property -> recycleDirFullPath;
            OC_Helper::createDirByFullPath($recycleDirFullPath);
            $property -> status = OC_Recycle_Status::create_dir_successed;
        } catch(exception $e) {
            $property -> status = OC_Recycle_Status::create_dir_failed;
        }
    }

}
?>