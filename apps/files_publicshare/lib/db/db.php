<?php
/**
 * ownCloud - Public Share plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 分享連結DB相關處理
 */

class OC_PublicShare_DB {
    /**
     * 依 token 取得資料
     * @param token
     * @return array
     */
    static function getDbByToken($token) {
        $query = OC_DB::prepare("SELECT * FROM *PREFIX*files_publicshare WHERE token = ? LIMIT 1");
        $result = $query -> execute(array($token)) -> fetchAll();
        if (count($result) > 0) {
            return $result[0];
        }
    }

    /**
     * 透過來源路徑取得使用者分享的資料
     * @param user id
     * @param source path
     * @return array
     */
    static function getDbBySource($uid, $sourcePath) {
        $query = OC_DB::prepare("SELECT * FROM *PREFIX*files_publicshare WHERE uid = ? AND source_path = ?");
        $result = $query -> execute(array(
            $uid,
            $sourcePath,
        )) -> fetchAll();
        if (count($result) > 0) {
            return $result[0];
        }
    }

    /**
     * 取得使用者所有的分享資料
     * @param 使用者id, 依什麼排序, 升/降序
     * @param sourt by what
     * @param sort ASC/DESC
     * @return array
     */
    static function getDbs($uid, $sortBy = 'time', $sort = 'ASC') {
        $sortBy = ($sortBy == 'time') ? ' ORDER BY deadline ' : ' ORDER BY CONVERT(`source_path` using big5) ';
        $sortBy .= strtoupper($sort);
        $query = OC_DB::prepare("SELECT * FROM *PREFIX*files_publicshare WHERE uid = ?" . $sortBy);
        $result = $query -> execute(array($uid)) -> fetchAll();
        return $result;
    }

    /**
     * 找出指定路徑底下所有的分享資料
     * @param user id
     * @param path
     */
    static function getDbsUnderPath($uid, $path) {
        $path .= '%';
        $query = OC_DB::prepare("SELECT * FROM *PREFIX*files_publicshare WHERE uid = ? AND source_path LIKE ?");
        $result = $query -> execute(array(
            $uid,
            $path,
        )) -> fetchAll();
        return $result;
    }

    /**
     * 新增/更新public share 資料
     * @param user id
     * @param source path
     * @param token
     * @param password
     * @param deadline
     * @param insert time utc
     * @return boolean
     */
    static function insertDb($uid, $sourcePath, $token, $pwd, $deadline, $insertTimeUtc = null) {
        $updateTimeUtc = null;
        # 沒有設定 insertTimeUtc ，代表是新一筆資料
        if (!$insertTimeUtc) {
            # 取得現在的時間(UTC格式)，做為create date
            $insertTimeUtc = OC_Helper::formatDateTimeLocalToUTC();
        } else {
            # 代表資料是從 DB 過來的，所以是更新
            $updateTimeUtc = OC_Helper::formatDateTimeLocalToUTC();
        }
        $quStr = "REPLACE INTO *PREFIX*files_publicshare (uid, source_path, token, pwd, insert_time, update_time, deadline) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = OC_DB::prepare($quStr);
        $query -> execute(array(
            $uid,
            $sourcePath,
            $token,
            $pwd,
            $insertTimeUtc,
            $updateTimeUtc,
            $deadline,
        ));
        return true;
    }

    /**
     * 以token為key值,刪除分享資料
     * @param token
     * @return bool
     */
    static function deleteDbByToken($token) {
        $query = OC_DB::prepare("DELETE FROM *PREFIX*files_publicshare WHERE token = ?");
        $query -> execute(array($token));
        return true;
    }

}
?>
