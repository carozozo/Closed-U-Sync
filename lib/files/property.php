<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 負責儲存檔案的相關資訊
 */
class OC_Files_Property {
    # 讓其它 function 可設置其它屬性
    public $data = array();
    # 檔案擁有者
    public $uid;
    # 檔案路徑
    public $path;
    # 所在資料夾路徑
    public $dirname;
    # 檔名(含副檔名)
    public $basename;
    # 檔名(不含副檔名)
    public $filename;
    # 副檔名
    public $extension;
    # 檔案類型
    public $type;
    # 是否為資料夾
    public $isDir;
    # 是否為空的資料夾(在 $isDir 為 true 的狀況下)
    public $isEmptyFolder = true;
    # 檔案大小(bytes)
    public $size;
    # 檔案大小
    public $sizeHuman;
    # mime type
    public $mime;
    # 是否可讀
    public $readable;
    # 是否可寫
    public $writeable;

    # 檔案完整路徑
    public $fullPath;
    # 要顯示在前端的名稱
    public $markName;
    # 要顯示在前端的路徑
    public $markPath;
    # 經過 url 編碼的檔名
    public $encodeName;
    # 檔案url
    public $fileUrl;
    # 檔案要顯示在前端的縮圖路徑
    public $imgSrc;
    # 底下的資料夾構
    public $tree;

    /**
     * 設置其它屬性
     * EX： $property = new OC_Files_Property();
     * $property -> a = '1';
     * 則 $property 裡面會自動產生一個變數 a，其值 = 1
     */
    function __set($name, $value) {
        $this -> data[$name] = $value;
    }

    /**
     * 讀取特定屬性
     * EX：
     * $property = new OC_Files_Property();
     * $property -> a = '1';
     * echo $property -> a; 則會印出 1
     */
    function __get($name) {
        return array_key_exists($name, $this -> data) ? $this -> data[$name] : null;
    }

}
