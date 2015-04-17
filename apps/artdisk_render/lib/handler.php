<?php
/**
 * ownCloud - Artdisk Render plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * render 檔案操作
 * 因為資料夾是產生在 user 底下，所以透過 filessystem 做檔案操作
 */

class OC_ArtdiskRender_Handler {
    # 屬性
    private $property;

    /**
     * 帶入屬性
     */
    function __construct($property) {
        $this -> property = $property;
    }

    /**
     * 建立 Artdisk Render 相關檔案
     */
    function createRender() {
        if ($this -> _createFolder()) {
            $this -> _createLink();
        }
    }

    /**
     * 刪除 Artdisk Render 相關檔案
     */
    function deleteRender() {
        # 先移除連結，再刪除資料夾
        $this -> _removeLink();
        $this -> _deleteFolder();
    }

    /**
     * 刪除連結
     */
    function removeLink() {
        $this -> _removeLink();
    }

    /**
     * 產生資料夾
     */
    private function _createFolder() {
        $property = $this -> property;
        $renderFullPath = $property -> renderFullPath;
        $renderPath = $property -> renderPath;
        $renderPath = trim($renderPath, '/');
        if (file_exists($renderFullPath) || OC_Filesystem::mkdir($renderPath)) {
            $property -> currentStatus = OC_ArtdiskRender_Status::create_folder_successed;
            return true;
        }
        $property -> currentStatus = OC_ArtdiskRender_Status::create_folder_failed;
    }

    /**
     * 產生連結
     */
    private function _createLink() {
        $property = $this -> property;
        $renderFullPath = $property -> renderFullPath;
        $linkFullPath = $property -> linkFullPath;

        $linkDirFullPath = dirname($linkFullPath);
        OC_Helper::createDirByFullPath($linkDirFullPath);
        # 如果 lnik 原本就存在或產生成功
        if (file_exists($linkFullPath) || symlink($renderFullPath, $linkFullPath)) {
            $property -> currentStatus = OC_ArtdiskRender_Status::create_link_successed;
            return true;
        }
        $property -> currentStatus = OC_ArtdiskRender_Status::create_link_failed;
    }

    /**
     * 刪除資料夾
     */
    private function _deleteFolder() {
        $property = $this -> property;
        $renderPath = $property -> renderPath;
        if ($renderPath && OC_Filesystem::file_exists($renderPath)) {
            OC_Filesystem::rmdir($renderPath);
        }
    }

    /**
     * 刪除連結
     */
    private function _removeLink() {
        $property = $this -> property;
        $linkFullPath = $property -> linkFullPath;
        if ($linkFullPath && file_exists($linkFullPath)) {
            unlink($linkFullPath);
        }
    }

}
?>