<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 *
 * 處理 DB table [oc_recycle]資料
 */
class OC_Recycle_DB {

    /**
     * 取得指定帳號底下所有的回收資料
     * @param $uid 使用者帳號
     * @param $sotBy 依什麼排序(time/path)
     * @param $sort ASC/DESC
     * @return arr
     */
    static function getDbsByUid($uid, $sortBy = 'time', $sort = 'ASC') {
        $sortBy = ($sortBy == 'time') ? ' ORDER BY recycle_time ' : ' ORDER BY CONVERT(`source_path` using big5) ';
        $sortBy .= strtoupper($sort);
        $query = OC_DB::prepare('SELECT * FROM *PREFIX*recycle WHERE uid = ?' . $sortBy);
        return $result = $query -> execute(array($uid, )) -> fetchAll();
    }

    /**
     * 取得指定帳號及回收時間的所有回收資料
     * @param $uid 使用者帳號
     * @param $recycleTimeUtc 回收時間(UTC)
     * @return arr
     */
    static function getDbsByRecTime($uid, $recycleTimeUtc) {
        $query = OC_DB::prepare('SELECT * FROM *PREFIX*recycle WHERE uid = ? AND recycle_time = ?');
        return $result = $query -> execute(array(
            $uid,
            $recycleTimeUtc
        )) -> fetchAll();

    }

    /**
     * 依 sn 取得資料
     * @param $sn 序號
     * @return arr
     */
    static function getDb($sn) {
        $query = OC_DB::prepare('SELECT * FROM *PREFIX*recycle WHERE sn = ?');
        $result = $query -> execute(array($sn, )) -> fetchAll();
        if (count($result) > 0) {
            return $result[0];
        }
    }

    /**
     * 依來源路徑和回收時間取得資料
     * @param $sourcePath 檔案路徑
     * @param $recycleTimeUtc 回收時間
     * @return arr
     */
    static function getDbBySouAndRec($sourcePath, $recycleTimeUtc) {
        $query = OC_DB::prepare('SELECT * FROM *PREFIX*recycle WHERE source_path = ? AND recycle_time = ?');
        $result = $query -> execute(array(
            $sourcePath,
            $recycleTimeUtc,
        )) -> fetchAll();
        if (count($result) > 0) {
            return $result[0];
        }
    }

    /**
     * 寫入資料
     * @param $uid 帳號
     * @param $sourcePath 檔案路徑
     * @param $recycleTimeUtc 回收時間
     * @return bool
     */
    static function insertDb($uid, $sourcePath, $recycleTimeUtc) {
        $query = OC_DB::prepare('INSERT INTO *PREFIX*recycle (uid, source_path, recycle_time) VALUES (?, ?, ?)');
        $result = $query -> execute(array(
            $uid,
            $sourcePath,
            $recycleTimeUtc,
        ));
        return true;
    }

    /**
     * 刪除資料
     * @param $sn 序號
     * @return bool
     */
    static function deleteDb($sn) {
        $query = OC_DB::prepare('DELETE FROM *PREFIX*recycle WHERE sn = ?');
        $result = $query -> execute(array($sn, ));
        return true;
    }

    /**
     * 刪除 user 所有回收資料
     * @param $uid 帳號
     * @return bool
     */
    static function delDbByUid($uid) {
        $query = OC_DB::prepare('DELETE FROM *PREFIX*recycle WHERE uid = ?');
        $result = $query -> execute(array($uid, ));
        return true;
    }

}
