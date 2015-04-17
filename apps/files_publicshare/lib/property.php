<?php
/**
 * ownCloud - Public Share plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 存放分享連結的屬性
 */

class OC_PublicShare_Property extends OC_Files_Property{
    # 資料是否是由DB取得
    public $ifFromDb = false;

    # 記錄處理狀態
    public $status;

    # 檔案所屬的user
    public $uid;
    # 來源真實路徑(不是完整路徑)
    public $sourcePath;
    # token
    public $token;
    # 密碼
    public $pwd;
    # 寫入 DB 的時間(UTC)
    public $insertTimeUtc;
    # 寫入 DB 的時間(本地)
    public $insertTimeLocal;
    # 更新 DB 的時間(UTC)
    public $updateTimeUtc;
    # 更新 DB 的時間(本地)
    public $updateTimeLocal;
    # 到期日(UTC)
    public $deadlineUtc;
    # 到期日(本地，只顯示日期)
    public $deadlineLocal;

    # 真正的連結網址
    public $url;
    # 短連結網址
    public $shortUrl;
    # 預設分享天數
    public $shareLimitDays;
    # 是否已到期
    public $isOutOfDeadline;

}
?>