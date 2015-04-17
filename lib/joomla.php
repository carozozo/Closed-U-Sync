<?php

/**
 * Class for query Joomla data
 *
 */
class OC_Joomla {

    static function createDB() {
        
        require_once OC::$SERVERROOT . '/home/configuration.php';

        $db = new JConfig;
        $dbname = $db -> db;
        $dbuser = $db -> user;
        $dbpass = $db -> password;
        $dbhost = $db -> host;
        $prefix = $db -> dbprefix;

        $link = mysql_connect($dbhost, $dbuser, $dbpass);
        if (!$link) {
            die('Could not connect: ' . mysql_error());
        }

        $sel = mysql_select_db($dbname, $link);
        if (!$sel) {
            die('Can\'t use db: ' . mysql_error());
        }

        // 確定讀出的資料為 utf8 格式
        mysql_query("SET CHARACTER_SET_RESULTS=utf8"); 
        
        return array($db, $link);
    }


    /*
     * query joomla user data
     *
     * para   $uid    string
     *
     * return $result array()
     */
    static function getUserInfo($uid) {

        list($db, $link) = self::createDB();
 
        $sql = "select * from " . $db -> dbprefix . "users where username='" . $uid . "'";
        $dataset = mysql_query($sql);
        if ($result = mysql_fetch_assoc($dataset)) {
            mysql_close($link);
            return $result;
        } else {
            mysql_close($link);
            return false;
        }
    }

    static function getUserIdByEmail($email) {

        list($db, $link) = self::createDB();
 
        $sql = "select username from " . $db -> dbprefix . "users where email='" . $email . "'";
        $dataset = mysql_query($sql);
        if ($result = mysql_fetch_assoc($dataset)) {
            mysql_close($link);
            return $result['username'];
        } else {
            mysql_close($link);
            return false;
        }
    }

}
