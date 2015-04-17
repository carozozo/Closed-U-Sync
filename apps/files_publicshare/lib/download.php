<?php
/**
 * ownCloud - Public Share plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 分享連結下載相關處理
 */

class OC_PublicShare_Download {

    static function downloadByPwdToken($pwd, $token, $filePath) {
        $property = OC_PublicShare::getByPwdToken($pwd, $token);
        if ($property) {
            $isOutOfDeadline = $property -> isOutOfDeadline;
            $sourcePath = $property -> sourcePath;
            # 防呆，路徑拿掉尾巴的/
            $filePath = rtrim($filePath, '/');
            $sourcePath = rtrim($sourcePath, '/');
            # 如果沒過期，要下載的檔案路徑是在分享的 sourcePath 底下，檔案存在
            if (!$isOutOfDeadline && strpos($filePath, $sourcePath) === 0 && OC_Filesystem::file_exists($filePath)) {
                $dir = dirname($filePath);
                $fileName = basename($filePath);
                $uid = $property -> uid;
                OC_Files::get($dir, $fileName);
                return;
            }
        }
        header("HTTP/1.0 404 Not Found");
        $tmpl = new OC_Template("", "expired", "guest");
        $tmpl -> printPage();
    }

}
?>
