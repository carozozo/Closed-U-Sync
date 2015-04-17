<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 資源回收桶相關操作
 * 包含檔案處理，DB資料處理
 */
class OC_Recycle_Item {
    # 屬性
    public $property;
    # 檔案操作
    public $handler;
    /**
     * 根據前端傳送過來的資訊建立回收資料
     * 如果只傳一個參數，且為array，則判定為 DB 資料，否則為判定為 sn
     * 傳兩個參數時：
     * 當第一個參數為數字，則判定為 sn 和 assig path
     * 否則判定為 user id 和 source path
     */
    function __construct() {
        $property = $this -> property = new OC_Recycle_Property();
        $this -> handler = new OC_Recycle_Handler($property);
        $numargs = func_num_args();
        $db = null;
        if ($numargs == 1) {
            $arg1 = func_get_arg(0);
            if (is_array($arg1)) {
                $db = $arg1;
            } else {
                # 把第一個變數當 sn
                $db = OC_Recycle_DB::getDb($arg1);
            }
        }
        if ($numargs == 2) {
            $arg1 = func_get_arg(0);
            if (is_numeric($arg1)) {
                $sn = $arg1;
                $assignPath = func_get_arg(1);
                # 如果有值的話，才做路徑矯正
                if ($assignPath) {
                    $assignPath = OC_Helper::pathForbiddenChar($assignPath);
                }
                $db = OC_Recycle_DB::getDb($sn);
                # 寫入指定要取得的檔案資訊的檔案路徑
                $property -> assignPath = $assignPath;
            } else {
                $uid = $arg1;
                $sourcePath = func_get_arg(1);
            }
        }
        if ($db) {
            $this -> _initByDb($db);
            return;
        }
        $this -> _init($uid, $sourcePath);
    }

    /**
     * 將檔案移到回收桶
     */
    function recycle() {
        $property = $this -> property;
        $handler = $this -> handler;
        $handler -> recycleFile();
        if ($property -> status == OC_Recycle_Status::recycle_successed) {
            $uid = $property -> uid;
            $sourcePath = $property -> sourcePath;
            $recycleTimeUtc = $property -> recycleTimeUtc;
            OC_Recycle_DB::insertDb($uid, $sourcePath, $recycleTimeUtc);
        }
    }

    /**
     * 回復檔案資料
     */
    function revert() {
        $handler = $this -> handler;
        $handler -> revertFile();
    }

    /**
     * 刪除回收桶裡的檔案資料
     */
    function delete() {
        $handler = $this -> handler;
        $handler -> delFile();

        $property = $this -> property;
        $uid = $property -> uid;
        $recycleTimeUtc = $property -> recycleTimeUtc;

        $this -> _delDb();

        # 如果沒有其它相同回收時間的資料，則刪除資料夾
        $dbs = OC_Recycle_DB::getDbsByRecTime($uid, $recycleTimeUtc);
        if (count($dbs) <= 0) {
            $handler -> deleteDir();
        }
    }

    /**
     * 回收資料底下沒檔案的話，才可刪除 DB 資料
     */
    private function _delDb() {
        $property = $this -> property;
        $sn = $property -> sn;
        $delDb = true;
        # 回收資料是資料夾的話，確認下還有沒有檔案
        $recycleFullPath = $property -> recycleFullPath;
        if (is_dir($recycleFullPath)) {
            $files = array_diff(scandir($recycleFullPath), array(
                '.',
                '..'
            ));
            if (count($files) > 0) {
                # 底下有檔案，不可刪除 DB
                $delDb = false;
            }
        }
        if ($delDb) {
            OC_Recycle_DB::deleteDb($sn);
        }
    }

    /**
     * 初始基本回收資料並放到屬性中
     * @param $uid 使用者帳號
     * @param $sourcePath 檔案路徑
     */
    private function _init($uid, $sourcePath) {
        $property = $this -> property;
        $property -> set($uid, $sourcePath);
    }

    /**
     * 將 DB 中取得的資料放到屬性中
     * @param $db DB data
     */
    private function _initByDb($db) {
        $property = $this -> property;
        $uid = $db['uid'];
        $sourcePath = $db['source_path'];
        $recycleTimeUtc = $db['recycle_time'];
        $property -> ifFromDb = true;
        $property -> sn = $db['sn'];
        $property -> set($uid, $sourcePath, $recycleTimeUtc);
    }

}
?>