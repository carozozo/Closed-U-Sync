<?php
/**
 * ownCloud - Artdisk Render plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 負責所有 Artdisk Render 操作
 */

class OC_ArtdiskRender_Item {
    # 屬性
    public $property;
    # 檔案操作
    public $handler;
    # 狀態
    public $status;

    /**
     * 根據前端傳送過來的資訊建立 Render
     * @param user id
     */
    function __construct($userId) {
        $property = $this -> property = new OC_ArtdiskRender_Property();
        $status = $this -> status = new OC_ArtdiskRender_Status($property);
        $this -> handler = new OC_ArtdiskRender_Handler($property);
        # 初始串流屬性
        $this -> _init($userId);
    }

    /**
     * 建立算圖資料夾，並建立 symbo link 至帳號 render 底下
     */
    function create() {
        $handler = $this -> handler;
        $handler -> createRender();
        # 產生失敗，則刪除相關檔案
        if ($this -> property -> currentStatus < 0) {
            // $handler -> deleteRender();
        }
    }

    /**
     * 刪除 Artdisk Render 相關檔案
     */
    function delete() {
        $handler = $this -> handler;
        $handler -> deleteRender();
    }

    /**
     * 刪除 Artdisk Render 連結
     */
    function removeLink() {
        $handler = $this -> handler;
        $handler -> removeLink();
    }

    /**
     * 將前端取得的資料放到通用變數中
     * @param user id
     */
    private function _init($userId) {
        $property = $this -> property;
        $property -> userId = $userId;
        $property -> renderPath = OC_ArtdiskRender_Helper::renderPath($userId);
        $property -> renderFullPath = OC_ArtdiskRender_Helper::renderPath($userId, true);
        $property -> linkFullPath = OC_ArtdiskRender_Helper::linkFullPath($userId);
    }

}
?>