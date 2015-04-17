<?php
/**
 * ownCloud - Artdisk Render plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 存放 Artdisk Render 屬性
 */

class OC_ArtdiskRender_Property extends OC_ArtdiskRender_Status {

    # 使用者帳號
    public $userId;
    # 記錄目前狀態
    public $currentStatus;

    # User 算圖資料夾路徑
    public $renderPath;
    # User 算圖資料夾完整路徑
    public $renderFullPath;
    # 連結完整路徑
    public $linkFullPath;

    # 狀態訊息
    public function message() {
        return $this -> _statusArr();
    }

    private function _statusArr() {
        $l = new OC_L10N(OC_ArtdiskRender_Config::appId);
        $renderPath = $this -> renderPath;
        $currentStatus = $this -> currentStatus;
        $statusArr = array(
            self::create_folder_failed => $l -> t('Create Folder Successed'),
            self::create_folder_failed => $l -> t('Create Folder [%s] Failed', array($renderPath)),
            self::create_link_successed => $l -> t('Create Link Successed'),
            self::create_link_failed => $l -> t('Create Link Failed'),
        );
        return $statusArr[$currentStatus];
    }

}
?>