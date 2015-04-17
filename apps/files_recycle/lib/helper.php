<?php
/**
 * ownCloud - Files Recycle plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 回收桶通用 function
 */
class OC_Recycle_Helper {
    /**
     * 將日期格式字串轉為純數字
     */
    static function dateTimeToFolderName($dateStr = '') {
        if ($dateStr)
            return preg_replace('/[-, ,:]/', '', $dateStr);
    }

    /**
     * 路徑有相同的檔案，則修改檔名(資料夾則不需更動)
     * @param $fullPath 完整路徑
     * @param $dataStr 時間格式的字串
     * EX：
     * /var/www/html/data/caro/files/1.txt 會轉為 /var/www/html/data/caro/files/1(20140101235959).txt
     * 如果[/var/www/html/data/caro/files/1(20140101235959).txt]存在
     * 則轉為[/var/www/html/data/caro/files/1(20140101235959)_copy.txt]
     */
    static function renamePathIfConflict($fullPath, $extenStr = '') {
        $dir = pathinfo($fullPath, PATHINFO_DIRNAME);
        $fileName = pathinfo($fullPath, PATHINFO_FILENAME);
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        if ($extension)
            $extension = '.' . $extension;
        if ($extenStr) {
            $fullPath = $dir . '/' . $fileName . '(' . self::dateTimeToFolderName($extenStr) . ')' . $extension;
        } else {
            $fullPath = $dir . '/' . $fileName . '_copy' . $extension;
        }
        # 新路徑已經存在檔案，而且不是資料夾，則再一次修改檔名
        $originExists = file_exists($fullPath);
        if ($originExists and !is_dir($fullPath)) {
            return self::renamePathIfConflict($fullPath);
        }
        return $fullPath;
    }

}
?>